<?php

namespace Tests\Feature\Organigrama;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class OrganigramaPantallaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private function url($sede, bool $pestana = true): string
    {
        return route('sedes.show', ['sede' => $sede] + ($pestana ? ['pestana' => 'organigrama'] : []));
    }

    public function test_el_detalle_de_sede_tiene_pestanas_y_abre_en_datos(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $this->get($this->url($this->A['sede'], false))
            ->assertOk()
            ->assertSee('class="nav nav-tabs', false)
            ->assertSee('Usuarios de la sede')
            ->assertDontSee('data-organigrama', false);
    }

    public function test_el_arbol_llega_colapsado_con_contadores_y_botones_ocultos(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get($this->url($this->A['sede']))->assertOk()->getContent();

        $this->assertStringContainsString('data-organigrama', $html);
        $this->assertStringContainsString('1 líder', $html);
        $this->assertStringContainsString('1 miembro', $html);
        $this->assertStringNotContainsString('Usuarios de la sede', $html);
        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\bopen\b/', $html);
        $this->assertMatchesRegularExpression('/class="organigrama-acciones"\s+hidden/', $html);
        $this->assertStringContainsString('data-organigrama-accion="expandir"', $html);
        $this->assertStringContainsString('data-organigrama-accion="colapsar"', $html);
    }

    public function test_la_pestana_del_organigrama_no_se_guarda_en_cache(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $this->assertStringContainsString('no-store', $this->get($this->url($this->A['sede']))->headers->get('Cache-Control'));
    }

    public function test_estado_vacio_muestra_mensaje(): void
    {
        $sede = $this->crearSede();
        $this->comoAdminDeOtraSedeEn($sede);

        $this->get($this->url($sede))
            ->assertOk()
            ->assertSee('Aún no hay pastores, líderes ni miembros')
            ->assertDontSee('data-organigrama', false);
    }

    public function test_sede_sin_pastor_muestra_el_aviso(): void
    {
        $sede = $this->crearSede();
        Persona::factory()->deSede($sede->id)->lider()->create();
        $this->comoAdminEnSede($sede);

        $this->get($this->url($sede))->assertOk()->assertSee('Sin pastor asignado');
    }

    public function test_los_nombres_enlazan_a_la_ficha(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $this->get($this->url($this->A['sede']))
            ->assertSee(route('personas.show', $this->A['persona']->id), false)
            ->assertSee(route('personas.show', $this->A['pastorPersona']->id), false);
    }
}
