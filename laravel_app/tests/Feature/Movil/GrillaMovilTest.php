<?php

namespace Tests\Feature\Movil;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** La grilla estándar entrega lo que necesita el modo tarjeta en teléfono (spec 008, RF-005, RF-006). */
class GrillaMovilTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_cada_celda_lleva_su_etiqueta_y_las_secundarias_su_clase(): void
    {
        $html = $this->get(route('personas.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-etiqueta="Nombre"', $html);
        $this->assertStringContainsString('data-etiqueta="Estado"', $html);
        $this->assertStringContainsString('data-etiqueta="Líder"', $html);
        // «Líder» tiene prioridad 4: va bajo «Ver más». «Nombre» (1) y «Estado» (2) siempre se ven.
        $this->assertMatchesRegularExpression('/<td[^>]*grilla-sec[^>]*data-etiqueta="Líder"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<td[^>]*grilla-sec[^>]*data-etiqueta="Nombre"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<td[^>]*grilla-sec[^>]*data-etiqueta="Estado"/', $html);
    }

    public function test_la_barra_ofrece_el_boton_de_filtros_y_el_selector_de_orden(): void
    {
        $html = $this->get(route('personas.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-grilla-filtros', $html);
        $this->assertStringContainsString('data-grilla-filtros-n', $html);
        $this->assertStringContainsString('data-grilla-orden', $html);
    }

    public function test_la_configuracion_de_columnas_incluye_titulo_y_prioridad_para_el_script(): void
    {
        $html = $this->get(route('personas.index'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-columnas=\'[^\']*"titulo":"Nombre"[^\']*\'/', $html);
        $this->assertMatchesRegularExpression('/data-columnas=\'[^\']*"prioridad":4[^\']*\'/', $html);
    }

    public function test_las_acciones_no_se_pliegan_nunca(): void
    {
        $html = $this->get(route('personas.index'))->assertOk()->getContent();

        $this->assertStringContainsString('grilla-celda-acciones', $html);
        $this->assertDoesNotMatchRegularExpression('/<td[^>]*grilla-sec[^>]*grilla-celda-acciones/', $html);
    }

    public function test_el_datos_json_entrega_las_celdas_sin_cambios_de_estructura(): void
    {
        $json = $this->getJson(route('personas.datos', ['length' => 10]))->assertOk()->json();

        $this->assertNotEmpty($json['data']);
        $this->assertCount(6, $json['data'][0], 'nombre, estado, celular, líder, nivel, acciones');
    }
}
