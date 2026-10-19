<?php

namespace Tests\Feature\Grillas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** RF-005b: los datos de acceso (usuarios y solicitudes de clave) solo los exporta un administrador. */
class ExportacionRestringidaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public static function sensibles(): array
    {
        return ['usuarios' => ['users'], 'solicitudes de clave' => ['solicitudes-clave']];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    #[DataProvider('sensibles')]
    public function test_un_pastor_ve_el_listado_pero_no_puede_exportarlo(string $modulo): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $html = $this->get(route("$modulo.index"))->assertOk()->getContent();

        $this->assertStringNotContainsString('data-grilla-exportar', $html, 'el botón no aparece');
        $this->get(route("$modulo.exportar"))->assertForbidden();
    }

    #[DataProvider('sensibles')]
    public function test_un_administrador_si_exporta_con_los_filtros_actuales(string $modulo): void
    {
        $this->comoAdminEnTodas();

        $this->get(route("$modulo.index"))->assertOk()->assertSee('data-grilla-exportar', false);
        $csv = $this->get(route("$modulo.exportar", ['q' => 'zzz-sin-coincidencia']))->assertOk()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertSame(1, substr_count(trim($csv), "\n") + 1, 'solo la cabecera: el filtro se respetó');
    }

    #[DataProvider('sensibles')]
    public function test_un_lider_no_puede_ver_ni_pedir_los_datos(string $modulo): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);

        $this->getJson(route("$modulo.datos"))->assertForbidden();
        $this->get(route("$modulo.exportar"))->assertForbidden();
    }
}
