<?php

namespace Tests\Feature\Historial;

use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Excepción declarada al aislamiento (clarificación 3, RF-023): la sede actual de un miembro trasladado ve todo su
 * histórico anterior, pero nada más de la otra sede.
 */
class TrasladoHistorialTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private function trasladar(Persona $persona, Sede $destino): Persona
    {
        $this->comoAdminEnSede(Sede::findOrFail($persona->sede_id));
        $this->post(route('traslados.store', $persona), ['sede_destino_id' => $destino->id])->assertSessionHasNoErrors();

        return Persona::withoutGlobalScopes()->where('sede_id', $destino->id)->where('trasladada_desde_persona_id', $persona->id)->firstOrFail();
    }

    public function test_la_sede_destino_ve_todo_el_historico_anterior_con_la_sede_de_origen_y_sin_enlaces(): void
    {
        $this->armarDosSedes();
        $nueva = $this->trasladar($this->A['persona'], $this->B['sede']);

        $this->iniciarSesionComo($this->B['pastor']);
        $html = $this->get(route('personas.show', [$nueva, 'pestana' => 'historico']))->assertOk()->getContent();

        $this->assertStringContainsString('Inscrito al evento', $html, 'entradas de la sede anterior');
        $this->assertStringContainsString('Evento Alfa', $html);
        $this->assertStringContainsString('$ 10.000', $html, 'con sus montos');
        $this->assertStringContainsString('Ocurrió en la sede Iglesia Alfa', $html);
        $this->assertStringContainsString('Llegó por traslado', $html);
        $this->assertStringNotContainsString('href="'.route('eventos.show', $this->A['evento']).'"', $html, 'las fichas de la otra sede no se enlazan');
    }

    public function test_no_se_abre_nada_mas_de_la_sede_de_origen(): void
    {
        $this->armarDosSedes();
        $nueva = $this->trasladar($this->A['persona'], $this->B['sede']);
        $this->iniciarSesionComo($this->B['pastor']);

        $html = $this->get(route('personas.show', [$nueva, 'pestana' => 'historico']))->getContent();
        $this->assertStringNotContainsString('LiderAlfa Guia</strong>', $html);
        $this->get(route('personas.show', [$this->A['lider'], 'pestana' => 'historico']))->assertNotFound();
        $this->get(route('personas.show', [$this->A['persona'], 'pestana' => 'historico']))->assertNotFound();
    }

    public function test_un_traslado_doble_muestra_la_cadena_completa(): void
    {
        $this->armarDosSedes();
        $tercera = Sede::factory()->create(['nombre' => 'Iglesia Gamma']);
        $pastorGamma = $this->comoPastorDe($tercera);

        $enB = $this->trasladar($this->A['persona'], $this->B['sede']);
        $enC = $this->trasladar($enB, $tercera);

        $this->iniciarSesionComo($pastorGamma);
        $html = $this->get(route('personas.show', [$enC, 'pestana' => 'historico']))->assertOk()->getContent();

        $this->assertStringContainsString('Evento Alfa', $html, 'lo de la primera sede');
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'Llegó por traslado'), 'los dos traslados');
    }
}
