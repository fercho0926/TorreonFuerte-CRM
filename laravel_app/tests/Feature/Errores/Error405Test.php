<?php

namespace Tests\Feature\Errores;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RF-001/RF-002: ningún visitante (anónimo o autenticado) debe ver trazas técnicas del servidor
 * cuando una ruta recibe un método HTTP que no soporta.
 */
class Error405Test extends TestCase
{
    use RefreshDatabase;

    public function test_un_metodo_no_soportado_en_una_ruta_publica_no_expone_informacion_tecnica(): void
    {
        config(['app.debug' => false]);

        // route('login.post') solo acepta POST; un GET debe ser un error amigable, nunca la página de depuración.
        $respuesta = $this->get(route('login.post'));

        $respuesta->assertStatus(405);
        $contenido = $respuesta->getContent();

        $this->assertStringNotContainsString('Exception trace', $contenido);
        $this->assertStringNotContainsString('vendor/laravel', $contenido);
        $this->assertStringNotContainsString('Symfony\\Component', $contenido);
        $this->assertStringNotContainsString('Headers', $contenido);
        $this->assertStringNotContainsString('XSRF-TOKEN', $contenido);

        // La vista propia (resources/views/errors/405.blade.php) se usa en vez del fallback genérico de Laravel.
        $this->assertStringContainsString('Esa acción no se puede hacer de esta forma', $contenido);
    }

    public function test_un_metodo_no_soportado_en_una_ruta_publica_de_registro_por_qr_tampoco_expone_informacion(): void
    {
        config(['app.debug' => false]);

        // registro.buscar solo acepta POST; probamos con un token cualquiera: el 404 de "token inválido"
        // también debe quedar limpio, no solo el 405.
        $respuesta = $this->get(route('registro.buscar', 'token-de-prueba'));

        $this->assertContains($respuesta->getStatusCode(), [404, 405]);
        $contenido = $respuesta->getContent();

        $this->assertStringNotContainsString('Exception trace', $contenido);
        $this->assertStringNotContainsString('vendor/laravel', $contenido);
        $this->assertStringNotContainsString('Symfony\\Component', $contenido);
    }
}
