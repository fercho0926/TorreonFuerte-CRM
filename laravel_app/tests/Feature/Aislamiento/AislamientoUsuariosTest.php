<?php

namespace Tests\Feature\Aislamiento;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AislamientoUsuariosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function datosUsuario(array $extra = []): array
    {
        return $extra + [
            'name' => 'Nuevo Usuario', 'email' => 'nuevo@iglesia.co', 'password' => 'secreto123', 'password_confirmation' => 'secreto123', 'role' => 'lider_celula',
        ];
    }

    public function test_el_pastor_solo_ve_usuarios_de_su_sede(): void
    {
        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee($this->A['liderUser']->email)
            ->assertDontSee($this->B['liderUser']->email)
            ->assertDontSee($this->B['pastor']->email);
    }

    public function test_el_pastor_crea_lideres_y_usuarios_en_su_propia_sede(): void
    {
        foreach (['lider_celula' => 'a1@iglesia.co', 'user' => 'a2@iglesia.co'] as $rol => $correo) {
            $this->post(route('users.store'), $this->datosUsuario(['role' => $rol, 'email' => $correo]))->assertSessionHasNoErrors();

            $creado = User::withoutGlobalScopes()->where('email', $correo)->firstOrFail();
            $this->assertSame($this->A['sede']->id, $creado->sede_id);
            $this->assertSame($rol, $creado->role);
        }
    }

    public function test_el_pastor_no_crea_pastores_ni_admins_ni_elige_sede(): void
    {
        foreach (['pastor', 'admin'] as $rol) {
            $this->post(route('users.store'), $this->datosUsuario(['role' => $rol, 'email' => $rol.'@iglesia.co']))->assertSessionHasErrors('role');
            $this->assertSame(0, User::withoutGlobalScopes()->where('email', $rol.'@iglesia.co')->count());
        }

        $this->post(route('users.store'), $this->datosUsuario(['email' => 'cruzado@iglesia.co', 'sede_id' => $this->B['sede']->id]))->assertSessionHasErrors('sede_id');
        $this->assertSame(0, User::withoutGlobalScopes()->where('email', 'cruzado@iglesia.co')->count());
    }

    public function test_el_pastor_no_toca_usuarios_de_otra_sede_ni_cuentas_pastorales(): void
    {
        $this->get(route('users.edit', $this->B['liderUser']))->assertNotFound();
        $this->put(route('users.update', $this->B['liderUser']), $this->datosUsuario(['email' => $this->B['liderUser']->email]))->assertNotFound();
        $this->delete(route('users.destroy', $this->B['liderUser']))->assertNotFound();

        // La cuenta del propio pastor y otras cuentas pastorales de su sede las gestiona el admin.
        $this->get(route('users.edit', $this->A['pastor']))->assertForbidden();
        $this->delete(route('users.destroy', $this->A['pastor']))->assertForbidden();

        // No puede ascender a un líder de su sede a pastor ni a admin.
        $this->put(route('users.update', $this->A['liderUser']), $this->datosUsuario(['email' => $this->A['liderUser']->email, 'role' => 'pastor']))->assertSessionHasErrors('role');
        $this->assertSame('lider_celula', $this->A['liderUser']->fresh()->role);
    }

    public function test_no_se_vincula_una_persona_de_otra_sede(): void
    {
        $this->post(route('users.store'), $this->datosUsuario(['email' => 'vinculo@iglesia.co', 'persona_id' => $this->B['persona']->id]))
            ->assertSessionHasErrors('persona_id');
    }

    public function test_el_correo_es_unico_en_todo_el_sistema(): void
    {
        $this->post(route('users.store'), $this->datosUsuario(['email' => $this->B['liderUser']->email]))->assertSessionHasErrors('email');
    }

    public function test_un_lider_y_un_usuario_no_gestionan_usuarios(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();

        $this->iniciarSesionComo($this->crearUsuario('user', null, $this->A['sede']->id));
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_el_admin_crea_cuentas_en_la_sede_que_elige_y_admins_sin_sede(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('users.store'), $this->datosUsuario(['email' => 'enb@iglesia.co', 'sede_id' => $this->B['sede']->id]))->assertSessionHasNoErrors();
        $this->assertSame($this->B['sede']->id, User::withoutGlobalScopes()->where('email', 'enb@iglesia.co')->value('sede_id'));

        $this->post(route('users.store'), $this->datosUsuario(['role' => 'pastor', 'email' => 'pastorb@iglesia.co', 'sede_id' => $this->B['sede']->id]))->assertSessionHasNoErrors();
        $this->assertSame('pastor', User::withoutGlobalScopes()->where('email', 'pastorb@iglesia.co')->value('role'));

        $this->post(route('users.store'), $this->datosUsuario(['role' => 'admin', 'email' => 'otroadmin@iglesia.co']))->assertSessionHasNoErrors();
        $this->assertNull(User::withoutGlobalScopes()->where('email', 'otroadmin@iglesia.co')->value('sede_id'));

        // Sin sede (y sin sede activa) no se puede crear un usuario de sede.
        $this->post(route('users.store'), $this->datosUsuario(['email' => 'sinsede@iglesia.co']))->assertStatus(422);
        $this->assertSame(0, User::withoutGlobalScopes()->where('email', 'sinsede@iglesia.co')->count());

        $this->get(route('users.index'))->assertOk()->assertSee($this->A['liderUser']->email)->assertSee($this->B['liderUser']->email);
    }
}
