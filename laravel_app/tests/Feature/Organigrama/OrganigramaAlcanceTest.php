<?php

namespace Tests\Feature\Organigrama;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** El organigrama vive en la pestaña «Organigrama» del detalle de sede (módulo exclusivo del administrador). */
class OrganigramaAlcanceTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function url(array $sede): string
    {
        return route('sedes.show', ['sede' => $sede['sede'], 'pestana' => 'organigrama']);
    }

    public function test_el_admin_ve_el_organigrama_de_la_sede_que_abre_sin_importar_la_sede_activa(): void
    {
        $this->iniciarSesionComo($this->crearUsuario('admin'), $this->A['sede']->id);

        $this->get($this->url($this->B))
            ->assertOk()
            ->assertSee('PastorBeta')
            ->assertSee('LiderBeta')
            ->assertDontSee('PersonaAlfa')
            ->assertDontSee('LiderAlfa')
            ->assertDontSee('PastorAlfa');
    }

    public function test_el_admin_en_todas_las_sedes_tambien_lo_ve(): void
    {
        $this->comoAdminEnTodas();

        $this->get($this->url($this->A))
            ->assertOk()
            ->assertSee('LiderAlfa')
            ->assertDontSee('LiderBeta');
    }

    public function test_pastor_y_lider_no_acceden_al_modulo_de_sedes(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $this->get($this->url($this->A))->assertForbidden();

        $this->iniciarSesionComo($this->A['liderUser']);
        $this->get($this->url($this->A))->assertForbidden();
    }

    public function test_sin_sesion_redirige_al_inicio_de_sesion(): void
    {
        $this->get($this->url($this->A))->assertRedirect(route('login'));
    }

    public function test_ya_no_existe_la_opcion_ni_la_ruta_independiente(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('organigrama.index'));

        $this->comoAdminEnSede($this->A['sede']);
        $this->get(route('dashboard'))->assertDontSee('Organigrama');
    }
}
