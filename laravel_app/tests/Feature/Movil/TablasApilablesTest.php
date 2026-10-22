<?php

namespace Tests\Feature\Movil;

use Tests\TestCase;

/**
 * Regla de la spec 008 (RF-011, RF-018): toda tabla de datos se ve como tarjetas en teléfono.
 * Las tablas de listados las genera `<x-ui.grilla>`; las demás llevan `tabla-apilable` y una etiqueta por celda.
 */
class TablasApilablesTest extends TestCase
{
    /** @return array<int, string> */
    private function vistas(): array
    {
        $dir = new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS);
        $archivos = [];
        foreach (new \RecursiveIteratorIterator($dir) as $archivo) {
            if (str_ends_with($archivo->getFilename(), '.blade.php')) {
                $archivos[] = $archivo->getPathname();
            }
        }

        return $archivos;
    }

    public function test_toda_tabla_fuera_de_la_grilla_lleva_tabla_apilable(): void
    {
        $infractores = [];

        foreach ($this->vistas() as $vista) {
            if (str_ends_with(str_replace('\\', '/', $vista), 'components/ui/grilla.blade.php')) {
                continue;
            }
            preg_match_all('/<table\b[^>]*>/i', file_get_contents($vista), $tablas);
            foreach ($tablas[0] as $etiqueta) {
                if (!str_contains($etiqueta, 'tabla-apilable')) {
                    $infractores[] = basename(dirname($vista)).'/'.basename($vista).' → '.$etiqueta;
                }
            }
        }

        $this->assertSame([], $infractores, "Tablas sin la clase tabla-apilable (ver README → Diseño para celular):\n".implode("\n", $infractores));
    }

    public function test_cada_celda_de_una_tabla_apilable_tiene_su_etiqueta(): void
    {
        $sinEtiqueta = [];

        foreach ($this->vistas() as $vista) {
            $contenido = file_get_contents($vista);
            preg_match_all('/<table\b[^>]*tabla-apilable[^>]*>.*?<\/table>/is', $contenido, $tablas);
            foreach ($tablas[0] as $tabla) {
                preg_match_all('/<td\b[^>]*>/i', $tabla, $celdas);
                foreach ($celdas[0] as $celda) {
                    // Las filas de «sin resultados» ocupan todo el ancho y no llevan etiqueta.
                    if (!str_contains($celda, 'data-etiqueta') && !str_contains($celda, 'colspan')) {
                        $sinEtiqueta[] = basename(dirname($vista)).'/'.basename($vista).' → '.$celda;
                    }
                }
            }
        }

        $this->assertSame([], $sinEtiqueta, "Celdas sin data-etiqueta:\n".implode("\n", $sinEtiqueta));
    }

    public function test_el_estilo_de_tarjeta_existe_para_telefono(): void
    {
        $css = file_get_contents(resource_path('css/movil.css'));

        $this->assertStringContainsString('.tabla-apilable', $css);
        $this->assertStringContainsString('max-width: 767.98px', $css);
        $this->assertStringContainsString('attr(data-etiqueta)', $css);
    }
}
