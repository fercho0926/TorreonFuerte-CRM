<?php

namespace Tests\Feature\Lideres;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 3: designar líder con autocompletado de personas activas (FR-005). */
class DesignarConAutocompletadoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_la_pantalla_usa_un_campo_con_autocompletado_remoto_y_sin_buscar_ni_red(): void
    {
        $html = $this->get(route('lideres.designar'))->assertOk()->getContent();

        $this->assertStringContainsString('name="persona_id"', $html);
        $this->assertStringContainsString('data-fuente="'.route('personas.buscar').'"', $html);
        $this->assertStringContainsString('&quot;sin_lideres&quot;:1', $html);
        $this->assertStringContainsString('&quot;activas&quot;:1', $html);
        $this->assertStringNotContainsString('name="red_id"', $html);
        $this->assertStringNotContainsString('>Buscar<', $html);
        $this->assertStringContainsString('Designar líder', $html);
    }

    public function test_designar_con_persona_id_marca_el_rol_y_deja_traza(): void
    {
        $persona = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Rosa']);

        $this->post(route('lideres.designar.store'), ['persona_id' => $persona->id])
            ->assertRedirect(route('lideres.show', $persona))
            ->assertSessionHas('success');

        $this->assertTrue($persona->fresh()->es_lider);
        $this->assertSame(1, $persona->cambios()->where('accion', 'rol_lider')->count());
    }

    public function test_una_persona_inactiva_o_que_ya_es_lider_se_rechaza(): void
    {
        $inactiva = Persona::factory()->deSede($this->A['sede']->id)->inactiva()->create();

        $this->post(route('lideres.designar.store'), ['persona_id' => $inactiva->id])
            ->assertSessionHasErrors(['persona_id' => 'No se puede designar a una persona inactiva.']);
        $this->post(route('lideres.designar.store'), ['persona_id' => $this->A['lider']->id])
            ->assertSessionHasErrors(['persona_id' => 'Esa persona ya es líder.']);

        $this->assertFalse($inactiva->fresh()->es_lider);
    }

    public function test_sin_elegir_persona_pide_elegirla(): void
    {
        $this->post(route('lideres.designar.store'), [])->assertSessionHasErrors(['persona_id' => 'Elige a la persona que será líder.']);
    }

    public function test_un_pastor_no_designa_a_una_persona_de_otra_sede(): void
    {
        $this->post(route('lideres.designar.store'), ['persona_id' => $this->B['persona']->id])->assertSessionHasErrors('persona_id');

        $this->assertFalse($this->B['persona']->fresh()->es_lider);
    }

    public function test_un_lider_no_puede_designar(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);

        $this->get(route('lideres.designar'))->assertForbidden();
        $this->post(route('lideres.designar.store'), ['persona_id' => $this->A['persona']->id])->assertForbidden();
    }
}
