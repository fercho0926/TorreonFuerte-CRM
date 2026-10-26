<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\CambioHistorial;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Registro y retiro manual del avance de un miembro (spec 014, HU3, RF-009 y RF-010). */
class AvancesManualesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function nivel(int $posicion): NivelFormacion
    {
        return NivelFormacion::where('posicion', $posicion)->firstOrFail();
    }

    private function registrar(array $extra = [])
    {
        return $this->post(route('niveles.avances.store'), $extra + [
            'persona_id' => $this->A['persona']->id, 'nivel_id' => $this->nivel(1)->id, 'fecha' => '2026-03-12',
        ]);
    }

    public function test_registra_un_nivel_con_origen_manual_fecha_y_quien_lo_registro(): void
    {
        $this->registrar(['nota' => 'Retiro de 2025'])->assertRedirect(route('personas.show', $this->A['persona']))->assertSessionHas('success');

        $avance = AvanceNivel::where('persona_id', $this->A['persona']->id)->firstOrFail();
        $this->assertSame('manual', $avance->origen);
        $this->assertSame('2026-03-12', $avance->fecha->toDateString());
        $this->assertSame($this->A['pastor']->id, $avance->registrado_por);
        $this->assertSame('Retiro de 2025', $avance->nota);
        $this->assertSame($this->A['sede']->id, $avance->sede_id);

        $this->assertTrue(CambioHistorial::de('persona', $this->A['persona']->id)->where('accion', 'nivel_registrado')->exists());
        $this->get(route('personas.show', $this->A['persona']))->assertSee('Completado el 12 de marzo de 2026');
        $this->get(route('personas.show', [$this->A['persona'], 'pestana' => 'historico']))->assertSee('Nivel completado');
    }

    public function test_la_fecha_por_defecto_es_hoy_y_una_fecha_futura_se_rechaza(): void
    {
        $this->registrar(['fecha' => null])->assertSessionHasNoErrors();
        $this->assertSame(now()->toDateString(), AvanceNivel::firstOrFail()->fecha->toDateString());

        $this->registrar(['nivel_id' => $this->nivel(2)->id, 'fecha' => now()->addDay()->toDateString()])->assertSessionHasErrors('fecha');
        $this->assertSame(1, AvanceNivel::count());
    }

    public function test_un_nivel_ya_completado_no_se_duplica(): void
    {
        $this->registrar()->assertSessionHasNoErrors();

        $this->registrar()->assertSessionHasErrors(['nivel_id' => 'Ya tiene este nivel completado.']);

        $this->assertSame(1, AvanceNivel::count());
    }

    public function test_un_nivel_inactivo_o_inexistente_se_rechaza(): void
    {
        $this->nivel(2)->update(['activo' => false]);

        $this->registrar(['nivel_id' => $this->nivel(2)->id])->assertSessionHasErrors('nivel_id');
        $this->registrar(['nivel_id' => 999999])->assertSessionHasErrors('nivel_id');
        $this->registrar(['nivel_id' => null])->assertSessionHasErrors('nivel_id');
        $this->assertSame(0, AvanceNivel::count());
    }

    public function test_un_miembro_fuera_de_alcance_responde_404(): void
    {
        $this->registrar(['persona_id' => $this->B['persona']->id])->assertSessionHasErrors('persona_id');
        $this->registrar(['persona_id' => 999999])->assertSessionHasErrors('persona_id');
        $this->assertSame(0, AvanceNivel::count());
    }

    public function test_solo_con_escritura_en_niveles(): void
    {
        $rol = Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'miembros' => 2, 'niveles' => 1])->create();
        $this->iniciarSesionComo($this->crearUsuario($rol->clave, $this->A['lider'], $this->A['sede']->id));

        $this->registrar()->assertForbidden();
        $this->assertSame(0, AvanceNivel::count());
    }

    public function test_quitar_un_avance_manual_lo_retira_y_deja_historial(): void
    {
        $this->registrar();
        $avance = AvanceNivel::firstOrFail();

        $this->delete(route('niveles.avances.destroy', $avance))->assertRedirect(route('personas.show', $this->A['persona']))->assertSessionHas('success');

        $this->assertSame(0, AvanceNivel::count());
        $this->assertTrue(CambioHistorial::de('persona', $this->A['persona']->id)->where('accion', 'nivel_quitado')->exists());
    }

    public function test_un_avance_automatico_no_se_puede_quitar(): void
    {
        $encuentro = \App\Models\Encuentro::create(['sede_id' => $this->A['sede']->id, 'nombre' => 'Retiro', 'fecha_realizacion' => '2026-03-12', 'valor' => 0, 'estado' => 'realizado', 'nivel_id' => $this->nivel(1)->id]);
        $avance = AvanceNivel::factory()->de($this->A['persona'], $this->nivel(1))->create(['origen' => 'encuentro', 'encuentro_id' => $encuentro->id]);

        $this->delete(route('niveles.avances.destroy', $avance))->assertRedirect()->assertSessionHas('error');

        $this->assertSame(1, AvanceNivel::count());
    }

    public function test_no_se_puede_quitar_el_avance_de_otra_sede(): void
    {
        $ajeno = AvanceNivel::factory()->de($this->B['persona'], $this->nivel(1))->create();

        $this->delete(route('niveles.avances.destroy', $ajeno))->assertNotFound();

        $this->assertSame(1, AvanceNivel::withoutGlobalScopes()->count());
    }

    public function test_el_administrador_en_todas_las_sedes_no_registra_avances(): void
    {
        $this->comoAdminEnTodas();

        $this->registrar()->assertForbidden();

        $this->assertSame(0, AvanceNivel::withoutGlobalScopes()->count());
    }
}
