<?php

namespace Tests\Feature\Lideres;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\Sede;
use App\Services\LiderazgoService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Invariantes del árbol de liderazgo: una sola cabeza por sede (spec 016, data-model). */
class InvariantesLiderazgoTest extends TestCase
{
    use RefreshDatabase;

    private function assertInvariantes(): void
    {
        foreach (Sede::all() as $sede) {
            $pastorales = array_values(array_filter([$sede->pastor_persona_id, $sede->pastora_persona_id]));

            foreach (Persona::withoutGlobalScopes()->whereKey($pastorales)->get() as $cabeza) {
                $this->assertTrue($cabeza->es_lider, "{$cabeza->nombre_completo} debe ser líder");
                $this->assertNull($cabeza->lider_id, "{$cabeza->nombre_completo} no debe tener líder");
            }

            $sueltos = Persona::withoutGlobalScopes()->where('sede_id', $sede->id)->where('es_lider', true)
                ->whereNull('lider_id')->whereNotIn('id', $pastorales)->pluck('nombres')->all();
            $this->assertSame([], $sueltos, "Líderes sin líder en {$sede->nombre}");

            foreach (Persona::withoutGlobalScopes()->where('sede_id', $sede->id)->whereNotNull('lider_id')->get() as $persona) {
                $vistos = [];
                $actual = $persona;
                while ($actual?->lider_id) {
                    $this->assertNotContains($actual->id, $vistos, 'ciclo de liderazgo');
                    $vistos[] = $actual->id;
                    $actual = Persona::withoutGlobalScopes()->find($actual->lider_id);
                }
            }
        }
    }

    public function test_los_datos_sembrados_cumplen_las_invariantes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertInvariantes();
    }

    public function test_la_correccion_pone_a_los_lideres_sueltos_bajo_el_pastor_y_se_puede_repetir(): void
    {
        $sede = Sede::factory()->create();
        $pastor = Persona::factory()->deSede($sede->id)->create();
        $pastora = Persona::factory()->deSede($sede->id)->create();
        $sede->update(['pastor_persona_id' => $pastor->id, 'pastora_persona_id' => $pastora->id]);
        $suelto = Persona::factory()->deSede($sede->id)->lider()->create();
        $pastora->update(['lider_id' => $suelto->id]);
        $sinPastor = Sede::factory()->create(['nombre' => 'Sede Sin Pastor']);

        $resultado = app(LiderazgoService::class)->corregirCabezas();

        $this->assertSame(['Sede Sin Pastor'], $resultado['sedes_sin_pastor']);
        $this->assertSame($pastor->id, $suelto->fresh()->lider_id);
        $this->assertInvariantes();
        $this->assertSame('sistema', CambioHistorial::query()->de('persona', $suelto->id)->where('accion', 'lider')->latest('id')->value('actor'));

        $segunda = app(LiderazgoService::class)->corregirCabezas();
        $this->assertSame(0, $segunda['corregidos']);
        $this->assertNotNull($sinPastor);
    }
}
