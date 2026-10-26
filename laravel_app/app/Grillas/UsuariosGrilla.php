<?php

namespace App\Grillas;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/** Listado de usuarios. Contiene datos de acceso: solo un administrador puede exportarlo. */
class UsuariosGrilla extends Grilla
{
    public function id(): string
    {
        return 'users';
    }

    public function extras(): array
    {
        return ['sede_id'];
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay usuarios registrados.', 'icono' => 'fa-users'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario && Gate::forUser($usuario)->allows('verUsuarios', User::class);
    }

    public function puedeExportar(?User $usuario): bool
    {
        return $this->puedeVer($usuario) && $usuario->esAdmin();
    }

    protected function definirColumnas(): array
    {
        return [
            // El nombre es el del miembro dueño de la cuenta (spec 016).
            Columna::make('nombre', 'Nombre')
                ->ordena(fn (Builder $q, string $dir) => self::ordenarPorMiembro($q, $dir))
                ->filtraPersona('persona')
                ->valor(fn (User $u) => $u->name)
                ->prioridad(1),
            Columna::make('email', 'Email')->ordena('users.email')->filtraTexto('users.email')->valor(fn (User $u) => $u->email)->prioridad(3),
            Columna::make('rol', 'Rol')
                ->ordena('users.role')
                ->filtraLista(\App\Models\Rol::orderBy('nombre')->pluck('nombre', 'clave')->all(), 'users.role')
                ->valor(fn (User $u) => User::etiquetaDeRol($u->role))
                ->prioridad(2),
            Columna::sede('users')
                ->valor(fn (User $u) => $u->getRelationValue('sede')?->nombre ?? 'Todas (administrador)')
                ->visibleSi(fn (?User $a) => $a && $a->esAdmin()),
            // Cuentas que la unificación dejó con un vínculo dudoso (spec 016): las revisa el administrador.
            Columna::make('vinculo', 'Vínculo')
                ->filtraLista(['revisar' => 'Por revisar'], fn (Builder $q, string $valor) => $valor === 'revisar' ? $q->whereNotNull('users.revision_vinculo') : $q)
                ->valor(fn (User $u) => $u->revision_vinculo ? 'Por revisar' : '')
                ->visibleSi(fn (?User $a) => $a && $a->esAdmin())
                ->prioridad(5),
            Columna::make('clave', 'Clave')->valor(fn (User $u) => '')->sinExportar()->prioridad(6),
            Columna::make('registrado', 'Registrado')->ordena('users.created_at')->valor(fn (User $u) => $u->created_at?->format('Y-m-d H:i'))->prioridad(7),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['registrado', 'desc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return User::query()
            ->with(['persona', 'sede', 'ultimoRegistroClave'])
            ->when($usuario?->esAdmin() && filled($estado['sede_id'] ?? null), fn ($q) => $q->where('users.sede_id', $estado['sede_id']));
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        $like = '%'.str_replace(['%', '_'], '', $q).'%';

        return $consulta->where(fn ($w) => $w->where('users.email', 'like', $like)->orWhereHas('persona', fn ($p) => $p->buscar($q)));
    }

    /**
     * Ordena cuentas por el nombre de su miembro (nombres y luego apellidos), con subconsultas que
     * funcionan igual en SQLite y MySQL (spec 016).
     */
    public static function ordenarPorMiembro(Builder|\Illuminate\Database\Eloquent\Relations\Relation $consulta, string $dir = 'asc', string $llave = 'users.persona_id'): Builder|\Illuminate\Database\Eloquent\Relations\Relation
    {
        foreach (['nombres', 'apellidos'] as $campo) {
            $consulta->orderBy(
                \App\Models\Persona::query()->withoutGlobalScopes()->from('personas as p_nombre')->select('p_nombre.'.$campo)->whereColumn('p_nombre.id', $llave),
                $dir
            );
        }

        return $consulta;
    }
}
