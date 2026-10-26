<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Nadie asigna ni toca cuentas con más permisos o alcance que los suyos (spec 013, RF-015).
 */
class EscaladaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private Rol $aprendiz;

    private Rol $superior;

    private Rol $noAsignable;

    private Rol $mayorAlcance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();

        $this->aprendiz = Rol::factory()->asignable()->conAlcance('rama')->conPermisos(['tablero' => 1, 'miembros' => 1])->create(['nombre' => 'Aprendiz']);
        $this->superior = Rol::factory()->asignable()->conAlcance('rama')->conPermisos(['tablero' => 1, 'miembros' => 2])->create(['nombre' => 'Superior']);
        $this->noAsignable = Rol::factory()->conAlcance('registro')->conPermisos([])->create(['nombre' => 'Reservado']);
        $this->mayorAlcance = Rol::factory()->asignable()->conAlcance('sede')->conPermisos(['tablero' => 1])->create(['nombre' => 'Amplio']);
    }

    private function gestor(string $alcance = 'sede', int $usuarios = 2): User
    {
        $rol = Rol::factory()->conAlcance($alcance)->conPermisos(['tablero' => 1, 'miembros' => 1, 'eventos' => 1, 'usuarios' => $usuarios])->create(['nombre' => 'Gestor '.$alcance.$usuarios]);

        return $this->iniciarSesionComo(User::factory()->create([
            'role' => $rol->clave, 'sede_id' => $this->A['sede']->id,
            'persona_id' => Persona::factory()->deSede($this->A['sede']->id)->create()->id,
        ]));
    }

    private function datosUsuario(string $rol, array $extra = []): array
    {
        // Spec 016: la cuenta nace con su miembro.
        return $extra + [
            'email' => 'nueva'.uniqid().'@example.com', 'celular' => '3101234567', 'role' => $rol,
            'miembro' => ['nombres' => 'Nueva', 'apellidos' => 'Cuenta'],
        ];
    }

    /** Paso 2 de «Nuevo usuario», donde se elige el rol. */
    private function formularioDeCuenta(): string
    {
        return route('users.create', ['celular' => '3101234567']);
    }

    public function test_solo_se_ofrecen_los_roles_dentro_del_techo(): void
    {
        $this->gestor();

        $this->get($this->formularioDeCuenta())
            ->assertOk()
            ->assertSee('Aprendiz')
            ->assertSee('Amplio')
            ->assertDontSee('Superior')
            ->assertDontSee('Reservado')
            ->assertDontSee('>Administrador<', false)
            ->assertDontSee('>Pastor<', false);
    }

    public function test_un_gestor_de_alcance_menor_no_puede_dar_un_rol_de_alcance_mayor(): void
    {
        $this->gestor('rama');

        $this->get($this->formularioDeCuenta())->assertOk()->assertSee('Aprendiz')->assertDontSee('Amplio');
        $this->post(route('users.store'), $this->datosUsuario($this->mayorAlcance->clave))->assertSessionHasErrors('role');
    }

    public function test_enviar_a_mano_un_rol_fuera_del_techo_se_rechaza_sin_crear_nada(): void
    {
        $this->gestor();
        $antes = User::withoutGlobalScopes()->count();

        foreach (['superior' => $this->superior->clave, 'reservado' => $this->noAsignable->clave, 'admin' => 'admin', 'pastor' => 'pastor', 'inexistente' => 'no_existe'] as $caso => $clave) {
            $this->post(route('users.store'), $this->datosUsuario($clave))->assertSessionHasErrors('role');
        }

        $this->assertSame($antes, User::withoutGlobalScopes()->count());

        $this->post(route('users.store'), $this->datosUsuario($this->aprendiz->clave))->assertOk();
        $this->assertSame($antes + 1, User::withoutGlobalScopes()->count());
    }

    public function test_no_puede_cambiar_su_propio_rol_ni_tocar_cuentas_de_un_rol_que_no_podria_asignar(): void
    {
        $actor = $this->gestor();

        $this->get(route('users.edit', $actor))->assertForbidden();
        $this->put(route('users.update', $actor), $this->datosUsuario($this->aprendiz->clave, ['email' => $actor->email]))->assertForbidden();

        // La cuenta del pastor de la sede (rol «pastor», no asignable) no se puede editar ni cambiar de rol ni recibir clave.
        $pastor = $this->A['pastor'];
        $this->get(route('users.edit', $pastor))->assertForbidden();
        $this->put(route('users.update', $pastor), $this->datosUsuario($this->aprendiz->clave, ['email' => $pastor->email]))->assertForbidden();
        $this->post(route('users.clave.store', $pastor))->assertForbidden();
        $this->delete(route('users.destroy', $pastor))->assertForbidden();
        $this->assertSame('pastor', $pastor->fresh()->role);

        // Una cuenta con un rol dentro del techo sí.
        $dentro = User::factory()->create(['role' => $this->aprendiz->clave, 'sede_id' => $this->A['sede']->id]);
        $this->get(route('users.edit', $dentro))->assertOk();
        $this->post(route('users.clave.store', $dentro))->assertOk();
    }

    public function test_solo_gestiona_cuentas_de_su_propia_sede(): void
    {
        $this->gestor();
        $ajena = User::factory()->create(['role' => $this->aprendiz->clave, 'sede_id' => $this->B['sede']->id]);

        $this->get(route('users.edit', $ajena))->assertNotFound();
        $this->put(route('users.update', $ajena), $this->datosUsuario($this->aprendiz->clave, ['email' => $ajena->email]))->assertNotFound();
    }

    public function test_con_solo_lectura_en_usuarios_ve_la_lista_sin_acciones(): void
    {
        $this->gestor('sede', 1);
        $dentro = User::factory()->create(['role' => $this->aprendiz->clave, 'sede_id' => $this->A['sede']->id]);

        $this->get(route('users.index'))->assertOk()->assertDontSee(route('users.create'), false);
        $this->get(route('users.create'))->assertForbidden();
        $this->get(route('users.edit', $dentro))->assertForbidden();
        $this->post(route('users.clave.store', $dentro))->assertForbidden();
        $this->post(route('users.store'), $this->datosUsuario($this->aprendiz->clave))->assertForbidden();
    }

    public function test_el_administrador_ve_y_puede_asignar_todos_los_roles(): void
    {
        $this->comoAdmin();

        $this->get($this->formularioDeCuenta())
            ->assertOk()
            ->assertSee('Aprendiz')->assertSee('Superior')->assertSee('Reservado')->assertSee('Amplio')->assertSee('Administrador')->assertSee('Pastor');

        $this->post(route('users.store'), $this->datosUsuario($this->superior->clave, ['sede_id' => $this->A['sede']->id]))->assertOk();
    }
}
