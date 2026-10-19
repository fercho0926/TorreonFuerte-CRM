<?php

namespace Tests\Support;

use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Evento;
use App\Models\EventoPago;
use App\Models\EventoPersona;
use App\Models\Ministerio;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;

/**
 * Arma dos sedes (A y B) con datos de nombres distintivos para detectar fugas entre ellas.
 * Cada sede tiene pastor, líder, persona a cargo, célula, evento con inscripción y
 * pago, encuentro y ministerio. Requiere los traits IniciaSesion y ConSedes.
 */
trait EscenarioDosSedes
{
    /** @var array<string, mixed> */
    protected array $A = [];

    /** @var array<string, mixed> */
    protected array $B = [];

    protected function armarDosSedes(): void
    {
        $this->A = $this->armarSede('Alfa');
        $this->B = $this->armarSede('Beta');
    }

    /**
     * @return array<string, mixed>
     */
    private function armarSede(string $etiqueta): array
    {
        $sede = Sede::factory()->create(['nombre' => "Iglesia {$etiqueta}", 'ciudad' => "Ciudad {$etiqueta}"]);
        $id = $sede->id;

        $pastorPersona = Persona::factory()->deSede($id)->create(['nombres' => "Pastor{$etiqueta}", 'apellidos' => 'Principal']);
        $pastor = User::factory()->create([
            'role' => 'pastor', 'persona_id' => $pastorPersona->id, 'sede_id' => $id,
            'email' => strtolower($etiqueta).'.pastor@example.com',
        ]);
        $sede->update(['pastor_persona_id' => $pastorPersona->id]);

        $lider = Persona::factory()->deSede($id)->lider()->create(['nombres' => "Lider{$etiqueta}", 'apellidos' => 'Guia']);
        $liderUser = User::factory()->create(['role' => 'lider_celula', 'persona_id' => $lider->id, 'sede_id' => $id]);
        $persona = Persona::factory()->deSede($id)->create(['nombres' => "Persona{$etiqueta}", 'apellidos' => 'Miembro', 'lider_id' => $lider->id]);

        $celula = Celula::factory()->create(['sede_id' => $id, 'nombre' => "Celula {$etiqueta}", 'lider_id' => $lider->id]);
        $celula->miembros()->attach($persona->id);

        $evento = Evento::create([
            'sede_id' => $id, 'nombre' => "Evento {$etiqueta}", 'tipo' => Evento::TIPOS[0],
            'fecha' => now()->addWeek()->toDateString(), 'costo' => 50000, 'estado' => 'planificado',
        ]);
        $inscripcion = EventoPersona::create(['sede_id' => $id, 'evento_id' => $evento->id, 'persona_id' => $persona->id]);
        $pago = EventoPago::create(['sede_id' => $id, 'evento_persona_id' => $inscripcion->id, 'monto' => 10000, 'fecha_pago' => now()->toDateString()]);

        $ministerio = Ministerio::create(['sede_id' => $id, 'nombre' => "Ministerio {$etiqueta}", 'estado' => 'activo', 'encargado_id' => $lider->id]);

        $encuentro = Encuentro::create([
            'sede_id' => $id, 'nombre' => "Encuentro {$etiqueta}", 'fecha_realizacion' => now()->toDateString(), 'estado' => 'planificado',
        ]);

        return compact('sede', 'pastorPersona', 'pastor', 'lider', 'liderUser', 'persona', 'celula', 'evento', 'inscripcion', 'pago', 'ministerio', 'encuentro');
    }
}
