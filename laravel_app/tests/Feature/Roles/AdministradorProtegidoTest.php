<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use App\Support\Modulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AdministradorProtegidoTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_el_rol_administrador_no_se_edita_ni_se_elimina(): void
    {
        $this->comoAdmin();
        $antes = Rol::where('clave', 'admin')->firstOrFail();

        $this->get(route('roles.edit', 'admin'))->assertForbidden()->assertSee('Rol protegido', false);
        $this->put(route('roles.update', 'admin'), ['nombre' => 'Otro', 'alcance' => 'registro', 'permisos' => []])->assertForbidden();
        $this->delete(route('roles.destroy', 'admin'))->assertForbidden();

        $despues = Rol::where('clave', 'admin')->firstOrFail();
        $this->assertSame([$antes->nombre, $antes->alcance], [$despues->nombre, $despues->alcance]);
        foreach ($despues->niveles() as $modulo => $nivel) {
            $this->assertSame(Modulos::nivelMaximo($modulo), $nivel, $modulo);
        }
    }

    public function test_el_detalle_del_administrador_explica_que_esta_protegido(): void
    {
        $this->comoAdmin();

        $this->get(route('roles.show', 'admin'))->assertOk()->assertSee('Rol protegido')->assertDontSee(route('roles.edit', 'admin'), false);
    }

    public function test_no_se_puede_dejar_el_sistema_sin_administrador(): void
    {
        $unico = $this->comoAdminEnTodas();

        // Cambiar el rol del único administrador.
        $this->put(route('users.update', $unico), [
            'name' => $unico->name, 'email' => $unico->email, 'celular' => '3101234567', 'role' => 'user',
        ])->assertSessionHasErrors('role');
        $this->assertSame('admin', $unico->fresh()->role);

        // Eliminarlo (con otro administrador sí se puede).
        $this->delete(route('users.destroy', $unico))->assertStatus(422);
        $this->assertNotNull(User::withoutGlobalScopes()->find($unico->id));

        $otro = User::factory()->create(['role' => 'admin', 'sede_id' => null]);
        $this->delete(route('users.destroy', $otro))->assertRedirect(route('users.index'));
        $this->assertNull(User::withoutGlobalScopes()->find($otro->id));
        $this->assertSame(1, User::withoutGlobalScopes()->where('role', 'admin')->count());
    }

    public function test_los_roles_no_administradores_se_pueden_editar_pero_no_dar_permiso_a_sedes_ni_roles(): void
    {
        $this->comoAdmin();

        $this->put(route('roles.update', 'pastor'), [
            'nombre' => 'Pastor', 'alcance' => 'sede', 'asignable' => '1', 'confirmo_afectados' => '1',
            'permisos' => ['eventos' => 1],
        ])->assertRedirect();

        $this->assertTrue(Rol::where('clave', 'pastor')->firstOrFail()->asignable);
        $this->assertFalse(Modulos::existe('sedes'));
        $this->put(route('roles.update', 'pastor'), ['nombre' => 'Pastor', 'alcance' => 'sede', 'confirmo_afectados' => '1', 'permisos' => ['sedes' => 2]])
            ->assertSessionHasErrors('permisos');
    }
}
