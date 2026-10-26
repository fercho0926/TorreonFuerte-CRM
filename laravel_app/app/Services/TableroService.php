<?php

namespace App\Services;

use App\Models\EventoPago;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Datos de las gráficas del tablero (estilo «Dashboard v2» de AdminLTE): serie de los últimos
 * 12 meses y reparto de miembros por estado, siempre dentro del alcance del usuario.
 */
class TableroService
{
    public function __construct(private AlcanceService $alcance) {}

    /**
     * Miembros nuevos (y, si se pide, recaudo de eventos) por mes en los últimos 12 meses.
     * Se agrupa en PHP para no depender de funciones de fecha del motor (SQLite en desarrollo, MySQL en producción).
     *
     * @return array{meses: list<string>, miembros: list<int>, recaudo: list<float>|null, desde: Carbon, hasta: Carbon}
     */
    public function mensual(?User $usuario, bool $conRecaudo): array
    {
        $desde = now()->startOfMonth()->subMonths(11);
        $claves = collect(range(0, 11))->map(fn (int $i) => $desde->copy()->addMonths($i));

        $altas = $this->alcance->personasVisibles($usuario)
            ->where('personas.created_at', '>=', $desde)
            ->reorder()
            ->pluck('personas.created_at')
            ->countBy(fn ($fecha) => Carbon::parse($fecha)->format('Y-m'));

        $recaudo = $conRecaudo
            ? EventoPago::query()->whereDate('fecha_pago', '>=', $desde)->get(['fecha_pago', 'monto'])
                ->groupBy(fn (EventoPago $pago) => $pago->fecha_pago->format('Y-m'))
                ->map(fn ($pagos) => (float) $pagos->sum('monto'))
            : null;

        return [
            'meses' => $claves->map(fn (Carbon $mes) => ucfirst($mes->locale('es')->isoFormat('MMM YY')))->all(),
            'miembros' => $claves->map(fn (Carbon $mes) => (int) ($altas[$mes->format('Y-m')] ?? 0))->all(),
            'recaudo' => $recaudo ? $claves->map(fn (Carbon $mes) => round($recaudo[$mes->format('Y-m')] ?? 0, 2))->all() : null,
            'desde' => $desde,
            'hasta' => now(),
        ];
    }

    /**
     * Cantidad de miembros visibles por estado, en el orden de Persona::ESTADOS.
     *
     * @return array<string, int> etiqueta => total
     */
    public function porEstado(?User $usuario): array
    {
        $totales = $this->alcance->personasVisibles($usuario)
            ->reorder()
            ->selectRaw('personas.estado as estado, count(*) as total')
            ->groupBy('personas.estado')
            ->pluck('total', 'estado');

        return collect(Persona::ESTADOS)
            ->mapWithKeys(fn (string $etiqueta, string $clave) => [$etiqueta => (int) ($totales[$clave] ?? 0)])
            ->all();
    }
}
