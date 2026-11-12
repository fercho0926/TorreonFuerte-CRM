<?php

namespace Tests\Feature\Personas;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** RF-009: "falta un medio de contacto" se muestra una sola vez en el resumen, no una vez por campo. */
class ValidacionContactoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    public function test_el_resumen_muestra_el_mensaje_del_celular_obligatorio_una_sola_vez(): void
    {
        $respuesta = $this->from(route('personas.create'))->followingRedirects()->post(route('personas.store'), [
            'nombres' => 'Ana', 'apellidos' => 'Prueba', 'estado' => 'visitante',
        ]);

        $xp = $this->xpath($respuesta->getContent());
        $mensaje = 'El celular es obligatorio: es el canal para enviar información por WhatsApp.';

        $resumen = $xp->query('//*[@data-resumen-servidor]//li[contains(., "'.$mensaje.'")]');
        $this->assertSame(1, $resumen->length, 'El resumen no debe repetir el mismo mensaje.');

        // El campo sigue marcado como inválido individualmente (no se pierde la señal visual).
        $campo = $xp->query('//*[@id="fm-celular"]')->item(0);
        $this->assertNotNull($campo, 'No se encontró el campo #fm-celular');
        $this->assertSame('true', $campo->getAttribute('aria-invalid'));
    }
}
