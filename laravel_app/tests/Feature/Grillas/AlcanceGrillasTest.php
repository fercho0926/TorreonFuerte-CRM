<?php

namespace Tests\Feature\Grillas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Ninguna grilla (ni su exportación) muestra filas de otra sede ni fuera del alcance del rol (CE-004). */
class AlcanceGrillasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public static function listados(): array
    {
        return [
            'personas' => ['personas'],
            'lideres' => ['lideres'],
            'celulas' => ['celulas'],
            'encuentros' => ['encuentros'],
            'eventos' => ['eventos'],
            'ministerios' => ['ministerios'],
            'users' => ['users'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    #[DataProvider('listados')]
    public function test_el_pastor_solo_ve_filas_de_su_sede_en_pantalla_datos_y_csv(string $modulo): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        foreach ([
            $this->get(route("$modulo.index"))->assertOk()->getContent(),
            json_encode($this->getJson(route("$modulo.datos", ['length' => 100]))->assertOk()->json('data')),
        ] as $contenido) {
            $this->assertStringNotContainsString('Beta', $contenido, "$modulo muestra datos de otra sede");
        }

        $this->assertStringNotContainsString('Beta', (string) $this->exportarSiPuede($modulo));
    }

    #[DataProvider('listados')]
    public function test_otro_pastor_no_ve_las_filas_de_la_primera_sede(string $modulo): void
    {
        $this->iniciarSesionComo($this->B['pastor']);

        $datos = json_encode($this->getJson(route("$modulo.datos", ['length' => 100]))->assertOk()->json('data'));

        $this->assertStringNotContainsString('Alfa', $datos, "$modulo muestra datos de otra sede");
    }

    public function test_un_lider_solo_ve_a_su_gente_y_sus_celulas(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);

        $personas = json_encode($this->getJson(route('personas.datos', ['length' => 100]))->json('data'));
        $celulas = json_encode($this->getJson(route('celulas.datos', ['length' => 100]))->json('data'));

        $this->assertStringContainsString('PersonaAlfa', $personas);
        $this->assertStringNotContainsString('PastorAlfa', $personas, 'un líder no ve a quien no lidera');
        $this->assertStringContainsString('Celula Alfa', $celulas);
        $this->assertStringNotContainsString('Beta', $personas.$celulas);
    }

    public function test_el_administrador_en_todas_las_sedes_ve_ambas_y_la_columna_sede(): void
    {
        $this->comoAdminEnTodas();

        $json = $this->getJson(route('personas.datos', ['length' => 100]))->json('data');
        $texto = json_encode($json);

        $this->assertStringContainsString('PersonaAlfa', $texto);
        $this->assertStringContainsString('PersonaBeta', $texto);
        $this->assertStringContainsString('Iglesia Beta', $texto);
    }

    private function exportarSiPuede(string $modulo): ?string
    {
        $respuesta = $this->get(route("$modulo.exportar"));

        return $respuesta->getStatusCode() === 200 ? $respuesta->streamedContent() : null;
    }
}
