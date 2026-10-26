<?php

namespace App\Grillas;

use App\Models\Sede;
use App\Models\User;
use App\Services\SedeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Listado de sedes. Son pocas filas y vienen de un resumen agregado (no de una consulta de modelo),
 * así que ordenar, filtrar y paginar se hace en memoria. Cada fila es un arreglo del resumen.
 */
class SedesGrilla extends Grilla
{
    public function __construct(private SedeService $sedes)
    {
    }

    public function id(): string
    {
        return 'sedes';
    }

    public function vacio(): array
    {
        return ['mensaje' => 'Aún no hay sedes. Crea la primera.', 'icono' => 'fa-church'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario && Gate::forUser($usuario)->allows('ver', Sede::class);
    }

    protected function definirColumnas(): array
    {
        $sede = fn (array $f): Sede => $f['sede'];

        return [
            Columna::make('sede', 'Sede')->ordena()->filtraTexto()->valor(fn (array $f) => $sede($f)->nombre)->prioridad(1),
            Columna::make('ciudad', 'Ciudad')->ordena()->filtraTexto()->valor(fn (array $f) => $sede($f)->ciudad)->prioridad(4),
            Columna::make('pastor', 'Pastor')->ordena()->filtraTexto()->valor(fn (array $f) => $sede($f)->pastor?->nombre_completo)->prioridad(5),
            Columna::make('pastora', 'Pastora')->ordena()->filtraTexto()->valor(fn (array $f) => $sede($f)->pastora?->nombre_completo)->prioridad(6),
            Columna::make('personas', 'Miembros')->ordena()->valor(fn (array $f) => $f['personas'])->prioridad(3),
            Columna::make('lideres', 'Líderes')->ordena()->valor(fn (array $f) => $f['lideres'])->prioridad(7),
            Columna::make('celulas', 'Células')->ordena()->valor(fn (array $f) => $f['celulas'])->prioridad(8),
            Columna::make('estado', 'Estado')
                ->ordena()
                ->filtraLista(['Activa' => 'Activa', 'Inactiva' => 'Inactiva'])
                ->valor(fn (array $f) => $sede($f)->activa ? 'Activa' : 'Inactiva')
                ->prioridad(2),
            Columna::acciones(),
        ];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return $this->sedes->resumen()->values();
    }
}
