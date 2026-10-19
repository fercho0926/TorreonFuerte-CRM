<?php

namespace Tests\Feature\Aislamiento;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class UsuarioSinSedeTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_un_usuario_no_admin_sin_sede_no_inicia_sesion_y_recibe_un_mensaje_claro(): void
    {
        $user = User::factory()->create(['role' => 'pastor', 'sede_id' => null, 'email' => 'sinsede@iglesia.co']);

        $this->post(route('login.post'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Tu cuenta aún no está asociada a una sede; contacta al administrador.']);
    }

    public function test_una_sesion_abierta_sin_sede_se_cierra_con_el_mensaje(): void
    {
        $user = User::factory()->create(['role' => 'pastor', 'sede_id' => null]);
        $this->iniciarSesionComo($user);

        $this->get(route('personas.index'))->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_el_dashboard_de_un_pastor_solo_cuenta_datos_de_su_sede(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('personaCount', 3)
            ->assertViewHas('liderCount', 1)
            ->assertViewHas('eventoCount', 1)
            ->assertViewHas('activeSessions', null)
            ->assertViewHas('resumenSedes', null)
            ->assertSee('Iglesia Alfa')
            ->assertDontSee('Iglesia Beta')
            ->assertDontSee('Red Beta');
    }

    public function test_el_nombre_de_la_sede_se_muestra_siempre(): void
    {
        $this->iniciarSesionComo($this->B['liderUser']);

        $this->get(route('personas.index'))->assertSee('Iglesia Beta')->assertDontSee('Iglesia Alfa');
    }
}
