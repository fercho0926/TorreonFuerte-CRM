<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Inscripciones y abonos de eventos y retiros en el histórico del miembro (spec 015, historia 1). */
class MiembroEventosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_inscripcion_abonos_y_retiro_quedan_en_el_miembro_y_en_el_evento(): void
    {
        $evento = $this->A['evento'];
        $nueva = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Nueva', 'apellidos' => 'Inscrita']);

        $this->post(route('eventos.personas.store', $evento), ['persona_id' => $nueva->id])->assertSessionHasNoErrors();
        $inscripcion = $evento->inscripciones()->where('persona_id', $nueva->id)->firstOrFail();
        $this->post(route('eventos.pagos.store', [$evento, $inscripcion]), ['monto' => 15000, 'fecha_pago' => now()->toDateString()])->assertSessionHasNoErrors();
        $this->post(route('eventos.pagos.store', [$evento, $inscripcion]), ['monto' => 5000, 'fecha_pago' => now()->toDateString()])->assertSessionHasNoErrors();
        $pago = $inscripcion->pagos()->latest('id')->firstOrFail();
        $this->delete(route('eventos.pagos.destroy', [$evento, $inscripcion, $pago]));

        $acciones = CambioHistorial::query()->de('persona', $nueva->id)->orderBy('id')->pluck('accion')->all();
        $this->assertSame(['inscrito_evento', 'abono_registrado', 'abono_registrado', 'abono_eliminado'], $acciones);
        $this->assertTrue(CambioHistorial::query()->de('persona', $nueva->id)->get()->every(fn ($c) => $c->categoria === 'encuentros_retiros' && $c->relacionado_id === $evento->id));
        $this->assertSame(4, CambioHistorial::query()->de('evento', $evento->id)->where('relacionado_id', $nueva->id)->count());

        $html = $this->get(route('personas.show', [$nueva, 'pestana' => 'historico']))->getContent();
        $this->assertStringContainsString('Abono registrado', $html);
        $this->assertStringContainsString('$ 15.000', $html);
    }

    public function test_quien_no_tiene_lectura_en_eventos_ve_la_inscripcion_sin_enlace_ni_monto(): void
    {
        $rol = Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'miembros' => 1])->create();
        $usuario = User::factory()->create(['role' => $rol->clave, 'sede_id' => $this->A['sede']->id, 'persona_id' => Persona::factory()->deSede($this->A['sede']->id)->create()->id]);
        $this->iniciarSesionComo($usuario);

        $html = $this->get(route('personas.show', [$this->A['persona'], 'pestana' => 'historico']))->assertOk()->getContent();

        $this->assertStringContainsString('Inscrito al evento', $html);
        $this->assertStringContainsString('Evento Alfa', $html);
        $this->assertStringNotContainsString('href="'.route('eventos.show', $this->A['evento']).'"', $html);
        $this->assertStringNotContainsString('$ 10.000', $html, 'el monto del abono no se muestra');
    }
}
