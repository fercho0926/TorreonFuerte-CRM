<?php

namespace Tests\Feature\Claves;

use App\Models\RegistroClave;
use App\Models\User;
use App\Services\ClavesTemporales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class ClavesTemporalesServiceTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private ClavesTemporales $servicio;

    private User $pastor;

    private User $objetivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = app(ClavesTemporales::class);
        $sede = $this->crearSede(['nombre' => 'Cocorná']);
        $this->pastor = $this->crearUsuario('pastor', null, $sede->id);
        $this->objetivo = $this->crearUsuario('lider_celula', null, $sede->id);
        $this->objetivo->persona->update(['celular' => '310 555 9999']);
    }

    public function test_la_clave_tiene_formato_legible_y_alfabeto_sin_caracteres_ambiguos(): void
    {
        $generada = $this->servicio->generar($this->objetivo, RegistroClave::MOTIVO_ALTA, $this->pastor);

        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{5}-[A-HJ-NP-Z2-9]{5}$/', $generada['clave']);
        $this->assertStringNotContainsString('O', $generada['clave']);
        $this->assertStringNotContainsString('0', $generada['clave']);
    }

    public function test_dos_generaciones_dan_claves_distintas_y_la_anterior_deja_de_valer(): void
    {
        $primera = $this->servicio->generar($this->objetivo, RegistroClave::MOTIVO_ALTA, $this->pastor);
        $this->assertTrue(Hash::check($this->servicio->normalizar($primera['clave']), $this->objetivo->fresh()->password));

        $segunda = $this->servicio->generar($this->objetivo->fresh(), RegistroClave::MOTIVO_REENVIO, $this->pastor);

        $this->assertNotSame($primera['clave'], $segunda['clave']);
        $this->assertFalse(Hash::check($this->servicio->normalizar($primera['clave']), $this->objetivo->fresh()->password));
        $this->assertTrue(Hash::check($this->servicio->normalizar($segunda['clave']), $this->objetivo->fresh()->password));
        $this->assertSame(RegistroClave::REEMPLAZADA, $primera['registro']->fresh()->estado);
    }

    public function test_marca_la_cuenta_para_cambiar_la_clave_con_vigencia_de_24_horas(): void
    {
        $this->servicio->generar($this->objetivo, RegistroClave::MOTIVO_ALTA, $this->pastor);

        $user = $this->objetivo->fresh();
        $this->assertTrue($user->debe_cambiar_clave);
        $this->assertEqualsWithDelta(now()->addHours(24)->timestamp, $user->clave_vence_en->timestamp, 5);
        $this->assertFalse($user->claveTemporalVencida());
    }

    public function test_el_registro_guarda_responsables_motivo_y_ninguna_clave(): void
    {
        $generada = $this->servicio->generar($this->objetivo, RegistroClave::MOTIVO_REENVIO, $this->pastor, null, $this->pastor);

        $registro = $generada['registro']->fresh();
        $this->assertSame(RegistroClave::PENDIENTE_ENVIO, $registro->estado);
        $this->assertSame('reenvio', $registro->motivo);
        $this->assertSame($this->pastor->id, $registro->preparada_por);
        $this->assertSame($this->pastor->id, $registro->solicitada_por);
        $this->assertSame($this->objetivo->sede_id, $registro->sede_id);

        $fila = json_encode(\DB::table('registros_clave')->get());
        $this->assertStringNotContainsString($generada['clave'], $fila);
        $this->assertStringNotContainsString($this->servicio->normalizar($generada['clave']), $fila);
        $this->assertStringNotContainsString($generada['clave'], json_encode(\DB::table('users')->where('id', $this->objetivo->id)->get()));
    }

    public function test_sin_celular_se_rechaza_y_no_cambia_la_contrasena(): void
    {
        $this->objetivo->persona->update(['celular' => null]);
        $hashAntes = $this->objetivo->fresh()->password;

        try {
            $this->servicio->generar($this->objetivo->fresh(), RegistroClave::MOTIVO_ALTA, $this->pastor);
            $this->fail('Debió rechazarse');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('celular', $e->errors());
        }

        $this->assertSame($hashAntes, $this->objetivo->fresh()->password);
        $this->assertSame(0, RegistroClave::withoutGlobalScopes()->count());
    }

    public function test_el_mensaje_y_el_enlace_de_whatsapp(): void
    {
        $generada = $this->servicio->generar($this->objetivo, RegistroClave::MOTIVO_ALTA, $this->pastor);
        $mensaje = $this->servicio->mensaje($this->objetivo->fresh(), $generada['clave']);
        $enlace = $this->servicio->enlaceWhatsapp($this->objetivo->fresh(), $mensaje);

        $this->assertStringContainsString($generada['clave'], $mensaje);
        $this->assertStringContainsString('24 horas', $mensaje);
        $this->assertStringContainsString('Cocorná', $mensaje);
        $this->assertStringNotContainsString('http', $mensaje);
        $this->assertStringStartsWith('https://wa.me/573105559999?text=', $enlace);
        $this->assertStringContainsString(rawurlencode('Clave temporal: '.$generada['clave']), $enlace);
    }

    public function test_normalizar_ignora_guion_espacios_y_mayusculas(): void
    {
        $this->assertSame('ABCDEFGHJK', $this->servicio->normalizar(' abcde-fghjk '));
    }
}
