<?php

namespace Tests\Feature\Claves;

use App\Models\RegistroClave;
use App\Models\User;
use App\Services\ClavesTemporales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 4: la clave temporal solo sirve para elegir la contraseña propia (FR-003, FR-007, FR-008). */
class CambioObligatorioTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private User $pastor;

    private User $persona;

    private string $clave;

    private RegistroClave $registro;

    protected function setUp(): void
    {
        parent::setUp();
        $sede = $this->crearSede(['nombre' => 'Cocorná']);
        $this->pastor = $this->crearUsuario('pastor', null, $sede->id);
        $this->persona = $this->crearUsuario('lider_celula', null, $sede->id);
        $this->persona->persona->update(['celular' => '3105559999']);

        $generada = app(ClavesTemporales::class)->generar($this->persona, RegistroClave::MOTIVO_ALTA, $this->pastor);
        $this->clave = $generada['clave'];
        $this->registro = $generada['registro'];
    }

    private function entrar(?string $clave = null)
    {
        return $this->post(route('login.post'), ['email' => $this->persona->email, 'password' => $clave ?? $this->clave]);
    }

    public function test_con_clave_temporal_toda_pantalla_lleva_a_elegir_la_contrasena(): void
    {
        $this->entrar()->assertRedirect(route('dashboard'));

        foreach (['dashboard', 'personas.index', 'celulas.index', 'cuenta.edit'] as $ruta) {
            $this->get(route($ruta))->assertRedirect(route('cambiar-clave.edit'));
        }

        $this->get(route('cambiar-clave.edit'))->assertOk()->assertSee('Elige tu contraseña');
    }

    public function test_la_clave_se_acepta_sin_guion_y_en_minusculas(): void
    {
        $this->entrar(strtolower(str_replace('-', '', $this->clave)))->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertRedirect(route('cambiar-clave.edit'));
    }

    public function test_se_puede_cerrar_sesion_estando_en_cambio_obligatorio(): void
    {
        $this->entrar();

        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_reglas_de_la_contrasena_nueva(): void
    {
        $this->entrar();

        $this->put(route('cambiar-clave.update'), ['password' => 'corta12', 'password_confirmation' => 'corta12'])->assertSessionHasErrors('password');
        $this->put(route('cambiar-clave.update'), ['password' => 'unaclavebuena1', 'password_confirmation' => 'otradistinta1'])->assertSessionHasErrors('password');
        $this->put(route('cambiar-clave.update'), ['password' => str_replace('-', '', $this->clave).'x', 'password_confirmation' => str_replace('-', '', $this->clave).'x'])->assertSessionHasNoErrors();
    }

    public function test_no_puede_ser_igual_a_la_clave_temporal(): void
    {
        $this->entrar(str_replace('-', '', $this->clave));
        $sinGuion = str_replace('-', '', $this->clave);

        // La clave de 10 caracteres cumple la longitud mínima, pero es igual a la temporal.
        $this->put(route('cambiar-clave.update'), ['password' => $sinGuion, 'password_confirmation' => $sinGuion])
            ->assertSessionHasErrors('password');
        $this->assertTrue($this->persona->fresh()->debe_cambiar_clave);
    }

    public function test_una_contrasena_valida_cierra_el_ciclo_y_la_temporal_deja_de_servir(): void
    {
        $this->entrar();

        $this->put(route('cambiar-clave.update'), ['password' => 'mi-contrasena-1', 'password_confirmation' => 'mi-contrasena-1'])
            ->assertRedirect(route('dashboard'));

        $user = $this->persona->fresh();
        $this->assertFalse($user->debe_cambiar_clave);
        $this->assertNull($user->clave_vence_en);
        $this->assertTrue(Hash::check('mi-contrasena-1', $user->password));
        $this->assertFalse(Hash::check(str_replace('-', '', $this->clave), $user->password));

        $registro = $this->registro->fresh();
        $this->assertSame(RegistroClave::USADA, $registro->estado);
        $this->assertNotNull($registro->usada_en);

        // Ya no hay redirección obligatoria y la temporal no sirve para entrar de nuevo.
        $this->get(route('dashboard'))->assertOk();
        $this->flushSession();
        $this->entrar()->assertSessionHasErrors('email');
        $this->post(route('login.post'), ['email' => $this->persona->email, 'password' => 'mi-contrasena-1'])->assertRedirect(route('dashboard'));
    }

    public function test_una_clave_temporal_vencida_rechaza_el_ingreso(): void
    {
        $this->persona->forceFill(['clave_vence_en' => now()->subMinute()])->save();

        $this->entrar()->assertSessionHasErrors(['email' => 'Tu clave temporal venció; pide una nueva.']);
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_si_vence_con_la_sesion_abierta_se_cierra_la_sesion(): void
    {
        $this->entrar();
        $this->persona->forceFill(['clave_vence_en' => now()->subMinute()])->save();

        $this->get(route('cambiar-clave.edit'))->assertOk(); // la pantalla de cambio sigue disponible
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_una_cuenta_sin_clave_temporal_entra_sin_interrupciones(): void
    {
        $normal = $this->crearUsuario('lider_celula', null, $this->pastor->sede_id);

        $this->post(route('login.post'), ['email' => $normal->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('cambiar-clave.edit'))->assertRedirect(route('dashboard'));
    }

    public function test_el_inicio_de_sesion_se_bloquea_tras_demasiados_fallos(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.post'), ['email' => $this->persona->email, 'password' => 'incorrecta'])->assertSessionHasErrors('email');
        }

        // Aunque ahora sea la clave correcta, el sexto intento queda bloqueado.
        $this->entrar()->assertSessionHasErrors(['email' => 'Demasiados intentos; espera un minuto antes de volver a intentarlo.']);
    }
}
