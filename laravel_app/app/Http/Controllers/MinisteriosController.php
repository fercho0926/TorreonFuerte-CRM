<?php

namespace App\Http\Controllers;

use App\Services\SedeContext;
use App\Models\Ministerio;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MinisteriosController extends Controller
{
    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $ministerios = Ministerio::with('encargado')
            ->withCount('integrantes')
            ->orderBy('nombre')
            ->get();

        return view('ministerios.index', [
            'ministerios' => $ministerios,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('ministerios.create', [
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

        $ministerio = Ministerio::create($data);
        $ministerio->integrantes()->sync($personaIds);

        return redirect()->route('ministerios.index')->with('success', 'Ministerio creado correctamente.');
    }

    public function show(Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $ministerio->load(['encargado', 'integrantes' => function ($query) {
            $query->orderBy('apellidos')->orderBy('nombres');
        }]);

        return view('ministerios.show', [
            'ministerio' => $ministerio,
        ]);
    }

    public function edit(Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('ministerios.edit', [
            'ministerio' => $ministerio,
            'personas' => $this->personasDisponibles(),
            'personasSeleccionadas' => $ministerio->integrantes()->pluck('personas.id')->all(),
        ]);
    }

    public function update(Request $request, Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $personaIds = $request->input('personas', []);

        $ministerio->update($data);
        // sync() con IDs simples conserva el rol/fecha_ingreso de quienes ya estaban.
        $ministerio->integrantes()->sync($personaIds);

        return redirect()->route('ministerios.index')->with('success', 'Ministerio actualizado correctamente.');
    }

    public function destroy(Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $ministerio->delete();

        return redirect()->route('ministerios.index')->with('success', 'Ministerio eliminado correctamente.');
    }

    /**
     * Actualiza, desde el detalle del ministerio, el rol y la fecha de ingreso de cada integrante.
     */
    public function actualizarIntegrantes(Request $request, Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $request->validate([
            'rol' => ['array'],
            'rol.*' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['array'],
            'fecha_ingreso.*' => ['nullable', 'date'],
        ]);

        $roles = $data['rol'] ?? [];
        $fechas = $data['fecha_ingreso'] ?? [];

        foreach ($ministerio->integrantes as $persona) {
            $ministerio->integrantes()->updateExistingPivot($persona->id, [
                'rol' => $roles[$persona->id] ?? null,
                'fecha_ingreso' => $fechas[$persona->id] ?? null,
            ]);
        }

        return redirect()->route('ministerios.show', $ministerio)->with('success', 'Integrantes actualizados correctamente.');
    }

    private function personasDisponibles()
    {
        return Persona::orderBy('apellidos')->orderBy('nombres')->get();
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'encargado_id' => ['nullable', app(SedeContext::class)->existe('personas')],
            'estado' => ['required', Rule::in(Ministerio::ESTADOS)],
            'personas' => ['nullable', 'array'],
            'personas.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        $data['encargado_id'] = ($data['encargado_id'] ?? null) ?: null;
        unset($data['personas']);

        return $data;
    }
}
