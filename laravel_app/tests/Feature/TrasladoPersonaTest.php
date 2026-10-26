<?php

namespace Tests\Feature;

use App\Models\Celula;
use App\Models\EventoPago;
use App\Models\EventoPersona;
use App\Models\Persona;
use App\Services\SedeContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class TrasladoPersonaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_el_traslado_crea_el_registro_en_la_sede_destino_y_deja_el_origen_inactivo_con_su_historial(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $persona = $this->A['persona'];

        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $this->B['sede']->id])
            ->assertSessionHasNoErrors()->assertRedirect(route('personas.show', $persona));

        $origen = Persona::withoutGlobalScopes()->find($persona->id);
        $this->assertSame('inactivo', $origen->estado);
        $this->assertSame($this->B['sede']->id, $origen->trasladada_a_sede_id);
        $this->assertNotNull($origen->trasladada_en);
        $this->assertSame($this->A['sede']->id, $origen->sede_id);

        // Historial intacto en la sede de origen (célula, inscripción y pago).
        $this->assertTrue($this->A['celula']->miembros()->whereKey($persona->id)->exists());
        $this->assertSame(1, EventoPersona::withoutGlobalScopes()->where('persona_id', $persona->id)->count());
        $this->assertSame(1, EventoPago::withoutGlobalScopes()->where('sede_id', $this->A['sede']->id)->count());

        // Registro nuevo en la sede destino: sin líder, célula ni ministerios.
        $nueva = Persona::withoutGlobalScopes()->where('sede_id', $this->B['sede']->id)->where('numero_documento', $persona->numero_documento)->firstOrFail();
        $this->assertNotSame($persona->id, $nueva->id);
        $this->assertNull($nueva->lider_id);
        $this->assertFalse($nueva->es_lider);
        $this->assertSame($persona->nombres, $nueva->nombres);
        $this->assertSame(0, $nueva->celulas()->withoutGlobalScopes()->count());
        $this->assertSame(0, $nueva->ministerios()->withoutGlobalScopes()->count());

        // Spec 015: el traslado queda en la sección «Sede» de ambos registros, unidos por trasladada_desde_persona_id.
        $this->assertDatabaseHas('historial_cambios', ['sujeto_tipo' => 'persona', 'sujeto_id' => $persona->id, 'accion' => 'trasladada', 'categoria' => 'sede', 'sede_id' => $this->A['sede']->id]);
        $this->assertDatabaseHas('historial_cambios', ['sujeto_tipo' => 'persona', 'sujeto_id' => $nueva->id, 'accion' => 'llego_por_traslado', 'categoria' => 'sede', 'sede_id' => $this->B['sede']->id]);
        $this->assertSame($persona->id, (int) $nueva->trasladada_desde_persona_id);
    }

    public function test_la_persona_trasladada_aparece_sin_lider_en_la_sede_destino_y_no_se_muestra_el_historial_de_origen(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->post(route('traslados.store', $this->A['persona']), ['sede_destino_id' => $this->B['sede']->id]);

        $this->iniciarSesionComo($this->B['pastor']);
        $this->get(route('sin-lider.index'))->assertSee('PersonaAlfa Miembro');

        $nueva = Persona::where('nombres', 'PersonaAlfa')->firstOrFail();
        $this->get(route('personas.show', $nueva))->assertOk()->assertDontSee('Evento Alfa')->assertDontSee('Celula Alfa');
    }

    public function test_es_422_si_la_persona_es_lider_con_personas_o_celulas_a_cargo_o_encargada_de_un_ministerio(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->post(route('traslados.store', $this->A['lider']), ['sede_destino_id' => $this->B['sede']->id])
            ->assertStatus(422)->assertSee('1 miembro(s)')->assertSee('1 célula(s)')->assertSee('1 ministerio(s)');

        $this->assertSame($this->A['sede']->id, $this->A['lider']->fresh()->sede_id);
        $this->assertNull($this->A['lider']->fresh()->trasladada_a_sede_id);
        $this->assertSame(0, Persona::withoutGlobalScopes()->where('sede_id', $this->B['sede']->id)->where('numero_documento', $this->A['lider']->numero_documento)->count());
    }

    public function test_un_lider_sin_nada_a_cargo_si_se_traslada_y_llega_sin_rol(): void
    {
        $libre = Persona::factory()->deSede($this->A['sede']->id)->lider()->create(['nombres' => 'LiderLibre']);
        $this->comoAdminEnSede($this->A['sede']);

        $this->post(route('traslados.store', $libre), ['sede_destino_id' => $this->B['sede']->id])->assertSessionHasNoErrors();

        $nueva = Persona::withoutGlobalScopes()->where('sede_id', $this->B['sede']->id)->where('nombres', 'LiderLibre')->firstOrFail();
        $this->assertFalse($nueva->es_lider);
    }

    public function test_falla_si_el_documento_ya_existe_en_la_sede_destino(): void
    {
        $this->B['persona']->update(['numero_documento' => $this->A['persona']->numero_documento]);
        $this->comoAdminEnSede($this->A['sede']);

        $this->post(route('traslados.store', $this->A['persona']), ['sede_destino_id' => $this->B['sede']->id])->assertSessionHasErrors('sede_destino_id');

        $this->assertNull($this->A['persona']->fresh()->trasladada_a_sede_id);
        $this->assertSame('miembro_activo', $this->A['persona']->fresh()->estado);
    }

    public function test_no_se_traslada_a_la_misma_sede_a_una_inactiva_ni_dos_veces(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $persona = $this->A['persona'];

        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $this->A['sede']->id])->assertSessionHasErrors('sede_destino_id');

        $inactiva = $this->crearSede(['nombre' => 'Inactiva', 'activa' => false]);
        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $inactiva->id])->assertSessionHasErrors('sede_destino_id');

        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $this->B['sede']->id])->assertSessionHasNoErrors();
        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $this->B['sede']->id])->assertSessionHasErrors('sede_destino_id');
    }

    public function test_solo_el_admin_puede_trasladar(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('traslados.create', $this->A['persona']))->assertForbidden();
        $this->post(route('traslados.store', $this->A['persona']), ['sede_destino_id' => $this->B['sede']->id])->assertForbidden();

        $this->iniciarSesionComo($this->A['liderUser']);
        $this->post(route('traslados.store', $this->A['persona']), ['sede_destino_id' => $this->B['sede']->id])->assertForbidden();
        $this->get(route('personas.show', $this->A['persona']))->assertDontSee('Trasladar a otra sede');

        $this->assertNull($this->A['persona']->fresh()->trasladada_a_sede_id);
    }

    public function test_el_formulario_muestra_los_pendientes_y_las_sedes_destino(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->get(route('traslados.create', $this->A['lider']))
            ->assertOk()->assertSee('Antes de trasladar hay que reasignar')->assertSee('Iglesia Beta')->assertDontSee('Iglesia Alfa —');
        $this->get(route('personas.show', $this->A['persona']))->assertSee('Trasladar a otra sede');
    }

    public function test_el_traslado_no_deja_filas_sin_sede(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->post(route('traslados.store', $this->A['persona']), ['sede_destino_id' => $this->B['sede']->id]);

        $this->assertSame([], array_filter(\App\Support\MigracionSedes::contarSinSede()));
        $this->assertSame(0, Celula::withoutGlobalScopes()->whereNull('sede_id')->count());
        $this->assertNotNull(app(SedeContext::class));
    }
}
