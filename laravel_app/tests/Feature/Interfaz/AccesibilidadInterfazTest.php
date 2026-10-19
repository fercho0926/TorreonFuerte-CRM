<?php

namespace Tests\Feature\Interfaz;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Accesibilidad básica y uso en pantallas pequeñas (FR-014, FR-018). */
class AccesibilidadInterfazTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private const RUTAS = ['dashboard', 'personas.index', 'lideres.index', 'celulas.index', 'eventos.index', 'encuentros.index',
        'ministerios.index', 'sin-lider.index', 'users.index', 'sedes.index'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    private function xpath(string $html): DOMXPath
    {
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        return new DOMXPath($doc);
    }

    public function test_los_dos_layouts_declaran_idioma_y_viewport(): void
    {
        $html = $this->get(route('dashboard'))->getContent();
        $this->assertStringContainsString('<html lang="es"', $html);
        $this->assertStringContainsString('name="viewport"', $html);

        $this->flushSession();
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('<html lang="es"', $html);
        $this->assertStringContainsString('name="viewport"', $html);
    }

    public function test_los_botones_de_solo_icono_tienen_nombre_accesible(): void
    {
        foreach (self::RUTAS as $ruta) {
            $xp = $this->xpath($this->get(route($ruta))->getContent());

            foreach ($xp->query('//a[contains(@class,"btn")]|//button') as $boton) {
                $texto = trim($boton->textContent);
                $tiene = $texto !== '' || $boton->getAttribute('aria-label') !== '' || $boton->getAttribute('title') !== '';
                $this->assertTrue($tiene, "{$ruta}: botón sin texto ni aria-label: ".$boton->getAttribute('class'));
            }
        }
    }

    public function test_las_tablas_se_desplazan_dentro_de_su_tarjeta(): void
    {
        foreach (self::RUTAS as $ruta) {
            $xp = $this->xpath($this->get(route($ruta))->getContent());

            foreach ($xp->query('//table[contains(@class,"table")]') as $tabla) {
                $envueltas = $xp->query('ancestor::*[contains(@class,"table-responsive") or contains(@class,"d-none")]', $tabla);
                $this->assertGreaterThan(0, $envueltas->length, "{$ruta}: tabla sin contenedor desplazable");
            }
        }
    }

    public function test_el_menu_y_las_migas_tienen_etiquetas_de_navegacion(): void
    {
        $html = $this->get(route('personas.index'))->getContent();

        $this->assertStringContainsString('aria-label="Menú principal"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('aria-label="Mostrar u ocultar el menú"', $html);
    }
}
