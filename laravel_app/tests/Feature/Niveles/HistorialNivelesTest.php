<?php

namespace Tests\Feature\Niveles;

use App\Models\CambioHistorial;
use App\Models\NivelFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historial del plan de niveles (spec 014, RF-021). */
class HistorialNivelesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function acciones(): array
    {
        return CambioHistorial::where('sujeto_tipo', 'nivel')->orderBy('id')->pluck('accion')->all();
    }

    public function test_cada_cambio_del_plan_deja_su_registro_con_quien_lo_hizo(): void
    {
        $this->post(route('niveles.store'), ['nombre' => 'Nuevo']);
        $nuevo = NivelFormacion::where('nombre', 'Nuevo')->firstOrFail();
        $this->put(route('niveles.update', $nuevo), ['nombre' => 'Renombrado', 'descripcion' => 'x']);
        $this->post(route('niveles.subir', $nuevo));
        $this->post(route('niveles.desactivar', $nuevo));
        $this->post(route('niveles.reactivar', $nuevo));
        $this->delete(route('niveles.destroy', $nuevo));

        $this->assertSame(['creado', 'editado', 'reordenado', 'desactivado', 'reactivado', 'eliminado'], $this->acciones());
        $this->assertTrue(CambioHistorial::where('sujeto_tipo', 'nivel')->get()->every(fn ($c) => $c->user_id === $this->A['pastor']->id));
    }

    public function test_guarda_antes_y_despues_y_sobrevive_al_nivel_eliminado(): void
    {
        $this->post(route('niveles.store'), ['nombre' => 'Efímero']);
        $nivel = NivelFormacion::where('nombre', 'Efímero')->firstOrFail();
        $this->put(route('niveles.update', $nivel), ['nombre' => 'Efímero 2']);
        $this->delete(route('niveles.destroy', $nivel));

        $edicion = CambioHistorial::where('sujeto_tipo', 'nivel')->where('accion', 'editado')->firstOrFail();
        $this->assertSame('Efímero', $edicion->antes['nombre']);
        $this->assertSame('Efímero 2', $edicion->despues['nombre']);

        $eliminado = CambioHistorial::where('sujeto_tipo', 'nivel')->where('accion', 'eliminado')->firstOrFail();
        $this->assertNull(\App\Models\NivelFormacion::find($eliminado->sujeto_id), 'el historial sobrevive al nivel');
        $this->assertSame('Efímero 2', $eliminado->sujeto_nombre);
    }

    public function test_editar_sin_cambios_no_escribe_historial(): void
    {
        $nivel = NivelFormacion::where('posicion', 1)->firstOrFail();

        $this->put(route('niveles.update', $nivel), ['nombre' => $nivel->nombre, 'descripcion' => ''])->assertSessionHasNoErrors();

        $this->assertSame([], $this->acciones());
    }

    public function test_el_historial_se_ve_en_el_modulo(): void
    {
        $this->post(route('niveles.store'), ['nombre' => 'Visible']);

        $this->get(route('niveles.index'))->assertOk()->assertSee('Historial del plan')->assertSee('Nivel creado')->assertSee('Visible');
    }
}
