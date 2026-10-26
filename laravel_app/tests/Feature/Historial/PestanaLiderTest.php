<?php

namespace Tests\Feature\Historial;

use App\Services\Historial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** La ficha del líder tiene el mismo histórico en secciones que su ficha de miembro (spec 015). */
class PestanaLiderTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_el_lider_muestra_sus_secciones_y_los_cambios_de_liderazgo(): void
    {
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
        app(Historial::class)->registrar($this->A['lider'], 'rol_lider', ['es_lider' => false], ['es_lider' => true]);

        $this->get(route('lideres.show', $this->A['lider']))->assertOk()->assertSee('Histórico');

        $this->get(route('lideres.show', [$this->A['lider'], 'pestana' => 'historico']))->assertOk()
            ->assertSeeInOrder(['Cambios de datos', 'Encuentros y retiros', 'Células y ministerios', 'Liderazgo'])
            ->assertSee('Rol de líder');
    }
}
