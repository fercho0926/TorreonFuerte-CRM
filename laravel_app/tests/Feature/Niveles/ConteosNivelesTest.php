<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Services\NivelesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Tarjetas y conteos de «Quién está en cada nivel» (spec 014, HU4, RF-015). */
class ConteosNivelesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function nivel(int $posicion): NivelFormacion
    {
        return NivelFormacion::where('posicion', $posicion)->firstOrFail();
    }

    private function miembro(string $nombre, int ...$posiciones): Persona
    {
        $p = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => $nombre]);
        foreach ($posiciones as $posicion) {
            AvanceNivel::factory()->de($p, $this->nivel($posicion))->create();
        }

        return $p;
    }

    private function totales(): array
    {
        $r = app(NivelesService::class)->resumen($this->A['pastor']);

        return ['niveles' => $r['niveles']->map(fn ($f) => $f['total'])->all(), 'sin' => $r['sin_iniciar'], 'total' => $r['total']];
    }

    public function test_cuenta_a_cada_miembro_activo_en_su_nivel_actual_y_los_sin_iniciar(): void
    {
        $this->miembro('Ana', 1);
        $this->miembro('Beto', 1, 2);
        $this->miembro('Carla', 2);
        $this->miembro('Dario', 1, 2, 3, 4);
        $base = $this->totales();

        $this->assertSame([1, 2, 0, 1], $base['niveles']);
        $this->assertSame($base['total'] - 4, $base['sin']);
    }

    public function test_los_inactivos_no_cuentan_y_los_niveles_desactivados_tampoco(): void
    {
        $this->miembro('Ana', 1, 2);
        $inactiva = $this->miembro('Inactiva', 1);
        $inactiva->update(['estado' => 'inactivo', 'inactivado_en' => now()]);
        $antes = $this->totales();
        $this->assertSame(1, $antes['niveles'][1]);
        $this->assertSame(0, $antes['niveles'][0], 'la inactiva no suma');

        $this->nivel(2)->update(['activo' => false]);
        $despues = $this->totales();

        $this->assertCount(3, $despues['niveles']);
        $this->assertSame(1, $despues['niveles'][0], 'Ana pasa a tener el 1 como actual');
    }

    public function test_las_tarjetas_de_la_pagina_coinciden_con_el_servicio(): void
    {
        $this->miembro('Ana', 1);
        $this->miembro('Beto', 1, 2);

        $html = $this->get(route('niveles.index'))->assertOk()
            ->assertSee('Quién está en cada nivel')->assertSee('Sin iniciar')
            ->assertSee(route('niveles.miembros.index', ['nivel' => $this->nivel(2)->id]), false)
            ->assertSee(route('niveles.miembros.index', ['nivel' => 'sin']), false)
            ->getContent();

        $this->assertMatchesRegularExpression('/Libres para servir<\/span>\s*<span class="nivel-tarjeta-total">1 miembro</', $html);
        $this->assertMatchesRegularExpression('/Aliviando el fuego<\/span>\s*<span class="nivel-tarjeta-total">1 miembro</', $html);
    }

    public function test_los_conteos_coinciden_con_el_camino_de_cada_miembro(): void
    {
        $miembros = [$this->miembro('Uno', 1), $this->miembro('Dos', 1, 2), $this->miembro('Saltado', 1, 3), $this->miembro('Nadie')];
        $porNivel = [];
        foreach ($miembros as $m) {
            $actual = app(\App\Services\CaminoService::class)->camino($m)['actual']?->posicion ?? 0;
            $porNivel[$actual] = ($porNivel[$actual] ?? 0) + 1;
        }
        $totales = $this->totales();

        foreach ([1, 2, 3] as $posicion) {
            $this->assertSame($porNivel[$posicion] ?? 0, $totales['niveles'][$posicion - 1], "nivel {$posicion}");
        }
    }

    public function test_respeta_el_alcance_de_sede(): void
    {
        $this->miembro('Ana', 1);
        AvanceNivel::factory()->de($this->B['persona'], $this->nivel(1))->create();

        $a = $this->totales()['niveles'][0];
        $this->iniciarSesionComo($this->B['pastor']);
        $b = app(NivelesService::class)->resumen($this->B['pastor'])['niveles']->first()['total'];

        $this->assertSame(1, $a);
        $this->assertSame(1, $b);
    }

    public function test_hace_un_numero_fijo_de_consultas_sin_importar_cuantos_miembros_haya(): void
    {
        $this->totales();
        \DB::enableQueryLog();
        $this->totales();
        $base = count(\DB::getQueryLog());
        \DB::flushQueryLog();

        foreach (range(1, 30) as $i) {
            $this->miembro("Miembro{$i}", 1);
        }
        \DB::flushQueryLog();
        $this->totales();
        $con = count(\DB::getQueryLog());
        \DB::disableQueryLog();

        $this->assertSame($base, $con);
    }
}
