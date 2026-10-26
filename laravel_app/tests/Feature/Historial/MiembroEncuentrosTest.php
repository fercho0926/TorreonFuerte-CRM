<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Inscripción, asistencia y pagos de encuentros en el histórico del miembro (spec 015, historia 1). */
class MiembroEncuentrosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function datosEncuentro(array $personas): array
    {
        $e = $this->A['encuentro'];

        return ['nombre' => $e->nombre, 'fecha_realizacion' => $e->fecha_realizacion->toDateString(), 'valor' => 0, 'estado' => $e->estado, 'personas' => $personas];
    }

    private function acciones(): array
    {
        return CambioHistorial::query()->de('persona', $this->A['persona']->id)->where('categoria', 'encuentros_retiros')
            ->where('relacionado_tipo', 'encuentro')->orderBy('id')->pluck('accion')->all();
    }

    public function test_inscribir_asistir_pagar_y_quitar_quedan_en_el_miembro(): void
    {
        $encuentro = $this->A['encuentro'];
        $persona = $this->A['persona'];

        $this->put(route('encuentros.update', $encuentro), $this->datosEncuentro([$persona->id]))->assertSessionHasNoErrors();
        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => [$persona->id], 'valor_pagado' => [$persona->id => 20000]])->assertSessionHasNoErrors();
        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => [], 'valor_pagado' => [$persona->id => 20000]])->assertSessionHasNoErrors();
        $this->put(route('encuentros.update', $encuentro), $this->datosEncuentro([]))->assertSessionHasNoErrors();

        $this->assertSame(['inscrito_encuentro', 'asistio_encuentro', 'pago_encuentro', 'no_asistio_encuentro', 'quitado_encuentro'], $this->acciones());

        $asistencia = CambioHistorial::query()->de('persona', $persona->id)->where('accion', 'asistio_encuentro')->sole();
        $this->assertSame($encuentro->fecha_realizacion->toDateString(), $asistencia->detalle['fecha_evento']);
        $this->assertSame($encuentro->id, $asistencia->relacionado_id);
        $this->assertSame($this->A['pastor']->id, $asistencia->user_id);

        $html = $this->get(route('personas.show', [$persona, 'pestana' => 'historico']))->getContent();
        $this->assertStringContainsString('Asistió al encuentro', $html);
        $this->assertStringContainsString('href="'.route('encuentros.show', $encuentro).'"', $html);
    }

    public function test_guardar_dos_veces_lo_mismo_no_duplica(): void
    {
        $encuentro = $this->A['encuentro'];
        $persona = $this->A['persona'];

        $this->put(route('encuentros.update', $encuentro), $this->datosEncuentro([$persona->id]));
        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => [$persona->id]]);
        $antes = CambioHistorial::count();

        $this->put(route('encuentros.update', $encuentro), $this->datosEncuentro([$persona->id]));
        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => [$persona->id]]);

        $this->assertSame($antes, CambioHistorial::count());
    }

    public function test_el_encuentro_guarda_el_resumen_de_la_asistencia(): void
    {
        $encuentro = $this->A['encuentro'];
        $persona = $this->A['persona'];
        $this->put(route('encuentros.update', $encuentro), $this->datosEncuentro([$persona->id]));
        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => [$persona->id]]);

        $resumen = CambioHistorial::query()->de('encuentro', $encuentro->id)->where('accion', 'asistencia_actualizada')->sole();
        $this->assertSame([$persona->nombre_completo], collect($resumen->detalle['agregados'])->pluck('nombre')->all());

        $individual = CambioHistorial::query()->de('persona', $persona->id)->where('accion', 'asistio_encuentro')->sole();
        $this->assertSame($resumen->lote, $individual->lote);
    }
}
