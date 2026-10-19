<?php

namespace Tests\Feature\Personas;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Grilla de personas: datos JSON (DataTables, modo servidor) y exportación a CSV. */
class GrillaDatosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function persona(array $a = []): Persona
    {
        return Persona::factory()->deSede($this->A['sede']->id)->create($a);
    }

    public function test_devuelve_el_protocolo_de_datatables(): void
    {
        $this->persona(['nombres' => 'Ana', 'apellidos' => 'Zuluaga']);

        $json = $this->getJson(route('personas.datos', ['draw' => 7, 'start' => 0, 'length' => 10]))->assertOk()->json();

        $this->assertSame(7, $json['draw']);
        $this->assertSame($json['recordsTotal'], $json['recordsFiltered']);
        $this->assertStringContainsString('Ana Zuluaga', implode('', array_merge(...$json['data'])));
        $this->assertCount(5, $json['data'][0], 'nombre, estado, celular, líder, acciones');
    }

    public function test_filtra_por_columna_y_cuenta_los_filtrados(): void
    {
        $this->persona(['nombres' => 'Buscada', 'apellidos' => 'Unica', 'celular' => '3201112233']);
        $this->persona(['nombres' => 'Otra', 'apellidos' => 'Persona', 'celular' => '3009998877']);

        $json = $this->getJson(route('personas.datos', ['nombre' => 'Buscada', 'length' => 10]))->json();
        $this->assertSame(1, $json['recordsFiltered']);
        $this->assertGreaterThan(1, $json['recordsTotal']);

        $json = $this->getJson(route('personas.datos', ['celular' => '320111', 'length' => 10]))->json();
        $this->assertSame(1, $json['recordsFiltered']);
    }

    public function test_ordena_por_la_columna_pedida_y_pagina(): void
    {
        $this->persona(['nombres' => 'A', 'apellidos' => 'Aaa']);
        $this->persona(['nombres' => 'Z', 'apellidos' => 'Zzz']);

        $desc = $this->getJson(route('personas.datos', ['order' => [['column' => 0, 'dir' => 'desc']], 'length' => 10, 'q' => 'Zzz']))->json('data');
        $this->assertStringContainsString('Zzz', $desc[0][0]);

        $pag = $this->getJson(route('personas.datos', ['length' => 10, 'start' => 0]))->json();
        $resto = $this->getJson(route('personas.datos', ['length' => 10, 'start' => 10]))->json();
        $this->assertGreaterThan(0, count($pag['data']));
        $this->assertSame([], $resto['data']);
    }

    public function test_una_longitud_no_permitida_vuelve_a_la_inicial(): void
    {
        $this->persona();
        $json = $this->getJson(route('personas.datos', ['length' => 100000]))->assertOk()->json();
        $this->assertLessThanOrEqual(25, count($json['data']));
    }

    public function test_no_muestra_personas_de_otra_sede(): void
    {
        $json = $this->getJson(route('personas.datos', ['length' => 100]))->json();
        $this->assertStringNotContainsString($this->B['persona']->nombres, json_encode($json['data']));
    }

    public function test_sin_sesion_responde_401_json(): void
    {
        session()->flush();
        $this->getJson(route('personas.datos'))->assertStatus(401);
    }

    public function test_exportar_genera_un_csv_con_bom_y_respeta_los_filtros(): void
    {
        $this->persona(['nombres' => '=Peligro', 'apellidos' => 'Exportada']);
        $this->persona(['nombres' => 'Otra', 'apellidos' => 'Fuera']);

        $respuesta = $this->get(route('personas.exportar', ['q' => 'Exportada']))->assertOk();
        $csv = $respuesta->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Nombre;Estado;Celular;Correo;Líder', $csv);
        $this->assertStringContainsString("'=Peligro", $csv, 'neutraliza fórmulas');
        $this->assertStringNotContainsString('Fuera', $csv);
        $this->assertStringContainsString('text/csv', $respuesta->headers->get('Content-Type'));
    }
}
