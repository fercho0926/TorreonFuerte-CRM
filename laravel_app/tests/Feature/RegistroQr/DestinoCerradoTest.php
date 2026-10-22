<?php

namespace Tests\Feature\RegistroQr;

use App\Models\Persona;
use App\Models\RegistroQr;
use PHPUnit\Framework\Attributes\DataProvider;

class DestinoCerradoTest extends RegistroQrTestCase
{
    public function test_un_token_inexistente_responde_enlace_no_valido(): void
    {
        $this->get(route('registro.show', str_repeat('x', 32)))->assertNotFound();
        $this->get(route('registro.show', 'corto'))->assertNotFound();
        $this->postJson(route('registro.buscar', str_repeat('x', 32)), ['celular' => '3105559999'])->assertNotFound();
    }

    #[DataProvider('destinos')]
    public function test_un_registro_desactivado_se_ve_cerrado_y_no_inscribe(string $destino): void
    {
        $this->A[$destino]->update(['registro_abierto' => false]);
        $token = $this->token($destino);

        $this->get(route('registro.show', $token))->assertOk()->assertSee('Registro cerrado');
        $this->post(route('registro.store', $token), $this->datosNuevos())->assertOk()->assertSee('Registro cerrado');

        $this->assertSame(0, RegistroQr::withoutGlobalScopes()->count());
        $this->assertNull(Persona::withoutGlobalScopes()->where('celular', '3105559999')->first());
    }

    public function test_un_evento_cancelado_o_realizado_y_una_celula_inactiva_se_cierran_solos(): void
    {
        $this->A['evento']->update(['estado' => 'cancelado']);
        $this->A['encuentro']->update(['estado' => 'realizado']);
        $this->A['celula']->update(['estado' => 'inactiva']);

        foreach (['evento', 'encuentro', 'celula'] as $destino) {
            $this->get(route('registro.show', $this->token($destino)))->assertOk()->assertSee('Registro cerrado');
        }
    }

    public function test_un_enlace_regenerado_deja_de_funcionar(): void
    {
        $viejo = $this->token('evento');
        $this->A['evento']->fresh()->regenerarToken();

        $this->get(route('registro.show', $viejo))->assertNotFound();
        $this->get(route('registro.show', $this->token('evento')))->assertOk();
    }
}
