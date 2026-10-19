<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class SinLiderTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_admin_y_pastor_ven_las_personas_sin_lider(): void
    {
        Persona::factory()->visitante()->create(['nombres' => 'Huérfana', 'apellidos' => 'Uno']);

        $this->comoAdmin();
        $this->get(route('sin-lider.index'))->assertOk()->assertSee('Huérfana Uno');

        $this->comoPastor();
        $this->get(route('sin-lider.index'))->assertOk()->assertSee('Huérfana Uno');
    }

    public function test_un_lider_recibe_403_y_no_la_ve_en_su_listado(): void
    {
        Persona::factory()->visitante()->create(['nombres' => 'Huérfana', 'apellidos' => 'Uno']);
        $this->comoLider();

        $this->get(route('sin-lider.index'))->assertForbidden();
        $this->get(route('personas.index'))->assertDontSee('Huérfana Uno');
    }

    public function test_asignar_un_lider_saca_a_la_persona_de_la_vista(): void
    {
        $lider = Persona::factory()->lider()->create();
        $persona = Persona::factory()->visitante()->create();

        $this->comoPastor();

        $this->post(route('sin-lider.asignar', $persona), ['lider_id' => $lider->id])->assertRedirect(route('sin-lider.index'));

        $this->assertSame($lider->id, $persona->fresh()->lider_id);
        $this->assertDatabaseHas('persona_cambios', ['persona_id' => $persona->id, 'accion' => 'lider']);
        $this->get(route('sin-lider.index'))->assertViewHas('personas', fn ($p) => $p->total() === 0);
    }

    public function test_un_lider_no_puede_asignar_personas_desde_la_vista(): void
    {
        $lider = Persona::factory()->lider()->create();
        $persona = Persona::factory()->visitante()->create();

        $this->comoLider($lider);

        $this->post(route('sin-lider.asignar', $persona), ['lider_id' => $lider->id])->assertForbidden();
        $this->assertNull($persona->fresh()->lider_id);
    }
}
