<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class DashboardGlobalAdminTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        Persona::factory()->count(2)->deSede($this->B['sede']->id)->create();
    }

    public function test_en_todas_las_sedes_muestra_la_tabla_por_sede_y_el_total_global(): void
    {
        $this->comoAdminEnTodas();

        $respuesta = $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Resumen por sede')
            ->assertSee('Iglesia Alfa')
            ->assertSee('Iglesia Beta')
            ->assertSee('Total global (todas las sedes)');

        $respuesta->assertViewHas('resumenSedes', function ($resumen) {
            $a = $resumen[$this->A['sede']->id];
            $b = $resumen[$this->B['sede']->id];

            return $a['personas'] === 3 && $b['personas'] === 5
                && $a['lideres'] === 1 && $b['lideres'] === 1
                && $a['celulas'] === 1 && $b['celulas'] === 1
                && $a['eventos_proximos'] === 1 && $b['eventos_proximos'] === 1
                && $a['recaudo'] === 10000.0 && $b['recaudo'] === 10000.0;
        });
    }

    public function test_con_sede_activa_el_admin_ve_el_resumen_de_esa_sede(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('personaCount', 3)
            ->assertViewHas('resumenSedes', null)
            ->assertDontSee('Resumen por sede')
            ->assertSee('Iglesia Alfa');
    }

    public function test_los_pastores_nunca_ven_datos_de_otra_sede(): void
    {
        foreach ([[$this->A['pastor'], 'Beta'], [$this->B['pastor'], 'Alfa']] as [$pastor, $ajena]) {
            $this->iniciarSesionComo($pastor);

            $this->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee('Resumen por sede')
                ->assertDontSee("Iglesia {$ajena}")
                ->assertDontSee("Red {$ajena}");
        }
    }
}
