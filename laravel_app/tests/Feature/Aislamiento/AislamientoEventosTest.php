<?php

namespace Tests\Feature\Aislamiento;

use App\Models\Encuentro;
use App\Models\Evento;
use App\Models\EventoPago;
use App\Models\EventoPersona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AislamientoEventosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_eventos_inscripciones_y_pagos_solo_de_la_sede_propia(): void
    {
        $this->get(route('eventos.index'))->assertOk()->assertSee('Evento Alfa')->assertDontSee('Evento Beta');
        $this->get(route('eventos.show', $this->A['evento']))->assertOk()->assertSee('PersonaAlfa')->assertDontSee('PersonaBeta');

        $this->get(route('eventos.show', $this->B['evento']))->assertNotFound();
        $this->get(route('eventos.edit', $this->B['evento']))->assertNotFound();
        $this->put(route('eventos.update', $this->B['evento']), ['nombre' => 'Hack'])->assertNotFound();
        $this->delete(route('eventos.destroy', $this->B['evento']))->assertNotFound();
        $this->assertNotNull($this->B['evento']->fresh());
    }

    public function test_no_se_inscribe_ni_se_cobra_con_datos_de_otra_sede(): void
    {
        $this->post(route('eventos.personas.store', $this->A['evento']), ['persona_id' => $this->B['persona']->id])
            ->assertSessionHasErrors('persona_id');
        $this->assertSame(1, EventoPersona::withoutGlobalScopes()->where('evento_id', $this->A['evento']->id)->count());

        $this->post(route('eventos.pagos.store', [$this->B['evento'], $this->B['inscripcion']]), ['monto' => 1000, 'fecha_pago' => now()->toDateString()])
            ->assertNotFound();
        $this->post(route('eventos.pagos.store', [$this->A['evento'], $this->B['inscripcion']]), ['monto' => 1000, 'fecha_pago' => now()->toDateString()])
            ->assertNotFound();
        $this->delete(route('eventos.pagos.destroy', [$this->B['evento'], $this->B['inscripcion'], $this->B['pago']]))->assertNotFound();

        $this->assertSame(1, EventoPago::withoutGlobalScopes()->where('evento_persona_id', $this->B['inscripcion']->id)->count());
    }

    public function test_los_pagos_nuevos_quedan_en_la_sede_propia(): void
    {
        $this->post(route('eventos.pagos.store', [$this->A['evento'], $this->A['inscripcion']]), ['monto' => 5000, 'fecha_pago' => now()->toDateString()])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, EventoPago::withoutGlobalScopes()->where('sede_id', $this->A['sede']->id)->count());
        $this->assertSame(1, EventoPago::withoutGlobalScopes()->where('sede_id', $this->B['sede']->id)->count());
    }

    public function test_crear_un_evento_con_personas_ajenas_se_rechaza(): void
    {
        $this->post(route('eventos.store'), [
            'nombre' => 'Evento Cruzado', 'tipo' => Evento::TIPOS[0], 'fecha' => now()->addDay()->toDateString(), 'costo' => 0, 'estado' => 'planificado',
            'personas' => [$this->B['persona']->id],
        ])->assertSessionHasErrors('personas.0');

        $this->assertSame(0, Evento::withoutGlobalScopes()->where('nombre', 'Evento Cruzado')->count());
    }

    public function test_encuentros_de_otra_sede_no_se_ven_ni_se_tocan(): void
    {
        $this->get(route('encuentros.index'))->assertOk()->assertSee('Encuentro Alfa')->assertDontSee('Encuentro Beta');
        $this->get(route('encuentros.show', $this->B['encuentro']))->assertNotFound();
        $this->get(route('encuentros.edit', $this->B['encuentro']))->assertNotFound();
        $this->delete(route('encuentros.destroy', $this->B['encuentro']))->assertNotFound();
        $this->assertNotNull(Encuentro::withoutGlobalScopes()->find($this->B['encuentro']->id));

        $this->post(route('encuentros.store'), [
            'nombre' => 'Encuentro Cruzado', 'fecha_realizacion' => now()->toDateString(), 'estado' => 'planificado', 'personas' => [$this->B['persona']->id],
        ])->assertSessionHasErrors('personas.0');
    }
}
