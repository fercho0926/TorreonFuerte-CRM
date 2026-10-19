<?php

namespace Tests\Feature\Personas;

use FilesystemIterator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** El concepto «Red» ya no existe en ninguna parte de la aplicación (FR-004, SC-002). */
class SinRedTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    /** Archivos que conservan la palabra por razones históricas (migración de datos antiguos). */
    private const EXCEPCIONES = ['app/Support/MigracionRedesZonas.php'];

    public function test_ninguna_ruta_ni_clase_de_red_existe(): void
    {
        foreach (Route::getRoutes() as $ruta) {
            $this->assertStringStartsNotWith('redes.', (string) $ruta->getName());
            $this->assertStringNotContainsString('/redes', $ruta->uri());
        }

        $this->assertFalse(class_exists('App\\Models\\Red'));
        $this->assertFalse(class_exists('App\\Http\\Controllers\\RedesController'));
        $this->assertFalse(is_dir(resource_path('views/redes')));
    }

    public function test_el_codigo_y_las_vistas_no_mencionan_red(): void
    {
        $base = dirname(__DIR__, 3);
        $hallazgos = [];

        foreach (['app', 'resources/views', 'resources/js', 'config', 'routes', 'database/factories', 'database/seeders'] as $carpeta) {
            $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base.'/'.$carpeta, FilesystemIterator::SKIP_DOTS));

            foreach ($iterador as $archivo) {
                $ruta = str_replace('\\', '/', $archivo->getPathname());
                $relativa = ltrim(str_replace(str_replace('\\', '/', $base), '', $ruta), '/');

                if (!preg_match('/\.(php|js)$/', $ruta) || str_ends_with($ruta, '.test.js') || in_array($relativa, self::EXCEPCIONES, true)) {
                    continue;
                }

                if (preg_match('/\bredes?\b|red_id|redLider/i', file_get_contents($ruta), $m)) {
                    $hallazgos[] = "{$relativa} ({$m[0]})";
                }
            }
        }

        $this->assertSame([], $hallazgos, "Menciones de red:\n".implode("\n", $hallazgos));
    }

    public function test_el_menu_el_formulario_y_el_tablero_no_tienen_red(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $this->get(route('dashboard'))->assertOk()->assertDontSee('Personas por red')->assertDontSee('Redes');
        $this->get(route('personas.create'))->assertOk()->assertDontSee('name="red_id"', false)->assertDontSee('Redes');
        $this->get(route('lideres.designar'))->assertOk()->assertDontSee('name="red_id"', false);
        $this->get(route('lideres.index'))->assertOk()->assertDontSee('red_id', false);
        $this->get(route('personas.index'))->assertOk()->assertDontSee('red_id', false);
    }

    public function test_designar_un_lider_ya_no_pide_red(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $this->post(route('lideres.designar.store'), ['persona_id' => $this->A['persona']->id])
            ->assertRedirect(route('lideres.show', $this->A['persona']));

        $this->assertTrue($this->A['persona']->fresh()->es_lider);
    }
}
