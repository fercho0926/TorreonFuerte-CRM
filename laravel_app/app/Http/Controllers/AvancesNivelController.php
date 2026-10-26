<?php

namespace App\Http\Controllers;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Services\AvancesNivelService;
use App\Services\SedeContext;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Registro y retiro manual del avance de un miembro en el plan de niveles (spec 014). */
class AvancesNivelController extends Controller
{
    public function __construct(private AvancesNivelService $avances)
    {
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $datos = $request->validate([
            'persona_id' => ['required', 'integer', app(SedeContext::class)->existe('personas')],
            'nivel_id' => ['required', 'integer', 'exists:niveles_formacion,id'],
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'nota' => ['nullable', 'string', 'max:255'],
        ], [
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'nivel_id.required' => 'Elige un nivel.',
        ]);

        // Solo miembros que el usuario puede ver (alcance de su rol): fuera de él es como si no existieran.
        $persona = $this->alcance()->personasVisibles($this->usuarioActual())->findOrFail($datos['persona_id']);

        $this->avances->registrarManual(
            $persona,
            NivelFormacion::findOrFail($datos['nivel_id']),
            $datos['fecha'] ?? now()->toDateString(),
            $datos['nota'] ?? null,
            $this->usuarioActual(),
        );

        return redirect()->route('personas.show', $persona)->with('success', 'Nivel registrado en el camino.');
    }

    public function destroy(AvanceNivel $avance)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $persona = $this->alcance()->personasVisibles($this->usuarioActual())->findOrFail($avance->persona_id);

        try {
            $this->avances->quitarManual($avance, $this->usuarioActual());
        } catch (ValidationException $e) {
            return redirect()->route('personas.show', $persona)->with('error', $e->errors()['avance'][0]);
        }

        return redirect()->route('personas.show', $persona)->with('success', 'Nivel retirado del camino.');
    }
}
