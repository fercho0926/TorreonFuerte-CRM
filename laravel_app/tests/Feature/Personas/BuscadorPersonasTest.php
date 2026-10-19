<?php

namespace Tests\Feature\Personas;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Búsqueda con autocompletado (FR-005, FR-012, FR-013, SC-003, SC-006). */
class BuscadorPersonasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function buscar(array $parametros = [])
    {
        return $this->getJson(route('personas.buscar', $parametros));
    }

    private function ids(array $parametros): array
    {
        return collect($this->buscar($parametros)->assertOk()->json('resultados'))->pluck('id')->all();
    }

    public function test_con_menos_de_dos_letras_no_devuelve_nada(): void
    {
        $this->buscar(['q' => 'a'])->assertOk()->assertExactJson(['resultados' => [], 'mas' => false]);
        $this->buscar()->assertOk()->assertJson(['resultados' => []]);
    }

    public function test_encuentra_por_nombre_apellido_documento_y_celular_con_el_formato_del_contrato(): void
    {
        $p = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Zulema', 'apellidos' => 'Quintero Rey', 'tipo_documento' => 'CC', 'numero_documento' => '99887766', 'celular' => '3171234567']);

        foreach (['zulema', 'quintero', '99887766', '3171234567', 'zule quin'] as $texto) {
            $this->assertContains($p->id, $this->ids(['q' => $texto]), $texto);
        }

        $this->buscar(['q' => 'zulema'])->assertJsonPath('resultados.0', [
            'id' => $p->id, 'texto' => 'Zulema Quintero Rey', 'apoyo' => 'CC 99887766 · 3171234567', 'estado' => 'miembro_activo', 'sinDocumento' => false,
        ]);
        $this->buscar(['q' => 'zulema'])->assertHeader('Cache-Control');
    }

    public function test_una_persona_sin_documento_se_marca_como_tal(): void
    {
        Persona::factory()->deSede($this->A['sede']->id)->enCelula()->create(['nombres' => 'Gema', 'apellidos' => 'Sinpapel', 'celular' => '3009998877']);

        $this->buscar(['q' => 'gema'])
            ->assertJsonPath('resultados.0.sinDocumento', true)
            ->assertJsonPath('resultados.0.apoyo', 'Sin documento · 3009998877')
            ->assertJsonPath('resultados.0.estado', 'celula');
    }

    public function test_no_devuelve_inactivas_ni_con_sin_lideres_a_los_lideres(): void
    {
        $inactiva = Persona::factory()->deSede($this->A['sede']->id)->inactiva()->create(['nombres' => 'Ines', 'apellidos' => 'Fuera']);
        $lider = $this->A['lider'];

        $this->assertNotContains($inactiva->id, $this->ids(['q' => 'ines']));
        $this->assertContains($inactiva->id, $this->ids(['q' => 'ines', 'activas' => 0]), 'con activas=0 sí aparecen');

        $this->assertContains($lider->id, $this->ids(['q' => $lider->nombres]));
        $this->assertNotContains($lider->id, $this->ids(['q' => $lider->nombres, 'sin_lideres' => 1]));
    }

    public function test_excluir_omite_los_ids_indicados(): void
    {
        $a = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Omar', 'apellidos' => 'Uno']);
        $b = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Omar', 'apellidos' => 'Dos']);

        $this->assertSame([$b->id, $a->id], $this->ids(['q' => 'omar']), 'orden por apellido: Dos, Uno');
        $this->assertSame([$b->id], $this->ids(['q' => 'omar', 'excluir' => [$a->id]]));
        $this->assertSame([$a->id], $this->ids(['q' => 'omar', 'excluir' => (string) $b->id]));
    }

    public function test_pagina_de_veinte_en_veinte(): void
    {
        Persona::factory()->count(21)->deSede($this->A['sede']->id)->create(['nombres' => 'Pagina']);

        $this->buscar(['q' => 'pagina'])->assertJsonCount(20, 'resultados')->assertJsonPath('mas', true);
        $this->buscar(['q' => 'pagina', 'pagina' => 2])->assertJsonCount(1, 'resultados')->assertJsonPath('mas', false);
    }

    public function test_un_pastor_nunca_ve_personas_de_otra_sede(): void
    {
        $beta = Persona::factory()->deSede($this->B['sede']->id)->create(['nombres' => 'Beatriz', 'apellidos' => 'Lejana']);

        $this->assertNotContains($beta->id, $this->ids(['q' => 'beatriz']));
        $this->assertNotContains($beta->id, $this->ids(['q' => 'lejana', 'activas' => 0]));

        $this->iniciarSesionComo($this->B['pastor']);
        $this->assertContains($beta->id, $this->ids(['q' => 'beatriz']));
    }

    public function test_un_lider_solo_ve_su_ambito(): void
    {
        $suya = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Suya', 'apellidos' => 'Aldia', 'lider_id' => $this->A['lider']->id]);
        $ajena = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Suya', 'apellidos' => 'Ajena']);

        $this->iniciarSesionComo($this->A['liderUser']);

        $this->assertContains($suya->id, $this->ids(['q' => 'suya']));
        $this->assertNotContains($ajena->id, $this->ids(['q' => 'suya']));
    }

    public function test_sin_sesion_responde_401(): void
    {
        $this->flushSession();

        $this->getJson(route('personas.buscar', ['q' => 'ana']))->assertUnauthorized();
    }

    public function test_con_500_personas_responde_en_menos_de_un_segundo(): void
    {
        Persona::factory()->count(500)->deSede($this->A['sede']->id)->create();
        Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Buscada', 'apellidos' => 'Entremuchas']);

        $inicio = microtime(true);
        $ids = $this->ids(['q' => 'entremuchas']);
        $duracion = microtime(true) - $inicio;

        $this->assertCount(1, $ids);
        $this->assertLessThan(1.0, $duracion);
    }
}
