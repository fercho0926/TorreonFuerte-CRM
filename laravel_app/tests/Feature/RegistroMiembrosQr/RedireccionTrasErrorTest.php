<?php

namespace Tests\Feature\RegistroMiembrosQr;

use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\TestCase;

/**
 * RF-006: igual que en el QR de eventos/encuentros/células, un error de validación que solo detecta
 * el servidor no debe terminar en un `GET` hacia la ruta de «buscar» (solo-POST → 405).
 */
class RedireccionTrasErrorTest extends TestCase
{
    use ConSedes, RefreshDatabase;

    private Sede $sede;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sede = $this->crearSede(['nombre' => 'Iglesia Central']);
    }

    public function test_un_error_que_solo_detecta_el_servidor_no_redirige_a_la_ruta_de_buscar(): void
    {
        $token = $this->sede->registro_token;

        $respuesta = $this->from(route('registro-sede.buscar', $token))
            ->post(route('registro-sede.store', $token), [
                'celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez',
                'fecha_nacimiento' => '1990-05-04', 'estado_inicial' => 'visitante',
                'numero_documento' => '123456', // sin tipo_documento: solo el servidor lo exige
            ]);

        $respuesta->assertSessionHasErrors('tipo_documento');
        $destino = $respuesta->headers->get('Location');

        $this->assertNotNull($destino);
        $this->assertStringNotContainsString('/buscar', $destino, 'No debe redirigir a la ruta de solo-POST.');
        $this->get($destino)->assertOk();
    }
}
