<?php

namespace Tests\Feature\Historial;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Tabla del historial único y vínculo de traslados (spec 015, data-model). */
class MigracionTablaHistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_tabla_tiene_las_columnas_y_la_nulabilidad_acordadas(): void
    {
        $columnas = collect(Schema::getColumns('historial_cambios'))->keyBy('name');

        $noNulas = ['id', 'created_at', 'actor', 'actor_nombre', 'modulo', 'accion', 'sujeto_tipo', 'sujeto_id', 'sujeto_nombre'];
        $nulas = ['sede_id', 'user_id', 'categoria', 'relacionado_tipo', 'relacionado_id', 'relacionado_nombre', 'lote', 'antes', 'despues', 'detalle'];

        foreach ($noNulas as $columna) {
            $this->assertTrue($columnas->has($columna), $columna);
            $this->assertFalse($columnas[$columna]['nullable'], "{$columna} no debe aceptar nulos");
        }
        foreach ($nulas as $columna) {
            $this->assertTrue($columnas->has($columna), $columna);
            $this->assertTrue($columnas[$columna]['nullable'], "{$columna} debe aceptar nulos");
        }
        $this->assertFalse($columnas->has('updated_at'), 'las entradas no se actualizan');
    }

    public function test_no_tiene_claves_foraneas_para_que_nada_la_borre(): void
    {
        $this->assertSame([], Schema::getForeignKeys('historial_cambios'));
    }

    public function test_tiene_los_indices_de_consulta(): void
    {
        $indices = collect(Schema::getIndexes('historial_cambios'))->pluck('columns')->map(fn ($c) => implode(',', $c));

        foreach ([
            'sujeto_tipo,sujeto_id,categoria,created_at',
            'sujeto_tipo,sujeto_id,created_at',
            'sede_id,created_at',
            'user_id,created_at',
            'modulo,created_at',
            'relacionado_tipo,relacionado_id',
            'lote',
        ] as $esperado) {
            $this->assertContains($esperado, $indices, $esperado);
        }
    }

    public function test_personas_tiene_el_vinculo_del_traslado_sin_clave_foranea(): void
    {
        $this->assertTrue(Schema::hasColumn('personas', 'trasladada_desde_persona_id'));
        $foraneas = collect(Schema::getForeignKeys('personas'))->pluck('columns')->flatten();
        $this->assertNotContains('trasladada_desde_persona_id', $foraneas);
    }
}
