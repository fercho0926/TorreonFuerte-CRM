<?php

namespace Tests\Feature\Claves;

use App\Models\Persona;
use App\Models\RegistroClave;
use App\Models\User;
use App\Services\ClavesTemporales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 1: crear un usuario sin asignarle contraseña (FR-001 a FR-003, FR-014, FR-015). */
class CrearUsuarioSinClaveTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function datos(array $extra = []): array
    {
        return $extra + ['name' => 'Rosa Gómez', 'email' => 'rosa@iglesia.co', 'celular' => '310 555 9999', 'role' => 'lider_celula'];
    }

    public function test_el_formulario_no_pide_contrasena_y_si_celular(): void
    {
        $html = $this->get(route('users.create'))->assertOk()->getContent();

        $this->assertStringNotContainsString('name="password"', $html);
        $this->assertStringNotContainsString('password_confirmation', $html);
        $this->assertStringContainsString('name="celular"', $html);
        $this->assertStringContainsString('Crear y generar clave', $html);
    }

    public function test_al_guardar_se_muestra_la_clave_una_sola_vez_con_el_boton_de_whatsapp(): void
    {
        $respuesta = $this->post(route('users.store'), $this->datos());

        $respuesta->assertOk()
            ->assertSee('Clave generada')
            ->assertSee('Enviar por WhatsApp')
            ->assertSee('Copiar mensaje')
            ->assertSee('Ya la envié')
            ->assertSee('310 555 9999')
            ->assertSee('https://wa.me/573105559999?text=', false);
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));

        $usuario = User::withoutGlobalScopes()->where('email', 'rosa@iglesia.co')->firstOrFail();
        $this->assertSame($this->A['sede']->id, $usuario->sede_id);
        $this->assertTrue($usuario->debe_cambiar_clave);
        $this->assertNotNull($usuario->clave_vence_en);

        $registro = RegistroClave::withoutGlobalScopes()->where('user_id', $usuario->id)->firstOrFail();
        $this->assertSame('alta', $registro->motivo);
        $this->assertSame(RegistroClave::PENDIENTE_ENVIO, $registro->estado);
        $this->assertSame($this->A['pastor']->id, $registro->preparada_por);
        $this->assertNull($registro->solicitada_por);
    }

    public function test_la_clave_mostrada_es_la_unica_que_valida_y_no_queda_guardada_en_ningun_lugar(): void
    {
        $contenido = $this->post(route('users.store'), $this->datos())->getContent();

        preg_match('/id="clave-temporal">([A-Z2-9]{5}-[A-Z2-9]{5})</', $contenido, $m);
        $this->assertNotEmpty($m, 'la pantalla debe mostrar la clave');
        $clave = $m[1];

        $usuario = User::withoutGlobalScopes()->where('email', 'rosa@iglesia.co')->firstOrFail();
        $this->assertTrue(Hash::check(str_replace('-', '', $clave), $usuario->password));

        // Ni la clave ni su versión sin guion aparecen en ninguna tabla de datos.
        foreach (['users', 'registros_clave', 'solicitudes_clave', 'sessions', 'cache'] as $tabla) {
            $volcado = json_encode(DB::table($tabla)->get());
            $this->assertStringNotContainsString($clave, $volcado, "{$tabla} contiene la clave");
            $this->assertStringNotContainsString(str_replace('-', '', $clave), $volcado, "{$tabla} contiene la clave sin guion");
        }

        // El mensaje enlazado la contiene (solo en esta respuesta).
        $this->assertStringContainsString(rawurlencode('Clave temporal: '.$clave), $contenido);
    }

    public function test_recargar_la_pagina_no_vuelve_a_mostrar_la_clave(): void
    {
        $this->post(route('users.store'), $this->datos())->assertOk();

        // No hay ruta GET que la repita: la pantalla no se puede abrir de nuevo.
        $this->assertContains($this->get('/users/clave')->getStatusCode(), [404, 405]);
        $this->get(route('users.index'))->assertOk()->assertDontSee('Clave temporal:');
    }

    public function test_celular_faltante_o_invalido_se_rechaza_y_no_crea_la_cuenta(): void
    {
        foreach ([null, '', 'abc', '12345'] as $celular) {
            $this->post(route('users.store'), $this->datos(['celular' => $celular]))->assertSessionHasErrors('celular');
        }

        $this->assertSame(0, User::withoutGlobalScopes()->where('email', 'rosa@iglesia.co')->count());
        $this->assertSame(0, RegistroClave::withoutGlobalScopes()->count());
    }

    public function test_sin_celular_se_usa_el_de_la_persona_vinculada(): void
    {
        $persona = Persona::factory()->deSede($this->A['sede']->id)->create(['celular' => '3201112233']);

        $this->post(route('users.store'), $this->datos(['celular' => '', 'persona_id' => $persona->id]))->assertOk()->assertSee('320 111 2233');

        $this->assertSame('3201112233', User::withoutGlobalScopes()->where('email', 'rosa@iglesia.co')->value('celular'));
    }

    public function test_enviar_una_contrasena_ya_no_tiene_efecto(): void
    {
        $this->post(route('users.store'), $this->datos(['password' => 'secreto123', 'password_confirmation' => 'secreto123']))->assertOk();

        $usuario = User::withoutGlobalScopes()->where('email', 'rosa@iglesia.co')->firstOrFail();
        $this->assertFalse(Hash::check('secreto123', $usuario->password));
    }

    public function test_la_edicion_no_cambia_contrasenas_pero_permite_completar_el_celular(): void
    {
        $lider = $this->A['liderUser'];
        $hash = $lider->password;

        $html = $this->get(route('users.edit', $lider))->assertOk()->getContent();
        $this->assertStringNotContainsString('name="password"', $html);

        $this->put(route('users.update', $lider), $this->datos(['email' => $lider->email, 'password' => 'otra-clave-123', 'celular' => '3004445566']))
            ->assertRedirect(route('users.index'));

        $lider->refresh();
        $this->assertSame($hash, $lider->password);
        $this->assertSame('3004445566', $lider->celular);
    }

    public function test_el_administrador_elige_la_sede_y_crea_admins_sin_sede(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('users.store'), $this->datos(['email' => 'enb@iglesia.co', 'sede_id' => $this->B['sede']->id]))->assertOk();
        $this->assertSame($this->B['sede']->id, User::withoutGlobalScopes()->where('email', 'enb@iglesia.co')->value('sede_id'));

        $this->post(route('users.store'), $this->datos(['email' => 'adm@iglesia.co', 'role' => 'admin']))->assertOk()->assertSee('Enviar por WhatsApp');
        $this->assertNull(User::withoutGlobalScopes()->where('email', 'adm@iglesia.co')->value('sede_id'));
    }

    public function test_el_mensaje_va_en_espanol_con_sistema_sede_y_vigencia_sin_enlaces(): void
    {
        $this->post(route('users.store'), $this->datos())->assertOk();
        $usuario = User::withoutGlobalScopes()->where('email', 'rosa@iglesia.co')->firstOrFail();

        $mensaje = app(ClavesTemporales::class)->mensaje($usuario, 'ABCDE-FGHJK');

        $this->assertStringContainsString('Rosa Gómez', $mensaje);
        $this->assertStringContainsString(config('app.sistema'), $mensaje);
        $this->assertStringContainsString('Iglesia Alfa', $mensaje);
        $this->assertStringContainsString('24 horas', $mensaje);
        $this->assertStringNotContainsString('http', $mensaje);
    }
}
