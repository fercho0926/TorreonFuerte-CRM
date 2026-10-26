<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Qué muestra el histórico de cada módulo (spec 015, historia 3). */
class ContenidoPestanasModulosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    public function test_celula_con_miembro_agregado_reunion_y_cambio_de_lider(): void
    {
        $celula = $this->A['celula'];
        $nuevo = Persona::factory()->deSede($this->A['sede']->id)->create(['estado' => 'miembro_activo', 'nombres' => 'Nuevo', 'apellidos' => 'Integrante']);
        $otroLider = Persona::factory()->deSede($this->A['sede']->id)->lider()->create(['nombres' => 'Otro', 'apellidos' => 'Líder']);

        $this->post(route('celulas.miembros.store', $celula), ['personas' => [$nuevo->id]]);
        $this->post(route('celulas.reuniones.store', $celula), ['fecha' => now()->toDateString(), 'tema' => 'Gracia']);
        $this->put(route('celulas.update', $celula), ['nombre' => $celula->nombre, 'lider_id' => $otroLider->id, 'estado' => 'activa']);

        $this->get(route('celulas.show', [$celula, 'pestana' => 'historico']))->assertOk()
            ->assertSee('Miembros actualizados')->assertSee('Nuevo Integrante')
            ->assertSee('Reunión registrada')->assertSee('Gracia')
            ->assertSee('Líder:', false)->assertSee('Otro Líder');
    }

    public function test_evento_con_inscripcion_abono_y_abono_eliminado(): void
    {
        $evento = $this->A['evento'];
        $this->delete(route('eventos.pagos.destroy', [$evento, $this->A['inscripcion'], $this->A['pago']]));

        $this->get(route('eventos.show', [$evento, 'pestana' => 'historico']))->assertOk()
            ->assertSee('Inscripción')->assertSee('Abono registrado')->assertSee('Abono eliminado')
            ->assertSee('PersonaAlfa Miembro')->assertSee('$ 10.000');
    }

    public function test_rol_muestra_el_cambio_de_acceso_por_modulo(): void
    {
        $this->post(route('roles.store'), ['nombre' => 'Apoyo', 'alcance' => 'sede', 'permisos' => ['eventos' => 1]]);
        $this->put(route('roles.update', 'apoyo'), ['nombre' => 'Apoyo', 'alcance' => 'sede', 'permisos' => ['eventos' => 2]]);

        $this->get(route('roles.show', ['apoyo', 'pestana' => 'historico']))->assertOk()->assertSee('Eventos: Lectura → Escritura');
    }

    public function test_usuario_muestra_cambio_de_rol_sin_claves(): void
    {
        $usuario = $this->A['liderUser'];
        $this->put(route('users.update', $usuario), ['email' => $usuario->email, 'role' => 'user'])->assertSessionHasNoErrors();

        $entrada = CambioHistorial::query()->de('usuario', $usuario->id)->where('accion', 'editado')->latest('id')->firstOrFail();
        $this->assertSame(['Rol', 'Líder', 'Usuario'], [$entrada->cambios[0]['etiqueta'], $entrada->cambios[0]['antes'], $entrada->cambios[0]['despues']]);

        $this->get(route('users.edit', [$usuario, 'pestana' => 'historico']))->assertOk()->assertSee('Rol:', false)->assertDontSee('remember_token');
    }

    public function test_las_entradas_antiguas_se_mezclan_en_orden_cronologico(): void
    {
        $sede = $this->A['sede'];
        DB::table('historial_cambios')->insert([
            'created_at' => now()->subYear(), 'sede_id' => $sede->id, 'actor' => 'sistema', 'user_id' => null, 'actor_nombre' => 'Sistema',
            'modulo' => 'sedes', 'accion' => 'editada', 'categoria' => null, 'sujeto_tipo' => 'sede', 'sujeto_id' => $sede->id, 'sujeto_nombre' => $sede->nombre,
            'antes' => json_encode(['ciudad' => 'Vieja']), 'despues' => json_encode(['ciudad' => 'Nueva']),
            'detalle' => json_encode(['legado' => ['tabla' => 'sede_cambios', 'id' => 1, 'accion' => 'editada']]),
        ]);
        $this->put(route('sedes.update', $sede), ['nombre' => $sede->nombre, 'ciudad' => 'Más nueva']);

        $html = $this->get(route('sedes.show', [$sede, 'pestana' => 'historico']))->assertOk()->getContent();
        $html = substr($html, strpos($html, 'historial-lista'));

        $this->assertStringContainsString('ciudad: Nueva', $html, 'la entrada antigua se muestra con lo que tenía');
        $this->assertLessThan(strpos($html, 'ciudad: Nueva'), strpos($html, 'Más nueva'), 'la más reciente primero');
    }
}
