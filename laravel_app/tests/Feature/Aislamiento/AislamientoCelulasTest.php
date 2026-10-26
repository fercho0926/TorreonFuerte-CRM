<?php

namespace Tests\Feature\Aislamiento;

use App\Models\Celula;
use App\Models\CelulaReunion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AislamientoCelulasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_listado_y_detalle_solo_de_la_sede_propia(): void
    {
        $this->get(route('celulas.index'))->assertOk()->assertSee('Celula Alfa')->assertDontSee('Celula Beta');
        $this->get(route('celulas.show', $this->A['celula']))->assertOk()->assertSee('PersonaAlfa')->assertDontSee('PersonaBeta');
        $this->get(route('celulas.show', $this->B['celula']))->assertNotFound();
        $this->get(route('celulas.edit', $this->B['celula']))->assertNotFound();
        $this->put(route('celulas.update', $this->B['celula']), ['nombre' => 'Hack'])->assertNotFound();
        $this->delete(route('celulas.destroy', $this->B['celula']))->assertNotFound();
        $this->assertNotNull($this->B['celula']->fresh());
    }

    public function test_selectores_de_lider_y_colider_solo_ofrecen_personas_de_la_sede(): void
    {
        $this->get(route('celulas.create'))->assertOk()->assertSee('LiderAlfa')->assertDontSee('LiderBeta')->assertDontSee('PersonaBeta');
        $this->get(route('celulas.edit', $this->A['celula']))->assertOk()->assertDontSee('LiderBeta');
    }

    public function test_no_se_crea_una_celula_con_lider_de_otra_sede(): void
    {
        $this->post(route('celulas.store'), ['nombre' => 'Celula Cruzada', 'lider_id' => $this->B['lider']->id, 'estado' => 'activa'])
            ->assertSessionHasErrors('lider_id');
        $this->post(route('celulas.store'), ['nombre' => 'Celula Cruzada', 'lider_id' => $this->A['lider']->id, 'co_lider_id' => $this->B['lider']->id, 'estado' => 'activa'])
            ->assertSessionHasErrors('co_lider_id');

        $this->assertSame(0, Celula::withoutGlobalScopes()->where('nombre', 'Celula Cruzada')->count());
    }

    public function test_reuniones_y_miembros_de_otra_sede_no_se_tocan(): void
    {
        $this->post(route('celulas.miembros.store', $this->B['celula']), ['persona_id' => $this->B['persona']->id])->assertNotFound();
        $this->post(route('celulas.reuniones.store', $this->B['celula']), ['fecha' => now()->toDateString()])->assertNotFound();

        // Un miembro de otra sede no entra a una célula propia.
        $this->post(route('celulas.miembros.store', $this->A['celula']), ['personas' => [$this->B['persona']->id]])->assertSessionHasErrors('personas.0');
        $this->assertFalse($this->A['celula']->miembros()->whereKey($this->B['persona']->id)->exists());
    }

    public function test_la_asistencia_y_las_reuniones_propias_quedan_en_la_sede(): void
    {
        $this->post(route('celulas.reuniones.store', $this->A['celula']), [
            'fecha' => now()->toDateString(), 'tema' => 'Tema', 'asistio' => [$this->A['persona']->id],
        ])->assertSessionHasNoErrors();

        $reunion = CelulaReunion::withoutGlobalScopes()->where('celula_id', $this->A['celula']->id)->first();
        $this->assertSame($this->A['sede']->id, $reunion->sede_id);

        // Asistencia con un miembro ajena: se rechaza.
        $this->post(route('celulas.reuniones.store', $this->A['celula']), [
            'fecha' => now()->toDateString(), 'asistio' => [$this->B['persona']->id],
        ])->assertSessionHasErrors('asistio.0');
    }
}
