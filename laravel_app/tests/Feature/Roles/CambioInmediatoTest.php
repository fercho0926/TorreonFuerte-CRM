<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\RolPermiso;
use App\Models\User;
use App\Services\AutorizacionRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Cada petición se evalúa con el rol vigente (spec 013, RF-012): un cambio de permisos, de rol o la eliminación del
 * rol se aplican en la siguiente acción del usuario, sin cerrar sesión.
 */
class CambioInmediatoTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private function usuarioConRol(Rol $rol): User
    {
        $sede = $this->crearSede();

        return $this->iniciarSesionComo(User::factory()->create([
            'role' => $rol->clave, 'sede_id' => $sede->id, 'persona_id' => Persona::factory()->deSede($sede->id)->create()->id,
        ]));
    }

    public function test_quitar_un_permiso_se_aplica_en_la_siguiente_peticion(): void
    {
        $rol = Rol::factory()->conPermisos(['tablero' => 1, 'eventos' => 2])->create();
        $this->usuarioConRol($rol);

        $this->get(route('eventos.index'))->assertOk();
        $this->get(route('dashboard'))->assertSee(route('eventos.index'), false);

        RolPermiso::where('rol_id', $rol->id)->where('modulo', 'eventos')->update(['nivel' => 0]);

        $this->get(route('eventos.index'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()->assertDontSee(route('eventos.index'), false);
    }

    public function test_bajar_de_escritura_a_lectura_oculta_las_acciones_sin_cerrar_sesion(): void
    {
        $rol = Rol::factory()->conPermisos(['tablero' => 1, 'eventos' => 2])->create();
        $this->usuarioConRol($rol);

        $this->get(route('eventos.create'))->assertOk();

        RolPermiso::where('rol_id', $rol->id)->where('modulo', 'eventos')->update(['nivel' => 1]);

        $this->get(route('eventos.index'))->assertOk()->assertDontSee(route('eventos.create'), false);
        $this->get(route('eventos.create'))->assertForbidden();
    }

    public function test_cambiar_el_rol_del_usuario_cambia_su_acceso_en_la_siguiente_accion(): void
    {
        $lectura = Rol::factory()->conPermisos(['tablero' => 1, 'ministerios' => 2])->create();
        $otro = Rol::factory()->conPermisos(['tablero' => 1])->create();
        $usuario = $this->usuarioConRol($lectura);

        $this->get(route('ministerios.index'))->assertOk();

        $usuario->forceFill(['role' => $otro->clave])->save();

        $this->get(route('ministerios.index'))->assertForbidden();
    }

    public function test_un_usuario_sin_rol_vigente_solo_llega_a_su_cuenta(): void
    {
        $rol = Rol::factory()->conPermisos(['tablero' => 1, 'eventos' => 2])->create();
        $usuario = $this->usuarioConRol($rol);

        // El rol desaparece (por ejemplo, borrado a mano en la base): no hay acceso a nada.
        $usuario->forceFill(['role' => 'rol_que_ya_no_existe'])->save();

        $this->get(route('eventos.index'))->assertForbidden();
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('cuenta.edit'))->assertOk();
        $this->assertSame(0, app(AutorizacionRoles::class)->nivel($usuario->fresh(), 'eventos'));
    }
}
