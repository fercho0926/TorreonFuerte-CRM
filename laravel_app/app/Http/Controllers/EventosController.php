<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\EventosGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Services\SedeContext;
use App\Models\Evento;
use App\Models\EventoPago;
use App\Models\EventoPersona;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventosController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    protected function grilla(): Grilla
    {
        return app(EventosGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('eventos.index', $this->datosDeLaGrilla($request));
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('eventos.create', [
            'personas' => $this->personasDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $personaIds = $request->input('personas', []);

        $evento = Evento::create($data);

        foreach ($personaIds as $personaId) {
            EventoPersona::create([
                'evento_id' => $evento->id,
                'persona_id' => $personaId,
            ]);
        }

        return redirect()->route('eventos.index')->with('success', 'Evento creado correctamente.');
    }

    public function show(Request $request, Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($this->pestanaActiva($request) === 'historico') {
            return view('eventos.show', [
                'evento' => $evento,
                'pestana' => 'historico',
                'historico' => $this->historicoDeRegistro($request, $evento, route('eventos.show', $evento)),
            ]);
        }

        $visibles = $this->idsDePersonasVisibles();

        $evento->load(['inscripciones' => function ($query) use ($visibles) {
            $query->with(['persona', 'pagos' => fn ($q) => $q->orderByDesc('fecha_pago')])
                ->when($visibles !== null, fn ($q) => $q->whereIn('persona_id', $visibles));
        }]);

        return view('eventos.show', [
            'evento' => $evento,
            'personasSinInscribir' => $this->personasDisponibles()
                ->reject(fn (Persona $persona) => $evento->inscripciones->contains('persona_id', $persona->id)),
        ]);
    }

    public function edit(Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('eventos.edit', [
            'evento' => $evento,
        ]);
    }

    public function update(Request $request, Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);

        $evento->update($data);

        return redirect()->route('eventos.show', $evento)->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $evento->delete();

        return redirect()->route('eventos.index')->with('success', 'Evento eliminado correctamente.');
    }

    /**
     * Inscribe un miembro adicional al evento, ya en curso.
     */
    public function agregarPersona(Request $request, Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $request->validate([
            'persona_id' => ['required', app(SedeContext::class)->existe('personas')],
            'costo_individual' => ['nullable', 'numeric', 'min:0'],
        ]);

        EventoPersona::firstOrCreate(
            ['evento_id' => $evento->id, 'persona_id' => $data['persona_id']],
            ['costo_individual' => $data['costo_individual'] ?? null]
        );

        return redirect()->route('eventos.show', $evento)->with('success', 'Miembro inscrito correctamente.');
    }

    /**
     * Quita a un miembro del evento, solo si no tiene abonos registrados
     * (para no perder el historial de pagos ya hechos).
     */
    public function quitarPersona(Evento $evento, EventoPersona $inscripcion)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($inscripcion->evento_id === $evento->id, 404);

        if ($inscripcion->pagos()->exists()) {
            return redirect()->route('eventos.show', $evento)
                ->with('error', 'No se puede quitar a este miembro porque ya tiene abonos registrados.');
        }

        $inscripcion->delete();

        return redirect()->route('eventos.show', $evento)->with('success', 'Miembro retirado del evento.');
    }

    /**
     * Registra un abono (pago total o parcial) de un miembro para el evento.
     */
    public function registrarPago(Request $request, Evento $evento, EventoPersona $inscripcion)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($inscripcion->evento_id === $evento->id, 404);

        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_pago' => ['required', 'date'],
            'notas' => ['nullable', 'string', 'max:255'],
        ]);

        $data['evento_persona_id'] = $inscripcion->id;

        EventoPago::create($data);

        return redirect()->route('eventos.show', $evento)->with('success', 'Abono registrado correctamente.');
    }

    /**
     * Elimina un abono registrado por error.
     */
    public function eliminarPago(Evento $evento, EventoPersona $inscripcion, EventoPago $pago)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($inscripcion->evento_id === $evento->id, 404);
        abort_unless($pago->evento_persona_id === $inscripcion->id, 404);

        $pago->delete();

        return redirect()->route('eventos.show', $evento)->with('success', 'Abono eliminado.');
    }

    private function personasDisponibles()
    {
        return $this->alcance()->personasVisibles($this->usuarioActual())->orderBy('apellidos')->orderBy('nombres')->get();
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(Evento::TIPOS)],
            'fecha' => ['required', 'date'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'costo' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', Rule::in(Evento::ESTADOS)],
            'descripcion' => ['nullable', 'string'],
            'personas' => ['nullable', 'array'],
            'personas.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        unset($data['personas']);

        return $data;
    }
}
