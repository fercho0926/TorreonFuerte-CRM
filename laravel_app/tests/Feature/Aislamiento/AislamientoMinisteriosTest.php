<?php

namespace Tests\Feature\Aislamiento;

use App\Models\Ministerio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AislamientoMinisteriosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_ministerios_solo_de_la_sede_propia(): void
    {
        $this->get(route('ministerios.index'))->assertOk()->assertSee('Ministerio Alfa')->assertDontSee('Ministerio Beta');
        $this->get(route('ministerios.show', $this->A['ministerio']))->assertOk()->assertDontSee('LiderBeta');
        $this->get(route('ministerios.show', $this->B['ministerio']))->assertNotFound();
        $this->get(route('ministerios.edit', $this->B['ministerio']))->assertNotFound();
        $this->put(route('ministerios.update', $this->B['ministerio']), ['nombre' => 'Hack'])->assertNotFound();
        $this->delete(route('ministerios.destroy', $this->B['ministerio']))->assertNotFound();
        $this->assertNotNull(Ministerio::withoutGlobalScopes()->find($this->B['ministerio']->id));
    }

    public function test_selectores_de_encargado_e_integrantes_solo_de_la_sede(): void
    {
        $this->get(route('ministerios.create'))->assertOk()->assertSee('LiderAlfa')->assertDontSee('LiderBeta')->assertDontSee('PersonaBeta');
    }

    public function test_no_se_asigna_encargado_ni_integrantes_de_otra_sede(): void
    {
        $this->post(route('ministerios.store'), ['nombre' => 'Ministerio Cruzado', 'estado' => 'activo', 'encargado_id' => $this->B['lider']->id])
            ->assertSessionHasErrors('encargado_id');
        $this->post(route('ministerios.store'), ['nombre' => 'Ministerio Cruzado', 'estado' => 'activo', 'personas' => [$this->B['persona']->id]])
            ->assertSessionHasErrors('personas.0');

        $this->assertSame(0, Ministerio::withoutGlobalScopes()->where('nombre', 'Ministerio Cruzado')->count());
    }

    public function test_el_mismo_nombre_de_ministerio_en_dos_sedes_es_valido(): void
    {
        $this->post(route('ministerios.store'), ['nombre' => 'Ministerio Beta', 'estado' => 'activo'])->assertSessionHasNoErrors();

        $this->assertSame(2, Ministerio::withoutGlobalScopes()->where('nombre', 'Ministerio Beta')->count());
        $this->assertSame($this->A['sede']->id, Ministerio::withoutGlobalScopes()->where('nombre', 'Ministerio Beta')->orderByDesc('id')->value('sede_id'));
    }
}
