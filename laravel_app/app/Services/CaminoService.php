<?php

namespace App\Services;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use Illuminate\Support\Collection;

/**
 * Camino de niveles de un miembro (spec 014): estaciones en orden con su estado, nivel actual y siguiente, y la
 * geometría del dibujo. El nivel actual es el de mayor posición completado; el siguiente, el primer nivel activo sin completar.
 */
class CaminoService
{
    public const COMPLETADO = 'completado';

    public const SIGUIENTE = 'siguiente';

    public const PENDIENTE = 'pendiente';

    /** Posición horizontal (0 a 100) de las estaciones: izquierda, centro, derecha, centro. */
    private const PATRON_X = [20, 50, 80, 50];

    // Separación vertical compacta (la mitad de la original) para que el camino no domine la ficha.
    private const ESPACIO_Y = 95;

    private const MARGEN_Y = 40;

    // Abajo hace falta más aire: el nombre y el estado de la última estación van debajo de su círculo.
    private const MARGEN_ABAJO = 75;

    /**
     * @return array{estaciones: array<int, array<string, mixed>>, actual: ?NivelFormacion, siguiente: ?NivelFormacion,
     *               completados: int, total: int, terminado: bool, sin_iniciar: bool}
     */
    public function camino(Persona $miembro, ?Collection $niveles = null, bool $detalle = true): array
    {
        $niveles ??= NivelFormacion::query()->activos()->ordenados()->get();
        $avances = $this->avancesDe($miembro)->keyBy('nivel_id');

        $maxCompletada = $niveles->filter(fn (NivelFormacion $n) => $avances->has($n->id))->max('posicion');
        $siguiente = null;
        $actual = null;
        $estaciones = [];

        foreach ($niveles->values() as $indice => $nivel) {
            $avance = $avances->get($nivel->id);
            $estado = $avance ? self::COMPLETADO : self::PENDIENTE;

            if (!$avance && $siguiente === null) {
                $siguiente = $nivel;
                $estado = self::SIGUIENTE;
            }
            if ($avance && $maxCompletada === $nivel->posicion) {
                $actual = $nivel;
            }

            $estaciones[] = [
                'nivel' => $nivel,
                'numero' => $indice + 1,
                'estado' => $estado,
                'saltado' => !$avance && $maxCompletada !== null && $nivel->posicion < $maxCompletada,
                'fecha' => $avance?->fecha,
                'origen' => $avance?->origen,
                'encuentro' => $detalle ? $avance?->encuentro : null,
                'registrado_por' => $detalle ? $avance?->registradoPor : null,
                'avance' => $avance,
            ];
        }

        $completados = collect($estaciones)->where('estado', self::COMPLETADO)->count();

        return [
            'estaciones' => $estaciones,
            'actual' => $actual,
            'siguiente' => $siguiente,
            'completados' => $completados,
            'total' => count($estaciones),
            'terminado' => count($estaciones) > 0 && $completados === count($estaciones),
            'sin_iniciar' => $completados === 0,
        ];
    }

    /**
     * Geometría del dibujo: puntos (x de 0 a 100, y en unidades del lienzo) y trazado SVG del sendero.
     *
     * @return array{alto: int, puntos: array<int, array{x: int, y: int}>, sendero: string}
     */
    public function geometria(int $estaciones): array
    {
        if ($estaciones <= 0) {
            return ['alto' => 0, 'puntos' => [], 'sendero' => ''];
        }

        $puntos = [];
        for ($i = 0; $i < $estaciones; $i++) {
            $puntos[] = ['x' => self::PATRON_X[$i % count(self::PATRON_X)], 'y' => self::MARGEN_Y + $i * self::ESPACIO_Y];
        }

        $sendero = 'M '.$puntos[0]['x'].' '.$puntos[0]['y'];
        for ($i = 1; $i < $estaciones; $i++) {
            $a = $puntos[$i - 1];
            $b = $puntos[$i];
            $medio = (int) (($a['y'] + $b['y']) / 2);
            $sendero .= " C {$a['x']} {$medio}, {$b['x']} {$medio}, {$b['x']} {$b['y']}";
        }

        return ['alto' => self::MARGEN_Y + self::MARGEN_ABAJO + ($estaciones - 1) * self::ESPACIO_Y, 'puntos' => $puntos, 'sendero' => $sendero];
    }

    /** Avances del miembro con sus relaciones; usa los precargados si los hay (listas) para no consultar por fila. */
    private function avancesDe(Persona $miembro): Collection
    {
        if ($miembro->relationLoaded('avances')) {
            return $miembro->avances;
        }

        return AvanceNivel::query()->withoutGlobalScopes()->where('persona_id', $miembro->id)->with(['encuentro', 'registradoPor'])->get();
    }
}
