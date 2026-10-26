<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\Encuentro;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Models\CambioHistorial;
use App\Services\AvancesNivelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** El avance automático sigue a la asistencia a encuentros realizados vinculados a un nivel (spec 014, RF-012). */
class SincroniaEncuentrosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private AvancesNivelService $servicio;

    private NivelFormacion $nivel;

    private Persona $persona;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->servicio = app(AvancesNivelService::class);
        $this->nivel = NivelFormacion::where('posicion', 2)->firstOrFail();
        $this->persona = $this->A['persona'];
    }

    private function encuentro(array $atributos = []): Encuentro
    {
        return Encuentro::create($atributos + [
            'sede_id' => $this->A['sede']->id, 'nombre' => 'Retiro', 'fecha_realizacion' => '2026-03-12', 'valor' => 0,
            'estado' => 'realizado', 'nivel_id' => $this->nivel->id,
        ]);
    }

    private function asistir(Encuentro $e, Persona $p, bool $asistio = true): void
    {
        $e->personas()->syncWithoutDetaching([$p->id => ['asistio' => $asistio]]);
        $e->personas()->updateExistingPivot($p->id, ['asistio' => $asistio]);
    }

    private function avance(?Persona $p = null): ?AvanceNivel
    {
        return AvanceNivel::withoutGlobalScopes()->where('persona_id', ($p ?? $this->persona)->id)->where('nivel_id', $this->nivel->id)->first();
    }

    public function test_la_asistencia_a_un_encuentro_realizado_completa_el_nivel_con_la_fecha_del_encuentro(): void
    {
        $e = $this->encuentro();
        $this->asistir($e, $this->persona);

        $this->servicio->recalcularEncuentro($e);

        $avance = $this->avance();
        $this->assertNotNull($avance);
        $this->assertSame('encuentro', $avance->origen);
        $this->assertSame('2026-03-12', $avance->fecha->toDateString());
        $this->assertSame($e->id, $avance->encuentro_id);
        $this->assertSame($this->A['sede']->id, $avance->sede_id);
        $this->assertTrue(CambioHistorial::de('persona', $this->persona->id)->where('accion', 'nivel_automatico')->exists());
    }

    public function test_no_cuentan_los_encuentros_planificados_cancelados_ni_sin_nivel_ni_sin_asistencia(): void
    {
        foreach (['planificado', 'cancelado'] as $estado) {
            $e = $this->encuentro(['estado' => $estado, 'nombre' => $estado]);
            $this->asistir($e, $this->persona);
            $this->servicio->recalcularEncuentro($e);
        }
        $sinNivel = $this->encuentro(['nivel_id' => null, 'nombre' => 'sin nivel']);
        $this->asistir($sinNivel, $this->persona);
        $this->servicio->recalcularEncuentro($sinNivel);

        $faltó = $this->encuentro(['nombre' => 'faltó']);
        $this->asistir($faltó, $this->persona, false);
        $this->servicio->recalcularEncuentro($faltó);

        $this->assertNull($this->avance());
    }

    public function test_desmarcar_la_asistencia_retira_el_avance_automatico(): void
    {
        $e = $this->encuentro();
        $this->asistir($e, $this->persona);
        $this->servicio->recalcularEncuentro($e);
        $this->assertNotNull($this->avance());

        $this->asistir($e, $this->persona, false);
        $this->servicio->recalcularEncuentro($e);

        $this->assertNull($this->avance());
        $this->assertTrue(CambioHistorial::de('persona', $this->persona->id)->where('accion', 'nivel_quitado')->exists());
    }

    public function test_cancelar_desvincular_o_cambiar_de_nivel_retira_el_avance(): void
    {
        $e = $this->encuentro();
        $this->asistir($e, $this->persona);
        $this->servicio->recalcularEncuentro($e);

        $e->update(['estado' => 'cancelado']);
        $this->servicio->recalcularEncuentro($e);
        $this->assertNull($this->avance(), 'cancelado');

        $e->update(['estado' => 'realizado']);
        $this->servicio->recalcularEncuentro($e);
        $this->assertNotNull($this->avance(), 'vuelve a realizado');

        $anterior = $e->nivel_id;
        $e->update(['nivel_id' => null]);
        $this->servicio->recalcularEncuentro($e, $anterior);
        $this->assertNull($this->avance(), 'desvinculado');

        $e->update(['nivel_id' => $this->nivel->id]);
        $this->servicio->recalcularEncuentro($e);
        $otro = NivelFormacion::where('posicion', 3)->firstOrFail();
        $e->update(['nivel_id' => $otro->id]);
        $this->servicio->recalcularEncuentro($e, $this->nivel->id);
        $this->assertNull($this->avance(), 'ya no es de este nivel');
        $this->assertNotNull(AvanceNivel::withoutGlobalScopes()->where('persona_id', $this->persona->id)->where('nivel_id', $otro->id)->first(), 'sí del nuevo');
    }

    public function test_un_avance_manual_no_se_toca_ni_se_duplica(): void
    {
        $manual = AvanceNivel::factory()->de($this->persona, $this->nivel)->create(['fecha' => '2025-01-01']);
        $e = $this->encuentro();
        $this->asistir($e, $this->persona);

        $this->servicio->recalcularEncuentro($e);
        $this->assertSame(1, AvanceNivel::withoutGlobalScopes()->where('persona_id', $this->persona->id)->where('nivel_id', $this->nivel->id)->count());
        $this->assertSame('manual', $this->avance()->origen);
        $this->assertSame('2025-01-01', $this->avance()->fecha->toDateString());

        $this->asistir($e, $this->persona, false);
        $this->servicio->recalcularEncuentro($e);
        $this->assertNotNull($this->avance()->fresh(), 'quitar la asistencia no borra lo manual');
        $this->assertSame($manual->id, $this->avance()->id);
    }

    public function test_cuenta_el_primer_encuentro_y_si_se_retira_pasa_al_siguiente(): void
    {
        $primero = $this->encuentro(['nombre' => 'Primero', 'fecha_realizacion' => '2026-01-10']);
        $segundo = $this->encuentro(['nombre' => 'Segundo', 'fecha_realizacion' => '2026-02-20']);
        $this->asistir($primero, $this->persona);
        $this->asistir($segundo, $this->persona);

        $this->servicio->recalcularEncuentro($segundo);
        $this->assertSame($primero->id, $this->avance()->encuentro_id);
        $this->assertSame('2026-01-10', $this->avance()->fecha->toDateString());

        $this->asistir($primero, $this->persona, false);
        $this->servicio->recalcularEncuentro($primero);
        $this->assertSame($segundo->id, $this->avance()->encuentro_id);
        $this->assertSame('2026-02-20', $this->avance()->fecha->toDateString());
        $this->assertSame(1, AvanceNivel::withoutGlobalScopes()->where('persona_id', $this->persona->id)->count());
    }

    public function test_quitar_un_manual_deja_el_automatico_si_la_asistencia_lo_respalda(): void
    {
        $e = $this->encuentro();
        $this->asistir($e, $this->persona);
        $manual = AvanceNivel::factory()->de($this->persona, $this->nivel)->create();

        $this->servicio->quitarManual($manual);

        $this->assertSame('encuentro', $this->avance()->origen);
    }

    public function test_retirar_de_encuentro_antes_de_eliminarlo_borra_su_avance(): void
    {
        $e = $this->encuentro();
        $this->asistir($e, $this->persona);
        $this->servicio->recalcularEncuentro($e);
        $this->assertNotNull($this->avance());

        $this->servicio->retirarDeEncuentro($e);
        $e->delete();

        $this->assertNull($this->avance());
    }

    public function test_el_avance_de_un_miembro_no_afecta_al_de_otro(): void
    {
        $e = $this->encuentro();
        $this->asistir($e, $this->persona);
        $this->asistir($e, $this->A['lider'], false);

        $this->servicio->recalcularEncuentro($e);

        $this->assertNotNull($this->avance());
        $this->assertNull($this->avance($this->A['lider']));
    }
}
