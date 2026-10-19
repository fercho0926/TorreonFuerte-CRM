<?php

namespace Tests\Feature\Migraciones;

use App\Models\Celula;
use App\Models\Ministerio;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** La migración que retira «redes» no pierde ninguna persona, líder, célula ni ministerio (FR-014). */
class DropRedesTest extends TestCase
{
    use RefreshDatabase;

    private function migracion(): object
    {
        return require base_path('database/migrations/2026_10_25_000001_drop_redes.php');
    }

    public function test_el_esquema_actual_no_tiene_redes(): void
    {
        $this->assertFalse(Schema::hasTable('redes'));
        $this->assertFalse(Schema::hasColumn('personas', 'red_id'));
    }

    public function test_aplicarla_con_datos_conserva_todos_los_conteos(): void
    {
        $sede = \Database\Factories\SedeFactory::idPorDefecto();
        $lider = Persona::factory()->lider()->create();
        Persona::factory()->count(3)->create(['lider_id' => $lider->id]);
        Celula::factory()->create(['lider_id' => $lider->id]);
        Ministerio::create(['sede_id' => $sede, 'nombre' => 'Alabanza', 'estado' => 'activo']);

        $conteos = fn () => [
            'personas' => Persona::count(),
            'lideres' => Persona::lideres()->count(),
            'celulas' => Celula::count(),
            'ministerios' => Ministerio::count(),
        ];
        $antes = $conteos();

        $this->migracion()->up();

        $this->assertSame($antes, $conteos());
        $this->assertSame(['personas' => 4, 'lideres' => 1, 'celulas' => 1, 'ministerios' => 1], $antes);
    }

    // Con datos reales la migración se probó sobre una copia de la base local (ver quickstart.md):
    // personas 11, líderes 3, células 3 y ministerios 1, antes y después.

    public function test_aplicarla_dos_veces_no_falla(): void
    {
        $migracion = $this->migracion();

        $migracion->up();
        $migracion->up();

        $this->assertFalse(Schema::hasTable('redes'));
    }

    public function test_down_recrea_la_tabla_y_la_columna_vacias(): void
    {
        $migracion = $this->migracion();
        $migracion->down();

        $this->assertTrue(Schema::hasTable('redes'));
        $this->assertTrue(Schema::hasColumn('personas', 'red_id'));
        $this->assertSame(0, DB::table('redes')->count());

        $migracion->up();
    }
}
