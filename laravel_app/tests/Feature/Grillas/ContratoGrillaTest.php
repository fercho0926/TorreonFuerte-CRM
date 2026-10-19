<?php

namespace Tests\Feature\Grillas;

use App\Grillas\CelulasGrilla;
use App\Grillas\Columna;
use App\Grillas\EncuentrosGrilla;
use App\Grillas\EventosGrilla;
use App\Grillas\Grilla;
use App\Grillas\LideresGrilla;
use App\Grillas\MinisteriosGrilla;
use App\Grillas\PersonasGrilla;
use App\Grillas\SedesGrilla;
use App\Grillas\SinLiderGrilla;
use App\Grillas\SolicitudesClaveGrilla;
use App\Grillas\UsuariosGrilla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Contrato común de las grillas estándar (specs/007-grillas-datatables/contracts/rutas-y-componentes.md). */
class ContratoGrillaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    /** @return array<string, array{0: class-string<Grilla>}> */
    public static function grillas(): array
    {
        return collect([
            PersonasGrilla::class, LideresGrilla::class, CelulasGrilla::class, EncuentrosGrilla::class, EventosGrilla::class,
            MinisteriosGrilla::class, SedesGrilla::class, SinLiderGrilla::class, UsuariosGrilla::class, SolicitudesClaveGrilla::class,
        ])->mapWithKeys(fn ($clase) => [class_basename($clase) => [$clase]])->all();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnTodas();
    }

    #[DataProvider('grillas')]
    public function test_datos_devuelve_el_protocolo_de_datatables_con_una_celda_por_columna(string $clase): void
    {
        $grilla = app($clase);

        $json = $this->getJson(route($grilla->id().'.datos', ['draw' => 4, 'start' => 0, 'length' => 10]))->assertOk()->json();

        $this->assertSame(4, $json['draw']);
        $this->assertGreaterThanOrEqual($json['recordsFiltered'], $json['recordsTotal']);
        $columnas = $grilla->columnas(\App\Http\Controllers\Controller::usuarioDeLaPeticion());
        foreach ($json['data'] as $fila) {
            $this->assertCount(count($columnas), $fila);
        }
    }

    #[DataProvider('grillas')]
    public function test_cada_columna_ordena_y_filtra_sin_errores(string $clase): void
    {
        $grilla = app($clase);
        $columnas = $grilla->columnas(\App\Http\Controllers\Controller::usuarioDeLaPeticion());

        foreach ($columnas as $indice => $columna) {
            foreach (['asc', 'desc'] as $dir) {
                $this->getJson(route($grilla->id().'.datos', ['length' => 10, 'order' => [['column' => $indice, 'dir' => $dir]]]))
                    ->assertOk("orden por {$columna->clave} {$dir}");
            }

            if ($columna->filtro === Columna::FILTRO_TEXTO) {
                $this->getJson(route($grilla->id().'.datos', ['length' => 10, $columna->clave => 'a']))->assertOk("filtro de texto {$columna->clave}");
            }
            if ($columna->filtro === Columna::FILTRO_LISTA) {
                $valor = (string) array_key_first($columna->opciones);
                $this->getJson(route($grilla->id().'.datos', ['length' => 10, $columna->clave => $valor]))->assertOk("filtro de lista {$columna->clave}");
            }
        }
    }

    #[DataProvider('grillas')]
    public function test_la_busqueda_general_y_el_orden_por_url_no_fallan(string $clase): void
    {
        $grilla = app($clase);

        $this->get(route($grilla->id().'.index', ['q' => 'a', 'orden' => 'inventada', 'dir' => 'sideways', 'por_pagina' => 7]))->assertOk();
    }

    #[DataProvider('grillas')]
    public function test_exportar_entrega_un_csv_con_bom_si_el_usuario_puede(string $clase): void
    {
        $grilla = app($clase);

        $respuesta = $this->get(route($grilla->id().'.exportar'))->assertOk();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $respuesta->streamedContent());
        $this->assertStringContainsString('text/csv', $respuesta->headers->get('Content-Type'));
    }

    #[DataProvider('grillas')]
    public function test_sin_sesion_datos_responde_401_en_json(string $clase): void
    {
        $this->flushSession();

        $this->getJson(route(app($clase)->id().'.datos'))->assertStatus(401);
    }
}
