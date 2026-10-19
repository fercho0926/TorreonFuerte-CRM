<?php

namespace Tests\Feature\Claves;

use App\Models\RegistroClave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 3: «Enviar nueva clave» desde Usuarios (FR-010, FR-013, FR-014). */
class ReenviarClaveTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->A['liderUser']->forceFill(['celular' => '3105559999'])->save();
        $this->B['liderUser']->forceFill(['celular' => '3205559999'])->save();
    }

    public function test_el_pastor_reenvia_a_un_usuario_de_su_sede_y_la_anterior_queda_reemplazada(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $primera = $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk()->assertSee('Clave generada');
        $this->assertStringContainsString('no-store', $primera->headers->get('Cache-Control'));
        $segunda = $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk();

        $registros = RegistroClave::withoutGlobalScopes()->where('user_id', $this->A['liderUser']->id)->orderBy('id')->get();
        $this->assertCount(2, $registros);
        $this->assertSame(RegistroClave::REEMPLAZADA, $registros[0]->estado);
        $this->assertSame(RegistroClave::PENDIENTE_ENVIO, $registros[1]->estado);
        $this->assertSame('reenvio', $registros[1]->motivo);
        $this->assertSame($this->A['pastor']->id, $registros[1]->preparada_por);
    }

    public function test_no_alcanza_a_otra_sede_ni_a_pastores_ni_a_admins_y_el_lider_no_puede(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->post(route('users.clave.store', $this->B['liderUser']))->assertNotFound();
        $this->post(route('users.clave.store', $this->A['pastor']))->assertForbidden();
        $this->post(route('users.clave.store', $this->crearUsuario('admin')))->assertNotFound();

        $this->iniciarSesionComo($this->A['liderUser']);
        $this->post(route('users.clave.store', $this->A['liderUser']))->assertForbidden();

        $this->assertSame(0, RegistroClave::withoutGlobalScopes()->count());
    }

    public function test_el_administrador_puede_con_cualquier_usuario(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('users.clave.store', $this->B['liderUser']))->assertOk()->assertSee('https://wa.me/573205559999?text=', false);
        $this->post(route('users.clave.store', $this->B['pastor']->forceFill(['celular' => '3001112233'])))->assertOk();
    }

    public function test_sin_celular_se_rechaza_y_se_puede_completar_en_la_edicion(): void
    {
        $this->A['liderUser']->forceFill(['celular' => null])->save();
        $hash = $this->A['liderUser']->password;
        $this->iniciarSesionComo($this->A['pastor']);

        $this->post(route('users.clave.store', $this->A['liderUser']))->assertRedirect()->assertSessionHas('error');
        $this->assertSame($hash, $this->A['liderUser']->fresh()->password);

        $this->put(route('users.update', $this->A['liderUser']), ['name' => $this->A['liderUser']->name, 'email' => $this->A['liderUser']->email, 'celular' => '3004445566', 'role' => 'lider_celula'])
            ->assertRedirect(route('users.index'));
        $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk();
    }

    public function test_el_listado_muestra_la_accion_con_confirmacion_y_el_estado_de_la_clave(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk();

        $html = $this->get(route('users.index'))->assertOk()->getContent();

        $this->assertStringContainsString(route('users.clave.store', $this->A['liderUser']), $html);
        $this->assertStringContainsString('data-confirm', $html);
        $this->assertStringContainsString('Pendiente de envío', $html);
        $this->assertStringContainsString('Sin clave temporal', $html);
        $this->assertStringContainsString('Enviar nueva clave', $html);
    }

    public function test_el_formulario_de_edicion_ofrece_enviar_clave_y_no_pide_contrasena(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $html = $this->get(route('users.edit', $this->A['liderUser']))->assertOk()->getContent();

        $this->assertStringContainsString('Enviar nueva clave', $html);
        $this->assertStringContainsString(route('users.clave.store', $this->A['liderUser']), $html);
        $this->assertStringNotContainsString('name="password', $html);
    }
}
