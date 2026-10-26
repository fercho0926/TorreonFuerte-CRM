<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Services\Historial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Pestaña «Histórico» del miembro con sus secciones (spec 015, historia 1, RF-012 a RF-016). */
class PestanaMiembroTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function editar(array $cambios): void
    {
        $p = $this->A['persona'];
        $this->put(route('personas.update', $p), $cambios + [
            'nombres' => $p->nombres, 'apellidos' => $p->apellidos, 'estado' => $p->estado,
            'celular' => $p->celular, 'direccion' => $p->direccion, 'lider_id' => $p->lider_id,
        ])->assertSessionHasNoErrors();
    }

    public function test_sin_parametro_se_ve_la_ficha_y_con_historico_las_seis_secciones_en_orden(): void
    {
        $url = route('personas.show', $this->A['persona']);

        $this->get($url)->assertOk()->assertSee('Histórico')->assertDontSee('Cambios de datos');

        $this->get($url.'?pestana=historico')->assertOk()->assertSeeInOrder([
            'Cambios de datos', 'Encuentros y retiros', 'Células y ministerios', 'Liderazgo', 'Niveles', 'Sede',
        ])->assertSee('Sin movimientos');
    }

    public function test_la_edicion_muestra_cada_campo_con_su_valor_anterior_y_nuevo_y_quien_lo_hizo(): void
    {
        $this->A['persona']->update(['direccion' => 'Calle 10 # 5-20', 'celular' => '3001112233']);
        $this->editar(['direccion' => 'Carrera 8 # 3-15', 'celular' => '3009998877']);

        $html = $this->get(route('personas.show', [$this->A['persona'], 'pestana' => 'historico']))->assertOk()->getContent();

        $this->assertStringContainsString('Datos editados', $html);
        $this->assertStringContainsString('Dirección:', $html);
        $this->assertStringContainsString('Calle 10 # 5-20', $html);
        $this->assertStringContainsString('Carrera 8 # 3-15', $html);
        $this->assertStringContainsString('Celular:', $html);
        $this->assertStringContainsString($this->A['pastor']->name, $html);
        $this->assertStringNotContainsString('Nombres:', $html, 'no lista los campos que no cambiaron');
    }

    public function test_los_filtros_de_fecha_y_usuario_se_aplican_a_todas_las_secciones(): void
    {
        $historial = app(Historial::class);
        $persona = $this->A['persona'];
        $historial->registrar($persona, 'estado', ['estado' => 'visitante'], ['estado' => 'celula']);
        $historial->registrar($persona, 'asistio_encuentro', null, null, null, [], $this->A['encuentro']);
        CambioHistorial::query()->de('persona', $persona->id)->toBase()->update(['created_at' => '2026-01-15 10:00:00']);
        $historial->registrar($persona, 'lider', ['lider_id' => null], ['lider_id' => $this->A['lider']->id]);

        $html = $this->get(route('personas.show', [$persona, 'pestana' => 'historico', 'desde' => '2026-01-01', 'hasta' => '2026-01-31']))->getContent();
        $this->assertStringContainsString('Cambio de estado', $html);
        $this->assertStringContainsString('Asistió al encuentro', $html);
        $this->assertStringNotContainsString('Cambio de líder</span>', $html);

        $html = $this->get(route('personas.show', [$persona, 'pestana' => 'historico', 'usuario' => 'qr']))->getContent();
        $this->assertStringNotContainsString('Cambio de estado', $html);
        $this->assertStringNotContainsString('Asistió al encuentro', $html);
    }

    public function test_cada_seccion_tiene_su_propia_paginacion_de_veinte(): void
    {
        $historial = app(Historial::class);
        foreach (range(1, 22) as $i) {
            $historial->registrar($this->A['persona'], 'editada', ['celular' => "300{$i}"], ['celular' => '300'.($i + 1)]);
        }
        $historial->registrar($this->A['persona'], 'asistio_encuentro', null, null, null, [], $this->A['encuentro']);

        $seccion = function (string $html, string $desde, string $hasta): string {
            $inicio = strpos($html, 'id="seccion-'.$desde.'"');

            return substr($html, $inicio, strpos($html, 'id="seccion-'.$hasta.'"') - $inicio);
        };
        $url = fn (array $extra = []) => route('personas.show', [$this->A['persona'], 'pestana' => 'historico'] + $extra);

        $html = $this->get($url())->getContent();
        $this->assertSame(20, substr_count($seccion($html, 'datos', 'encuentros_retiros'), 'class="historial-entrada"'));
        $encuentros = substr_count($seccion($html, 'encuentros_retiros', 'celulas_ministerios'), 'class="historial-entrada"');
        $this->assertGreaterThanOrEqual(1, $encuentros);
        $this->assertStringContainsString('pagina_datos=2', $html);

        $html = $this->get($url(['pagina_datos' => 2]))->getContent();
        $this->assertSame(2, substr_count($seccion($html, 'datos', 'encuentros_retiros'), 'class="historial-entrada"'));
        $this->assertSame($encuentros, substr_count($seccion($html, 'encuentros_retiros', 'celulas_ministerios'), 'class="historial-entrada"'), 'las demás secciones no cambian de página');
    }

    public function test_un_filtro_invalido_se_ignora(): void
    {
        $this->get(route('personas.show', [$this->A['persona'], 'pestana' => 'historico', 'desde' => 'ayer', 'usuario' => "1' or 1=1", 'pagina_datos' => -3]))->assertOk();
    }
}
