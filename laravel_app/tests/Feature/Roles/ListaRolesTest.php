<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class ListaRolesTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_el_administrador_ve_los_cuatro_roles_con_su_alcance_y_usuarios(): void
    {
        $sede = $this->crearSede();
        $this->crearUsuario('user', Persona::factory()->deSede($sede->id)->create(), $sede->id);
        $this->comoAdmin();

        $this->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Administrador')
            ->assertSee('Pastor')
            ->assertSee('Líder')
            ->assertSee('Usuario')
            ->assertSee('data-url="'.route('roles.datos').'"', false)
            ->assertHeader('Cache-Control', 'no-store, private');

        $datos = $this->getJson(route('roles.datos', ['length' => 25, 'start' => 0, 'draw' => 1]))->assertOk()->json('data');

        $this->assertCount(4, $datos);
        $texto = json_encode($datos, JSON_UNESCAPED_UNICODE);
        foreach (['Administrador', 'Pastor', 'Líder', 'Usuario', 'Toda la sede', 'Solo su rama', 'Solo su registro', 'Sistema'] as $esperado) {
            $this->assertStringContainsString($esperado, $texto);
        }
    }

    public function test_los_datos_respetan_orden_filtro_y_paginacion(): void
    {
        $this->comoAdmin();
        Rol::factory()->count(8)->create();

        $primera = $this->getJson(route('roles.datos', ['length' => 10, 'start' => 0, 'draw' => 1, 'order' => [['column' => 0, 'dir' => 'asc']]]))
            ->assertOk()->json();

        $this->assertSame(12, $primera['recordsTotal']);
        $this->assertCount(10, $primera['data']);

        $segunda = $this->getJson(route('roles.datos', ['length' => 10, 'start' => 10, 'draw' => 2]))->assertOk()->json();
        $this->assertCount(2, $segunda['data']);

        $nombres = array_map(fn ($fila) => strip_tags($fila[0] ?? ''), $primera['data']);
        $ordenados = $nombres;
        sort($ordenados, SORT_NATURAL | SORT_FLAG_CASE);
        $this->assertCount(10, $nombres);
    }

    public function test_el_detalle_muestra_la_matriz_con_los_niveles_sembrados(): void
    {
        $this->comoAdmin();

        $this->get(route('roles.show', 'pastor'))
            ->assertOk()
            ->assertSee('Pastor')
            ->assertSee('Eventos')
            ->assertSee('Registro de miembros por QR')
            ->assertSee('Escritura')
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get(route('roles.show', 'user'))->assertOk()->assertSee('Solo su registro');
        $this->get(route('roles.show', 'no-existe'))->assertNotFound();
    }

    public function test_los_demas_roles_reciben_403_y_no_ven_la_pestana(): void
    {
        $this->comoPastor();
        $this->get(route('roles.index'))->assertForbidden();
        $this->getJson(route('roles.datos'))->assertForbidden();
        $this->get(route('roles.show', 'pastor'))->assertForbidden();
        $this->get(route('users.index'))->assertOk()->assertDontSee(route('roles.index'), false);

        $this->comoLider();
        $this->get(route('roles.index'))->assertForbidden();

        $this->iniciarSesionComo($this->crearUsuario('user', Persona::factory()->create()));
        $this->get(route('roles.index'))->assertForbidden();
    }

    public function test_el_administrador_ve_la_pestana_en_usuarios(): void
    {
        $this->comoAdmin();

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('nav nav-tabs', false)
            ->assertSee(route('roles.index'), false);
    }

    public function test_sin_sesion_redirige_al_inicio_de_sesion(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
        $this->getJson(route('roles.datos'))->assertUnauthorized();
    }
}
