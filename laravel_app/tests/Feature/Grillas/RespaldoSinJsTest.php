<?php

namespace Tests\Feature\Grillas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Sin JavaScript cada listado entrega su primera página con filas, acciones y paginación del servidor (RF-009, RF-011). */
class RespaldoSinJsTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public static function listados(): array
    {
        return array_map(fn ($m) => [$m], ['personas', 'lideres', 'celulas', 'encuentros', 'eventos', 'ministerios', 'sedes', 'sin-lider', 'users', 'solicitudes-clave']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnTodas();
    }

    #[DataProvider('listados')]
    public function test_el_listado_entrega_la_tabla_de_la_grilla_con_su_configuracion(string $modulo): void
    {
        $html = $this->get(route("$modulo.index"))->assertOk()->getContent();

        $this->assertStringContainsString('data-grilla="'.$modulo.'"', $html);
        $this->assertStringContainsString('data-url="'.route("$modulo.datos").'"', $html);
        $this->assertStringContainsString('table-responsive', $html);
        $this->assertStringContainsString('class="grilla-filtros"', $html);
    }

    public function test_la_primera_pagina_trae_filas_acciones_y_paginador_del_servidor(): void
    {
        \App\Models\Persona::factory()->count(30)->deSede($this->A['sede']->id)->create();

        $html = $this->get(route('personas.index'))->assertOk()->getContent();

        $this->assertStringContainsString('aria-label="Ver ', $html, 'acciones por fila presentes');
        $this->assertStringContainsString('grilla-pie-servidor', $html, 'paginador de respaldo');
        $this->assertStringContainsString('page=2', $html);
    }

    public function test_los_parametros_get_de_antes_siguen_filtrando(): void
    {
        $this->get(route('solicitudes-clave.index', ['estado' => 'todas']))->assertOk();
        $this->get(route('users.index', ['sede_id' => $this->B['sede']->id]))->assertOk()->assertSee('Beta')->assertDontSee('lider.alfa', false);
        $this->get(route('lideres.index', ['q' => 'LiderBeta']))->assertOk()->assertSee('LiderBeta')->assertDontSee('LiderAlfa');
    }
}
