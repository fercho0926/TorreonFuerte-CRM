<?php

namespace Tests\Feature\Formularios;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\Support\RecorreFormularios;
use Tests\TestCase;

class EstructuraFormulariosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RecorreFormularios, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararFormularios();
    }

    public function test_cada_pantalla_de_formulario_usa_los_componentes_con_secciones_y_acciones(): void
    {
        foreach ($this->pantallasDeFormulario() as $etiqueta => $url) {
            $respuesta = $this->get($url);
            $respuesta->assertOk();

            $html = $respuesta->getContent();
            $this->assertStringContainsString('data-form="1"', $html, "{$etiqueta}: falta el marcador data-form");
            $this->assertStringContainsString('class="fm-seccion', $html, "{$etiqueta}: no tiene secciones");
            $this->assertStringContainsString('fm-seccion__titulo', $html, "{$etiqueta}: sección sin título");
            $this->assertStringContainsString('fm-acciones', $html, "{$etiqueta}: falta la barra de acciones");
        }
    }

    public function test_los_formularios_completos_muestran_la_leyenda_de_obligatorios(): void
    {
        foreach (['persona (crear)', 'célula (crear)', 'evento (crear)', 'usuario (crear)', 'sede (crear)'] as $etiqueta) {
            $this->get($this->pantallasDeFormulario()[$etiqueta])->assertSee('Campo obligatorio');
        }
    }

    public function test_los_campos_conservan_los_mismos_nombres_que_antes(): void
    {
        foreach ($this->camposEsperados() as $etiqueta => $campos) {
            $html = $this->get($this->pantallasDeFormulario()[$etiqueta])->getContent();

            foreach ($campos as $campo) {
                $this->assertStringContainsString('name="'.$campo.'"', $html, "{$etiqueta}: falta el campo {$campo}");
            }
        }
    }

    public function test_los_campos_obligatorios_estan_marcados(): void
    {
        $html = $this->get(route('personas.create'))->getContent();

        foreach (['fm-nombres', 'fm-apellidos'] as $id) {
            $this->assertMatchesRegularExpression('/<label for="'.$id.'"[^>]*fm-req/', $html);
            $this->assertMatchesRegularExpression('/id="'.$id.'"[^>]*aria-required="true"/s', $html);
        }
        $this->assertDoesNotMatchRegularExpression('/<label for="fm-barrio"[^>]*fm-req/', $html);
    }

    public function test_persona_tiene_sus_tres_secciones_con_los_campos_en_su_lugar(): void
    {
        $html = $this->get(route('personas.create'))->getContent();

        $orden = ['Datos personales', 'Ubicación', 'Liderazgo'];
        $ultima = -1;
        foreach ($orden as $titulo) {
            $posicion = strpos($html, '<h3 class="fm-seccion__titulo">'.$titulo.'</h3>');
            $this->assertNotFalse($posicion, "Falta la sección {$titulo}");
            $this->assertGreaterThan($ultima, $posicion, "La sección {$titulo} está fuera de orden");
            $ultima = $posicion;
        }

        $seccion = fn (string $titulo) => substr($html, strpos($html, '<h3 class="fm-seccion__titulo">'.$titulo.'</h3>'));
        $this->assertStringContainsString('name="nombres"', substr($seccion('Datos personales'), 0, strpos($seccion('Datos personales'), 'fm-seccion__titulo">Ubicación') ?: 99999));
        $this->assertLessThan(strpos($html, 'name="direccion"'), strpos($html, 'name="numero_documento"'));
        $this->assertLessThan(strpos($html, 'name="lider_id"'), strpos($html, 'name="ciudad"'));
    }

    public function test_los_formularios_incrustados_y_filtros_tambien_usan_los_componentes(): void
    {
        foreach (['célula (ficha)', 'evento (ficha)', 'encuentro (ficha)', 'ministerio (ficha)', 'sin líder', 'personas (listado)', 'líderes (listado)', 'usuarios (listado)'] as $etiqueta) {
            $html = $this->get($this->pantallasConFormulariosIncrustados()[$etiqueta])->assertOk()->getContent();
            $this->assertStringContainsString('data-form="1"', $html, "{$etiqueta}: sin formularios con componentes");
        }
    }
}
