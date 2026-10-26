<?php

namespace Tests\Feature\Roles;

use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** El módulo Niveles (spec 014) aparece en la matriz de roles y un rol nuevo parte sin acceso (RF-020). */
class ModuloNivelesEnRolesTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_la_matriz_del_formulario_ofrece_el_modulo_niveles_con_lectura_y_escritura(): void
    {
        $this->comoAdmin();

        $html = $this->get(route('roles.create'))->assertOk()->getContent();

        $this->assertStringContainsString('name="permisos[niveles]"', $html);
        $this->assertStringContainsString('Niveles', $html);
        $this->assertStringContainsString('Plan de niveles, avance de los miembros', $html);
    }

    public function test_un_rol_nuevo_sin_marcar_niveles_queda_sin_acceso(): void
    {
        $this->comoAdmin();

        $this->post(route('roles.store'), ['nombre' => 'Apoyo', 'alcance' => 'sede', 'asignable' => '1', 'permisos' => ['miembros' => 1]])->assertRedirect();

        $this->assertSame(0, Rol::where('nombre', 'Apoyo')->firstOrFail()->nivelEn('niveles'));
    }

    public function test_se_puede_dar_escritura_en_niveles_a_un_rol_nuevo(): void
    {
        $this->comoAdmin();

        $this->post(route('roles.store'), ['nombre' => 'Formación', 'alcance' => 'sede', 'asignable' => '1', 'permisos' => ['tablero' => 1, 'miembros' => 1, 'niveles' => 2]])->assertRedirect();

        $this->assertSame(2, Rol::where('nombre', 'Formación')->firstOrFail()->nivelEn('niveles'));
    }

    public function test_el_detalle_del_rol_del_sistema_muestra_su_nivel_en_el_modulo(): void
    {
        $this->comoAdmin();

        $this->get(route('roles.show', 'lider_celula'))->assertOk()->assertSee('Niveles');
    }
}
