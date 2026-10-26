<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Filtro y orden por «Nivel» en el listado de miembros (spec 014, RF-017). */
class FiltroNivelMiembrosTest extends TestCase
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

    private function miembro(string $nombres, int ...$posiciones): Persona
    {
        $p = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => $nombres, 'apellidos' => 'Filtro']);
        foreach ($posiciones as $posicion) {
            AvanceNivel::factory()->de($p, $this->nivel($posicion))->create();
        }

        return $p;
    }

    private function nombres(array $parametros): array
    {
        $texto = strip_tags(json_encode($this->getJson(route('personas.datos', $parametros + ['length' => 100, 'q' => 'Filtro']))->assertOk()->json('data'), JSON_UNESCAPED_UNICODE));

        return array_values(array_filter(['Uno', 'Dos', 'Tres', 'Cero'], fn ($n) => str_contains($texto, "{$n} Filtro")));
    }

    public function test_el_filtro_por_nivel_devuelve_solo_a_quienes_lo_tienen_como_actual(): void
    {
        $this->miembro('Uno', 1);
        $this->miembro('Dos', 1, 2);
        $this->miembro('Tres', 1, 2, 3);
        $this->miembro('Cero');

        $this->assertSame(['Uno'], $this->nombres(['nivel' => $this->nivel(1)->id]));
        $this->assertSame(['Dos'], $this->nombres(['nivel' => $this->nivel(2)->id]));
        $this->assertSame(['Tres'], $this->nombres(['nivel' => $this->nivel(3)->id]));
        $this->assertSame(['Cero'], $this->nombres(['nivel' => 'sin']));
    }

    public function test_el_filtro_se_ofrece_en_la_pagina_con_los_niveles_y_sin_iniciar(): void
    {
        $html = $this->get(route('personas.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-filtro="nivel"', $html);
        $this->assertStringContainsString('Libres para servir', $html);
        $this->assertStringContainsString('Sin iniciar', $html);
    }

    public function test_las_tarjetas_de_niveles_y_el_filtro_coinciden(): void
    {
        $this->miembro('Uno', 1);
        $this->miembro('Dos', 1, 2);
        $this->miembro('Tres', 1, 2);

        $resumen = app(\App\Services\NivelesService::class)->resumen($this->A['pastor']);
        $enSegundo = collect($resumen['niveles'])->firstWhere(fn ($f) => $f['nivel']->posicion === 2)['total'];
        $filtrados = Persona::query()->where('sede_id', $this->A['sede']->id)->activas()->enNivel($this->nivel(2)->id)->count();

        $this->assertSame(2, $enSegundo);
        $this->assertSame($enSegundo, $filtrados);
    }

    public function test_se_puede_ordenar_por_nivel(): void
    {
        $this->miembro('Tres', 1, 2, 3);
        $this->miembro('Cero');

        $asc = $this->getJson(route('personas.datos', ['q' => 'Filtro', 'length' => 10, 'order' => [['column' => 4, 'dir' => 'asc']]]))->assertOk()->json('data');
        $desc = $this->getJson(route('personas.datos', ['q' => 'Filtro', 'length' => 10, 'order' => [['column' => 4, 'dir' => 'desc']]]))->assertOk()->json('data');

        $this->assertStringContainsString('Cero', $asc[0][0]);
        $this->assertStringContainsString('Tres', $desc[0][0]);
    }
}
