<?php

namespace Tests\Feature\Historial;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Quien ve la ficha del miembro ve su histórico; nadie más (spec 015, RF-021, RF-023). */
class PermisosPestanaMiembroTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function historico($persona)
    {
        return $this->get(route('personas.show', [$persona, 'pestana' => 'historico']));
    }

    public function test_administrador_pastor_lider_y_el_propio_miembro_lo_ven(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->historico($this->A['persona'])->assertOk()->assertSee('Cambios de datos');

        $this->iniciarSesionComo($this->A['pastor']);
        $this->historico($this->A['persona'])->assertOk()->assertSee('Cambios de datos');

        $this->iniciarSesionComo($this->A['liderUser']);
        $this->historico($this->A['persona'])->assertOk()->assertSee('Cambios de datos');

        $propio = User::factory()->create(['role' => 'user', 'persona_id' => $this->A['persona']->id, 'sede_id' => $this->A['sede']->id]);
        $this->iniciarSesionComo($propio);
        $this->historico($this->A['persona'])->assertOk()->assertSee('Cambios de datos');
    }

    public function test_fuera_del_alcance_o_de_otra_sede_no_se_ve(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);
        $this->historico($this->A['pastorPersona'])->assertNotFound();

        $this->iniciarSesionComo($this->B['pastor']);
        $this->historico($this->A['persona'])->assertNotFound();
    }

    public function test_ya_no_existe_la_tarjeta_vieja_ni_la_habilidad_ver_bitacora(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('personas.show', $this->A['persona']))->assertOk()->assertDontSee('Historial de cambios');
        $this->assertFalse(method_exists(\App\Policies\PersonaPolicy::class, 'verBitacora'));
    }
}
