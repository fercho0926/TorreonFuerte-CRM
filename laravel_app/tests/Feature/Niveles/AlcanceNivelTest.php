<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Services\CaminoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Alcances `enNivel` y `sinNivel` de Persona: misma definición de nivel actual que el camino (spec 014, D5). */
class AlcanceNivelTest extends TestCase
{
    use RefreshDatabase;

    private function nivel(int $posicion): NivelFormacion
    {
        return NivelFormacion::where('posicion', $posicion)->firstOrFail();
    }

    private function miembro(string $nombre, int ...$posiciones): Persona
    {
        $p = Persona::factory()->create(['nombres' => $nombre]);
        foreach ($posiciones as $posicion) {
            AvanceNivel::factory()->de($p, $this->nivel($posicion))->create();
        }

        return $p;
    }

    public function test_en_nivel_devuelve_a_quienes_tienen_ese_nivel_como_el_mas_alto(): void
    {
        $a = $this->miembro('Ana', 1);
        $b = $this->miembro('Beto', 1, 2);
        $c = $this->miembro('Carla', 1, 2, 3);
        $d = $this->miembro('Dario', 2);
        $this->miembro('Eva');

        $this->assertEqualsCanonicalizing([$b->id, $d->id], Persona::enNivel($this->nivel(2)->id)->pluck('id')->all());
        $this->assertSame([$a->id], Persona::enNivel($this->nivel(1)->id)->pluck('id')->all());
        $this->assertSame([$c->id], Persona::enNivel($this->nivel(3)->id)->pluck('id')->all());
        $this->assertSame([], Persona::enNivel($this->nivel(4)->id)->pluck('id')->all());
        $this->assertSame([], Persona::enNivel(999999)->pluck('id')->all());
    }

    public function test_sin_nivel_devuelve_a_quienes_no_completaron_ninguno(): void
    {
        $this->miembro('Ana', 1);
        $e = $this->miembro('Eva');

        $this->assertSame([$e->id], Persona::sinNivel()->pluck('id')->all());
    }

    public function test_un_nivel_desactivado_no_cuenta_como_actual(): void
    {
        $b = $this->miembro('Beto', 1, 2);
        $this->nivel(2)->update(['activo' => false]);

        $this->assertSame([$b->id], Persona::enNivel($this->nivel(1)->id)->pluck('id')->all(), 'el actual pasa a ser el 1');
        $this->assertSame([], Persona::enNivel($this->nivel(2)->id)->pluck('id')->all());

        $solo2 = $this->miembro('Solo dos', 2);
        $this->assertContains($solo2->id, Persona::sinNivel()->pluck('id')->all(), 'quien solo tiene un nivel desactivado queda sin iniciar');
    }

    public function test_coincide_con_el_camino_de_cada_miembro(): void
    {
        $miembros = [
            $this->miembro('Uno', 1),
            $this->miembro('Dos', 1, 2),
            $this->miembro('Saltado', 1, 3),
            $this->miembro('Todos', 1, 2, 3, 4),
            $this->miembro('Nadie'),
        ];
        $servicio = app(CaminoService::class);

        foreach ($miembros as $miembro) {
            $actual = $servicio->camino($miembro)['actual'];
            $enLista = $actual
                ? Persona::enNivel($actual->id)->whereKey($miembro->id)->exists()
                : Persona::sinNivel()->whereKey($miembro->id)->exists();

            $this->assertTrue($enLista, "{$miembro->nombres} debe aparecer en su nivel actual");
        }
    }
}
