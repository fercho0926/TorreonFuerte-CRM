<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PermisosAccionesTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_un_lider_no_ve_los_botones_de_designar_ni_quitar_rol(): void
    {
        $lider = Persona::factory()->lider()->create();
        $persona = Persona::factory()->create(['lider_id' => $lider->id]);
        $this->comoLider($lider);

        $this->get(route('lideres.index'))
            ->assertOk()
            ->assertDontSee('Designar líder')
            ->assertDontSee('Quitar rol');

        $this->get(route('personas.show', $persona))
            ->assertDontSee('Eliminar')
            ->assertSee('Editar');
    }

    public function test_el_pastor_ve_designar_y_sin_lider(): void
    {
        $this->comoPastor();

        $this->get(route('lideres.index'))->assertSee('Designar líder');
        $this->get(route('personas.index'))->assertSee('Sin líder asignado');
    }

    public function test_acciones_no_permitidas_responden_403_con_mensaje_claro(): void
    {
        $lider = Persona::factory()->lider()->create();
        $persona = Persona::factory()->create(['lider_id' => $lider->id]);
        $this->comoLider($lider);

        $this->get(route('lideres.quitar', $lider))
            ->assertForbidden()
            ->assertSee('No tienes permiso para esta acción');

        $this->delete(route('personas.destroy', $persona))
            ->assertForbidden()
            ->assertSee('No tienes permiso para esta acción');

    }

    /** Spec 015 (RF-021): quien ve la ficha ve su histórico; antes era solo para admin y pastor. */
    public function test_el_historico_lo_ve_quien_puede_ver_la_ficha(): void
    {
        $lider = Persona::factory()->lider()->create();
        $persona = Persona::factory()->create(['lider_id' => $lider->id]);
        $ajena = Persona::factory()->create();

        $this->comoLider($lider);
        $this->get(route('personas.show', [$persona, 'pestana' => 'historico']))->assertOk()->assertSee('Cambios de datos');
        $this->get(route('personas.show', [$ajena, 'pestana' => 'historico']))->assertNotFound();

        $this->comoPastor();
        $this->get(route('personas.show', [$persona, 'pestana' => 'historico']))->assertOk()->assertSee('Cambios de datos');
    }
}
