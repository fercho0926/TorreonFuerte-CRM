<?php

namespace Tests\Feature\Aislamiento;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AislamientoPersonasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_listados_y_busquedas_solo_muestran_la_sede_propia(): void
    {
        $this->get(route('personas.index'))
            ->assertOk()
            ->assertSee('PersonaAlfa Miembro')
            ->assertDontSee('PersonaBeta')
            ->assertDontSee('LiderBeta');

        $this->get(route('personas.index', ['q' => 'PersonaBeta']))->assertViewHas('pagina', fn ($p) => $p->total() === 0);
        $this->get(route('personas.index', ['q' => $this->B['persona']->numero_documento]))->assertViewHas('pagina', fn ($p) => $p->total() === 0);
        $this->get(route('lideres.index'))->assertSee('LiderAlfa')->assertDontSee('LiderBeta');
    }

    public function test_selectores_y_filtros_no_ofrecen_datos_de_otra_sede(): void
    {
        $this->get(route('personas.index'))->assertDontSee('PersonaBeta');

        $this->get(route('personas.create'))
            ->assertOk()
            ->assertSee('LiderAlfa')
            ->assertDontSee('LiderBeta');
    }

    public function test_enlaces_directos_a_otra_sede_responden_404(): void
    {
        $persona = $this->B['persona'];
        $lider = $this->B['lider'];

        $this->get(route('personas.show', $persona))->assertNotFound();
        $this->get(route('personas.edit', $persona))->assertNotFound();
        $this->put(route('personas.update', $persona), ['nombres' => 'Hack'])->assertNotFound();
        $this->post(route('personas.inactivar', $persona))->assertNotFound();
        $this->post(route('personas.reactivar', $persona))->assertNotFound();
        $this->delete(route('personas.destroy', $persona))->assertNotFound();
        $this->get(route('lideres.show', $lider))->assertNotFound();
        $this->get(route('lideres.quitar', $lider))->assertNotFound();
        $this->delete(route('lideres.destroy', $lider))->assertNotFound();

        $this->assertSame('PersonaBeta', $persona->fresh()->nombres);
        $this->assertSame('activo' !== $persona->fresh()->estado, true);
    }

    public function test_no_se_puede_asignar_a_lider_de_otra_sede(): void
    {
        $base = ['apellidos' => 'Prueba', 'celular' => '3009998888', 'estado' => 'visitante'];

        $this->post(route('personas.store'), $base + ['nombres' => 'ConLiderAjeno', 'lider_id' => $this->B['lider']->id])
            ->assertSessionHasErrors('lider_id');

        $this->assertSame(0, Persona::withoutGlobalScopes()->whereIn('nombres', ['ConLiderAjeno'])->count());

        $this->put(route('personas.update', $this->A['persona']), [
            'nombres' => 'PersonaAlfa', 'apellidos' => 'Miembro', 'estado' => 'miembro_activo', 'tipo_documento' => 'CC',
            'numero_documento' => $this->A['persona']->numero_documento, 'celular' => $this->A['persona']->celular, 'lider_id' => $this->B['lider']->id,
        ])->assertSessionHasErrors('lider_id');
        $this->assertSame($this->A['lider']->id, $this->A['persona']->fresh()->lider_id);
    }

    public function test_el_registro_nuevo_queda_en_la_sede_del_pastor(): void
    {
        $this->post(route('personas.store'), ['nombres' => 'Nueva', 'apellidos' => 'Visita', 'celular' => '3001010101', 'estado' => 'visitante'])
            ->assertSessionHasNoErrors();

        $this->assertSame($this->A['sede']->id, Persona::withoutGlobalScopes()->where('nombres', 'Nueva')->value('sede_id'));
    }

    public function test_sin_lider_no_muestra_personas_de_otra_sede(): void
    {
        Persona::factory()->deSede($this->A['sede']->id)->visitante()->create(['nombres' => 'HuerfanaAlfa', 'apellidos' => 'Uno']);
        Persona::factory()->deSede($this->B['sede']->id)->visitante()->create(['nombres' => 'HuerfanaBeta', 'apellidos' => 'Uno']);

        $this->get(route('sin-lider.index'))->assertOk()->assertSee('HuerfanaAlfa')->assertDontSee('HuerfanaBeta');

        $beta = Persona::withoutGlobalScopes()->where('nombres', 'HuerfanaBeta')->first();
        $this->post(route('sin-lider.asignar', $beta), ['lider_id' => $this->A['lider']->id])->assertNotFound();
    }

    public function test_tambien_aislado_para_el_pastor_de_la_otra_sede(): void
    {
        $this->iniciarSesionComo($this->B['pastor']);

        $this->get(route('personas.index'))->assertSee('PersonaBeta')->assertDontSee('PersonaAlfa');
        $this->get(route('personas.show', $this->A['persona']))->assertNotFound();
    }
}
