<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\EncuentrosGrilla;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Services\SedeContext;
use App\Models\Encuentro;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EncuentrosController extends Controller
{
    use ServicioGrilla;

    protected function grilla(): Grilla
    {
        return app(EncuentrosGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('encuentros.index', $this->datosDeLaGrilla($request));
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('encuentros.create', [
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

        $encuentro = Encuentro::create($data);
        $encuentro->personas()->sync($personaIds);

        return redirect()->route('encuentros.index')->with('success', 'Encuentro creado correctamente.');
    }

    public function show(Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $encuentro->load(['personas' => function ($query) {
            $query->orderBy('apellidos')->orderBy('nombres');
        }]);

        return view('encuentros.show', [
            'encuentro' => $encuentro,
        ]);
    }

    public function edit(Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('encuentros.edit', [
            'encuentro' => $encuentro,
            'personas' => $this->personasDisponibles(),
            'personasSeleccionadas' => $encuentro->personas()->pluck('personas.id')->all(),
        ]);
    }

    public function update(Request $request, Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $personaIds = $request->input('personas', []);

        $encuentro->update($data);
        $encuentro->personas()->sync($personaIds);

        return redirect()->route('encuentros.index')->with('success', 'Encuentro actualizado correctamente.');
    }

    public function destroy(Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $encuentro->delete();

        return redirect()->route('encuentros.index')->with('success', 'Encuentro eliminado correctamente.');
    }

    /**
     * Actualiza, desde el detalle del encuentro, quién asistió y cuánto pagó cada persona.
     */
    public function actualizarAsistencia(Request $request, Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $request->validate([
            'asistio' => ['array'],
            'asistio.*' => ['integer', app(SedeContext::class)->existe('personas')],
            'valor_pagado' => ['array'],
            'valor_pagado.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $asistieron = $data['asistio'] ?? [];
        $valoresPagados = $data['valor_pagado'] ?? [];

        foreach ($encuentro->personas as $persona) {
            $encuentro->personas()->updateExistingPivot($persona->id, [
                'asistio' => in_array($persona->id, $asistieron),
                'valor_pagado' => $valoresPagados[$persona->id] ?? null,
            ]);
        }

        return redirect()->route('encuentros.show', $encuentro)->with('success', 'Asistencia y pagos actualizados.');
    }

    private function personasDisponibles()
    {
        return Persona::orderBy('apellidos')->orderBy('nombres')->get();
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_realizacion' => ['required', 'date'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', Rule::in(Encuentro::ESTADOS)],
            'notas' => ['nullable', 'string'],
            'personas' => ['nullable', 'array'],
            'personas.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        unset($data['personas']);

        return $data;
    }
}
