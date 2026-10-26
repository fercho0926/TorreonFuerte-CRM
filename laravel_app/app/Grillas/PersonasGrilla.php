<?php

namespace App\Grillas;

use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;
use App\Services\CaminoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Listado de miembros: nombre, estado, celular, líder, sede (solo en «Todas las sedes») y acciones. */
class PersonasGrilla extends Grilla
{
    private ?Collection $niveles = null;

    public function __construct(private AlcanceService $alcance)
    {
    }

    public function id(): string
    {
        return 'personas';
    }

    public function archivo(): string
    {
        return 'miembros';
    }

    public function extras(): array
    {
        return ['inactivos', 'lider_id'];
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function puedeVer(?User $usuario): bool
    {
        return (bool) $usuario;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No se encontraron miembros.', 'icono' => 'fa-address-card'];
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy('personas.apellidos', $dir)->orderBy('personas.nombres', $dir))
                ->filtraTexto(fn (Builder $q, string $valor) => $q->buscar($valor))
                ->valor(fn (Persona $p) => $p->nombre_completo)
                ->prioridad(1),
            Columna::make('estado', 'Estado')
                ->ordena('personas.estado')
                ->filtraLista(Persona::ESTADOS, 'personas.estado')
                ->valor(fn (Persona $p) => $p->estado_etiqueta)
                ->prioridad(2),
            Columna::make('celular', 'Celular')
                ->ordena('personas.celular')
                ->filtraTexto(fn (Builder $q, string $valor) => $q->where('personas.celular', 'like', '%'.preg_replace('/\D+/', '', $valor).'%'))
                ->valor(fn (Persona $p) => $p->celular)
                ->prioridad(3),
            Columna::make('correo', 'Correo')->valor(fn (Persona $p) => $p->email)->soloExportar(),
            Columna::make('lider', 'Líder')
                ->ordenaPersona('personas', 'lider_id')
                ->filtraPersona('lider')
                ->valor(fn (Persona $p) => $p->lider?->nombre_completo)
                ->prioridad(4),
            Columna::make('nivel', 'Nivel')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy(
                    DB::table('avances_nivel as a')->join('niveles_formacion as n', 'n.id', '=', 'a.nivel_id')
                        ->where('n.activo', true)->whereColumn('a.persona_id', 'personas.id')->selectRaw('coalesce(max(n.posicion), 0)'),
                    $dir
                ))
                ->filtraLista(
                    $this->niveles()->mapWithKeys(fn (NivelFormacion $n) => [(string) $n->id => $n->nombre])->all() + ['sin' => 'Sin iniciar'],
                    fn (Builder $q, string $valor) => $valor === 'sin' ? $q->sinNivel() : $q->enNivel((int) $valor)
                )
                ->valor(fn (Persona $p) => app(CaminoService::class)->camino($p, $this->niveles(), false)['actual']?->nombre ?? 'Sin iniciar')
                ->visibleSi(fn (?User $u) => $u && $u->nivelEn('niveles') >= 1)
                ->prioridad(3),
            Columna::sede('personas'),
            Columna::acciones(),
        ];
    }

    /** Niveles activos del plan, en orden (se consultan una vez por petición). */
    public function niveles(): Collection
    {
        return $this->niveles ??= NivelFormacion::query()->activos()->ordenados()->get();
    }

    public function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        $incluirInactivos = !empty($estado['inactivos']) || ($estado['estado'] ?? '') === 'inactivo';

        return $this->alcance->personasVisibles($usuario)
            ->with(['lider', 'sede'])
            ->when($usuario?->nivelEn('niveles') >= 1, fn ($q) => $q->with('avances'))
            ->when(!$incluirInactivos, fn ($q) => $q->activas())
            ->when(filled($estado['lider_id'] ?? null), fn ($q) => $q->where('personas.lider_id', $estado['lider_id']));
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        return $consulta->buscar($q);
    }
}
