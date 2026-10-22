<?php

namespace Tests\Feature\Personas;

use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 6: el estado «En célula» en listados y formularios (FR-006). */
class FiltroEnCelulaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private Persona $invitada;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->invitada = Persona::factory()->deSede($this->A['sede']->id)->enCelula()->create(['nombres' => 'Invitada', 'apellidos' => 'Delagua']);
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_el_filtro_de_estado_ofrece_en_celula_y_devuelve_solo_esas_personas(): void
    {
        $html = $this->get(route('personas.index'))->assertOk()->getContent();
        $this->assertStringContainsString('value="celula"', $html);

        $this->get(route('personas.index', ['estado' => 'celula']))
            ->assertOk()
            ->assertSee('Invitada Delagua')
            ->assertDontSee($this->A['persona']->nombre_completo);
    }

    public function test_se_muestra_con_su_insignia_propia(): void
    {
        $html = $this->get(route('personas.index'))->getContent();

        $this->assertMatchesRegularExpression('/badge text-bg-dark[^>]*>En célula</', $html);
    }

    public function test_no_aparece_en_sin_lider(): void
    {
        $this->get(route('sin-lider.index'))->assertOk()->assertDontSee('Invitada Delagua');
    }

    public function test_puede_pasar_a_miembro_sin_perder_su_celula(): void
    {
        $celula = Celula::create(['sede_id' => $this->A['sede']->id, 'nombre' => 'Célula nueva', 'lider_id' => $this->A['lider']->id, 'estado' => 'activa']);
        $celula->miembros()->attach($this->invitada->id, ['fecha_ingreso' => now()->toDateString()]);

        $base = ['nombres' => 'Invitada', 'apellidos' => 'Delagua', 'celular' => $this->invitada->celular];

        $this->put(route('personas.update', $this->invitada), $base + ['estado' => 'miembro_activo'])
            ->assertSessionHasNoErrors();

        $this->assertSame('miembro_activo', $this->invitada->fresh()->estado);
        $this->assertTrue($celula->miembros()->whereKey($this->invitada->id)->exists());
    }
}
