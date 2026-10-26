<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\CambioHistorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class EliminarRolTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_un_rol_sin_usuarios_se_elimina_y_deja_historial(): void
    {
        $admin = $this->comoAdmin();
        $rol = Rol::factory()->conPermisos(['eventos' => 1])->create(['nombre' => 'Temporal', 'clave' => 'temporal']);

        $this->delete(route('roles.destroy', $rol))->assertRedirect(route('roles.index'));

        $this->assertNull(Rol::where('clave', 'temporal')->first());
        $cambio = CambioHistorial::where('sujeto_tipo', 'rol')->where('detalle->clave', 'temporal')->where('accion', 'eliminado')->firstOrFail();
        $this->assertSame('Temporal', $cambio->sujeto_nombre);
        $this->assertNull(\App\Models\Rol::find($cambio->sujeto_id), 'el historial sobrevive al rol');
        $this->assertSame($admin->id, $cambio->user_id);
        $this->assertSame('Temporal', $cambio->antes['nombre']);
    }

    public function test_un_rol_con_usuarios_no_se_elimina(): void
    {
        $this->comoAdmin();
        $rol = Rol::factory()->create(['nombre' => 'En uso', 'clave' => 'en_uso']);
        $sede = $this->crearSede();
        // Cada cuenta con su propio miembro en la sede (spec 016).
        User::factory()->count(2)->create(['role' => 'en_uso', 'sede_id' => $sede->id]);

        $this->delete(route('roles.destroy', $rol))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, '2 usuarios lo tienen') && str_contains($mensaje, 'reasígnalos'));

        $this->assertNotNull(Rol::where('clave', 'en_uso')->first());
        $this->assertSame(2, User::withoutGlobalScopes()->where('role', 'en_uso')->count());
    }

    public function test_un_rol_de_sistema_no_se_elimina(): void
    {
        $this->comoAdmin();

        foreach (['pastor', 'lider_celula', 'user'] as $clave) {
            $this->delete(route('roles.destroy', $clave))->assertRedirect()->assertSessionHas('error');
            $this->assertNotNull(Rol::where('clave', $clave)->first(), $clave);
        }
    }

    public function test_solo_el_administrador_elimina(): void
    {
        $rol = Rol::factory()->create(['clave' => 'otro']);
        $this->comoPastor();

        $this->delete(route('roles.destroy', $rol))->assertForbidden();
        $this->assertNotNull($rol->fresh());
    }
}
