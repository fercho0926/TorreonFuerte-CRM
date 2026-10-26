<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use App\Support\Modulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * El alcance de datos es un dato del rol y el aislamiento por sede no depende del rol (spec 013).
 */
class AislamientoRolesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    /** Rol con escritura en todos los módulos y el alcance dado; el usuario es la persona indicada. */
    private function entrarCon(string $alcance, ?Persona $persona = null): User
    {
        $todo = array_fill_keys(Modulos::claves(), 2);
        $todo['tablero'] = 1;
        $todo['organigrama'] = 1;
        $rol = Rol::factory()->conAlcance($alcance)->conPermisos($todo)->create();

        $persona ??= Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Titular', 'apellidos' => 'Del rol']);

        return $this->iniciarSesionComo($this->crearUsuario($rol->clave, $persona, $this->A['sede']->id));
    }

    private function nombresEnLaGrilla(string $ruta): string
    {
        return json_encode($this->getJson(route($ruta, ['length' => 100, 'start' => 0, 'draw' => 1]))->assertOk()->json('data'), JSON_UNESCAPED_UNICODE);
    }

    public function test_alcance_sede_con_escritura_total_nunca_toca_otra_sede(): void
    {
        $this->entrarCon('sede');

        $miembros = $this->nombresEnLaGrilla('personas.datos');
        $this->assertStringContainsString('PersonaAlfa', $miembros);
        $this->assertStringNotContainsString('PersonaBeta', $miembros);
        $this->assertStringNotContainsString('LiderBeta', $this->nombresEnLaGrilla('lideres.datos'));
        $this->assertStringNotContainsString('Evento Beta', $this->nombresEnLaGrilla('eventos.datos'));
        $this->assertStringNotContainsString('Ministerio Beta', $this->nombresEnLaGrilla('ministerios.datos'));
        $this->assertStringNotContainsString('Celula Beta', $this->nombresEnLaGrilla('celulas.datos'));

        foreach ([
            fn () => $this->get(route('personas.show', $this->B['persona'])),
            fn () => $this->get(route('personas.edit', $this->B['persona'])),
            fn () => $this->post(route('personas.inactivar', $this->B['persona'])),
            fn () => $this->get(route('eventos.show', $this->B['evento'])),
            fn () => $this->put(route('encuentros.asistencia', $this->B['encuentro'])),
            fn () => $this->get(route('celulas.show', $this->B['celula'])),
        ] as $peticion) {
            $this->assertContains($peticion()->getStatusCode(), [302, 403, 404]);
        }

        $this->assertSame('PersonaBeta', $this->B['persona']->fresh()->nombres);
        $this->assertNotSame('inactivo', $this->B['persona']->fresh()->estado);
    }

    public function test_alcance_rama_solo_ve_su_rama_y_alcance_registro_solo_su_ficha(): void
    {
        $miembroDeOtro = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'AjenoDeOtraRama', 'apellidos' => 'Z']);

        $this->entrarCon('rama', $this->A['lider']);
        $rama = $this->nombresEnLaGrilla('personas.datos');
        $this->assertStringContainsString('PersonaAlfa', $rama);
        $this->assertStringNotContainsString('AjenoDeOtraRama', $rama);
        $this->assertStringNotContainsString('PastorAlfa', $rama);

        $this->entrarCon('registro', $miembroDeOtro);
        $propio = $this->nombresEnLaGrilla('personas.datos');
        $this->assertStringContainsString('AjenoDeOtraRama', $propio);
        $this->assertStringNotContainsString('PersonaAlfa', $propio);
        $this->assertStringNotContainsString('LiderAlfa', $propio);
        $this->get(route('personas.show', $this->A['persona']))->assertNotFound();
        $this->get(route('personas.show', $miembroDeOtro))->assertOk();
    }

    public function test_los_modulos_sin_dueno_se_ven_completos_en_la_sede_con_cualquier_alcance(): void
    {
        foreach (['sede', 'rama', 'registro'] as $alcance) {
            $this->entrarCon($alcance);

            $this->assertStringContainsString('Evento Alfa', $this->nombresEnLaGrilla('eventos.datos'), $alcance);
            $this->assertStringContainsString('Ministerio Alfa', $this->nombresEnLaGrilla('ministerios.datos'), $alcance);
            $this->assertStringContainsString('Encuentro Alfa', $this->nombresEnLaGrilla('encuentros.datos'), $alcance);
        }
    }

    public function test_dentro_de_un_evento_las_personas_respetan_el_alcance(): void
    {
        $otro = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'InscritoAjeno', 'apellidos' => 'Fuera']);
        \App\Models\EventoPersona::create(['sede_id' => $this->A['sede']->id, 'evento_id' => $this->A['evento']->id, 'persona_id' => $otro->id]);

        $this->entrarCon('rama', $this->A['lider']);
        $this->get(route('eventos.show', $this->A['evento']))
            ->assertOk()
            ->assertSee('PersonaAlfa')
            ->assertDontSee('InscritoAjeno');
    }

    public function test_las_acciones_reservadas_siguen_siendo_del_administrador_aunque_haya_escritura(): void
    {
        $this->entrarCon('sede');

        $this->get(route('sedes.index'))->assertForbidden();
        $this->get(route('sedes.show', $this->A['sede']))->assertForbidden();
        $this->get(route('traslados.create', $this->A['persona']))->assertForbidden();
        $this->post(route('sede-activa.update'), ['sede_id' => $this->B['sede']->id])->assertForbidden();
        $this->get(route('roles.index'))->assertForbidden();
        $this->delete(route('personas.destroy', $this->A['persona']))->assertForbidden();
        $this->get(route('users.exportar'))->assertForbidden();
        $this->get(route('solicitudes-clave.exportar'))->assertForbidden();

        $this->assertNotNull($this->A['persona']->fresh());
    }
}
