<?php

namespace Tests\Feature\Roles;

use App\Models\Rol;
use App\Models\CambioHistorial;
use App\Support\Modulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class CrearRolTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    /** @return array<string, mixed> */
    private function datos(array $extra = []): array
    {
        return $extra + [
            'nombre' => 'Secretaría',
            'descripcion' => 'Apoyo administrativo',
            'alcance' => 'sede',
            'asignable' => '1',
            'permisos' => ['miembros' => 1, 'eventos' => 2],
        ];
    }

    public function test_el_formulario_muestra_alcance_y_la_matriz_de_los_12_modulos(): void
    {
        $this->comoAdmin();

        $html = $this->get(route('roles.create'))->assertOk()->assertHeader('Cache-Control', 'no-store, private')->getContent();

        foreach (Modulos::claves() as $modulo) {
            $this->assertStringContainsString('name="permisos['.$modulo.']"', $html, $modulo);
        }
        $this->assertStringContainsString('name="alcance"', $html);
        $this->assertStringContainsString('name="nombre"', $html);
        $this->assertStringContainsString('Toda la sede', $html);
        $this->assertStringContainsString('data-matriz-atajos', $html);
    }

    public function test_crear_guarda_el_rol_los_12_permisos_la_clave_y_el_historial(): void
    {
        $admin = $this->comoAdmin();

        $this->post(route('roles.store'), $this->datos())->assertRedirect();

        $rol = Rol::where('nombre', 'Secretaría')->firstOrFail();
        $this->assertSame('secretaria', $rol->clave);
        $this->assertSame('sede', $rol->alcance);
        $this->assertTrue($rol->asignable);
        $this->assertFalse($rol->es_sistema);
        $this->assertCount(count(\App\Support\Modulos::claves()), $rol->permisos);

        $niveles = $rol->niveles();
        $this->assertSame(1, $niveles['miembros']);
        $this->assertSame(2, $niveles['eventos']);
        $this->assertSame(0, $niveles['usuarios']);

        $historial = CambioHistorial::where('sujeto_tipo', 'rol')->where('detalle->clave', 'secretaria')->get();
        $this->assertCount(1, $historial);
        $this->assertSame('creado', $historial->first()->accion);
        $this->assertSame($admin->id, $historial->first()->user_id);
        $this->assertSame('Secretaría', $historial->first()->sujeto_nombre);
    }

    public function test_el_rol_creado_aparece_en_la_lista_y_en_el_selector_de_usuarios(): void
    {
        $this->comoAdmin();
        $this->post(route('roles.store'), $this->datos())->assertRedirect();

        $this->getJson(route('roles.datos', ['length' => 25]))->assertOk();
        $this->assertStringContainsString('Secretaría', json_encode($this->getJson(route('roles.datos', ['length' => 25]))->json('data'), JSON_UNESCAPED_UNICODE));
        // El rol se elige en el paso 2 de «Nuevo usuario» (spec 016).
        $this->get(route('users.create', ['celular' => '3009990000']))->assertOk()->assertSee('Secretaría');
    }

    public function test_un_rol_sin_permisos_se_puede_guardar(): void
    {
        $this->comoAdmin();

        $this->post(route('roles.store'), $this->datos(['nombre' => 'Observador', 'permisos' => []]))->assertRedirect();

        $rol = Rol::where('nombre', 'Observador')->firstOrFail();
        $this->assertSame(0, array_sum($rol->niveles()));
    }

    public function test_validaciones_del_formulario(): void
    {
        $this->comoAdmin();

        $casos = [
            'nombre' => [['nombre' => ''], ['nombre' => str_repeat('a', 61)], ['nombre' => 'administrador'], ['nombre' => 'PASTOR'], ['nombre' => 'Líder'], ['nombre' => 'lider']],
            'descripcion' => [['descripcion' => str_repeat('a', 256)]],
            'alcance' => [['alcance' => ''], ['alcance' => 'todo']],
            'permisos.miembros' => [['permisos' => ['miembros' => 3]], ['permisos' => ['miembros' => -1]], ['permisos' => ['miembros' => 'x']]],
            'permisos.tablero' => [['permisos' => ['tablero' => 2]]],
            'permisos.sedes' => [['permisos' => ['sedes' => 2]], ['permisos' => ['roles' => 1]]],
        ];

        foreach ($casos as $campo => $variantes) {
            foreach ($variantes as $variante) {
                $antes = Rol::count();
                $this->post(route('roles.store'), $this->datos($variante))->assertSessionHasErrors($campo === 'permisos.sedes' ? 'permisos' : $campo);
                $this->assertSame($antes, Rol::count(), json_encode($variante));
            }
        }
    }

    public function test_los_errores_conservan_lo_escrito(): void
    {
        $this->comoAdmin();

        $this->from(route('roles.create'))->post(route('roles.store'), $this->datos(['alcance' => 'todo', 'nombre' => 'Tesorería']))
            ->assertRedirect(route('roles.create'))
            ->assertSessionHasInput('nombre', 'Tesorería');
    }

    public function test_la_clave_es_unica_cuando_los_nombres_se_parecen(): void
    {
        $this->comoAdmin();
        $this->post(route('roles.store'), $this->datos(['nombre' => 'Secretaría']))->assertRedirect();
        $this->post(route('roles.store'), $this->datos(['nombre' => 'Secretaria General']))->assertRedirect();

        $this->assertSame(
            ['secretaria', 'secretaria_general'],
            Rol::whereIn('nombre', ['Secretaría', 'Secretaria General'])->orderBy('clave')->pluck('clave')->all(),
        );
    }

    public function test_el_administrador_en_todas_las_sedes_tambien_gestiona_roles(): void
    {
        // Los roles no pertenecen a una sede: no se bloquean aunque no haya sede activa.
        $this->comoAdminEnTodas();

        $this->get(route('roles.create'))->assertOk()->assertDontSee('Elige una sede activa para guardar');
        $this->post(route('roles.store'), $this->datos(['nombre' => 'Global']))->assertRedirect();
        $this->assertNotNull(Rol::where('nombre', 'Global')->first());

        $this->put(route('roles.update', 'global'), $this->datos(['nombre' => 'Global', 'alcance' => 'rama']))->assertRedirect();
        $this->delete(route('roles.destroy', 'global'))->assertRedirect(route('roles.index'));
    }

    public function test_solo_el_administrador_crea_roles(): void
    {
        $this->comoPastor();
        $this->get(route('roles.create'))->assertForbidden();
        $this->post(route('roles.store'), $this->datos())->assertForbidden();
        $this->assertNull(Rol::where('nombre', 'Secretaría')->first());
    }
}
