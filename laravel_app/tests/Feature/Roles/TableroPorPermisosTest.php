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
 * El Tablero se adapta a cada rol (spec 013): cada total, tarjeta y acceso rápido solo aparece con al menos
 * lectura en su módulo, y los totales respetan el alcance de datos.
 */
class TableroPorPermisosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function entrarCon(array $niveles, string $alcance = 'sede', ?Persona $persona = null)
    {
        $rol = Rol::factory()->conAlcance($alcance)->conPermisos(['tablero' => 1] + $niveles)->create();

        return $this->iniciarSesionComo($this->crearUsuario($rol->clave, $persona ?? $this->A['persona'], $this->A['sede']->id));
    }

    public function test_sin_modulos_el_tablero_no_muestra_cifras_ni_accesos(): void
    {
        $this->entrarCon([]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Miembros registrados')
            ->assertDontSee('Líderes')
            ->assertDontSee('Usuarios registrados')
            ->assertDontSee('Eventos registrados')
            ->assertDontSee('Próximos eventos')
            ->assertDontSee('Miembros nuevos')
            ->assertDontSee('Registrar miembro')
            ->assertDontSee('Crear evento')
            ->assertDontSee('Ver células');
    }

    public function test_cada_tarjeta_depende_de_su_modulo(): void
    {
        $this->entrarCon(['eventos' => 1]);
        $this->get(route('dashboard'))->assertSee('Eventos registrados')->assertSee('Próximos eventos')->assertDontSee('Miembros registrados')->assertDontSee('Crear evento');

        $this->entrarCon(['eventos' => 2, 'celulas' => 1, 'miembros' => 2]);
        $this->get(route('dashboard'))
            ->assertSee('Crear evento')->assertSee('Ver células')->assertSee('Registrar miembro')
            ->assertSee('Miembros registrados')->assertSee('Miembros nuevos');

        $this->entrarCon(['usuarios' => 1]);
        $this->get(route('dashboard'))->assertSee('Usuarios registrados')->assertDontSee('Eventos registrados');
    }

    public function test_los_totales_respetan_el_alcance_de_datos(): void
    {
        // Sede A: pastor (ficha), líder, un miembro del líder y el titular del rol.
        $this->entrarCon(['miembros' => 1, 'lideres' => 1], 'sede', $this->A['persona']);
        $vistaSede = $this->get(route('dashboard'))->assertOk()->viewData('personaCount');

        $this->entrarCon(['miembros' => 1, 'lideres' => 1], 'rama', $this->A['lider']);
        $vistaRama = $this->get(route('dashboard'))->viewData('personaCount');

        $this->entrarCon(['miembros' => 1, 'lideres' => 1], 'registro', $this->A['persona']);
        $vistaRegistro = $this->get(route('dashboard'))->viewData('personaCount');

        $this->assertGreaterThan($vistaRama, $vistaSede);
        $this->assertGreaterThanOrEqual($vistaRegistro, $vistaRama);
        $this->assertSame(1, $vistaRegistro);
    }
}
