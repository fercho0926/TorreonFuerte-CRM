<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\EventosGrilla;
use App\Grillas\InscripcionesEventoGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Services\SedeContext;
use App\Models\Evento;
use App\Models\EventoPago;
use App\Models\EventoPersona;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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

        return redirect()->route('eventos.index')->with('success', __('mensajes.evento_creado'));
    }

    public function show(Request $request, Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $pestana = $this->pestanaActiva($request, ['asistencia']);

        if ($pestana === 'historico') {
            return view('eventos.show', [
                'evento' => $evento,
                'pestana' => 'historico',
                'historico' => $this->historicoDeRegistro($request, $evento, route('eventos.show', $evento)),
            ]);
        }

        // Se carga siempre (no solo en «asistencia»): totalEsperado()/totalRecaudado() de «Datos» la necesitan
        // ya filtrada por el alcance del usuario ($visibles), no una carga perezosa sin ese filtro.
        $visibles = $this->idsDePersonasVisibles();

        $evento->load(['inscripciones' => function ($query) use ($visibles) {
            $query->with(['persona', 'pagos' => fn ($q) => $q->orderByDesc('fecha_pago')])
                ->when($visibles !== null, fn ($q) => $q->whereIn('persona_id', $visibles));
        }]);

        $personasSinInscribir = collect();
        $datosGrillaInscripciones = [];

        if ($pestana === 'asistencia') {
            $personasSinInscribir = $this->personasDisponibles()
                ->reject(fn (Persona $persona) => $evento->inscripciones->contains('persona_id', $persona->id));

            // Permite que <x-ui.grilla> genere sus enlaces (`route('eventos.inscripciones.datos')`, etc.)
            // sin pasar {evento} explícitamente: esta ficha siempre la fija a este evento.
            URL::defaults(['evento' => $evento->id]);
            $datosGrillaInscripciones = $this->datosDeLaGrillaInscripciones($request, $evento);
        }

        return view('eventos.show', [
            'evento' => $evento,
            'pestana' => $pestana,
            'personasSinInscribir' => $personasSinInscribir,
        ] + $datosGrillaInscripciones);
    }

    /** Variables para `<x-ui.grilla>` de los miembros inscritos de este evento (spec 019). */
    private function datosDeLaGrillaInscripciones(Request $request, Evento $evento): array
    {
        $grillaInscripciones = app(InscripcionesEventoGrilla::class);
        $usuario = $this->usuarioActual();
        $request->merge(['evento' => $evento->id]);
        $estadoInscripciones = $grillaInscripciones->estado($request, $usuario);
        $porPaginaInscripciones = $grillaInscripciones->porPagina($request->input('por_pagina'));

        $paginaInscripciones = $grillaInscripciones->paginar($usuario, $estadoInscripciones, $porPaginaInscripciones, max(1, (int) $request->input('page', 1)))
            ->withPath(route('eventos.inscripciones.index', $evento))
            ->appends($request->query());

        return [
            'grillaInscripciones' => $grillaInscripciones,
            'estadoInscripciones' => $estadoInscripciones,
            'paginaInscripciones' => $paginaInscripciones,
            'porPaginaInscripciones' => $porPaginaInscripciones,
            'totalInscripciones' => $grillaInscripciones->total($usuario, $estadoInscripciones),
        ];
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

        return redirect()->route('eventos.show', $evento)->with('success', __('mensajes.evento_actualizado'));
    }

    public function destroy(Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $evento->delete();

        return redirect()->route('eventos.index')->with('success', __('mensajes.evento_eliminado'));
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

        return redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'])->with('success', __('mensajes.miembro_inscrito_evento'));
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
            return redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'])
                ->with('error', __('mensajes.no_quitar_miembro_abonos'));
        }

        $inscripcion->delete();

        return redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'])->with('success', __('mensajes.miembro_retirado_evento'));
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

        return redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'])->with('success', __('mensajes.abono_registrado'));
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

        return redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'])->with('success', __('mensajes.abono_eliminado'));
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
