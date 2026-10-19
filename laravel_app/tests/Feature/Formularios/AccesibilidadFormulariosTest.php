<?php

namespace Tests\Feature\Formularios;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\Support\RecorreFormularios;
use Tests\TestCase;

class AccesibilidadFormulariosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RecorreFormularios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararFormularios();
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    public function test_todo_control_visible_de_un_formulario_tiene_etiqueta_asociada(): void
    {
        foreach ($this->pantallasDeFormulario() + $this->pantallasConFormulariosIncrustados() as $etiqueta => $url) {
            $xp = $this->xpath($this->get($url)->assertOk()->getContent());

            // Controles dentro de formularios con componentes (no los de la barra superior).
            $controles = $xp->query('//form[@data-form]//input[not(@type="hidden") and not(@type="submit") and not(@type="button")]|//form[@data-form]//select|//form[@data-form]//textarea');

            foreach ($controles as $control) {
                $id = $control->getAttribute('id');
                $this->assertNotSame('', $id, "{$etiqueta}: control sin id → ".$control->getAttribute('name'));

                $etiquetas = $xp->query('//label[@for="'.$id.'"]');
                $this->assertGreaterThan(0, $etiquetas->length, "{$etiqueta}: el control #{$id} no tiene <label for>");
            }
        }
    }

    public function test_los_obligatorios_tienen_aria_required_y_los_errores_aria_invalid(): void
    {
        foreach ($this->pantallasDeFormulario() as $etiqueta => $url) {
            $xp = $this->xpath($this->get($url)->getContent());

            foreach ($xp->query('//form[@data-form]//*[@required]') as $obligatorio) {
                $this->assertSame('true', $obligatorio->getAttribute('aria-required'), "{$etiqueta}: #".$obligatorio->getAttribute('id').' es obligatorio sin aria-required');
            }
            $this->assertSame(0, $xp->query('//*[@aria-invalid="true"]')->length, "{$etiqueta}: no debería haber errores al abrir el formulario vacío");
        }
    }

    public function test_cada_campo_enlaza_su_error_y_el_resumen_usa_role_alert(): void
    {
        $respuesta = $this->from(route('personas.create'))->followingRedirects()->post(route('personas.store'), [
            'nombres' => '', 'apellidos' => '', 'celular' => '3105559999', 'estado' => 'visitante',
        ]);

        $xp = $this->xpath($respuesta->getContent());
        $resumen = $xp->query('//*[@data-resumen-servidor]');
        $this->assertSame(1, $resumen->length);
        $this->assertSame('alert', $resumen->item(0)->getAttribute('role'));

        foreach (['fm-nombres', 'fm-apellidos'] as $id) {
            $campo = $xp->query('//*[@id="'.$id.'"]')->item(0);
            $this->assertSame('true', $campo->getAttribute('aria-invalid'));
            $this->assertStringContainsString($id.'-error', $campo->getAttribute('aria-describedby'));
            $this->assertGreaterThan(0, $xp->query('//*[@id="'.$id.'-error"]')->length);
            $this->assertGreaterThan(0, $xp->query('//a[@href="#'.$id.'"]')->length, "El resumen no enlaza a #{$id}");
        }
    }

    public function test_los_iconos_decorativos_se_ocultan_a_lectores_de_pantalla_y_las_secciones_tienen_encabezado(): void
    {
        $xp = $this->xpath($this->get(route('personas.create'))->getContent());

        foreach ($xp->query('//*[contains(@class,"input-group-text")]') as $icono) {
            $this->assertSame('true', $icono->getAttribute('aria-hidden'));
        }
        $this->assertGreaterThanOrEqual(3, $xp->query('//section[contains(@class,"fm-seccion")]//h3')->length);
    }

    public function test_los_interruptores_de_asistencia_tienen_nombre_accesible(): void
    {
        $xp = $this->xpath($this->get(route('celulas.show', $this->A['celula']))->getContent());

        $interruptores = $xp->query('//input[@name="asistio[]"]');
        $this->assertGreaterThan(0, $interruptores->length);
        foreach ($interruptores as $interruptor) {
            $etiqueta = $xp->query('//label[@for="'.$interruptor->getAttribute('id').'"]//*[contains(@class,"visually-hidden")]');
            $this->assertGreaterThan(0, $etiqueta->length, 'Un interruptor de asistencia no tiene nombre accesible');
        }
    }
}
