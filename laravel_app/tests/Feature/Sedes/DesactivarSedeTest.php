<?php

namespace Tests\Feature\Sedes;

use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class DesactivarSedeTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_al_desactivar_los_usuarios_no_inician_sesion_y_los_datos_se_conservan(): void
    {
        $admin = $this->comoAdminEnTodas();

        $this->post(route('sedes.desactivar', $this->B['sede']))->assertRedirect();

        $this->assertFalse($this->B['sede']->fresh()->activa);
        $this->assertDatabaseHas('sede_cambios', ['sede_id' => $this->B['sede']->id, 'accion' => 'desactivada', 'user_id' => $admin->id]);

        $this->flushSession();
        $this->post(route('login.post'), ['email' => $this->B['pastor']->email, 'password' => 'password'])->assertSessionHasErrors('email');

        $this->assertNotNull(Persona::withoutGlobalScopes()->find($this->B['persona']->id));
    }

    public function test_una_sesion_abierta_se_cierra_en_la_siguiente_peticion(): void
    {
        $this->iniciarSesionComo($this->B['pastor']);
        $this->get(route('personas.index'))->assertOk();

        $this->B['sede']->update(['activa' => false]);

        $this->get(route('personas.index'))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertNull(session('user_id'));
    }

    public function test_el_admin_ve_la_sede_inactiva_en_solo_lectura(): void
    {
        $this->B['sede']->update(['activa' => false]);
        $this->comoAdminEnSede($this->B['sede']);

        $this->get(route('personas.index'))->assertOk()->assertSee('PersonaBeta');

        $this->post(route('personas.store'), ['nombres' => 'Nueva', 'apellidos' => 'Visita', 'celular' => '3001234567', 'estado' => 'visitante'])
            ->assertForbidden()->assertSee('La sede está inactiva');
        $this->post(route('eventos.pagos.store', [$this->B['evento'], $this->B['inscripcion']]), ['monto' => 1000, 'fecha_pago' => now()->toDateString()])
            ->assertForbidden();

        $this->assertSame(1, \App\Models\EventoPago::withoutGlobalScopes()->where('sede_id', $this->B['sede']->id)->count());
    }

    public function test_reactivar_restablece_el_acceso(): void
    {
        $this->B['sede']->update(['activa' => false]);
        $this->comoAdminEnTodas();

        $this->post(route('sedes.reactivar', $this->B['sede']))->assertRedirect();
        $this->assertTrue($this->B['sede']->fresh()->activa);
        $this->assertDatabaseHas('sede_cambios', ['sede_id' => $this->B['sede']->id, 'accion' => 'reactivada']);

        $this->flushSession();
        $this->post(route('login.post'), ['email' => $this->B['pastor']->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
    }

    public function test_no_se_elimina_una_sede_con_datos(): void
    {
        $this->comoAdminEnTodas();

        $this->delete(route('sedes.destroy', $this->B['sede']))->assertStatus(422);

        $this->assertNotNull(Sede::find($this->B['sede']->id));
        $this->assertNotNull(Persona::withoutGlobalScopes()->find($this->B['persona']->id));
    }

    public function test_se_elimina_una_sede_recien_creada_sin_datos(): void
    {
        $this->comoAdminEnTodas();
        $this->post(route('sedes.store'), [
            'nombre' => 'Sede vacía', 'ciudad' => 'Torreón',
            'pastor' => ['nombres' => 'Pablo', 'apellidos' => 'Solo', 'email' => 'solo@iglesia.co', 'password' => 'secreto123'],
        ])->assertSessionHasNoErrors();
        $vacia = Sede::where('nombre', 'Sede vacía')->firstOrFail();

        $this->delete(route('sedes.destroy', $vacia))->assertRedirect(route('sedes.index'));

        $this->assertNull(Sede::find($vacia->id));
        $this->assertSame(0, \App\Models\User::withoutGlobalScopes()->where('email', 'solo@iglesia.co')->count());
    }

    public function test_editar_los_datos_de_la_sede_queda_en_la_bitacora(): void
    {
        $this->comoAdminEnTodas();

        $this->put(route('sedes.update', $this->A['sede']), ['nombre' => 'Iglesia Alfa Renovada', 'ciudad' => 'Gómez Palacio'])->assertSessionHasNoErrors();

        $this->assertSame('Gómez Palacio', $this->A['sede']->fresh()->ciudad);
        $this->assertDatabaseHas('sede_cambios', ['sede_id' => $this->A['sede']->id, 'accion' => 'editada']);

        $this->put(route('sedes.update', $this->A['sede']), ['nombre' => 'Iglesia Beta', 'ciudad' => 'X'])->assertSessionHasErrors('nombre');
    }

    public function test_la_bitacora_de_la_sede_solo_la_ve_el_admin(): void
    {
        $this->comoAdminEnTodas();
        $this->get(route('sedes.show', $this->A['sede']))->assertOk()->assertSee('Historial de la sede');

        $this->iniciarSesionComo($this->A['pastor']);
        $this->get(route('sedes.show', $this->A['sede']))->assertForbidden();
    }
}
