<?php

namespace App\Grillas;

use App\Models\Rol;
use App\Models\User;
use App\Support\Niveles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/** Listado de roles (pestaña «Roles» de Usuarios). Solo lo ve y lo exporta el administrador. */
class RolesGrilla extends Grilla
{
    public function id(): string
    {
        return 'roles';
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'Aún no hay roles.', 'icono' => 'fa-user-shield'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario && Gate::forUser($usuario)->allows('administrador');
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Rol')->ordena('roles.nombre')->filtraTexto('roles.nombre')->valor(fn (Rol $r) => $r->nombre)->prioridad(1),
            Columna::make('descripcion', 'Descripción')->ordena('roles.descripcion')->filtraTexto('roles.descripcion')->valor(fn (Rol $r) => $r->descripcion)->prioridad(4),
            Columna::make('alcance', 'Alcance de datos')
                ->ordena('roles.alcance')
                ->filtraLista(Niveles::ALCANCES, 'roles.alcance')
                ->valor(fn (Rol $r) => $r->etiquetaAlcance())
                ->prioridad(2),
            Columna::make('usuarios', 'Usuarios')->ordena('usuarios_count')->valor(fn (Rol $r) => $r->usuarios_count)->prioridad(3),
            Columna::make('tipo', 'Tipo')
                ->ordena('roles.es_sistema')
                ->valor(fn (Rol $r) => $r->es_sistema ? 'Sistema' : 'Personalizado')
                ->prioridad(5),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return Rol::query()->withCount('usuarios');
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        $like = '%'.str_replace(['%', '_'], '', $q).'%';

        return $consulta->where(fn ($w) => $w->where('roles.nombre', 'like', $like)->orWhere('roles.descripcion', 'like', $like));
    }
}
