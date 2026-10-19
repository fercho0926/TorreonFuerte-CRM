<?php

namespace Tests\Feature\Claves;

use App\Models\RegistroClave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 5: seguimiento y auditoría de las claves, sin secretos (FR-012, FR-013, SC-005, SC-007). */
class SeguimientoClavesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->A['liderUser']->forceFill(['celular' => '3105559999'])->save();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function generar(): RegistroClave
    {
        $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk();

        return RegistroClave::withoutGlobalScopes()->where('user_id', $this->A['liderUser']->id)->latest('id')->firstOrFail();
    }

    public function test_marcar_como_enviada_pasa_el_registro_de_pendiente_a_enviada(): void
    {
        $registro = $this->generar();
        $this->assertSame(RegistroClave::PENDIENTE_ENVIO, $registro->estado);

        $this->post(route('claves.enviada', $registro))->assertRedirect(route('users.index'));

        $registro->refresh();
        $this->assertSame(RegistroClave::ENVIADA, $registro->estado);
        $this->assertNotNull($registro->enviada_en);
        $this->get(route('users.index'))->assertSee('Enviada');
    }

    public function test_solo_quien_puede_gestionar_al_usuario_marca_el_envio(): void
    {
        $registro = $this->generar();

        $otroLider = $this->crearUsuario('lider_celula', null, $this->A['sede']->id);
        $this->iniciarSesionComo($otroLider);
        $this->post(route('claves.enviada', $registro))->assertForbidden();

        $this->iniciarSesionComo($this->B['pastor']);
        $this->post(route('claves.enviada', $registro))->assertNotFound();

        $this->assertSame(RegistroClave::PENDIENTE_ENVIO, $registro->fresh()->estado);
    }

    public function test_marcar_dos_veces_o_una_ya_usada_no_cambia_nada(): void
    {
        $registro = $this->generar();
        $this->post(route('claves.enviada', $registro));
        $registro->forceFill(['estado' => RegistroClave::USADA])->save();

        $this->post(route('claves.enviada', $registro))->assertRedirect(route('users.index'));

        $this->assertSame(RegistroClave::USADA, $registro->fresh()->estado);
    }

    public function test_una_clave_vencida_sin_usar_se_muestra_vencida_y_se_puede_generar_otra(): void
    {
        $registro = $this->generar();
        $this->travel(25)->hours();

        $this->assertSame(RegistroClave::VENCIDA, $registro->fresh()->estadoVigente());
        $this->get(route('users.index'))->assertSee('Vencida');
        $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk();
    }

    public function test_el_registro_guarda_quien_solicito_quien_preparo_motivo_y_fechas(): void
    {
        $registro = $this->generar();

        $this->assertSame($this->A['pastor']->id, $registro->preparada_por);
        $this->assertNull($registro->solicitada_por);
        $this->assertSame('reenvio', $registro->motivo);
        $this->assertSame($this->A['sede']->id, $registro->sede_id);
        $this->assertTrue($registro->vence_en->isFuture());
    }

    public function test_ninguna_clave_emitida_queda_en_la_base_ni_en_el_log(): void
    {
        $registroLog = storage_path('logs/laravel.log');
        $tamanoAntes = File::exists($registroLog) ? filesize($registroLog) : 0;

        $contenido = $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk()->getContent();
        preg_match('/id="clave-temporal">([A-Z2-9]{5}-[A-Z2-9]{5})</', $contenido, $m);
        $this->assertNotEmpty($m);
        $clave = $m[1];
        $sinGuion = str_replace('-', '', $clave);

        foreach (['users', 'registros_clave', 'solicitudes_clave', 'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs'] as $tabla) {
            if (!\Schema::hasTable($tabla)) {
                continue;
            }
            $volcado = json_encode(DB::table($tabla)->get());
            $this->assertStringNotContainsString($clave, $volcado, "{$tabla} contiene la clave");
            $this->assertStringNotContainsString($sinGuion, $volcado, "{$tabla} contiene la clave sin guion");
        }

        $nuevoLog = File::exists($registroLog) ? substr(File::get($registroLog), $tamanoAntes) : '';
        $this->assertStringNotContainsString($sinGuion, $nuevoLog);
        $this->assertStringNotContainsString($clave, $nuevoLog);

        // Ninguna pantalla posterior la repite.
        $this->get(route('users.index'))->assertDontSee($sinGuion)->assertDontSee($clave);
        $this->get(route('solicitudes-clave.index'))->assertDontSee($sinGuion);
    }

    public function test_el_unico_lugar_donde_se_ve_la_clave_es_la_respuesta_que_la_genera(): void
    {
        $this->post(route('users.clave.store', $this->A['liderUser']))->assertOk()->assertSee('Esta clave se muestra una sola vez');

        $this->assertSame(1, RegistroClave::withoutGlobalScopes()->count());
        $this->assertTrue(User::withoutGlobalScopes()->find($this->A['liderUser']->id)->debe_cambiar_clave);
    }
}
