<?php

namespace Tests\Feature;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AlcancePorRolTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    private Persona $liderA;

    private Persona $liderB;

    private Persona $personaA;

    private Persona $personaB;

    private Zona $zonaA;

    private function escenario(): void
    {
        $this->zonaA = Zona::create(['sede_id' => \Database\Factories\SedeFactory::idPorDefecto(), 'nombre' => 'Zona A']);
        $zonaB = Zona::create(['sede_id' => \Database\Factories\SedeFactory::idPorDefecto(), 'nombre' => 'Zona B']);

        $this->liderA = Persona::factory()->lider(null, $this->zonaA->id)->create(['nombres' => 'LiderA', 'apellidos' => 'Alfa']);
        $this->liderB = Persona::factory()->lider(null, $zonaB->id)->create(['nombres' => 'LiderB', 'apellidos' => 'Beta']);
        $this->personaA = Persona::factory()->create(['nombres' => 'PersonaA', 'apellidos' => 'Alfa', 'lider_id' => $this->liderA->id]);
        $this->personaB = Persona::factory()->create(['nombres' => 'PersonaB', 'apellidos' => 'Beta', 'lider_id' => $this->liderB->id]);
    }

    public function test_admin_y_pastor_ven_todo(): void
    {
        $this->escenario();

        foreach (['comoAdmin', 'comoPastor'] as $rol) {
            $this->$rol();
            $this->get(route('personas.index'))->assertSee('PersonaA Alfa')->assertSee('PersonaB Beta');
            $this->get(route('lideres.index'))->assertSee('LiderA Alfa')->assertSee('LiderB Beta');
        }
    }

    public function test_el_lider_ve_solo_a_sus_personas_y_selectores_acotados(): void
    {
        $this->escenario();
        $this->comoLider($this->liderA);

        $this->get(route('personas.index'))
            ->assertOk()
            ->assertSee('PersonaA Alfa')
            ->assertDontSee('PersonaB Beta');

        $this->get(route('personas.show', $this->personaB))->assertNotFound();
        $this->get(route('personas.show', $this->personaA))->assertOk();

        $this->get(route('personas.create'))
            ->assertOk()
            ->assertSee('LiderA Alfa')
            ->assertDontSee('LiderB Beta');
    }

    public function test_un_lider_no_puede_asignar_una_persona_a_un_lider_ajeno(): void
    {
        $this->escenario();
        $this->comoLider($this->liderA);

        $this->put(route('personas.update', $this->personaA), [
            'nombres' => 'PersonaA', 'apellidos' => 'Alfa', 'estado' => 'miembro_activo',
            'tipo_documento' => 'CC', 'numero_documento' => $this->personaA->numero_documento,
            'celular' => $this->personaA->celular, 'lider_id' => $this->liderB->id,
        ])->assertForbidden();

        $this->assertSame($this->liderA->id, $this->personaA->fresh()->lider_id);
    }

    public function test_un_lider_que_registra_a_alguien_lo_deja_a_su_cargo(): void
    {
        $this->escenario();
        $this->comoLider($this->liderA);

        $this->post(route('personas.store'), ['nombres' => 'Nueva', 'apellidos' => 'Visita', 'celular' => '3001239999', 'estado' => 'visitante'])
            ->assertSessionHasNoErrors();

        $this->assertSame($this->liderA->id, Persona::where('nombres', 'Nueva')->value('lider_id'));
    }

    public function test_un_lider_no_puede_designar_lideres_ni_cambiar_el_rol_por_formulario(): void
    {
        $this->escenario();
        $this->comoLider($this->liderA);

        $this->post(route('personas.store'), [
            'nombres' => 'Intento', 'apellidos' => 'Lider', 'celular' => '3001230000', 'estado' => 'visitante',
            'es_lider' => 1, 'red_id' => $this->liderA->red_id, 'zona_id' => $this->liderA->zona_id,
        ])->assertSessionHasNoErrors();

        $this->assertFalse(Persona::where('nombres', 'Intento')->firstOrFail()->es_lider);
        $this->get(route('lideres.designar'))->assertForbidden();
    }

    public function test_el_rol_usuario_solo_ve_su_propio_registro(): void
    {
        $this->escenario();
        $this->iniciarSesionComo($this->crearUsuario('user', $this->personaA));

        $this->get(route('personas.index'))->assertViewHas('personas', fn ($p) => $p->total() === 1 && $p->first()->is($this->personaA));
    }

    public function test_un_usuario_sin_persona_vinculada_ve_un_mensaje_claro(): void
    {
        $this->escenario();
        $this->iniciarSesionComo($this->crearUsuario('lider_celula'));

        $this->get(route('personas.index'))->assertSee('Tu cuenta aún no está asociada a una persona');
    }

    public function test_las_celulas_visibles_siguen_el_mismo_ambito(): void
    {
        $this->escenario();
        $celulaA = Celula::factory()->create(['lider_id' => $this->liderA->id, 'nombre' => 'Célula A']);
        Celula::factory()->create(['lider_id' => $this->liderB->id, 'nombre' => 'Célula B']);

        $this->comoLider($this->liderA);

        $this->get(route('celulas.index'))->assertSee('Célula A')->assertDontSee('Célula B');
        $this->get(route('celulas.show', $celulaA))->assertOk();
    }
}
