<?php

namespace App\Grillas;

use App\Models\Encuentro;
use App\Models\NivelFormacion;
use App\Models\User;
use App\Support\Estados;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Listado de encuentros: nombre, fecha, lugar, valor, estado, participantes, sede y acciones. */
class EncuentrosGrilla extends Grilla
{
    public function id(): string
    {
        return 'encuentros';
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay encuentros registrados.', 'icono' => 'fa-calendar-check'];
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')->ordena('encuentros.nombre')->filtraTexto('encuentros.nombre')->valor(fn (Encuentro $e) => $e->nombre)->prioridad(1),
            Columna::make('fecha', 'Fecha')->ordena('encuentros.fecha_realizacion')->filtraTexto('encuentros.fecha_realizacion')->valor(fn (Encuentro $e) => $e->fecha_realizacion?->format('Y-m-d'))->prioridad(2),
            Columna::make('nivel', 'Nivel')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy(
                    NivelFormacion::query()->select('niveles_formacion.posicion')->whereColumn('niveles_formacion.id', 'encuentros.nivel_id'),
                    $dir
                ))
                ->filtraLista(
                    NivelFormacion::query()->ordenados()->pluck('nombre', 'id')->mapWithKeys(fn ($n, $id) => [(string) $id => $n])->all(),
                    'encuentros.nivel_id'
                )
                ->valor(fn (Encuentro $e) => $e->nivel?->nombre ?? '')
                ->prioridad(3),
            Columna::make('lugar', 'Lugar')->ordena('encuentros.lugar')->filtraTexto('encuentros.lugar')->valor(fn (Encuentro $e) => $e->lugar)->prioridad(5),
            Columna::make('valor', 'Valor')->ordena('encuentros.valor')->valor(fn (Encuentro $e) => number_format((float) $e->valor, 2, '.', ''))->prioridad(6),
            Columna::make('estado', 'Estado')
                ->ordena('encuentros.estado')
                ->filtraLista(collect(Encuentro::ESTADOS)->mapWithKeys(fn ($e) => [$e => Estados::etiqueta($e)])->all(), 'encuentros.estado')
                ->valor(fn (Encuentro $e) => Estados::etiqueta($e->estado))
                ->prioridad(3),
            Columna::make('participantes', 'Participantes')->ordena('personas_count')->valor(fn (Encuentro $e) => $e->personas_count)->prioridad(4),
            Columna::sede('encuentros'),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['fecha', 'desc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return Encuentro::query()->with(['sede', 'nivel'])->withCount('personas');
    }
}
