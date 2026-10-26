<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Migración 1 de la spec 016: `personas.celular_clave` y `users.revision_vinculo`. */
class MigracionColumnasTest extends TestCase
{
    use RefreshDatabase;

    public function test_existen_las_columnas_nuevas(): void
    {
        $this->assertTrue(Schema::hasColumn('personas', 'celular_clave'));
        $this->assertTrue(Schema::hasColumn('users', 'revision_vinculo'));

        $indices = collect(Schema::getIndexes('personas'))->pluck('columns')->flatten();
        $this->assertContains('celular_clave', $indices->all());
    }

    public function test_la_migracion_rellena_la_clave_de_los_miembros_existentes(): void
    {
        $persona = Persona::factory()->create(['celular' => '+57 310 555 9999']);
        DB::table('personas')->where('id', $persona->id)->update(['celular_clave' => null]);

        $migracion = require database_path('migrations/2026_11_01_000001_add_celular_clave_y_revision_vinculo.php');
        $migracion->rellenar();

        $this->assertSame('3105559999', DB::table('personas')->where('id', $persona->id)->value('celular_clave'));
    }
}
