<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Celula;
use App\Services\Historial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Módulo «Control de cambios» (spec 015, historia 4, RF-017 a RF-020). */
class ControlCambiosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function datos(array $parametros = []): array
    {
        return $this->getJson(route('control-cambios.datos', ['length' => 100] + $parametros))->assertOk()->json();
    }

    public function test_el_administrador_ve_todo_con_las_columnas_acordadas_y_lo_mas_reciente_primero(): void
    {
        $this->comoAdminEnTodas();

        $html = $this->get(route('control-cambios.index'))->assertOk()->getContent();
        foreach (['Fecha y hora', 'Usuario', 'Sede', 'Módulo', 'Acción', 'Registro', 'Detalle'] as $columna) {
            $this->assertStringContainsString($columna, $html);
        }

        $json = $this->datos();
        $this->assertGreaterThan(0, $json['recordsTotal']);
        $ids = CambioHistorial::query()->recientes()->limit(100)->pluck('id')->all();
        $this->assertSame(count($ids), count($json['data']));
    }

    public function test_filtra_por_usuario_modulo_accion_registro_y_fechas(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $historial = app(Historial::class);
        $historial->registrar($this->A['persona'], 'estado', ['estado' => 'visitante'], ['estado' => 'celula'], $this->A['pastor']);
        CambioHistorial::query()->where('accion', 'estado')->toBase()->update(['created_at' => '2026-02-10 09:00:00']);

        $this->assertSame(1, $this->datos(['usuario' => $this->A['pastor']->name, 'desde' => '2026-02-01', 'hasta' => '2026-02-28'])['recordsFiltered']);
        $this->assertSame(0, $this->datos(['usuario' => $this->A['pastor']->name, 'desde' => '2026-03-01'])['recordsFiltered']);
        $this->assertGreaterThan(0, $this->datos(['modulo' => 'eventos'])['recordsFiltered']);
        $this->assertSame(1, $this->datos(['accion' => 'Cambio de estado'])['recordsFiltered']);
        $this->assertGreaterThan(0, $this->datos(['registro' => 'Evento Alfa'])['recordsFiltered']);
    }

    public function test_un_registro_eliminado_se_encuentra_con_sus_datos(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $celula = Celula::factory()->create(['sede_id' => $this->A['sede']->id, 'nombre' => 'Célula Efímera', 'lider_id' => $this->A['lider']->id]);
        $this->delete(route('celulas.destroy', $celula));

        $json = $this->datos(['registro' => 'Efímera']);
        $this->assertGreaterThanOrEqual(2, $json['recordsFiltered'], 'alta y eliminación');
        $this->assertStringContainsString('Célula eliminada', json_encode($json['data'], JSON_UNESCAPED_UNICODE));
        $this->assertStringContainsString('Datos que tenía', json_encode($json['data'], JSON_UNESCAPED_UNICODE));
    }

    public function test_el_registro_enlaza_a_su_historico_si_existe(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('control-cambios.index', ['registro' => 'Evento Alfa']))->assertOk()->getContent();

        $this->assertStringContainsString(route('eventos.show', [$this->A['evento'], 'pestana' => 'historico']), str_replace('&amp;', '&', $html));
    }

    public function test_no_hay_boton_ni_ruta_de_exportar(): void
    {
        $this->comoAdminEnTodas();

        $this->get(route('control-cambios.index'))->assertOk()->assertDontSee('Exportar CSV');
        $this->get('/control-cambios/exportar')->assertNotFound();
    }
}
