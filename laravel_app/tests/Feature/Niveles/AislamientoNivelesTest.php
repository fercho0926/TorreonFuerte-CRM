<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Los avances de los miembros se aíslan por sede y por alcance del rol (spec 014, RF-019, CE-008). */
class AislamientoNivelesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $uno = NivelFormacion::where('posicion', 1)->firstOrFail();
        AvanceNivel::factory()->de($this->A['persona'], $uno)->create();
        AvanceNivel::factory()->de($this->B['persona'], $uno)->create();
    }

    public function test_un_pastor_no_ve_el_camino_ni_registra_en_miembros_de_otra_sede(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('personas.show', $this->B['persona']))->assertNotFound();
        $this->post(route('niveles.avances.store'), ['persona_id' => $this->B['persona']->id, 'nivel_id' => NivelFormacion::where('posicion', 2)->value('id')])
            ->assertSessionHasErrors('persona_id');
        $this->assertSame(1, AvanceNivel::withoutGlobalScopes()->where('persona_id', $this->B['persona']->id)->count());
    }

    public function test_los_conteos_de_cada_sede_solo_incluyen_a_sus_miembros(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $a = app(\App\Services\NivelesService::class)->resumen($this->A['pastor']);

        $this->iniciarSesionComo($this->B['pastor']);
        $b = app(\App\Services\NivelesService::class)->resumen($this->B['pastor']);

        $this->assertSame(1, $a['niveles']->first()['total']);
        $this->assertSame(1, $b['niveles']->first()['total']);
        $this->assertSame($a['total'] - 1, $a['sin_iniciar']);
    }

    public function test_un_lider_solo_ve_a_su_rama_en_los_conteos(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);
        $ajeno = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'FueraDeRama']);
        AvanceNivel::factory()->de($ajeno, NivelFormacion::where('posicion', 1)->first())->create();

        $resumen = app(\App\Services\NivelesService::class)->resumen($this->A['liderUser']);

        $this->assertSame(1, $resumen['niveles']->first()['total'], 'solo su miembro a cargo, no el ajeno');
        $this->assertLessThanOrEqual(2, $resumen['total']);
    }

    public function test_el_avance_de_otra_sede_no_aparece_en_el_camino_propio(): void
    {
        $this->iniciarSesionComo($this->B['pastor']);

        $this->get(route('personas.show', $this->B['persona']))->assertOk()->assertSee('1 de 4 niveles');
    }
}
