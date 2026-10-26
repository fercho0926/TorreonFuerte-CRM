<?php

namespace App\Grillas;

use App\Models\SolicitudClave;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/** Solicitudes de clave pedidas desde el inicio de sesión. Solo un administrador puede exportarlas. */
class SolicitudesClaveGrilla extends Grilla
{
    public const FILTROS = ['pendientes' => 'Pendientes', 'vencidas' => 'Vencidas', 'atendidas' => 'Atendidas', 'descartadas' => 'Descartadas', 'todas' => 'Todas'];

    public function id(): string
    {
        return 'solicitudes-clave';
    }

    public function vistaCelda(): string
    {
        return 'claves._celda-solicitud';
    }

    public function extras(): array
    {
        return ['estado'];
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay solicitudes en este estado.', 'icono' => 'fa-check-circle'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario && Gate::forUser($usuario)->allows('verSolicitudesClave', User::class);
    }

    public function puedeExportar(?User $usuario): bool
    {
        return $this->puedeVer($usuario) && $usuario->esAdmin();
    }

    /** Estado elegido en el filtro: «pendientes» si no es uno válido. */
    public static function filtroDe(mixed $valor): string
    {
        return is_string($valor) && array_key_exists($valor, self::FILTROS) ? $valor : 'pendientes';
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('cuenta', 'Cuenta')
                // El nombre de la cuenta es el de su miembro (spec 016).
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy(
                    User::withoutGlobalScopes()->join('personas', 'personas.id', '=', 'users.persona_id')
                        ->select('personas.nombres')->whereColumn('users.id', 'solicitudes_clave.user_id'),
                    $dir
                ))
                ->valor(fn (SolicitudClave $s) => $s->user->name.' <'.$s->user->email.'>')
                ->prioridad(1),
            Columna::make('rol', 'Rol')
                ->valor(fn (SolicitudClave $s) => User::etiquetaDeRol($s->user->role))
                ->prioridad(4),
            Columna::sede('solicitudes_clave'),
            Columna::make('pedida', 'Pedida')
                ->ordena('solicitudes_clave.ultima_solicitud_en')
                ->valor(fn (SolicitudClave $s) => $s->ultima_solicitud_en?->format('Y-m-d H:i'))
                ->prioridad(3),
            Columna::make('estado', 'Estado')->valor(fn (SolicitudClave $s) => $s->estadoVigente())->prioridad(2),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['pedida', 'desc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        $filtro = self::filtroDe($estado['estado'] ?? null);
        $limite = now()->subHours((int) config('claves.solicitud_vence_horas'));

        return SolicitudClave::with(['user', 'sede', 'atendidaPor'])
            ->when($filtro === 'pendientes', fn ($q) => $q->pendientes()->where('ultima_solicitud_en', '>=', $limite))
            ->when($filtro === 'vencidas', fn ($q) => $q->pendientes()->where('ultima_solicitud_en', '<', $limite))
            ->when($filtro === 'atendidas', fn ($q) => $q->where('estado', SolicitudClave::ATENDIDA))
            ->when($filtro === 'descartadas', fn ($q) => $q->where('estado', SolicitudClave::DESCARTADA));
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        $like = '%'.str_replace(['%', '_'], '', $q).'%';

        return $consulta->whereHas('user', fn ($u) => $u->where('users.email', 'like', $like)->orWhereHas('persona', fn ($p) => $p->buscar($q)));
    }
}
