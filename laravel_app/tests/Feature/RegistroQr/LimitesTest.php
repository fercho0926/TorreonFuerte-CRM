<?php

namespace Tests\Feature\RegistroQr;

use Illuminate\Support\Facades\RateLimiter;

class LimitesTest extends RegistroQrTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('registro-buscar:127.0.0.1');
    }

    public function test_mas_de_30_busquedas_por_minuto_responden_429_en_json(): void
    {
        $token = $this->token('evento');

        for ($i = 0; $i < 30; $i++) {
            $this->postJson(route('registro.buscar', $token), ['celular' => '3170000000'])->assertOk();
        }

        $this->postJson(route('registro.buscar', $token), ['celular' => '3170000000'])
            ->assertStatus(429)
            ->assertJsonPath('estado', 'limite')
            ->assertHeader('Retry-After');
    }

    public function test_mas_de_10_envios_por_celular_en_la_ventana_responden_429(): void
    {
        $token = $this->token('evento');
        RateLimiter::clear('registro-envio:127.0.0.1:3105559999');

        // El primero registra; los demás chocan con «ya estás registrado». Todos cuentan para el límite.
        $this->post(route('registro.store', $token), $this->datosNuevos())->assertRedirect();
        for ($i = 0; $i < 9; $i++) {
            $this->post(route('registro.store', $token), $this->datosNuevos())->assertSessionHasErrors('celular');
        }

        $this->post(route('registro.store', $token), $this->datosNuevos())->assertStatus(429);
    }

    public function test_los_errores_de_validacion_de_la_precarga_responden_en_json(): void
    {
        $this->postJson(route('registro.buscar', $this->token('evento')), ['celular' => '123'])
            ->assertStatus(422)->assertJsonValidationErrors('celular');
    }
}
