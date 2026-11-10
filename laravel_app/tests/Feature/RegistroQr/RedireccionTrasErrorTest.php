<?php

namespace Tests\Feature\RegistroQr;

/**
 * RF-006: un error de validación que solo detecta el servidor (no el navegador) no debe terminar
 * en un `GET` hacia la ruta de «buscar», que solo acepta `POST` (eso produce un 405).
 */
class RedireccionTrasErrorTest extends RegistroQrTestCase
{
    public function test_un_error_que_solo_detecta_el_servidor_no_redirige_a_la_ruta_de_buscar(): void
    {
        $token = $this->token('evento');

        // "numero_documento" sin "tipo_documento" no lo bloquea el navegador (sin regla equivalente ahí),
        // pero el servidor sí lo exige (tipo_documento.required_with:numero_documento en RegistroQrRequest).
        $respuesta = $this->from(route('registro.buscar', $token))
            ->post(route('registro.store', $token), $this->datosNuevos(['numero_documento' => '123456']));

        $respuesta->assertSessionHasErrors('tipo_documento');
        $destino = $respuesta->headers->get('Location');

        $this->assertNotNull($destino);
        $this->assertStringNotContainsString('/buscar', $destino, 'No debe redirigir a la ruta de solo-POST.');

        // La redirección debe aterrizar en una ruta que sí acepta GET (el paso 1).
        $this->get($destino)->assertOk();
    }
}
