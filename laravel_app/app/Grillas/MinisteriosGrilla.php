<?php

namespace App\Grillas;

use App\Models\Ministerio;
use App\Models\User;
use App\Support\Estados;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Listado de ministerios: nombre, encargado, estado, integrantes, sede y acciones. */
class MinisteriosGrilla extends Grilla
{
    public function id(): string
    {
        return 'ministerios';
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay ministerios registrados.', 'icono' => 'fa-hands-praying'];
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')->ordena('ministerios.nombre')->filtraTexto('ministerios.nombre')->valor(fn (Ministerio $m) => $m->nombre)->prioridad(1),
            Columna::make('encargado', 'Encargado')->ordenaPersona('ministerios', 'encargado_id')->filtraPersona('encargado')->valor(fn (Ministerio $m) => $m->encargado?->nombre_completo)->prioridad(2),
            Columna::make('estado', 'Estado')
                ->ordena('ministerios.estado')
                ->filtraLista(collect(Ministerio::ESTADOS)->mapWithKeys(fn ($e) => [$e => Estados::etiqueta($e)])->all(), 'ministerios.estado')
                ->valor(fn (Ministerio $m) => Estados::etiqueta($m->estado))
                ->prioridad(3),
            Columna::make('integrantes', 'Integrantes')->ordena('integrantes_count')->valor(fn (Ministerio $m) => $m->integrantes_count)->prioridad(4),
            Columna::sede('ministerios'),
            Columna::acciones(),
        ];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return Ministerio::query()->with(['encargado', 'sede'])->withCount('integrantes');
    }
}
