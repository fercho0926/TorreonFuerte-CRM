<?php

namespace Tests\Feature\Niveles;

use App\Models\NivelFormacion;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Permisos del módulo Niveles según el rol (spec 014, RF-020). */
class PermisosNivelesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function entrarCon(int $nivel): void
    {
        $rol = Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'miembros' => 1, 'niveles' => $nivel])->create();
        $this->iniciarSesionComo($this->crearUsuario($rol->clave, $this->A['lider'], $this->A['sede']->id));
    }

    public function test_sin_acceso_no_hay_menu_y_las_rutas_responden_403(): void
    {
        $this->entrarCon(0);
        $nivel = NivelFormacion::first();

        $this->get(route('dashboard'))->assertOk()->assertDontSee(route('niveles.index'), false);
        $this->get(route('niveles.index'))->assertForbidden();
        $this->get(route('niveles.create'))->assertForbidden();
        $this->post(route('niveles.store'), ['nombre' => 'X'])->assertForbidden();
        $this->post(route('niveles.subir', $nivel))->assertForbidden();
        $this->getJson(route('niveles.miembros.datos'))->assertForbidden();
    }

    public function test_con_solo_lectura_se_ve_el_plan_sin_botones_y_no_se_cambia_nada(): void
    {
        $this->entrarCon(1);
        $nivel = NivelFormacion::first();

        $this->get(route('dashboard'))->assertSee(route('niveles.index'), false);
        $this->get(route('niveles.index'))->assertOk()->assertSee('Libres para servir')
            ->assertDontSee(route('niveles.create'), false)
            ->assertDontSee(route('niveles.subir', $nivel), false)
            ->assertDontSee(route('niveles.destroy', $nivel), false);

        $this->get(route('niveles.create'))->assertForbidden();
        $this->get(route('niveles.edit', $nivel))->assertForbidden();
        $this->post(route('niveles.store'), ['nombre' => 'X'])->assertForbidden();
        $this->put(route('niveles.update', $nivel), ['nombre' => 'Y'])->assertForbidden();
        $this->post(route('niveles.subir', $nivel))->assertForbidden();
        $this->post(route('niveles.bajar', $nivel))->assertForbidden();
        $this->post(route('niveles.desactivar', $nivel))->assertForbidden();
        $this->post(route('niveles.reactivar', $nivel))->assertForbidden();
        $this->delete(route('niveles.destroy', $nivel))->assertForbidden();
        $this->post(route('niveles.avances.store'), ['persona_id' => $this->A['persona']->id, 'nivel_id' => $nivel->id])->assertForbidden();
        $this->assertSame(4, NivelFormacion::count());
    }

    public function test_con_escritura_aparecen_los_controles_y_todo_funciona(): void
    {
        $this->entrarCon(2);
        $nivel = NivelFormacion::where('posicion', 2)->first();

        $this->get(route('niveles.index'))->assertOk()
            ->assertSee(route('niveles.create'), false)
            ->assertSee(route('niveles.subir', $nivel), false)
            ->assertSee(route('niveles.edit', $nivel), false);
        $this->post(route('niveles.subir', $nivel))->assertRedirect(route('niveles.index'));
    }

    public function test_el_administrador_en_todas_las_sedes_puede_administrar_el_plan_porque_es_comun(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('niveles.store'), ['nombre' => 'Desde todas'])->assertRedirect(route('niveles.index'));

        $this->assertTrue(NivelFormacion::where('nombre', 'Desde todas')->exists());
    }

    public function test_los_roles_de_origen_tienen_los_permisos_acordados(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']); // rol Líder: lectura
        $this->get(route('niveles.index'))->assertOk()->assertDontSee(route('niveles.create'), false);
        $this->post(route('niveles.store'), ['nombre' => 'X'])->assertForbidden();

        $this->iniciarSesionComo($this->A['pastor']); // escritura
        $this->post(route('niveles.store'), ['nombre' => 'Del pastor'])->assertRedirect();
    }
}
