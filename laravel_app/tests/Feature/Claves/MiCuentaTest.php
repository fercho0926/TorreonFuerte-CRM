<?php

namespace Tests\Feature\Claves;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** «Mi cuenta»: cambiar la propia contraseña (FR-011). */
class MiCuentaTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $sede = $this->crearSede(['nombre' => 'Cocorná']);
        $this->usuario = $this->iniciarSesionComo($this->crearUsuario('lider_celula', null, $sede->id));
    }

    public function test_la_pantalla_muestra_los_datos_y_el_menu_de_usuario_enlaza_a_ella(): void
    {
        $this->get(route('cuenta.edit'))
            ->assertOk()
            ->assertSee($this->usuario->email)
            ->assertSee('Cocorná')
            ->assertSee('Cambiar mi contraseña');

        $this->get(route('dashboard'))->assertSee(route('cuenta.edit'), false)->assertSee('Mi cuenta');
    }

    public function test_cambia_la_contrasena_con_la_actual_correcta(): void
    {
        $this->put(route('cuenta.clave.update'), ['password_actual' => 'password', 'password' => 'nueva-segura-1', 'password_confirmation' => 'nueva-segura-1'])
            ->assertRedirect(route('cuenta.edit'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nueva-segura-1', $this->usuario->fresh()->password));
    }

    public function test_con_la_actual_incorrecta_se_rechaza(): void
    {
        $hash = $this->usuario->password;

        $this->put(route('cuenta.clave.update'), ['password_actual' => 'equivocada', 'password' => 'nueva-segura-1', 'password_confirmation' => 'nueva-segura-1'])
            ->assertSessionHasErrors(['password_actual' => 'La contraseña actual no es correcta.']);

        $this->assertSame($hash, $this->usuario->fresh()->password);
    }

    public function test_exige_minimo_confirmacion_y_que_sea_distinta(): void
    {
        $this->put(route('cuenta.clave.update'), ['password_actual' => 'password', 'password' => 'corta1', 'password_confirmation' => 'corta1'])->assertSessionHasErrors('password');
        $this->put(route('cuenta.clave.update'), ['password_actual' => 'password', 'password' => 'nueva-segura-1', 'password_confirmation' => 'otra-cosa-123'])->assertSessionHasErrors('password');
        $this->put(route('cuenta.clave.update'), ['password_actual' => 'password', 'password' => 'password', 'password_confirmation' => 'password'])->assertSessionHasErrors('password');
    }

    public function test_sin_sesion_no_se_puede_entrar(): void
    {
        $this->flushSession();

        $this->get(route('cuenta.edit'))->assertRedirect(route('login'));
        $this->put(route('cuenta.clave.update'), [])->assertRedirect(route('login'));
    }
}
