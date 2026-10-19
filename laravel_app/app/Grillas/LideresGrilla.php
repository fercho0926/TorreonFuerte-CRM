<?php

namespace App\Grillas;

use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Listado de líderes: nombre, personas a cargo, células lideradas, celular, sede y acciones. */
class LideresGrilla extends Grilla
{
    public function __construct(private AlcanceService $alcance)
    {
    }

    public function id(): string
    {
        return 'lideres';
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay líderes registrados.', 'icono' => 'fa-user-tie'];
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy('personas.apellidos', $dir)->orderBy('personas.nombres', $dir))
                ->filtraTexto(fn (Builder $q, string $valor) => $q->buscar($valor))
                ->valor(fn (Persona $p) => $p->nombre_completo)
                ->prioridad(1),
            Columna::make('personas', 'Personas')->ordena('miembros_count')->valor(fn (Persona $p) => $p->miembros_count)->prioridad(3),
            Columna::make('celulas', 'Células')->ordena('celulas_lideradas_count')->valor(fn (Persona $p) => $p->celulas_lideradas_count)->prioridad(4),
            Columna::make('celular', 'Celular')
                ->ordena('personas.celular')
                ->filtraTexto(fn (Builder $q, string $valor) => $q->where('personas.celular', 'like', '%'.preg_replace('/\D+/', '', $valor).'%'))
                ->valor(fn (Persona $p) => $p->celular)
                ->prioridad(2),
            Columna::sede('personas'),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return $this->alcance->lideresVisibles($usuario)->with('sede')->withCount(['miembros', 'celulasLideradas']);
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        return $consulta->buscar($q);
    }
}
