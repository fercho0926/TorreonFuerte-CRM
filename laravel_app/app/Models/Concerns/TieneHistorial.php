<?php

namespace App\Models\Concerns;

use App\Models\CambioHistorial;
use App\Support\AccionesHistorial;

/**
 * Registro con pestaña «Histórico» (spec 015). Define cómo se nombran sus campos en el historial; los modelos que
 * además quieren que sus altas, ediciones y bajas se registren solas usan `RegistraHistorial`.
 *
 * Cada modelo puede redefinir:
 * - `camposHistorial()`: campo → etiqueta, o [etiqueta, tipo] con tipo `booleano`, `fecha`, `fecha_hora`, `dinero`,
 *   'persona' (nombre del miembro), `usuario`, `sede`, `nivel`, `rol`, un arreglo valor → texto o un Closure; `'sensible' => módulo` oculta
 *   el valor a quien no tiene lectura en ese módulo.
 * - `camposIgnorados()` y `camposSecretos()` (además de los secretos globales de `Historial`).
 * - `nombreHistorial()`: nombre legible del registro.
 */
trait TieneHistorial
{
    /** Entradas cuyo registro afectado es este (sin la cadena de traslados; ver `LecturaHistorial`). */
    public function historial()
    {
        return CambioHistorial::query()->de((string) AccionesHistorial::tipoDe($this), (int) $this->getKey())->recientes();
    }

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [];
    }

    /** @return array<int, string> */
    public function camposIgnorados(): array
    {
        return [];
    }

    /** @return array<int, string> */
    public function camposSecretos(): array
    {
        return [];
    }
}
