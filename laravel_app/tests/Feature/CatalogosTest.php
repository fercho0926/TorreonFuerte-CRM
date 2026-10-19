<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Red;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class CatalogosTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_una_red_con_nombre_parecido_es_rechazada_y_sugiere_la_existente(): void
    {
        $this->comoPastor();
        Red::create(['nombre' => 'Jóvenes']);

        $this->post(route('redes.store'), ['nombre' => ' jovenes '])
            ->assertSessionHasErrors('nombre');

        $this->assertSame(1, Red::count());
        $this->assertStringContainsString('Jóvenes', session('errors')->first('nombre'));
    }

    public function test_no_se_elimina_una_red_con_lideres(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create();

        $this->delete(route('redes.destroy', $lider->red_id))->assertStatus(422);

        $this->assertNotNull(Red::find($lider->red_id));
    }

    public function test_fusionar_redes_mueve_a_los_lideres(): void
    {
        $this->comoAdmin();
        $origen = Red::create(['nombre' => 'Jovenes']);
        $destino = Red::create(['nombre' => 'Jóvenes adultos']);
        $lider = Persona::factory()->lider($origen->id)->create();

        $this->post(route('redes.fusionar', $origen), ['destino_id' => $destino->id])->assertRedirect(route('redes.index'));

        $this->assertNull(Red::find($origen->id));
        $this->assertSame($destino->id, $lider->fresh()->red_id);
    }

    public function test_solo_admin_y_pastor_gestionan_catalogos(): void
    {
        $this->comoLider();

        $this->get(route('redes.index'))->assertForbidden();
    }
}
