<?php

namespace Tests\Feature\Claves;

use App\Http\Controllers\RecuperacionClaveController;
use App\Models\SolicitudClave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 2: «¿Olvidaste tu contraseña?» (FR-004 a FR-006). */
class RecuperacionTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private User $lider;

    protected function setUp(): void
    {
        parent::setUp();
        $sede = $this->crearSede(['nombre' => 'Cocorná']);
        $this->lider = $this->crearUsuario('lider_celula', null, $sede->id);
    }

    private function pedir(string $correo)
    {
        return $this->post(route('recuperacion.store'), ['email' => $correo]);
    }

    private function solicitudes(): int
    {
        return SolicitudClave::withoutGlobalScopes()->count();
    }

    public function test_el_login_ofrece_el_enlace_y_el_formulario(): void
    {
        $this->get(route('login'))->assertSee('¿Olvidaste tu contraseña?')->assertSee(route('recuperacion.create'), false);
        $this->get(route('recuperacion.create'))->assertOk()->assertSee('Pedir nueva clave');
    }

    public function test_correo_existente_e_inexistente_responden_exactamente_igual(): void
    {
        $existente = $this->pedir($this->lider->email);
        $inexistente = $this->pedir('nadie@iglesia.co');

        $this->assertSame($existente->getStatusCode(), $inexistente->getStatusCode());
        $this->assertSame($existente->headers->get('Location'), $inexistente->headers->get('Location'));
        $existente->assertSessionHas('status', RecuperacionClaveController::MENSAJE);
        $inexistente->assertSessionHas('status', RecuperacionClaveController::MENSAJE);
    }

    public function test_una_cuenta_crea_una_solicitud_pendiente_con_su_sede(): void
    {
        $this->pedir($this->lider->email);

        $solicitud = SolicitudClave::withoutGlobalScopes()->firstOrFail();
        $this->assertSame($this->lider->id, $solicitud->user_id);
        $this->assertSame($this->lider->sede_id, $solicitud->sede_id);
        $this->assertSame(SolicitudClave::PENDIENTE, $solicitud->estado);
        $this->assertNotNull($solicitud->origen_hash);
        $this->assertStringNotContainsString('127.0.0.1', (string) $solicitud->origen_hash);
    }

    public function test_pedirla_de_nuevo_no_crea_otra_solicitud(): void
    {
        $this->pedir($this->lider->email);
        $this->travel(10)->minutes();
        $this->pedir($this->lider->email);

        $this->assertSame(1, $this->solicitudes());
        $this->assertTrue(SolicitudClave::withoutGlobalScopes()->first()->ultima_solicitud_en->gt(now()->subMinutes(5)));
    }

    public function test_una_sede_inactiva_no_crea_solicitud_pero_responde_igual(): void
    {
        $this->lider->sede()->first()->update(['activa' => false]);

        $this->pedir($this->lider->email)->assertSessionHas('status', RecuperacionClaveController::MENSAJE);

        $this->assertSame(0, $this->solicitudes());
    }

    public function test_el_administrador_del_sistema_tambien_puede_pedirla(): void
    {
        $admin = $this->crearUsuario('admin');

        $this->pedir($admin->email)->assertSessionHas('status');

        $solicitud = SolicitudClave::withoutGlobalScopes()->firstOrFail();
        $this->assertSame($admin->id, $solicitud->user_id);
        $this->assertNull($solicitud->sede_id);
    }

    public function test_el_formato_del_correo_se_valida(): void
    {
        $this->pedir('no-es-correo')->assertSessionHasErrors('email');
        $this->assertSame(0, $this->solicitudes());
    }

    public function test_el_limite_por_cuenta_bloquea_la_cuarta_peticion_con_429(): void
    {
        foreach (range(1, 3) as $i) {
            $this->pedir($this->lider->email)->assertRedirect(route('login'));
        }

        $this->pedir($this->lider->email)->assertStatus(429)->assertSee('Espera unos minutos');
    }

    public function test_el_limite_por_origen_bloquea_la_peticion_once(): void
    {
        foreach (range(1, 10) as $i) {
            $this->pedir("persona{$i}@iglesia.co")->assertRedirect(route('login'));
        }

        $this->pedir('persona11@iglesia.co')->assertStatus(429);
    }

    public function test_una_persona_con_sesion_no_ve_el_formulario(): void
    {
        $this->iniciarSesionComo($this->lider);

        $this->get(route('recuperacion.create'))->assertRedirect(route('dashboard'));
    }
}
