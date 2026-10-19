<?php

namespace Tests\Feature\Grillas;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Exportación CSV: trae todo lo filtrado (no solo una página), en el orden pedido y sin HTML. */
class ExportacionGrillasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_exporta_mas_filas_que_una_pagina_y_respeta_el_orden(): void
    {
        Persona::factory()->count(60)->deSede($this->A['sede']->id)->create(['apellidos' => 'Masiva']);

        $csv = $this->get(route('personas.exportar', ['apellidos' => 'x', 'nombre' => 'Masiva', 'orden' => 'nombre', 'dir' => 'desc']))->assertOk()->streamedContent();
        $lineas = array_filter(explode("\n", trim($csv)));

        $this->assertCount(61, $lineas, 'cabecera + 60 personas, más de una página de 25');
        $this->assertStringNotContainsString('<a ', $csv);
        $this->assertStringNotContainsString('<span', $csv);
    }

    public function test_el_filtro_por_columna_se_aplica_a_la_exportacion(): void
    {
        $csv = $this->get(route('personas.exportar', ['nombre' => 'PersonaAlfa']))->assertOk()->streamedContent();

        $this->assertStringContainsString('PersonaAlfa', $csv);
        $this->assertStringNotContainsString('PastorAlfa', $csv);
    }

    public function test_una_exportacion_grande_se_hace_por_lotes_sin_cargar_todo(): void
    {
        Persona::factory()->count(1200)->deSede($this->A['sede']->id)->create(['apellidos' => 'Volumen']);
        $enlace = $this->get(route('personas.exportar', ['nombre' => 'Volumen']));

        DB::enableQueryLog();
        $csv = $enlace->streamedContent();
        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(1201, array_filter(explode("\n", trim($csv))));
        $this->assertLessThan(60, $consultas, 'lotes de 500 con carga ansiosa de relaciones, no una consulta por fila');
    }
}
