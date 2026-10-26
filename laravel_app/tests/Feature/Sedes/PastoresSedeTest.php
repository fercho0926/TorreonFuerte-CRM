<?php

namespace Tests\Feature\Sedes;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PastoresSedeTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnTodas();
    }

    public function test_cambiar_el_pastor_con_una_cuenta_nueva(): void
    {
        $anterior = $this->A['pastor'];

        $this->put(route('sedes.pastores.update', $this->A['sede']), [
            'rol' => 'pastor', 'nombres' => 'Nuevo', 'apellidos' => 'Pastor', 'email' => 'nuevo.pastor@iglesia.co', 'password' => 'secreto123',
        ])->assertSessionHasNoErrors()->assertRedirect(route('sedes.show', $this->A['sede']));

        $nuevo = User::withoutGlobalScopes()->where('email', 'nuevo.pastor@iglesia.co')->firstOrFail();
        $this->assertSame('pastor', $nuevo->role);
        $this->assertSame($this->A['sede']->id, $nuevo->sede_id);
        $this->assertSame($nuevo->persona_id, $this->A['sede']->fresh()->pastor_persona_id);

        // La cuenta anterior pasa a líder y conserva su registro como miembro.
        $this->assertSame('lider_celula', $anterior->fresh()->role);
        $this->assertNotNull(Persona::withoutGlobalScopes()->find($this->A['pastorPersona']->id));

        $this->assertDatabaseHas('historial_cambios', ['sujeto_tipo' => 'sede', 'sujeto_id' => $this->A['sede']->id, 'accion' => 'pastor_cambiado']);
    }

    public function test_designar_una_cuenta_existente_de_la_misma_sede_como_pastora(): void
    {
        $this->put(route('sedes.pastores.update', $this->A['sede']), ['rol' => 'pastora', 'user_id' => $this->A['liderUser']->id])
            ->assertSessionHasNoErrors();

        $this->assertSame('pastor', $this->A['liderUser']->fresh()->role);
        $this->assertSame($this->A['lider']->id, $this->A['sede']->fresh()->pastora_persona_id);
        $this->assertDatabaseHas('historial_cambios', ['sujeto_tipo' => 'sede', 'sujeto_id' => $this->A['sede']->id, 'accion' => 'pastora_cambiada']);
    }

    public function test_no_se_puede_designar_una_cuenta_de_otra_sede(): void
    {
        $this->put(route('sedes.pastores.update', $this->A['sede']), ['rol' => 'pastor', 'user_id' => $this->B['liderUser']->id])
            ->assertSessionHasErrors('user_id');

        $this->assertSame('lider_celula', $this->B['liderUser']->fresh()->role);
        $this->assertSame($this->A['pastorPersona']->id, $this->A['sede']->fresh()->pastor_persona_id);
    }

    public function test_el_nuevo_pastor_solo_ve_su_sede_y_el_anterior_ya_no_gestiona_usuarios(): void
    {
        $this->put(route('sedes.pastores.update', $this->A['sede']), [
            'rol' => 'pastor', 'nombres' => 'Nuevo', 'apellidos' => 'Pastor', 'email' => 'nuevo.pastor@iglesia.co', 'password' => 'secreto123',
        ]);

        $this->flushSession();
        $this->post(route('login.post'), ['email' => 'nuevo.pastor@iglesia.co', 'password' => 'secreto123'])->assertRedirect(route('dashboard'));
        $this->get(route('personas.index'))->assertOk()->assertSee('PersonaAlfa')->assertDontSee('PersonaBeta');

        $this->iniciarSesionComo($this->A['pastor']->fresh());
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_un_correo_repetido_al_cambiar_el_pastor_no_cambia_nada(): void
    {
        $this->put(route('sedes.pastores.update', $this->A['sede']), [
            'rol' => 'pastor', 'nombres' => 'Nuevo', 'apellidos' => 'Pastor', 'email' => $this->B['pastor']->email, 'password' => 'secreto123',
        ])->assertSessionHasErrors('email');

        $this->assertSame('pastor', $this->A['pastor']->fresh()->role);
        $this->assertSame($this->A['pastorPersona']->id, $this->A['sede']->fresh()->pastor_persona_id);
    }
}
