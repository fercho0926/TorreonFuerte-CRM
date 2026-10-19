<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class SedeActivaAdminTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_el_admin_elige_una_sede_y_solo_ve_datos_de_esa_sede(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('sede-activa.update'), ['sede_id' => $this->A['sede']->id])->assertRedirect();
        $this->assertSame($this->A['sede']->id, session('sede_activa_id'));

        $this->get(route('personas.index'))->assertOk()->assertSee('PersonaAlfa')->assertDontSee('PersonaBeta');
        $this->get(route('celulas.index'))->assertSee('Celula Alfa')->assertDontSee('Celula Beta');
        $this->get(route('personas.show', $this->B['persona']))->assertNotFound();

        $this->post(route('sede-activa.update'), ['sede_id' => $this->B['sede']->id]);
        $this->get(route('personas.index'))->assertSee('PersonaBeta')->assertDontSee('PersonaAlfa');
    }

    public function test_en_todas_las_sedes_ve_todo_con_la_columna_sede(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->post(route('sede-activa.update'), ['sede_id' => ''])->assertRedirect();
        $this->assertNull(session('sede_activa_id'));

        $this->get(route('personas.index'))
            ->assertOk()
            ->assertSee('PersonaAlfa')->assertSee('PersonaBeta')
            ->assertSee('<th>Sede</th>', false)
            ->assertSee('Iglesia Beta')
            ->assertSee('solo consulta');

        foreach (['lideres.index', 'celulas.index', 'encuentros.index', 'eventos.index', 'ministerios.index', 'redes.index'] as $ruta) {
            $this->get(route($ruta))->assertOk()->assertSee('<th>Sede</th>', false);
        }
    }

    public function test_en_todas_las_sedes_no_se_puede_crear_ni_editar(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('personas.store'), ['nombres' => 'Sin', 'apellidos' => 'Sede', 'celular' => '3001234567', 'estado' => 'visitante'])
            ->assertForbidden()->assertSee('Elige una sede activa para crear o editar');
        $this->put(route('personas.update', $this->A['persona']), ['nombres' => 'Hack'])->assertForbidden();
        $this->delete(route('eventos.destroy', $this->A['evento']))->assertForbidden();
        $this->post(route('redes.store'), ['nombre' => 'Nueva'])->assertForbidden();

        $this->assertSame(0, Persona::withoutGlobalScopes()->where('nombres', 'Sin')->count());
        $this->assertNotNull($this->A['evento']->fresh());

        // Consultar sí se puede.
        $this->get(route('personas.index'))->assertOk();
    }

    public function test_con_sede_activa_lo_que_crea_queda_en_esa_sede(): void
    {
        $this->comoAdminEnSede($this->B['sede']);

        $this->post(route('personas.store'), ['nombres' => 'EnBeta', 'apellidos' => 'Nueva', 'celular' => '3001234567', 'estado' => 'visitante'])->assertSessionHasNoErrors();
        $this->post(route('redes.store'), ['nombre' => 'Red Nueva'])->assertSessionHasNoErrors();

        $this->assertSame($this->B['sede']->id, Persona::withoutGlobalScopes()->where('nombres', 'EnBeta')->value('sede_id'));
        $this->assertSame($this->B['sede']->id, \App\Models\Red::withoutGlobalScopes()->where('nombre', 'Red Nueva')->value('sede_id'));
    }

    public function test_el_selector_de_sede_activa_solo_aparece_para_el_admin(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->get(route('personas.index'))->assertSee('id="sede-activa"', false)->assertSee('Todas las sedes');

        $this->iniciarSesionComo($this->A['pastor']);
        $this->get(route('personas.index'))->assertDontSee('id="sede-activa"', false)->assertSee('Iglesia Alfa');
    }

    public function test_un_no_admin_no_puede_cambiar_de_sede_activa(): void
    {
        foreach ([$this->A['pastor'], $this->A['liderUser']] as $usuario) {
            $this->iniciarSesionComo($usuario);

            $this->post(route('sede-activa.update'), ['sede_id' => $this->B['sede']->id])->assertForbidden();
            $this->get(route('personas.index'))->assertDontSee('PersonaBeta');
        }
    }

    public function test_una_sede_activa_inexistente_se_rechaza(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('sede-activa.update'), ['sede_id' => 99999])->assertSessionHasErrors('sede_id');
    }
}
