<?php

namespace Tests\Feature\RegistroQr;

use App\Models\Persona;
use App\Models\RegistroQr;
use PHPUnit\Framework\Attributes\DataProvider;

class FlujoExistenteTest extends RegistroQrTestCase
{
    private function persona(array $atributos = []): Persona
    {
        return Persona::factory()->deSede($this->A['sede']->id)->create(array_merge([
            'nombres' => 'Lucía', 'apellidos' => 'Torres', 'celular' => '3004445555',
            'numero_documento' => '1010101010', 'tipo_documento' => 'CC', 'fecha_nacimiento' => '1988-02-03',
        ], $atributos));
    }

    #[DataProvider('destinos')]
    public function test_la_persona_existente_se_inscribe_sin_crear_otra_ni_cambiar_sus_datos(string $destino): void
    {
        $persona = $this->persona(['estado' => 'visitante']);
        $antes = Persona::withoutGlobalScopes()->count();
        $token = $this->token($destino);

        $this->post(route('registro.store', $token), $this->datosNuevos([
            'celular' => '3004445555', 'nombres' => 'Lucia', 'apellidos' => 'Torres', 'fecha_nacimiento' => '1988-02-03',
        ]))->assertRedirect(route('registro.listo', $token));

        $this->assertSame($antes, Persona::withoutGlobalScopes()->count());
        $this->assertSame('Lucía', $persona->fresh()->nombres);

        $inscrita = match ($destino) {
            'evento' => $this->A['evento']->inscripciones()->withoutGlobalScopes()->where('persona_id', $persona->id)->count(),
            'encuentro' => $this->A['encuentro']->personas()->withoutGlobalScopes()->whereKey($persona->id)->count(),
            'celula' => $this->A['celula']->miembros()->withoutGlobalScopes()->whereKey($persona->id)->count(),
        };
        $this->assertSame(1, $inscrita);

        $registro = RegistroQr::withoutGlobalScopes()->firstOrFail();
        $this->assertSame('existente', $registro->resultado);
        $this->assertSame($persona->id, $registro->persona_id);
        $this->assertSame($destino === 'celula' ? 'celula' : 'visitante', $persona->fresh()->estado);
    }

    public function test_las_diferencias_escritas_quedan_como_nota_y_no_cambian_la_ficha(): void
    {
        $persona = $this->persona();

        $this->post(route('registro.store', $this->token('evento')), $this->datosNuevos([
            'celular' => '3004445555', 'nombres' => 'Lucy', 'apellidos' => 'Torres', 'fecha_nacimiento' => '1988-02-04',
        ]));

        $nota = RegistroQr::withoutGlobalScopes()->firstOrFail()->nota_correccion;
        $this->assertStringContainsString('Nombres', $nota);
        $this->assertStringContainsString('Lucy', $nota);
        $this->assertStringContainsString('Fecha de nacimiento', $nota);
        $this->assertSame('Lucía', $persona->fresh()->nombres);
        $this->assertSame('1988-02-03', $persona->fresh()->fecha_nacimiento->toDateString());
    }

    public function test_sin_diferencias_no_hay_nota(): void
    {
        $this->persona();

        $this->post(route('registro.store', $this->token('evento')), $this->datosNuevos([
            'celular' => '3004445555', 'nombres' => 'Lucía', 'apellidos' => 'Torres', 'fecha_nacimiento' => '1988-02-03',
        ]));

        $this->assertNull(RegistroQr::withoutGlobalScopes()->firstOrFail()->nota_correccion);
    }

    public function test_una_persona_inscrita_manualmente_no_se_vuelve_a_inscribir(): void
    {
        $this->persona();
        $token = $this->token('evento');
        $datos = $this->datosNuevos(['celular' => '3004445555', 'nombres' => 'Lucía', 'apellidos' => 'Torres', 'fecha_nacimiento' => '1988-02-03']);

        $this->post(route('registro.store', $token), $datos)->assertRedirect();
        $this->post(route('registro.store', $token), $datos)->assertSessionHasErrors('celular');

        $this->assertSame(1, $this->A['evento']->inscripciones()->withoutGlobalScopes()->count() - 1); // -1: la inscripción del escenario
    }

    public function test_una_persona_ya_miembro_de_la_celula_no_se_duplica(): void
    {
        $persona = $this->persona();
        $this->A['celula']->miembros()->attach($persona->id);

        $this->post(route('registro.store', $this->token('celula')), $this->datosNuevos([
            'celular' => '3004445555', 'nombres' => 'Lucía', 'apellidos' => 'Torres', 'fecha_nacimiento' => '1988-02-03',
        ]))->assertSessionHasErrors('celular');

        $this->assertSame(1, $this->A['celula']->miembros()->withoutGlobalScopes()->whereKey($persona->id)->count());
        $this->assertSame(0, RegistroQr::withoutGlobalScopes()->count());
    }

    public function test_una_persona_inactiva_o_eliminada_queda_pendiente_sin_inscribirse(): void
    {
        $inactiva = $this->persona(['estado' => 'inactivo', 'inactivado_en' => now()]);
        $eliminada = $this->persona(['celular' => '3112223333', 'numero_documento' => '4040404040']);
        $eliminada->delete();
        $antes = Persona::withoutGlobalScopes()->withTrashed()->count();
        $token = $this->token('evento');

        $this->post(route('registro.store', $token), $this->datosNuevos(['celular' => '3004445555']))->assertRedirect();
        $this->post(route('registro.store', $token), $this->datosNuevos(['celular' => '3112223333']))->assertRedirect();

        $this->assertSame($antes, Persona::withoutGlobalScopes()->withTrashed()->count());
        $this->assertSame(['pendiente', 'pendiente'], RegistroQr::withoutGlobalScopes()->pluck('resultado')->all());
        $this->assertSame(1, $this->A['evento']->inscripciones()->withoutGlobalScopes()->count(), 'Solo la inscripción del escenario');
        $this->assertSame('inactivo', $inactiva->fresh()->estado);
        $this->get(route('registro.listo', $token))->assertOk();
    }
}
