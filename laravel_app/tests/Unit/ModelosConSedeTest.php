<?php

namespace Tests\Unit;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Si una tabla tiene la columna `sede_id`, su modelo debe usar el trait del filtro de sede.
 * Evita que una tabla nueva quede sin aislamiento.
 */
class ModelosConSedeTest extends TestCase
{
    use RefreshDatabase;

    /** Tablas con `sede_id` que no llevan el filtro por decisión de diseño. */
    private const SIN_FILTRO = ['sede_cambios'];

    public function test_todo_modelo_de_una_tabla_con_sede_usa_el_filtro_global(): void
    {
        $modelos = [];
        foreach (File::files(app_path('Models')) as $archivo) {
            $clase = 'App\\Models\\'.$archivo->getFilenameWithoutExtension();
            if (class_exists($clase) && is_subclass_of($clase, \Illuminate\Database\Eloquent\Model::class)) {
                $modelos[(new $clase())->getTable()] = $clase;
            }
        }

        $sinCubrir = [];
        foreach (Schema::getTableListing(schema: null, schemaQualified: false) as $tabla) {
            if (!Schema::hasColumn($tabla, 'sede_id') || in_array($tabla, self::SIN_FILTRO, true)) {
                continue;
            }

            $clase = $modelos[$tabla] ?? null;

            if ($clase === null || !in_array(PerteneceASede::class, class_uses_recursive($clase), true)) {
                $sinCubrir[] = $tabla;
            }
        }

        $this->assertSame([], $sinCubrir, 'Tablas con sede_id cuyo modelo no usa PerteneceASede: '.implode(', ', $sinCubrir));
    }

    public function test_las_tablas_de_datos_esperadas_tienen_sede_id(): void
    {
        foreach (['personas', 'redes', 'celulas', 'celula_reuniones', 'encuentros', 'eventos', 'evento_persona', 'evento_pagos', 'ministerios', 'persona_cambios', 'users'] as $tabla) {
            $this->assertTrue(Schema::hasColumn($tabla, 'sede_id'), "La tabla {$tabla} no tiene sede_id");
        }
    }
}
