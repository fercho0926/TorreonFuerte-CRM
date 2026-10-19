<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PersonasInactivarTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_inactivar_y_reactivar_conserva_el_estado_previo_y_el_historial(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create();
        $persona = Persona::factory()->create(['estado' => 'nuevo_creyente', 'lider_id' => $lider->id]);

        $this->post(route('personas.inactivar', $persona))->assertRedirect();
        $this->assertSame('inactivo', $persona->fresh()->estado);
        $this->assertSame($lider->id, $persona->fresh()->lider_id);

        $this->post(route('personas.reactivar', $persona))->assertRedirect();
        $this->assertSame('nuevo_creyente', $persona->fresh()->estado);
        $this->assertNull($persona->fresh()->inactivado_en);
        $this->assertDatabaseHas('persona_cambios', ['persona_id' => $persona->id, 'accion' => 'inactivada']);
        $this->assertDatabaseHas('persona_cambios', ['persona_id' => $persona->id, 'accion' => 'reactivada']);
    }

    public function test_eliminar_con_historial_responde_422_y_sin_historial_solo_admin(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create();
        Persona::factory()->create(['lider_id' => $lider->id]);
        $sinHistorial = Persona::factory()->create();

        $this->delete(route('personas.destroy', $lider))->assertStatus(422);
        $this->assertNotNull($lider->fresh());

        $this->delete(route('personas.destroy', $sinHistorial))->assertRedirect(route('personas.index'));
        $this->assertSoftDeleted($sinHistorial);
    }

    public function test_un_pastor_no_puede_eliminar(): void
    {
        $this->comoPastor();
        $persona = Persona::factory()->create();

        $this->delete(route('personas.destroy', $persona))->assertForbidden();
    }
}
