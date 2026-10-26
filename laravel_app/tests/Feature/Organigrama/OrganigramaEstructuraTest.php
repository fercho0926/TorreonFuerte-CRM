<?php

namespace Tests\Feature\Organigrama;

use App\Models\Celula;
use App\Models\Persona;
use App\Services\OrganigramaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class OrganigramaEstructuraTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private function arbol(\App\Models\Sede $sede): array
    {
        return app(OrganigramaService::class)->construir($sede, \App\Http\Controllers\Controller::usuarioDeLaPeticion());
    }

    private function nombres(array $nodos): array
    {
        return array_map(fn ($n) => $n['titulo'], $nodos);
    }

    public function test_pastores_lideres_y_miembros_de_la_sede(): void
    {
        $sede = $this->crearSede(['nombre' => 'Sede Norte']);
        $pastor = Persona::factory()->deSede($sede->id)->create(['nombres' => 'Pablo', 'apellidos' => 'Pastor']);
        $pastora = Persona::factory()->deSede($sede->id)->create(['nombres' => 'Paula', 'apellidos' => 'Pastora']);
        $sede->update(['pastor_persona_id' => $pastor->id, 'pastora_persona_id' => $pastora->id]);

        $liderB = Persona::factory()->deSede($sede->id)->lider()->create(['nombres' => 'Ana', 'apellidos' => 'Beltrán']);
        $liderA = Persona::factory()->deSede($sede->id)->lider()->create(['nombres' => 'Luis', 'apellidos' => 'Acosta']);
        Persona::factory()->deSede($sede->id)->create(['nombres' => 'Zoe', 'apellidos' => 'Zapata', 'lider_id' => $liderA->id]);
        Persona::factory()->deSede($sede->id)->create(['nombres' => 'Eva', 'apellidos' => 'Ávila', 'lider_id' => $liderA->id]);

        $this->comoAdminDeOtraSedeEn($sede);
        $resultado = $this->arbol($sede);
        $raiz = $resultado['raiz'];

        $this->assertSame('arbol', $resultado['estado']);
        $this->assertSame('sede', $raiz['tipo']);
        $this->assertSame('Sede Norte', $raiz['titulo']);
        $this->assertSame(['Pablo Pastor', 'Paula Pastora'], array_column($raiz['pastores'], 'nombre'));
        $this->assertFalse($raiz['sinPastor']);
        $this->assertSame(['Luis Acosta', 'Ana Beltrán'], $this->nombres($raiz['hijos']));
        $this->assertSame(2, $raiz['totalHijos']);
        $this->assertSame('2 líderes', $raiz['etiquetaHijos']);

        $lider = $raiz['hijos'][0];
        $this->assertSame(['Eva Ávila', 'Zoe Zapata'], $this->nombres($lider['hijos']));
        $this->assertSame('2 miembros', $lider['etiquetaHijos']);
        $this->assertSame(0, $raiz['hijos'][1]['totalHijos']);
        $this->assertSame('0 miembros', $raiz['hijos'][1]['etiquetaHijos']);
    }

    public function test_miembros_por_celula_sin_duplicados_y_el_lider_no_es_su_propio_miembro(): void
    {
        $sede = $this->crearSede();
        $lider = Persona::factory()->deSede($sede->id)->lider()->create();
        $coLider = Persona::factory()->deSede($sede->id)->lider()->create();
        $ambos = Persona::factory()->deSede($sede->id)->create(['lider_id' => $lider->id]);
        $soloCelula = Persona::factory()->deSede($sede->id)->enCelula()->create();

        $celula = Celula::factory()->create(['sede_id' => $sede->id, 'lider_id' => $lider->id, 'co_lider_id' => $coLider->id]);
        $celula->miembros()->attach([$ambos->id, $soloCelula->id, $lider->id]);

        $this->comoAdminDeOtraSedeEn($sede);
        $hijos = collect($this->arbol($sede)['raiz']['hijos'])->keyBy('id');

        $this->assertSame([$ambos->id, $soloCelula->id], collect($hijos[$lider->id]['hijos'])->pluck('id')->sort()->values()->all());
        $this->assertNotContains($lider->id, collect($hijos[$lider->id]['hijos'])->pluck('id')->all());
        // El co-líder también ve a los miembros de la célula que co-lidera (incluido el otro líder, que es miembro).
        $this->assertSame(3, count($hijos[$coLider->id]['hijos']));
    }

    public function test_inactivos_y_eliminados_no_aparecen(): void
    {
        $sede = $this->crearSede();
        $lider = Persona::factory()->deSede($sede->id)->lider()->create();
        $inactivo = Persona::factory()->deSede($sede->id)->inactiva()->create(['lider_id' => $lider->id]);
        $eliminado = Persona::factory()->deSede($sede->id)->create(['lider_id' => $lider->id]);
        $eliminado->delete();
        $liderInactivo = Persona::factory()->deSede($sede->id)->lider()->inactiva()->create();
        $visible = Persona::factory()->deSede($sede->id)->create(['lider_id' => $lider->id]);

        $this->comoAdminDeOtraSedeEn($sede);
        $raiz = $this->arbol($sede)['raiz'];

        $this->assertSame([$lider->id], collect($raiz['hijos'])->pluck('id')->all());
        $this->assertSame([$visible->id], collect($raiz['hijos'][0]['hijos'])->pluck('id')->all());
        $this->assertNotContains($liderInactivo->id, collect($raiz['hijos'])->pluck('id')->all());
        $this->assertNotContains($inactivo->id, collect($raiz['hijos'][0]['hijos'])->pluck('id')->all());
    }

    public function test_sede_sin_pastores_marca_el_aviso(): void
    {
        $sede = $this->crearSede();
        Persona::factory()->deSede($sede->id)->lider()->create();

        $this->comoAdminDeOtraSedeEn($sede);
        $raiz = $this->arbol($sede)['raiz'];

        $this->assertTrue($raiz['sinPastor']);
        $this->assertSame([], $raiz['pastores']);
    }

    public function test_sede_vacia_devuelve_estado_vacio(): void
    {
        $sede = $this->crearSede();

        $this->comoAdminDeOtraSedeEn($sede);
        $resultado = $this->arbol($sede);

        $this->assertSame('vacio', $resultado['estado']);
    }

    public function test_personas_sin_lider_se_agrupan(): void
    {
        $sede = $this->crearSede();
        $sinLider = Persona::factory()->deSede($sede->id)->create(['nombres' => 'Suelto', 'apellidos' => 'Solo']);

        $this->comoAdminDeOtraSedeEn($sede);
        $raiz = $this->arbol($sede)['raiz'];
        $grupo = collect($raiz['hijos'])->firstWhere('tipo', 'sin_lider');

        $this->assertNotNull($grupo);
        $this->assertSame('Sin líder asignado', $grupo['titulo']);
        $this->assertSame([$sinLider->id], collect($grupo['hijos'])->pluck('id')->all());
    }

    public function test_el_numero_de_consultas_no_crece_con_los_miembros(): void
    {
        $sede = $this->crearSede();
        foreach (range(1, 20) as $i) {
            $lider = Persona::factory()->deSede($sede->id)->lider()->create();
            Persona::factory()->deSede($sede->id)->count(25)->create(['lider_id' => $lider->id]);
        }

        $this->comoAdminDeOtraSedeEn($sede);
        $this->arbol($sede); // calienta cachés (usuario de la petición)

        DB::flushQueryLog();
        DB::enableQueryLog();
        $resultado = $this->arbol($sede);
        $consultas = count(DB::getQueryLog());

        $this->assertSame(20, $resultado['raiz']['totalHijos']);
        $this->assertLessThanOrEqual(10, $consultas, "Demasiadas consultas: {$consultas}");
    }
}
