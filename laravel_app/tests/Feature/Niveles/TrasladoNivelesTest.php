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

/** Al trasladar a un miembro de sede su camino se conserva (spec 014, RF-022). */
class TrasladoNivelesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_el_avance_se_copia_al_nuevo_registro_con_nota_y_fecha_original(): void
    {
        $persona = $this->A['persona'];
        AvanceNivel::factory()->de($persona, NivelFormacion::where('posicion', 1)->first())->create(['fecha' => '2025-05-10']);
        AvanceNivel::factory()->de($persona, NivelFormacion::where('posicion', 2)->first())->create(['fecha' => '2025-11-02', 'nota' => 'Con su líder']);
        $this->comoAdminEnSede($this->A['sede']);

        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $this->B['sede']->id])->assertSessionHasNoErrors();

        $nueva = Persona::withoutGlobalScopes()->where('sede_id', $this->B['sede']->id)->where('numero_documento', $persona->numero_documento)->firstOrFail();
        $copiados = AvanceNivel::withoutGlobalScopes()->where('persona_id', $nueva->id)->orderBy('nivel_id')->get();

        $this->assertCount(2, $copiados);
        $this->assertSame([1, 2], $copiados->map(fn ($a) => $a->nivel->posicion)->all());
        $this->assertSame('2025-05-10', $copiados[0]->fecha->toDateString());
        $this->assertTrue($copiados->every(fn ($a) => $a->sede_id === $this->B['sede']->id && $a->origen === 'manual'));
        $this->assertStringContainsString('Trasladado desde Iglesia Alfa', $copiados[0]->nota);
        $this->assertStringContainsString('Con su líder', $copiados[1]->nota);
    }

    public function test_el_registro_de_origen_conserva_su_camino(): void
    {
        $persona = $this->A['persona'];
        AvanceNivel::factory()->de($persona, NivelFormacion::where('posicion', 1)->first())->create();
        $this->comoAdminEnSede($this->A['sede']);

        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $this->B['sede']->id]);

        $this->assertSame(1, AvanceNivel::withoutGlobalScopes()->where('persona_id', $persona->id)->count());
    }

    public function test_un_miembro_sin_avances_se_traslada_sin_crear_nada(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->post(route('traslados.store', $this->A['persona']), ['sede_destino_id' => $this->B['sede']->id])->assertSessionHasNoErrors();

        $this->assertSame(0, AvanceNivel::withoutGlobalScopes()->count());
    }
}
