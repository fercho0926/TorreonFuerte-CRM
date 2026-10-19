<?php

namespace Tests\Feature\Formularios;

use App\Models\EventoPersona;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class SelectoresTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_los_selectores_de_lider_imprimen_el_dato_de_apoyo(): void
    {
        $html = $this->get(route('personas.create'))->getContent();

        $this->assertStringContainsString('data-apoyo="Doc. '.$this->A['lider']->numero_documento.'"', $html);
        $this->assertStringContainsString('data-buscar', $html);
    }

    public function test_el_formulario_de_celula_ofrece_busqueda_en_lider_y_colider(): void
    {
        $html = $this->get(route('celulas.create'))->getContent();

        $this->assertMatchesRegularExpression('/<select id="fm-lider_id"[^>]*data-buscar/s', $html);
        $this->assertMatchesRegularExpression('/<select id="fm-co_lider_id"[^>]*data-buscar/s', $html);
    }

    public function test_evento_encuentro_y_ministerio_usan_seleccion_multiple_solo_con_personas_de_la_sede(): void
    {
        foreach (['eventos.create', 'encuentros.create', 'ministerios.create'] as $ruta) {
            $respuesta = $this->get(route($ruta));
            $html = $respuesta->getContent();

            $this->assertMatchesRegularExpression('/<select id="fm-personas"[^>]*name="personas\[\]"[^>]*multiple|<select id="fm-personas"[^>]*multiple[^>]*name="personas\[\]"/s', $html, "{$ruta}: no es selección múltiple");
            $respuesta->assertSee('PersonaAlfa Miembro')->assertDontSee('PersonaBeta');
        }
    }

    public function test_el_envio_con_varias_personas_guarda_las_mismas_inscripciones_que_antes(): void
    {
        $otra = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Otra', 'apellidos' => 'Persona']);

        $this->post(route('eventos.store'), [
            'nombre' => 'Evento Multi', 'tipo' => \App\Models\Evento::TIPOS[0], 'fecha' => now()->addDay()->toDateString(), 'costo' => '50000', 'estado' => 'planificado',
            'personas' => [$this->A['persona']->id, $otra->id],
        ])->assertSessionHasNoErrors();

        $evento = \App\Models\Evento::where('nombre', 'Evento Multi')->firstOrFail();
        $this->assertSame(2, EventoPersona::where('evento_id', $evento->id)->count());
        $this->assertEquals(50000, $evento->costo);
    }

    public function test_el_multiselect_marca_los_seleccionados_y_avisa_si_no_hay_opciones(): void
    {
        view()->share('errors', new ViewErrorBag());
        $con = Blade::render('<x-form.multiselect name="personas[]" label="Personas" :opciones="$o" :seleccionados="[2]" />', ['o' => [['id' => 1, 'texto' => 'Ana', 'apoyo' => 'Doc. 1'], ['id' => 2, 'texto' => 'Beto']]]);
        $this->assertMatchesRegularExpression('/<option value="2"[^>]*selected/', $con);
        $this->assertDoesNotMatchRegularExpression('/<option value="1"[^>]*selected/', $con);
        $this->assertStringContainsString('data-multi', $con);

        $sin = Blade::render('<x-form.multiselect name="personas[]" label="Personas" :opciones="[]" vacio="No hay personas." />');
        $this->assertStringContainsString('No hay personas.', $sin);
        $this->assertStringNotContainsString('<select', $sin);
    }
}
