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

class EditarRolTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private function rol(array $niveles = ['eventos' => 1, 'miembros' => 1]): Rol
    {
        return Rol::factory()->conPermisos($niveles)->create(['nombre' => 'Tesorería', 'clave' => 'tesoreria', 'alcance' => 'sede', 'descripcion' => 'Cuentas']);
    }

    private function datos(array $extra = []): array
    {
        return $extra + ['nombre' => 'Tesorería', 'descripcion' => 'Cuentas', 'alcance' => 'sede', 'permisos' => ['eventos' => 1, 'miembros' => 1]];
    }

    private function conUsuarios(Rol $rol, int $cuantos = 2): void
    {
        $sede = $this->crearSede();
        // Cada cuenta con su propio miembro en la sede (spec 016).
        User::factory()->count($cuantos)->create(['role' => $rol->clave, 'sede_id' => $sede->id]);
    }

    public function test_editar_guarda_los_cambios_y_registra_solo_lo_que_cambio(): void
    {
        $admin = $this->comoAdmin();
        $rol = $this->rol();

        $this->put(route('roles.update', $rol), $this->datos(['nombre' => 'Tesorería general', 'alcance' => 'rama', 'permisos' => ['eventos' => 2, 'miembros' => 1]]))
            ->assertRedirect(route('roles.show', $rol));

        $rol->refresh();
        $this->assertSame('Tesorería general', $rol->nombre);
        $this->assertSame('tesoreria', $rol->clave, 'La clave no cambia al renombrar');
        $this->assertSame('rama', $rol->alcance);
        $this->assertSame(2, $rol->niveles()['eventos']);

        $cambio = CambioHistorial::where('sujeto_tipo', 'rol')->where('detalle->clave', 'tesoreria')->where('accion', 'editado')->firstOrFail();
        $this->assertSame($admin->id, $cambio->user_id);
        $this->assertSame(['nombre' => 'Tesorería', 'alcance' => 'sede', 'permisos' => ['eventos' => 1]], $cambio->antes);
        $this->assertSame(['nombre' => 'Tesorería general', 'alcance' => 'rama', 'permisos' => ['eventos' => 2]], $cambio->despues);
    }

    public function test_sin_cambios_no_escribe_historial(): void
    {
        $this->comoAdmin();
        $rol = $this->rol();

        $this->put(route('roles.update', $rol), $this->datos())->assertRedirect();

        $this->assertSame(0, CambioHistorial::where('sujeto_tipo', 'rol')->where('accion', 'editado')->count());
    }

    public function test_editar_un_rol_con_usuarios_exige_confirmar_los_afectados(): void
    {
        $this->comoAdmin();
        $rol = $this->rol();
        $this->conUsuarios($rol, 3);

        $this->get(route('roles.edit', $rol))->assertOk()->assertSee('3 usuarios')->assertSee('confirmo_afectados', false);

        $this->from(route('roles.edit', $rol))->put(route('roles.update', $rol), $this->datos(['permisos' => ['eventos' => 2]]))
            ->assertRedirect(route('roles.edit', $rol))
            ->assertSessionHasErrors('confirmo_afectados')
            ->assertSessionHasInput('permisos');
        $this->assertSame(1, $rol->fresh()->niveles()['eventos']);

        $this->put(route('roles.update', $rol), $this->datos(['permisos' => ['eventos' => 2], 'confirmo_afectados' => '1']))->assertRedirect(route('roles.show', $rol));
        $this->assertSame(2, $rol->fresh()->niveles()['eventos']);
    }

    public function test_un_rol_sin_usuarios_no_pide_confirmacion(): void
    {
        $this->comoAdmin();
        $rol = $this->rol();

        $this->get(route('roles.edit', $rol))->assertOk()->assertDontSee('confirmo_afectados', false);
        $this->put(route('roles.update', $rol), $this->datos(['permisos' => ['eventos' => 2]]))->assertRedirect(route('roles.show', $rol));
    }

    public function test_el_nombre_se_valida_sin_chocar_consigo_mismo_y_si_con_otros(): void
    {
        $this->comoAdmin();
        $rol = $this->rol();
        Rol::factory()->create(['nombre' => 'Secretaría', 'clave' => 'secretaria']);

        $this->put(route('roles.update', $rol), $this->datos(['nombre' => 'tesoreria']))->assertSessionHasNoErrors();
        $this->put(route('roles.update', $rol->fresh()), $this->datos(['nombre' => 'SECRETARIA']))->assertSessionHasErrors('nombre');
    }

    public function test_duplicar_precarga_y_registra_la_accion_duplicado(): void
    {
        $admin = $this->comoAdmin();
        $origen = $this->rol(['eventos' => 2, 'miembros' => 1]);

        $this->get(route('roles.create', ['duplicar' => $origen->clave]))
            ->assertOk()
            ->assertSee('Duplicar rol')
            ->assertSee('Tesorería')
            ->assertSee('name="duplicado_de" value="tesoreria"', false)
            ->assertDontSee('value="Tesorería"', false);

        $this->post(route('roles.store'), $this->datos(['nombre' => 'Tesorería norte', 'permisos' => ['eventos' => 2, 'miembros' => 1], 'duplicado_de' => 'tesoreria']))->assertRedirect();

        $copia = Rol::where('nombre', 'Tesorería norte')->firstOrFail();
        $this->assertSame(2, $copia->niveles()['eventos']);
        $this->assertSame('duplicado', CambioHistorial::where('sujeto_tipo', 'rol')->where('detalle->clave', $copia->clave)->value('accion'));
        $this->assertSame($admin->id, CambioHistorial::where('sujeto_tipo', 'rol')->where('detalle->clave', $copia->clave)->value('user_id'));
    }

    public function test_solo_el_administrador_edita(): void
    {
        $rol = $this->rol();
        $this->comoPastor();

        $this->get(route('roles.edit', $rol))->assertForbidden();
        $this->put(route('roles.update', $rol), $this->datos(['nombre' => 'Otro']))->assertForbidden();
        $this->assertSame('Tesorería', $rol->fresh()->nombre);
    }
}
