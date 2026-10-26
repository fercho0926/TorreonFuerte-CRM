<?php

namespace Tests\Feature\RegistroMiembrosQr;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class GestionTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_el_admin_ve_la_pestana_con_qr_enlace_y_acciones(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->get(route('sedes.show', ['sede' => $this->A['sede'], 'pestana' => 'registro']))
            ->assertOk()
            ->assertSee('Registro de miembros')
            ->assertSee(route('registro-sede.show', $this->A['sede']->fresh()->registro_token), false)
            ->assertSee('Descargar QR')
            ->assertSee('Cerrar registro')
            ->assertSee('Regenerar enlace')
            ->assertSee('Todavía nadie se ha registrado')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_el_admin_gestiona_cualquier_sede_sin_importar_la_activa(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->get(route('sedes.show', ['sede' => $this->B['sede'], 'pestana' => 'registro']))
            ->assertOk()
            ->assertSee(route('registro-sede.show', $this->B['sede']->fresh()->registro_token), false);
    }

    public function test_el_pastor_ve_el_panel_de_su_sede_y_el_menu(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('registro-miembros.index'))
            ->assertOk()
            ->assertSee('Iglesia Alfa')
            ->assertSee(route('registro-sede.show', $this->A['sede']->fresh()->registro_token), false)
            ->assertDontSee($this->B['sede']->fresh()->registro_token)
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get(route('dashboard'))->assertSee(route('registro-miembros.index'), false);
    }

    public function test_el_svg_se_descarga_sin_cache(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $respuesta = $this->get(route('registro-miembros.svg', $this->A['sede']))->assertOk();

        $this->assertStringContainsString('image/svg+xml', $respuesta->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $respuesta->getContent());
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
    }

    public function test_abrir_y_cerrar_alterna_el_registro(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $this->assertTrue($this->A['sede']->fresh()->registro_abierto);

        $this->post(route('registro-miembros.abrir', $this->A['sede']))->assertRedirect();
        $this->assertFalse($this->A['sede']->fresh()->registro_abierto);

        $this->post(route('registro-miembros.abrir', $this->A['sede']))->assertRedirect();
        $this->assertTrue($this->A['sede']->fresh()->registro_abierto);
    }

    public function test_regenerar_cambia_el_token_y_el_enlace_anterior_deja_de_funcionar(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $anterior = $this->A['sede']->fresh()->registro_token;

        $this->post(route('registro-miembros.regenerar', $this->A['sede']))->assertRedirect();

        $nuevo = $this->A['sede']->fresh()->registro_token;
        $this->assertNotSame($anterior, $nuevo);
        $this->assertSame(32, strlen($nuevo));
        $this->get(route('registro-sede.show', $anterior))->assertNotFound();
        $this->get(route('registro-sede.show', $nuevo))->assertOk();
    }

    public function test_un_pastor_no_gestiona_otra_sede(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $tokenB = $this->B['sede']->fresh()->registro_token;

        $this->get(route('registro-miembros.svg', $this->B['sede']))->assertForbidden();
        $this->post(route('registro-miembros.abrir', $this->B['sede']))->assertForbidden();
        $this->post(route('registro-miembros.regenerar', $this->B['sede']))->assertForbidden();

        $this->assertTrue($this->B['sede']->fresh()->registro_abierto);
        $this->assertSame($tokenB, $this->B['sede']->fresh()->registro_token);
    }

    public function test_lider_y_usuario_no_tienen_acceso_ni_menu(): void
    {
        foreach ([$this->A['liderUser'], $this->crearUsuario('user', Persona::factory()->deSede($this->A['sede']->id)->create())] as $usuario) {
            $this->iniciarSesionComo($usuario);

            $this->get(route('registro-miembros.index'))->assertForbidden();
            $this->get(route('registro-miembros.svg', $this->A['sede']))->assertForbidden();
            $this->post(route('registro-miembros.abrir', $this->A['sede']))->assertForbidden();
            $this->post(route('registro-miembros.regenerar', $this->A['sede']))->assertForbidden();
            $this->get(route('dashboard'))->assertDontSee(route('registro-miembros.index'), false);
        }
    }

    public function test_el_admin_no_ve_la_opcion_de_menu_y_sin_sesion_se_redirige(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->get(route('dashboard'))->assertDontSee(route('registro-miembros.index'), false);

        $this->flushSession();
        $this->get(route('registro-miembros.index'))->assertRedirect(route('login'));
    }
}
