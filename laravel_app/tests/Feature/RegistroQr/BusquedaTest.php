<?php

namespace Tests\Feature\RegistroQr;

use App\Models\Persona;
use App\Models\RegistroQr;

class BusquedaTest extends RegistroQrTestCase
{
    private function personaDeA(array $atributos = []): Persona
    {
        return Persona::factory()->deSede($this->A['sede']->id)->create(array_merge([
            'nombres' => 'Lucía', 'apellidos' => 'Torres', 'celular' => '3004445555',
            'numero_documento' => '1010101010', 'tipo_documento' => 'CC', 'fecha_nacimiento' => '1988-02-03',
            'email' => 'secreto@example.com', 'direccion' => 'Calle Secreta 1',
        ], $atributos));
    }

    public function test_busca_por_celular_aunque_este_guardado_con_formato(): void
    {
        $this->personaDeA(['celular' => '300 444-5555']);

        $this->postJson(route('registro.buscar', $this->token('evento')), ['celular' => '+57 3004445555'])
            ->assertOk()->assertJsonPath('estado', 'existente');

        $this->postJson(route('registro.buscar', $this->token('evento')), ['celular' => '(300) 444 5555'])
            ->assertOk()->assertJsonPath('estado', 'existente')->assertJsonPath('nombres', 'Lucía');
    }

    public function test_busca_por_cedula_y_devuelve_solo_los_datos_minimos(): void
    {
        $this->personaDeA();

        $respuesta = $this->postJson(route('registro.buscar', $this->token('evento')), ['numero_documento' => '1010101010'])
            ->assertOk()
            ->assertExactJson([
                'estado' => 'existente', 'nombres' => 'Lucía', 'apellidos' => 'Torres',
                'fecha_nacimiento' => '1988-02-03', 'celular' => '3004445555',
            ]);

        $this->assertStringNotContainsString('secreto@example.com', $respuesta->getContent());
        $this->assertStringNotContainsString('Calle Secreta', $respuesta->getContent());
        $this->assertStringNotContainsString('1010101010', $respuesta->getContent());
    }

    public function test_una_cedula_inexistente_continua_como_persona_nueva(): void
    {
        $this->postJson(route('registro.buscar', $this->token('evento')), ['numero_documento' => '999'])
            ->assertOk()->assertExactJson(['estado' => 'nueva']);
    }

    public function test_cedula_y_celular_de_personas_distintas_es_un_conflicto_y_no_guarda_nada(): void
    {
        $this->personaDeA();
        $otra = $this->personaDeA(['celular' => '3009998888', 'numero_documento' => '2020202020', 'nombres' => 'Otra']);
        $token = $this->token('evento');

        $this->postJson(route('registro.buscar', $token), ['celular' => '3009998888', 'numero_documento' => '1010101010'])
            ->assertOk()->assertJsonPath('estado', 'conflicto');

        $this->post(route('registro.store', $token), $this->datosNuevos(['celular' => '3009998888', 'numero_documento' => '1010101010', 'tipo_documento' => 'CC']))
            ->assertSessionHasErrors('celular');

        $this->assertSame(0, RegistroQr::withoutGlobalScopes()->count());
        $this->assertSame('Otra', $otra->fresh()->nombres);
    }

    public function test_un_celular_repetido_en_dos_fichas_es_un_conflicto(): void
    {
        $this->personaDeA();
        $this->personaDeA(['numero_documento' => '3030303030', 'nombres' => 'Gemela']);

        $this->postJson(route('registro.buscar', $this->token('evento')), ['celular' => '3004445555'])
            ->assertOk()->assertJsonPath('estado', 'conflicto');
    }

    public function test_una_cedula_distinta_a_la_de_la_ficha_del_celular_es_un_conflicto(): void
    {
        $this->personaDeA();

        $this->postJson(route('registro.buscar', $this->token('evento')), ['celular' => '3004445555', 'numero_documento' => '777'])
            ->assertOk()->assertJsonPath('estado', 'conflicto');
    }

    public function test_sin_javascript_el_paso_1_devuelve_el_paso_2_con_los_datos_precargados(): void
    {
        $this->personaDeA();

        $this->post(route('registro.buscar', $this->token('evento')), ['celular' => '3004445555'])
            ->assertOk()
            ->assertSee('Encontramos tus datos')
            ->assertSee('value="Lucía"', false)
            ->assertDontSee('secreto@example.com');

        $this->post(route('registro.buscar', $this->token('evento')), ['celular' => '3170000000'])
            ->assertOk()->assertSee('Completa tus datos');

        $this->post(route('registro.buscar', $this->token('evento')), [])->assertSessionHasErrors('celular');
    }
}
