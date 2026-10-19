<?php

namespace Tests\Feature;

use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class LiderazgoTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_designar_lider_a_una_persona_existente_no_duplica_el_registro(): void
    {
        $this->comoAdmin();
        $persona = Persona::factory()->create(['nombres' => 'Rosa']);

        $this->post(route('lideres.designar.store'), ['persona_id' => $persona->id])
            ->assertRedirect(route('lideres.show', $persona));

        $this->assertSame(1, Persona::where('nombres', 'Rosa')->count());
        $persona->refresh();
        $this->assertTrue($persona->es_lider);
    }

    public function test_designar_exige_documento(): void
    {
        $this->comoAdmin();
        $persona = Persona::factory()->visitante()->create();

        $this->post(route('lideres.designar.store'), ['persona_id' => $persona->id])
            ->assertSessionHasErrors('persona_id');
        $this->assertFalse($persona->fresh()->es_lider);
    }

    public function test_quitar_rol_con_dependientes_sin_decision_responde_422_con_conteos(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create();
        Persona::factory()->count(2)->create(['lider_id' => $lider->id]);
        Celula::factory()->create(['lider_id' => $lider->id]);

        $this->delete(route('lideres.destroy', $lider))
            ->assertStatus(422)
            ->assertSee('1 célula(s)', false)
            ->assertSee('2 persona(s)', false);

        $this->assertTrue($lider->fresh()->es_lider);
    }

    public function test_quitar_rol_solo_con_personas_exige_reasignar_o_confirmar(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create();
        $persona = Persona::factory()->create(['lider_id' => $lider->id]);

        $this->delete(route('lideres.destroy', $lider))->assertStatus(422);
        $this->assertTrue($lider->fresh()->es_lider);

        $this->delete(route('lideres.destroy', $lider), ['confirmar_sin_lider' => 1])->assertRedirect(route('lideres.index'));
        $this->assertFalse($lider->fresh()->es_lider);
        $this->assertNull($persona->fresh()->lider_id);
    }

    public function test_con_reasignacion_las_personas_y_celulas_pasan_al_nuevo_lider(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create();
        $nuevo = Persona::factory()->lider()->create();
        $persona = Persona::factory()->create(['lider_id' => $lider->id]);
        $celula = Celula::factory()->create(['lider_id' => $lider->id]);

        $this->delete(route('lideres.destroy', $lider), ['reasignar_a' => $nuevo->id])->assertRedirect(route('lideres.index'));

        $this->assertFalse($lider->fresh()->es_lider);
        $this->assertSame($nuevo->id, $persona->fresh()->lider_id);
        $this->assertSame($nuevo->id, $celula->fresh()->lider_id);
        $this->assertDatabaseHas('persona_cambios', ['persona_id' => $lider->id, 'accion' => 'rol_lider']);
    }

    public function test_el_detalle_del_lider_muestra_personas_celulas_y_resumen(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create(['nombres' => 'Lidia']);
        Persona::factory()->create(['lider_id' => $lider->id, 'nombres' => 'Pedro', 'apellidos' => 'Cuidado']);
        Celula::factory()->create(['lider_id' => $lider->id, 'nombre' => 'Célula Esperanza']);

        $this->get(route('lideres.show', $lider))
            ->assertOk()
            ->assertSee('Pedro Cuidado')
            ->assertSee('Célula Esperanza')
            ->assertSee('Última reunión');
    }
}
