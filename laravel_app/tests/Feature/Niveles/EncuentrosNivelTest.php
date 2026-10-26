<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\Encuentro;
use App\Models\NivelFormacion;
use App\Models\CambioHistorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Los encuentros vinculados a un nivel completan el nivel de quienes asisten (spec 014, HU3, RF-011 y RF-012). */
class EncuentrosNivelTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private NivelFormacion $nivel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
        $this->nivel = NivelFormacion::where('posicion', 2)->firstOrFail();
    }

    private function datos(array $extra = []): array
    {
        return $extra + [
            'nombre' => 'Retiro Aliviando', 'fecha_realizacion' => '2026-03-12', 'valor' => 0, 'estado' => 'realizado',
            'nivel_id' => $this->nivel->id, 'personas' => [$this->A['persona']->id],
        ];
    }

    private function avance(): ?AvanceNivel
    {
        return AvanceNivel::where('persona_id', $this->A['persona']->id)->where('nivel_id', $this->nivel->id)->first();
    }

    private function asistencia(Encuentro $e, bool $asistio): void
    {
        $this->put(route('encuentros.asistencia', $e), $asistio ? ['asistio' => [$this->A['persona']->id]] : [])->assertRedirect();
    }

    public function test_el_formulario_ofrece_el_selector_de_nivel_y_se_guarda(): void
    {
        $this->get(route('encuentros.create'))->assertOk()->assertSee('name="nivel_id"', false)->assertSee('Aliviando el fuego');

        $this->post(route('encuentros.store'), $this->datos())->assertSessionHasNoErrors();

        $encuentro = Encuentro::where('nombre', 'Retiro Aliviando')->firstOrFail();
        $this->assertSame($this->nivel->id, $encuentro->nivel_id);
        $this->get(route('encuentros.show', $encuentro))->assertSee('Aliviando el fuego');
        $this->get(route('encuentros.edit', $encuentro))->assertSee('name="nivel_id"', false);
    }

    public function test_el_nivel_es_opcional_y_debe_existir(): void
    {
        $this->post(route('encuentros.store'), $this->datos(['nivel_id' => null, 'nombre' => 'Sin nivel']))->assertSessionHasNoErrors();
        $this->assertNull(Encuentro::where('nombre', 'Sin nivel')->first()->nivel_id);

        $this->post(route('encuentros.store'), $this->datos(['nivel_id' => 999999, 'nombre' => 'Malo']))->assertSessionHasErrors('nivel_id');
    }

    public function test_marcar_la_asistencia_completa_el_nivel_con_la_fecha_del_encuentro(): void
    {
        $this->post(route('encuentros.store'), $this->datos());
        $encuentro = Encuentro::where('nombre', 'Retiro Aliviando')->firstOrFail();
        $this->assertNull($this->avance(), 'inscrito pero sin asistencia marcada todavía');

        $this->asistencia($encuentro, true);

        $avance = $this->avance();
        $this->assertNotNull($avance);
        $this->assertSame('encuentro', $avance->origen);
        $this->assertSame('2026-03-12', $avance->fecha->toDateString());
        $this->assertSame($encuentro->id, $avance->encuentro_id);
        $this->assertTrue(CambioHistorial::de('persona', $this->A['persona']->id)->where('accion', 'nivel_automatico')->exists());
        $this->get(route('personas.show', $this->A['persona']))->assertSee('Origen: encuentro')->assertSee('Retiro Aliviando');
    }

    public function test_desmarcar_la_asistencia_retira_el_avance_automatico(): void
    {
        $this->post(route('encuentros.store'), $this->datos());
        $encuentro = Encuentro::where('nombre', 'Retiro Aliviando')->firstOrFail();
        $this->asistencia($encuentro, true);
        $this->assertNotNull($this->avance());

        $this->asistencia($encuentro, false);

        $this->assertNull($this->avance());
    }

    public function test_cambiar_el_estado_el_nivel_o_los_asistentes_recalcula(): void
    {
        $this->post(route('encuentros.store'), $this->datos());
        $encuentro = Encuentro::where('nombre', 'Retiro Aliviando')->firstOrFail();
        $this->asistencia($encuentro, true);

        $this->put(route('encuentros.update', $encuentro), $this->datos(['estado' => 'cancelado']));
        $this->assertNull($this->avance(), 'cancelado no cuenta');

        $this->put(route('encuentros.update', $encuentro), $this->datos(['estado' => 'realizado']));
        $this->assertNotNull($this->avance(), 'vuelve a realizado');

        $this->put(route('encuentros.update', $encuentro), $this->datos(['nivel_id' => null]));
        $this->assertNull($this->avance(), 'desvinculado');

        $this->put(route('encuentros.update', $encuentro), $this->datos());
        $this->assertNotNull($this->avance());

        $this->put(route('encuentros.update', $encuentro), $this->datos(['personas' => []]));
        $this->assertNull($this->avance(), 'quitado de la lista de inscritos');
    }

    public function test_eliminar_el_encuentro_retira_los_avances_que_dependian_de_el(): void
    {
        $this->post(route('encuentros.store'), $this->datos());
        $encuentro = Encuentro::where('nombre', 'Retiro Aliviando')->firstOrFail();
        $this->asistencia($encuentro, true);

        $this->delete(route('encuentros.destroy', $encuentro))->assertRedirect(route('encuentros.index'));

        $this->assertNull($this->avance());
        $this->assertNull(Encuentro::find($encuentro->id));
    }

    public function test_un_avance_manual_sobrevive_a_los_cambios_del_encuentro(): void
    {
        AvanceNivel::factory()->de($this->A['persona'], $this->nivel)->create(['fecha' => '2025-01-01']);
        $this->post(route('encuentros.store'), $this->datos());
        $encuentro = Encuentro::where('nombre', 'Retiro Aliviando')->firstOrFail();
        $this->asistencia($encuentro, true);
        $this->asistencia($encuentro, false);
        $this->delete(route('encuentros.destroy', $encuentro));

        $avance = $this->avance();
        $this->assertNotNull($avance);
        $this->assertSame('manual', $avance->origen);
        $this->assertSame('2025-01-01', $avance->fecha->toDateString());
    }

    public function test_la_grilla_de_encuentros_muestra_la_columna_nivel(): void
    {
        $this->post(route('encuentros.store'), $this->datos());

        $this->get(route('encuentros.index'))->assertOk()->assertSee('data-columna="nivel"', false);
        $texto = json_encode($this->getJson(route('encuentros.datos', ['length' => 50]))->assertOk()->json('data'), JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('Aliviando el fuego', $texto);
    }
}
