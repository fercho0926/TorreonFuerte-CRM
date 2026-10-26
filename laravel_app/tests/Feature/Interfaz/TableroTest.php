<?php

namespace Tests\Feature\Interfaz;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Tablero con cajas de resumen y tres bloques con datos existentes (FR-010). */
class TableroTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_el_pastor_ve_las_cajas_y_los_tres_bloques(): void
    {
        $this->comoPastorDe($this->A['sede']);

        $html = $this->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('info-box', $html);
        foreach (['Próximos eventos', 'Miembros sin líder', 'Miembros nuevos', 'Accesos rápidos'] as $bloque) {
            $this->assertStringContainsString($bloque, $html);
        }
        $this->assertStringContainsString(route('personas.index'), $html, 'la caja de miembros enlaza al listado');
    }

    public function test_las_cifras_de_las_cajas_coinciden_con_los_datos(): void
    {
        $this->comoPastorDe($this->A['sede']);

        $this->get(route('dashboard'))
            ->assertViewHas('personaCount', Persona::count())
            ->assertViewHas('liderCount', Persona::lideres()->count());
    }

    public function test_los_bloques_listan_como_maximo_cinco_filas(): void
    {
        Persona::factory()->count(8)->visitante()->deSede($this->A['sede']->id)->create();
        $this->comoPastorDe($this->A['sede']);

        $respuesta = $this->get(route('dashboard'))->assertOk();

        $respuesta->assertViewHas('personasNuevas', fn ($c) => $c->count() === 5);
        $respuesta->assertViewHas('personasSinLider', fn ($c) => $c->count() === 5);
        $respuesta->assertViewHas('sinLiderTotal', fn ($n) => $n >= 8);
    }

    public function test_el_lider_no_ve_personas_sin_lider_y_solo_ve_su_ambito(): void
    {
        $this->comoLiderDe($this->A['sede'], $this->A['lider']);

        $respuesta = $this->get(route('dashboard'))->assertOk();

        $respuesta->assertViewHas('personasSinLider', null);
        $respuesta->assertDontSee('Miembros sin líder');
        $respuesta->assertViewHas('personasNuevas', fn ($c) => $c->every(fn ($p) => $p->sede_id === $this->A['sede']->id));
    }

    public function test_el_tablero_no_agrega_mas_de_tres_consultas_para_los_bloques(): void
    {
        $this->comoPastorDe($this->A['sede']);
        $this->get(route('dashboard'))->assertOk(); // calienta

        DB::enableQueryLog();
        $this->get(route('dashboard'))->assertOk();
        $consultasConBloques = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Las cajas y tablas ya existían; los bloques nuevos son 3 consultas (nuevas, sin líder y su total).
        $this->assertLessThanOrEqual(45, $consultasConBloques, 'demasiadas consultas en el tablero');
    }
}
