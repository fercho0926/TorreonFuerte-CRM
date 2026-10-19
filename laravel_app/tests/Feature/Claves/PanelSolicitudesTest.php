<?php

namespace Tests\Feature\Claves;

use App\Models\RegistroClave;
use App\Models\SolicitudClave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 2: el panel de solicitudes y su alcance por sede (FR-009, FR-017). */
class PanelSolicitudesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private SolicitudClave $solicitudA;

    private SolicitudClave $solicitudB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->A['liderUser']->forceFill(['celular' => '3105559999'])->save();
        $this->B['liderUser']->forceFill(['celular' => '3205559999'])->save();

        $this->solicitudA = $this->crearSolicitud($this->A['liderUser']);
        $this->solicitudB = $this->crearSolicitud($this->B['liderUser']);
    }

    private function crearSolicitud(User $user, ?\DateTimeInterface $fecha = null): SolicitudClave
    {
        return SolicitudClave::create(['user_id' => $user->id, 'sede_id' => $user->sede_id, 'ultima_solicitud_en' => $fecha ?? now()]);
    }

    public function test_el_pastor_solo_ve_las_solicitudes_de_su_sede(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('solicitudes-clave.index'))
            ->assertOk()
            ->assertSee($this->A['liderUser']->email)
            ->assertDontSee($this->B['liderUser']->email)
            ->assertSee('Generar clave');
    }

    public function test_el_administrador_ve_todas_en_todas_las_sedes(): void
    {
        $this->comoAdminEnTodas();

        $this->get(route('solicitudes-clave.index'))
            ->assertOk()
            ->assertSee($this->A['liderUser']->email)
            ->assertSee($this->B['liderUser']->email);
    }

    public function test_un_lider_no_puede_ver_el_panel(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);

        $this->get(route('solicitudes-clave.index'))->assertForbidden();
        $this->post(route('solicitudes-clave.atender', $this->solicitudA))->assertForbidden();
    }

    public function test_atender_genera_la_clave_invalida_la_anterior_y_marca_la_solicitud(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $hashAntes = $this->A['liderUser']->password;

        $respuesta = $this->post(route('solicitudes-clave.atender', $this->solicitudA));

        $respuesta->assertOk()->assertSee('Clave generada')->assertSee('https://wa.me/573105559999?text=', false);
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));

        $lider = $this->A['liderUser']->fresh();
        $this->assertNotSame($hashAntes, $lider->password);
        $this->assertFalse(Hash::check('password', $lider->password));
        $this->assertTrue($lider->debe_cambiar_clave);

        $solicitud = $this->solicitudA->fresh();
        $this->assertSame(SolicitudClave::ATENDIDA, $solicitud->estado);
        $this->assertSame($this->A['pastor']->id, $solicitud->atendida_por);
        $this->assertNotNull($solicitud->atendida_en);

        $registro = RegistroClave::withoutGlobalScopes()->where('user_id', $lider->id)->firstOrFail();
        $this->assertSame('recuperacion', $registro->motivo);
        $this->assertSame($this->solicitudA->id, $registro->solicitud_id);
        $this->assertNull($registro->solicitada_por, 'la propia persona');
        $this->assertSame($this->A['pastor']->id, $registro->preparada_por);
    }

    public function test_atender_dos_veces_no_genera_otra_clave(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $this->post(route('solicitudes-clave.atender', $this->solicitudA))->assertOk();
        $hash = $this->A['liderUser']->fresh()->password;

        $this->post(route('solicitudes-clave.atender', $this->solicitudA))->assertRedirect(route('solicitudes-clave.index'));

        $this->assertSame($hash, $this->A['liderUser']->fresh()->password);
    }

    public function test_una_solicitud_de_otra_sede_responde_404(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->post(route('solicitudes-clave.atender', $this->solicitudB))->assertNotFound();
        $this->post(route('solicitudes-clave.descartar', $this->solicitudB))->assertNotFound();
        $this->assertSame(SolicitudClave::PENDIENTE, $this->solicitudB->fresh()->estado);
    }

    public function test_el_pastor_no_atiende_solicitudes_de_otro_pastor_ni_de_admins(): void
    {
        $otroPastor = $this->crearUsuario('pastor', null, $this->A['sede']->id);
        $otroPastor->forceFill(['celular' => '3001112233'])->save();
        $deOtroPastor = $this->crearSolicitud($otroPastor);

        $this->iniciarSesionComo($this->A['pastor']);

        $this->post(route('solicitudes-clave.atender', $deOtroPastor))->assertForbidden();
    }

    public function test_descartar_marca_la_solicitud(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->post(route('solicitudes-clave.descartar', $this->solicitudA))->assertRedirect(route('solicitudes-clave.index'));

        $this->assertSame(SolicitudClave::DESCARTADA, $this->solicitudA->fresh()->estado);
        $this->assertSame(0, RegistroClave::withoutGlobalScopes()->count());
    }

    public function test_sin_celular_no_se_genera_la_clave_y_la_solicitud_sigue_pendiente(): void
    {
        $this->A['liderUser']->forceFill(['celular' => null])->save();
        $this->iniciarSesionComo($this->A['pastor']);

        $this->post(route('solicitudes-clave.atender', $this->solicitudA))->assertRedirect()->assertSessionHas('error');

        $this->assertSame(SolicitudClave::PENDIENTE, $this->solicitudA->fresh()->estado);
        $this->assertSame(0, RegistroClave::withoutGlobalScopes()->count());
    }

    public function test_una_solicitud_sin_atender_pasa_a_vencida_y_aun_se_puede_atender(): void
    {
        $vieja = $this->crearSolicitud($this->crearUsuarioConCelular(), now()->subHours(49));
        $this->iniciarSesionComo($this->A['pastor']);

        $this->assertSame('vencida', $vieja->fresh()->estadoVigente());
        $this->get(route('solicitudes-clave.index', ['estado' => 'vencidas']))->assertOk()->assertSee($vieja->user->email);
        $this->get(route('solicitudes-clave.index'))->assertDontSee($vieja->user->email);
        $this->post(route('solicitudes-clave.atender', $vieja))->assertOk()->assertSee('Clave generada');
    }

    public function test_la_insignia_del_menu_cuenta_las_pendientes_del_alcance(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('dashboard'))->assertSee('Solicitudes de clave');

        $opciones = collect(\App\Support\Menu::secciones($this->A['pastor'], ['solicitudes' => 1], 'dashboard'))->pluck('opciones')->flatten(1);
        $this->assertSame(1, $opciones->firstWhere('ruta', 'solicitudes-clave.index')['insignia']);
    }

    private function crearUsuarioConCelular(): User
    {
        $user = $this->crearUsuario('lider_celula', null, $this->A['sede']->id);
        $user->forceFill(['celular' => '3001112233'])->save();

        return $user;
    }
}
