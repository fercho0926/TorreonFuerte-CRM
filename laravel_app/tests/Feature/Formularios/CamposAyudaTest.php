<?php

namespace Tests\Feature\Formularios;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class CamposAyudaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    public function test_persona_declara_las_reglas_de_dependencia(): void
    {
        $html = $this->get(route('personas.create'))->getContent();

        $this->assertSame(1, substr_count($html, 'data-visible-si="es_lider:1"'), 'La red se muestra solo al marcar "es líder"');
        $this->assertSame(2, substr_count($html, 'data-requerido-si="estado:miembro_activo|es_lider:1"'), 'Tipo y número de documento obligatorios según estado o liderazgo');
        $this->assertMatchesRegularExpression('/<select id="fm-red_id"[^>]*data-requerido-si="es_lider:1"/s', $html);
    }

    public function test_celular_documento_y_fecha_ayudan_a_escribir(): void
    {
        $html = $this->get(route('personas.create'))->getContent();

        $this->assertMatchesRegularExpression('/<input type="tel" id="fm-celular"[^>]*inputmode="tel"[^>]*data-mascara="celular"/s', $html);
        $this->assertMatchesRegularExpression('/id="fm-numero_documento"[^>]*data-mascara="documento"/s', $html);
        $this->assertMatchesRegularExpression('/<input type="date" id="fm-fecha_nacimiento"[^>]*data-fecha[^>]*max="'.now()->toDateString().'"[^>]*data-max="hoy"/s', $html);
        $this->assertMatchesRegularExpression('/id="fm-email"[^>]*type|type="email" id="fm-email"/s', $html);
    }

    public function test_los_montos_declaran_formato_de_miles_y_teclado_decimal(): void
    {
        foreach ([route('eventos.create') => 'fm-costo', route('encuentros.create') => 'fm-valor'] as $url => $id) {
            $html = $this->get($url)->getContent();
            $this->assertMatchesRegularExpression('/id="'.$id.'"[^>]*inputmode="decimal"[^>]*data-monto/s', $html, "{$url}: monto sin formato");
        }
    }

    public function test_las_fechas_y_montos_se_siguen_enviando_con_el_mismo_formato(): void
    {
        $this->post(route('personas.store'), [
            'nombres' => 'Formato', 'apellidos' => 'Plano', 'celular' => '3105559999', 'estado' => 'visitante', 'fecha_nacimiento' => '1990-03-05',
        ])->assertSessionHasNoErrors();

        $this->assertSame('1990-03-05', Persona::where('nombres', 'Formato')->first()->fecha_nacimiento->format('Y-m-d'));
        $this->assertSame('3105559999', Persona::where('nombres', 'Formato')->value('celular'));

        $this->post(route('eventos.store'), [
            'nombre' => 'Evento Monto', 'tipo' => \App\Models\Evento::TIPOS[0], 'fecha' => now()->addDay()->toDateString(), 'costo' => '50000.50', 'estado' => 'planificado',
        ])->assertSessionHasNoErrors();

        $this->assertEquals(50000.50, \App\Models\Evento::where('nombre', 'Evento Monto')->value('costo'));
    }

    public function test_una_fecha_de_nacimiento_futura_sigue_siendo_rechazada_por_el_servidor(): void
    {
        $this->post(route('personas.store'), [
            'nombres' => 'Futura', 'apellidos' => 'Fecha', 'celular' => '3105550000', 'estado' => 'visitante', 'fecha_nacimiento' => now()->addYear()->toDateString(),
        ])->assertSessionHasErrors('fecha_nacimiento');
    }
}
