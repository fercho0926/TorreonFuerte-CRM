<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Utilidades de los componentes de formulario (<x-form.*>).
 */
class Formulario
{
    /** Identificador HTML estable de un campo: pastor[nombres] → fm-pastor-nombres. */
    public static function id(string $name): string
    {
        return 'fm-'.trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    }

    /** Clave de notación con puntos para errores y old(): pastor[nombres] → pastor.nombres, personas[] → personas. */
    public static function clave(string $name): string
    {
        return Str::of($name)->replace('[]', '')->replace(['[', ']'], ['.', ''])->trim('.')->toString();
    }

    /**
     * Normaliza una colección de opciones a arreglos con id, texto y apoyo.
     *
     * @param  iterable<mixed>  $items
     * @return array<int, array{id: mixed, texto: string, apoyo: ?string}>
     */
    public static function opciones(iterable $items, ?callable $texto = null, ?callable $apoyo = null): array
    {
        $salida = [];
        foreach ($items as $clave => $item) {
            if (is_array($item) && array_key_exists('id', $item)) {
                $salida[] = ['id' => $item['id'], 'texto' => (string) ($item['texto'] ?? $item['id']), 'apoyo' => $item['apoyo'] ?? null];
            } elseif (is_object($item)) {
                $salida[] = ['id' => $item->id, 'texto' => (string) ($texto ? $texto($item) : ($item->nombre_completo ?? $item->nombre ?? $item->id)), 'apoyo' => $apoyo ? $apoyo($item) : null];
            } else {
                // Lista asociativa valor => etiqueta (por ejemplo, estados).
                $salida[] = ['id' => $clave, 'texto' => (string) $item, 'apoyo' => null];
            }
        }

        return $salida;
    }
}
