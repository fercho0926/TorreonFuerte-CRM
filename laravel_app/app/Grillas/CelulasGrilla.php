<?php

namespace App\Grillas;

use App\Models\Celula;
use App\Models\User;
use App\Services\AlcanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Listado de células: nombre, líder, co-líder, reunión, estado, integrantes, sede y acciones. */
class CelulasGrilla extends Grilla
{
    public function __construct(private AlcanceService $alcance)
    {
    }

    public function id(): string
    {
        return 'celulas';
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay células registradas.', 'icono' => 'fa-people-group'];
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')->ordena('celulas.nombre')->filtraTexto('celulas.nombre')->valor(fn (Celula $c) => $c->nombre)->prioridad(1),
            Columna::make('lider', 'Líder')->ordenaPersona('celulas', 'lider_id')->filtraPersona('lider')->valor(fn (Celula $c) => $c->lider?->nombre_completo)->prioridad(2),
            Columna::make('colider', 'Co-líder')->ordenaPersona('celulas', 'co_lider_id')->filtraPersona('coLider')->valor(fn (Celula $c) => $c->coLider?->nombre_completo)->prioridad(6),
            Columna::make('reunion', 'Reunión')->ordena('celulas.dia_reunion')->valor(fn (Celula $c) => trim(($c->dia_reunion ?? '').' '.substr((string) $c->hora_reunion, 0, 5)))->prioridad(5),
            Columna::make('estado', 'Estado')
                ->ordena('celulas.estado')
                ->filtraLista(['activa' => 'Activa', 'inactiva' => 'Inactiva'], 'celulas.estado')
                ->valor(fn (Celula $c) => ucfirst((string) $c->estado))
                ->prioridad(3),
            Columna::make('miembros', 'Miembros')->ordena('miembros_count')->valor(fn (Celula $c) => $c->miembros_count)->prioridad(4),
            Columna::sede('celulas'),
            Columna::acciones(),
        ];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return $this->alcance->celulasVisibles($usuario)->with(['lider', 'coLider', 'sede'])->withCount('miembros');
    }
}
