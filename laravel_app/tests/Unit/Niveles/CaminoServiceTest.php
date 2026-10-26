<?php

namespace Tests\Unit\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Services\CaminoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Estados del camino de un miembro (spec 014, RF-013 y RF-014). */
class CaminoServiceTest extends TestCase
{
    use RefreshDatabase;

    private CaminoService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = app(CaminoService::class);
    }

    private function nivel(int $posicion): NivelFormacion
    {
        return NivelFormacion::where('posicion', $posicion)->firstOrFail();
    }

    private function completar(Persona $p, int ...$posiciones): void
    {
        foreach ($posiciones as $posicion) {
            AvanceNivel::factory()->de($p, $this->nivel($posicion))->create();
        }
    }

    private function estados(array $camino): array
    {
        return array_map(fn ($e) => $e['estado'], $camino['estaciones']);
    }

    public function test_sin_avances_esta_sin_iniciar_y_el_siguiente_es_el_primero(): void
    {
        $camino = $this->servicio->camino(Persona::factory()->create());

        $this->assertTrue($camino['sin_iniciar']);
        $this->assertFalse($camino['terminado']);
        $this->assertNull($camino['actual']);
        $this->assertSame('Libres para servir', $camino['siguiente']->nombre);
        $this->assertSame(['siguiente', 'pendiente', 'pendiente', 'pendiente'], $this->estados($camino));
        $this->assertSame([1, 2, 3, 4], array_column($camino['estaciones'], 'numero'));
    }

    public function test_con_dos_niveles_el_actual_es_el_segundo_y_el_siguiente_el_tercero(): void
    {
        $p = Persona::factory()->create();
        $this->completar($p, 1, 2);

        $camino = $this->servicio->camino($p);

        $this->assertSame(['completado', 'completado', 'siguiente', 'pendiente'], $this->estados($camino));
        $this->assertSame('Aliviando el fuego', $camino['actual']->nombre);
        $this->assertSame('Caminando en santidad', $camino['siguiente']->nombre);
        $this->assertSame(2, $camino['completados']);
        $this->assertSame(4, $camino['total']);
        $this->assertFalse($camino['sin_iniciar']);
    }

    public function test_con_todos_los_niveles_el_camino_esta_terminado(): void
    {
        $p = Persona::factory()->create();
        $this->completar($p, 1, 2, 3, 4);

        $camino = $this->servicio->camino($p);

        $this->assertTrue($camino['terminado']);
        $this->assertNull($camino['siguiente']);
        $this->assertSame('Viviendo en el espíritu', $camino['actual']->nombre);
    }

    public function test_un_nivel_saltado_queda_pendiente_y_es_el_siguiente(): void
    {
        $p = Persona::factory()->create();
        $this->completar($p, 1, 3);

        $camino = $this->servicio->camino($p);

        $this->assertSame(['completado', 'siguiente', 'completado', 'pendiente'], $this->estados($camino));
        $this->assertTrue($camino['estaciones'][1]['saltado']);
        $this->assertFalse($camino['estaciones'][3]['saltado'], 'el 4 no está saltado: es posterior al actual');
        $this->assertSame('Caminando en santidad', $camino['actual']->nombre);
        $this->assertSame('Aliviando el fuego', $camino['siguiente']->nombre);
    }

    public function test_los_niveles_inactivos_no_se_dibujan_ni_cuentan(): void
    {
        $p = Persona::factory()->create();
        $this->completar($p, 1, 2);
        $this->nivel(2)->update(['activo' => false]);

        $camino = $this->servicio->camino($p);

        $this->assertSame(3, $camino['total']);
        $this->assertSame(1, $camino['completados']);
        $this->assertSame('Libres para servir', $camino['actual']->nombre);
        $this->assertSame('Caminando en santidad', $camino['siguiente']->nombre);
    }

    public function test_sin_niveles_devuelve_una_estructura_vacia_sin_error(): void
    {
        NivelFormacion::query()->update(['activo' => false]);

        $camino = $this->servicio->camino(Persona::factory()->create());

        $this->assertSame([], $camino['estaciones']);
        $this->assertSame(0, $camino['total']);
        $this->assertFalse($camino['terminado']);
        $this->assertNull($camino['actual']);
        $this->assertNull($camino['siguiente']);
    }

    public function test_el_camino_hace_pocas_consultas(): void
    {
        $p = Persona::factory()->create();
        $this->completar($p, 1, 2);

        \DB::enableQueryLog();
        $this->servicio->camino($p);
        $consultas = count(\DB::getQueryLog());
        \DB::disableQueryLog();

        $this->assertLessThanOrEqual(4, $consultas, 'niveles, avances y sus relaciones precargadas');
    }
}
