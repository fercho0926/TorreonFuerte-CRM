<?php

namespace Tests\Feature\RegistroQr;

use App\Models\Persona;
use PHPUnit\Framework\Attributes\DataProvider;

class ListaRegistradosTest extends RegistroQrTestCase
{
    #[DataProvider('destinos')]
    public function test_la_ficha_lista_a_los_registrados_con_su_origen_fecha_y_hora(string $destino): void
    {
        $existente = Persona::factory()->deSede($this->A['sede']->id)->create([
            'nombres' => 'Lucía', 'apellidos' => 'Torres', 'celular' => '3004445555',
        ]);
        $token = $this->token($destino);

        $this->post(route('registro.store', $token), $this->datosNuevos(['nombres' => 'Nueva', 'apellidos' => 'Persona']));
        $this->post(route('registro.store', $token), $this->datosNuevos(['celular' => '3004445555', 'nombres' => 'Lucía', 'apellidos' => 'Torres']));

        $nueva = Persona::withoutGlobalScopes()->where('celular', '3105559999')->firstOrFail();

        $this->iniciarSesionComo($this->A['pastor']);
        $ruta = ['evento' => 'eventos.show', 'encuentro' => 'encuentros.show', 'celula' => 'celulas.show'][$destino];

        $this->get(route($ruta, $this->A[$destino]))
            ->assertOk()
            ->assertSee('Registrados por QR')
            ->assertSee('Nuevo miembro')
            ->assertSee('Ya existía')
            ->assertSee(now()->format('Y-m-d'))
            ->assertSee(route('personas.show', $nueva), false)
            ->assertSee(route('personas.show', $existente), false);

        // El miembro creada por el QR aparece en Miembros de la sede del destino.
        $this->get(route('personas.index'))->assertOk();
        $this->assertSame($this->A['sede']->id, $nueva->sede_id);
    }

    public function test_sin_registros_se_muestra_el_estado_vacio(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('eventos.show', $this->A['evento']))->assertOk()->assertSee('Todavía nadie se ha registrado con el QR.');
    }

    public function test_otra_sede_no_ve_los_registros(): void
    {
        $this->post(route('registro.store', $this->token('evento')), $this->datosNuevos(['nombres' => 'SoloDeAlfa']));

        $this->iniciarSesionComo($this->B['pastor']);
        $this->get(route('eventos.show', $this->A['evento']))->assertNotFound();
        $this->get(route('eventos.show', $this->B['evento']))->assertOk()->assertDontSee('SoloDeAlfa');
    }
}
