<?php

namespace Tests\Feature\Historial;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Nadie ve el historial de otra sede (spec 015, RF-023, CE-006; constitución, principio II). */
class AislamientoHistorialTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_las_pestanas_de_la_sede_a_no_muestran_nada_de_la_sede_b(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        foreach ([
            route('personas.show', [$this->A['persona'], 'pestana' => 'historico']),
            route('celulas.show', [$this->A['celula'], 'pestana' => 'historico']),
            route('eventos.show', [$this->A['evento'], 'pestana' => 'historico']),
            route('encuentros.show', [$this->A['encuentro'], 'pestana' => 'historico']),
            route('ministerios.show', [$this->A['ministerio'], 'pestana' => 'historico']),
        ] as $url) {
            $this->assertStringNotContainsString('Beta', $this->get($url)->assertOk()->getContent(), $url);
        }
    }

    public function test_las_fichas_de_la_sede_b_no_se_abren_desde_la_sede_a(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        foreach ([
            route('personas.show', [$this->B['persona'], 'pestana' => 'historico']),
            route('celulas.show', [$this->B['celula'], 'pestana' => 'historico']),
            route('eventos.show', [$this->B['evento'], 'pestana' => 'historico']),
            route('encuentros.show', [$this->B['encuentro'], 'pestana' => 'historico']),
            route('ministerios.show', [$this->B['ministerio'], 'pestana' => 'historico']),
        ] as $url) {
            $this->assertContains($this->get($url)->getStatusCode(), [302, 403, 404], $url);
        }
    }

    public function test_control_de_cambios_del_admin_en_la_sede_a_no_trae_la_sede_b(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $json = json_encode($this->getJson(route('control-cambios.datos', ['length' => 100]))->assertOk()->json('data'), JSON_UNESCAPED_UNICODE);

        $this->assertStringNotContainsString('Beta', $json);
        $this->assertStringContainsString('Alfa', $json);
    }
}
