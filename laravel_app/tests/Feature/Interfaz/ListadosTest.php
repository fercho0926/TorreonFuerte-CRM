<?php

namespace Tests\Feature\Interfaz;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Todos los listados comparten disposición, tabla desplazable, acciones con icono y estado vacío (FR-006, FR-009). */
class ListadosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private const LISTADOS = ['personas.index', 'lideres.index', 'celulas.index', 'eventos.index', 'encuentros.index',
        'ministerios.index', 'redes.index', 'users.index', 'sedes.index', 'sin-lider.index'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    public function test_cada_listado_usa_tarjeta_y_tabla_dentro_de_la_tarjeta(): void
    {
        foreach (self::LISTADOS as $ruta) {
            $html = $this->get(route($ruta))->assertOk()->getContent();

            $this->assertStringContainsString('class="card', $html, $ruta);
            if ($ruta !== 'sin-lider.index') { // la lista «sin líder» se compone de tarjetas con formulario por persona
                $this->assertMatchesRegularExpression('/table-responsive|<table class="table table-hover/', $html, $ruta);
            }
            $this->assertStringNotContainsString('table-bordered', $html, "{$ruta} mezcla estilos de tabla");
        }
    }

    public function test_las_acciones_de_fila_llevan_icono_y_nombre_accesible(): void
    {
        $html = $this->get(route('eventos.index'))->getContent();

        $this->assertStringContainsString('fa-eye', $html);
        $this->assertStringContainsString('fa-edit', $html);
        $this->assertStringContainsString('fa-trash', $html);
        $this->assertStringContainsString('aria-label="Ver '.$this->A['evento']->nombre.'"', $html);
        $this->assertStringContainsString('aria-label="Eliminar '.$this->A['evento']->nombre.'"', $html);
    }

    public function test_el_boton_de_crear_vive_en_el_encabezado_de_la_pagina(): void
    {
        foreach (['personas.index' => 'Crear persona', 'celulas.index' => 'Crear célula', 'eventos.index' => 'Crear evento',
            'encuentros.index' => 'Crear encuentro', 'ministerios.index' => 'Crear ministerio', 'users.index' => 'Crear usuario',
            'sedes.index' => 'Nueva sede'] as $ruta => $texto) {
            $html = $this->get(route($ruta))->getContent();
            $encabezado = substr($html, (int) strpos($html, 'app-content-header'), (int) strpos($html, 'class="app-content"') - (int) strpos($html, 'app-content-header'));

            $this->assertStringContainsString($texto, $encabezado, "{$ruta}: «{$texto}» debe estar en el encabezado");
        }
    }

    public function test_un_filtro_sin_resultados_muestra_el_estado_vacio_con_icono_y_mensaje(): void
    {
        $this->get(route('personas.index', ['q' => 'zzzNadaCoincide']))
            ->assertOk()
            ->assertSee('No se encontraron personas.')
            ->assertSee('fa-address-card', false);
    }

    public function test_el_contador_de_resultados_aparece_en_el_titulo_de_la_tarjeta(): void
    {
        $this->get(route('personas.index'))->assertSee('resultados', false);
        $this->get(route('celulas.index'))->assertSee('resultados', false);
    }

    public function test_la_paginacion_usa_bootstrap_5(): void
    {
        \App\Models\Persona::factory()->count(30)->deSede($this->A['sede']->id)->create();

        $html = $this->get(route('personas.index'))->getContent();

        $this->assertStringContainsString('pagination', $html);
        $this->assertStringNotContainsString('tailwind', $html);
    }
}
