<?php

namespace Tests\Feature\Interfaz;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Guardia de la migración a AdminLTE 4 / Bootstrap 5: ninguna vista, hoja de estilos o módulo de
 * formularios debe volver a usar clases o atributos exclusivos de Bootstrap 4 / AdminLTE 3.
 */
class ClasesBootstrapTest extends TestCase
{
    private const PATRONES = [
        'badge-<color>' => '/(?<![\w-])badge-(primary|secondary|success|danger|warning|info|light|dark|pill)(?![\w-])/',
        'custom-*' => '/(?<![\w-])custom-(control|switch|select|checkbox|radio|file|range)[\w-]*/',
        'font-weight-*' => '/(?<![\w-])font-weight-[\w]+/',
        'ml-/mr-/pl-/pr-' => '/(?<![\w-])(ml|mr|pl|pr)-(\d|auto)(?![\w-])/',
        'text-left/right' => '/(?<![\w-])text-(left|right)(?![\w-])/',
        'float-left/right' => '/(?<![\w-])float-(left|right)(?![\w-])/',
        'sr-only' => '/(?<![\w-])sr-only(?![\w-])/',
        'form-group/form-row' => '/(?<![\w-])form-(group|row)(?![\w-])/',
        'no-gutters' => '/(?<![\w-])no-gutters(?![\w-])/',
        'btn-block/btn-default/btn-flat' => '/(?<![\w-])btn-(block|default|flat)(?![\w-])/',
        'input-group-prepend/append' => '/(?<![\w-])input-group-(prepend|append)(?![\w-])/',
        'atributos data-toggle/dismiss/widget' => '/\sdata-(toggle|dismiss|widget)=/',
        'estructura de AdminLTE 3' => '/(?<![\w-])(main-header|main-sidebar|content-wrapper|nav-sidebar|hold-transition|content-header)(?![\w-])/',
        'select2 para Bootstrap 4' => '/bootstrap4/',
    ];

    public function test_ninguna_vista_ni_estilo_usa_clases_de_bootstrap_4(): void
    {
        $raiz = dirname(__DIR__, 3).'/resources';
        $hallazgos = [];

        foreach (['views', 'css', 'js'] as $carpeta) {
            $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$raiz}/{$carpeta}", RecursiveDirectoryIterator::SKIP_DOTS));

            foreach ($iterador as $archivo) {
                if (!preg_match('/\.(blade\.php|css|js)$/', $archivo->getFilename()) || str_ends_with($archivo->getFilename(), '.test.js')) {
                    continue;
                }

                $contenido = file_get_contents($archivo->getPathname());

                foreach (self::PATRONES as $nombre => $patron) {
                    if (preg_match($patron, $contenido, $coincidencia)) {
                        $hallazgos[] = str_replace($raiz.DIRECTORY_SEPARATOR, '', $archivo->getPathname())." → {$nombre} ({$coincidencia[0]})";
                    }
                }
            }
        }

        $this->assertSame([], $hallazgos, "Clases de Bootstrap 4 / AdminLTE 3:\n".implode("\n", $hallazgos));
    }
}
