<?php

namespace App\Grillas;

use App\Models\Evento;
use App\Models\User;
use App\Support\Estados;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Listado de eventos: nombre, tipo, fecha, lugar, costo, estado, inscritos, sede y acciones. */
class EventosGrilla extends Grilla
{
    public function id(): string
    {
        return 'eventos';
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay eventos registrados.', 'icono' => 'fa-calendar-days'];
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')->ordena('eventos.nombre')->filtraTexto('eventos.nombre')->valor(fn (Evento $e) => $e->nombre)->prioridad(1),
            Columna::make('tipo', 'Tipo')
                ->ordena('eventos.tipo')
                ->filtraLista(array_combine(Evento::TIPOS, Evento::TIPOS), 'eventos.tipo')
                ->valor(fn (Evento $e) => $e->tipo)
                ->prioridad(5),
            Columna::make('fecha', 'Fecha')->ordena('eventos.fecha')->filtraTexto('eventos.fecha')->valor(fn (Evento $e) => $e->fecha?->format('Y-m-d'))->prioridad(2),
            Columna::make('lugar', 'Lugar')->ordena('eventos.lugar')->filtraTexto('eventos.lugar')->valor(fn (Evento $e) => $e->lugar)->prioridad(6),
            Columna::make('costo', 'Costo')->ordena('eventos.costo')->valor(fn (Evento $e) => number_format((float) $e->costo, 2, '.', ''))->prioridad(7),
            Columna::make('estado', 'Estado')
                ->ordena('eventos.estado')
                ->filtraLista(collect(Evento::ESTADOS)->mapWithKeys(fn ($e) => [$e => Estados::etiqueta($e)])->all(), 'eventos.estado')
                ->valor(fn (Evento $e) => Estados::etiqueta($e->estado))
                ->prioridad(3),
            Columna::make('inscritos', 'Inscritos')->ordena('inscripciones_count')->valor(fn (Evento $e) => $e->inscripciones_count)->prioridad(4),
            Columna::sede('eventos'),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['fecha', 'desc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return Evento::query()->with('sede')->withCount('inscripciones');
    }
}
