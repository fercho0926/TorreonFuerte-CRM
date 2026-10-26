<?php

namespace Tests\Feature\Grillas;

use Tests\TestCase;

/**
 * Regla permanente (spec 007, RF-014): todo listado principal usa la grilla estándar `<x-ui.grilla>`.
 * Si necesitas una tabla simple en un listado, no la agregues aquí como excepción: conviértela en una grilla.
 * Ver README → «Grillas».
 */
class GuardaGrillaEstandarTest extends TestCase
{
    /** Listados que, por decisión explícita, no son una grilla estándar. Debe permanecer vacía. */
    private const EXCEPCIONES = [];

    public function test_ningun_listado_principal_usa_una_tabla_simple(): void
    {
        $infractores = [];

        foreach (glob(resource_path('views/*/index.blade.php')) as $archivo) {
            $carpeta = basename(dirname($archivo));
            $contenido = file_get_contents($archivo);

            if (in_array($carpeta, self::EXCEPCIONES, true)) {
                continue;
            }

            if (str_contains($contenido, '<table') && !str_contains($contenido, '<x-ui.grilla')) {
                $infractores[] = "$carpeta/index.blade.php";
            }
        }

        $this->assertSame([], $infractores, 'Listados con tabla simple en lugar de <x-ui.grilla> (ver README → Grillas): '.implode(', ', $infractores));
    }

    /**
     * Grillas sin exportación por decisión del responsable (excepción declarada al principio III). Spec 015: el
     * historial («Control de cambios») no se exporta por ningún medio (clarificación 1, RF-020).
     */
    public const SIN_EXPORTACION = ['control-cambios'];

    public function test_los_listados_con_grilla_declaran_sus_rutas_de_datos_y_exportacion(): void
    {
        foreach (glob(resource_path('views/*/index.blade.php')) as $archivo) {
            if (!str_contains(file_get_contents($archivo), '<x-ui.grilla')) {
                continue;
            }

            $modulo = basename(dirname($archivo));
            $this->assertTrue(\Route::has("$modulo.datos"), "falta la ruta $modulo.datos");

            if (in_array($modulo, self::SIN_EXPORTACION, true)) {
                $this->assertFalse(\Route::has("$modulo.exportar"), "$modulo no debe tener ruta de exportación");
            } else {
                $this->assertTrue(\Route::has("$modulo.exportar"), "falta la ruta $modulo.exportar");
            }
        }
    }
}
