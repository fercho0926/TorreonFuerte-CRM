<?php

namespace Tests\Feature\Interfaz;

use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Evento;
use App\Models\Ministerio;
use App\Models\Persona;
use App\Support\Estados;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** Un único esquema de colores para los estados (FR-008, SC-005). */
class EstadosTest extends TestCase
{
    public function test_todo_estado_de_la_aplicacion_tiene_etiqueta_y_color_definidos(): void
    {
        $estados = array_merge(
            array_keys(Persona::ESTADOS), Celula::ESTADOS, Evento::ESTADOS, Encuentro::ESTADOS, Ministerio::ESTADOS,
            ['pagado', 'pendiente', 'si', 'no', 'lider'],
        );

        foreach ($estados as $estado) {
            $this->assertContains(Estados::color($estado), ['success', 'info', 'warning', 'secondary', 'danger', 'primary', 'dark'], $estado);
            $this->assertNotSame('', Estados::etiqueta($estado));
        }
    }

    public function test_los_mismos_estados_siempre_tienen_el_mismo_color(): void
    {
        $this->assertSame('success', Estados::color('activo'));
        $this->assertSame('success', Estados::color('activa'));
        $this->assertSame('success', Estados::color('realizado'));
        $this->assertSame('info', Estados::color('planificado'));
        $this->assertSame('warning', Estados::color('visitante'));
        $this->assertSame('danger', Estados::color('cancelado'));
        $this->assertSame('secondary', Estados::color('inactivo'));
        $this->assertSame('primary', Estados::color('lider'));
    }

    public function test_un_estado_desconocido_usa_secondary_y_su_texto(): void
    {
        $this->assertSame('secondary', Estados::color('algo_nuevo'));
        $this->assertSame('Algo nuevo', Estados::etiqueta('algo_nuevo'));
    }

    public function test_las_vistas_no_dibujan_insignias_con_clases_badge_sueltas(): void
    {
        $raiz = dirname(__DIR__, 3).'/resources/views';
        $hallazgos = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz, FilesystemIterator::SKIP_DOTS)) as $archivo) {
            $ruta = str_replace('\\', '/', $archivo->getPathname());
            if (!str_ends_with($ruta, '.blade.php') || str_contains($ruta, '/components/ui/') || str_contains($ruta, '/layouts/')) {
                continue;
            }
            if (preg_match('/class="badge[^"]*"/', file_get_contents($ruta))) {
                $hallazgos[] = $ruta;
            }
        }

        $this->assertSame([], $hallazgos, 'Use <x-ui.estado> o <x-ui.insignia> en lugar de clases badge directas');
    }
}
