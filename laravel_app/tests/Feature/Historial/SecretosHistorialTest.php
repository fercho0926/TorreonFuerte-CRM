<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\RegistroClave;
use App\Models\User;
use App\Services\ClavesTemporales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Ninguna entrada guarda claves, tokens ni códigos (spec 015, RF-007, CE-005). */
class SecretosHistorialTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_claves_y_tokens_solo_dicen_que_cambiaron(): void
    {
        $this->armarDosSedes();
        $admin = $this->comoAdminEnSede($this->A['sede']);
        $usuario = $this->A['liderUser'];
        $usuario->persona->update(['celular' => '3005556666']);
        $secretos = [];

        // Clave temporal generada por el administrador.
        $clave = app(ClavesTemporales::class)->generar($usuario, RegistroClave::MOTIVO_REENVIO, $admin)['clave'];
        $secretos[] = $clave;
        $secretos[] = str_replace('-', '', $clave);

        // Contraseña elegida por el propio usuario.
        $propio = User::factory()->create(['role' => 'user', 'sede_id' => $this->A['sede']->id, 'password' => 'password']);
        $this->iniciarSesionComo($propio);
        $this->put(route('cuenta.clave.update'), ['password_actual' => 'password', 'password' => 'mi-secreta-9', 'password_confirmation' => 'mi-secreta-9'])->assertSessionHasNoErrors();
        $secretos[] = 'mi-secreta-9';

        // Tokens de QR regenerados.
        $this->comoAdminEnSede($this->A['sede']);
        $secretos[] = $this->A['evento']->registro_token;
        $this->post(route('registro-qr.regenerar', ['evento', $this->A['evento']->id]));
        $secretos[] = $this->A['evento']->fresh()->registro_token;
        $secretos[] = $this->A['sede']->fresh()->registro_token;
        $this->post(route('registro-miembros.regenerar', $this->A['sede']));
        $secretos[] = $this->A['sede']->fresh()->registro_token;
        $secretos[] = $usuario->fresh()->getRawOriginal('password');
        $secretos[] = $propio->fresh()->getRawOriginal('password');

        $todo = CambioHistorial::query()->get()->map(fn ($c) => json_encode($c->getAttributes(), JSON_UNESCAPED_UNICODE))->join("\n");
        foreach (array_filter($secretos) as $secreto) {
            $this->assertStringNotContainsString($secreto, $todo, 'un secreto quedó en el historial');
        }

        $this->assertTrue(CambioHistorial::query()->de('usuario', $usuario->id)->where('accion', 'clave_generada')->exists());
        $this->assertTrue(CambioHistorial::query()->de('usuario', $propio->id)->where('accion', 'clave_cambiada')->exists());

        $html = $this->get(route('users.edit', [$usuario, 'pestana' => 'historico']))->getContent();
        $this->assertStringContainsString('Clave generada', $html);
        $this->assertStringContainsString('Clave cambiada', $html, 'el campo contraseña se muestra como «Clave cambiada»');
    }

    public function test_ningun_campo_secreto_tiene_valor_guardado(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
        $this->post(route('registro-qr.regenerar', ['celula', $this->A['celula']->id]));

        foreach (CambioHistorial::query()->get() as $entrada) {
            foreach ([$entrada->antes ?? [], $entrada->despues ?? []] as $valores) {
                foreach (array_keys($valores) as $campo) {
                    $this->assertDoesNotMatchRegularExpression('/^(password|remember_token)$|^(clave|token|codigo)|_token$|_hash$/', (string) $campo);
                }
            }
            foreach ($entrada->cambios as $cambio) {
                if ($cambio['secreto']) {
                    $this->assertSame(['', ''], [$cambio['antes'], $cambio['despues']]);
                }
            }
        }
    }
}
