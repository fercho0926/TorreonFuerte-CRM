<?php

namespace Tests\Feature\RegistroQr;

use App\Models\Persona;
use App\Models\RegistroQr;
use PHPUnit\Framework\Attributes\DataProvider;

class FlujoNuevaPersonaTest extends RegistroQrTestCase
{
    #[DataProvider('destinos')]
    public function test_el_formulario_publico_se_abre_sin_sesion(string $destino): void
    {
        $this->get(route('registro.show', $this->token($destino)))
            ->assertOk()
            ->assertSee($this->A[$destino]->nombre)
            ->assertHeader('Cache-Control');
    }

    #[DataProvider('destinos')]
    public function test_un_celular_nuevo_crea_la_persona_en_la_sede_del_destino_y_la_inscribe(string $destino): void
    {
        $token = $this->token($destino);

        $this->post(route('registro.store', $token), $this->datosNuevos(['celular' => '310 555 9999']))
            ->assertRedirect(route('registro.listo', $token));

        $persona = Persona::withoutGlobalScopes()->where('celular', '3105559999')->firstOrFail();
        $this->assertSame($this->A['sede']->id, $persona->sede_id);
        $this->assertSame($destino === 'celula' ? 'celula' : 'visitante', $persona->estado);
        $this->assertNull($persona->lider_id);

        $inscrita = match ($destino) {
            'evento' => $this->A['evento']->inscripciones()->withoutGlobalScopes()->where('persona_id', $persona->id)->exists(),
            'encuentro' => $this->A['encuentro']->personas()->withoutGlobalScopes()->whereKey($persona->id)->exists(),
            'celula' => $this->A['celula']->miembros()->withoutGlobalScopes()->whereKey($persona->id)->exists(),
        };
        $this->assertTrue($inscrita);

        $registro = RegistroQr::withoutGlobalScopes()->firstOrFail();
        $this->assertSame('nueva', $registro->resultado);
        $this->assertSame($persona->id, $registro->persona_id);
        $this->assertSame($this->A['sede']->id, $registro->sede_id);

        $this->get(route('registro.listo', $token))->assertOk()->assertSee('quedaste registrado');
    }

    public function test_los_datos_obligatorios_y_su_formato_se_validan(): void
    {
        $token = $this->token('evento');
        $largo = str_repeat('a', 101);

        foreach ([
            'celular' => ['celular' => '12345'],
            'nombres' => ['nombres' => ''],
            'apellidos' => ['apellidos' => $largo],
            'fecha_nacimiento' => ['fecha_nacimiento' => ''],
        ] as $campo => $cambio) {
            $this->post(route('registro.store', $token), $this->datosNuevos($cambio))->assertSessionHasErrors($campo);
        }

        $this->post(route('registro.store', $token), $this->datosNuevos(['fecha_nacimiento' => now()->addDay()->toDateString()]))->assertSessionHasErrors('fecha_nacimiento');
        $this->post(route('registro.store', $token), $this->datosNuevos(['fecha_nacimiento' => '1899-12-31']))->assertSessionHasErrors('fecha_nacimiento');
        $this->post(route('registro.store', $token), $this->datosNuevos(['numero_documento' => '123456']))->assertSessionHasErrors('tipo_documento');

        $this->assertSame(0, RegistroQr::withoutGlobalScopes()->count());
        $this->assertNull(Persona::withoutGlobalScopes()->where('celular', '3105559999')->first());
    }

    public function test_la_cedula_es_opcional_y_se_guarda_con_su_tipo(): void
    {
        $token = $this->token('evento');

        $this->post(route('registro.store', $token), $this->datosNuevos(['tipo_documento' => 'CC', 'numero_documento' => '9988776655']))
            ->assertRedirect();

        $persona = Persona::withoutGlobalScopes()->where('numero_documento', '9988776655')->firstOrFail();
        $this->assertSame('CC', $persona->tipo_documento);
    }

    #[DataProvider('destinos')]
    public function test_registrarse_dos_veces_no_duplica_nada(string $destino): void
    {
        $token = $this->token($destino);
        $this->post(route('registro.store', $token), $this->datosNuevos());

        $this->post(route('registro.store', $token), $this->datosNuevos())->assertSessionHasErrors('celular');

        $this->assertSame(1, RegistroQr::withoutGlobalScopes()->count());
        $this->assertSame(1, Persona::withoutGlobalScopes()->where('celular', '3105559999')->count());
    }
}
