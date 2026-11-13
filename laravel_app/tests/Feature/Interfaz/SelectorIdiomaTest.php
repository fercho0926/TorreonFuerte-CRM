<?php

namespace Tests\Feature\Interfaz;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class SelectorIdiomaTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_cambiar_a_ingles_traduce_toda_la_interfaz(): void
    {
        $usuario = $this->comoAdmin();

        $this->post(route('idioma.update'), ['idioma' => 'en'])->assertRedirect();
        $this->assertSame('en', $usuario->fresh()->idioma);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSee('Dashboard');
    }

    public function test_un_idioma_invalido_se_rechaza_y_no_cambia_el_actual(): void
    {
        $usuario = $this->comoAdmin();

        $this->post(route('idioma.update'), ['idioma' => 'fr'])->assertSessionHasErrors('idioma');
        $this->assertSame('es', $usuario->fresh()->idioma);
    }

    public function test_el_idioma_elegido_se_mantiene_al_volver_a_iniciar_sesion(): void
    {
        $usuario = $this->comoAdmin();
        $this->post(route('idioma.update'), ['idioma' => 'en']);

        // "Cerrar sesión" en esta app es simplemente perder la sesión (no hay Auth real que mockear);
        // simulamos una sesión nueva reiniciando la sesión de prueba e iniciando sesión otra vez.
        $this->flushSession();
        $this->iniciarSesionComo($usuario->fresh());

        $this->get(route('dashboard'))->assertOk()->assertSee('<html lang="en"', false);
    }

    public function test_un_usuario_nuevo_ve_la_interfaz_en_espanol_por_defecto(): void
    {
        $usuario = User::factory()->create(['role' => 'admin']);
        $this->assertSame('es', $usuario->idioma);

        $this->iniciarSesionComo($usuario);
        $this->get(route('dashboard'))->assertOk()->assertSee('<html lang="es"', false);
    }
}
