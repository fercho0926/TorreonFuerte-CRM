<?php

namespace App\Http\Controllers;

use App\Services\SedeContext;
use App\Models\Celula;
use App\Models\CelulaReunion;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CelulasController extends Controller
{
    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $celulas = $this->alcance()->celulasVisibles($this->usuarioActual())
            ->with(['lider', 'coLider'])
            ->withCount('miembros')
            ->orderBy('nombre')
            ->get();

        $user = $this->usuarioActual();

        return view('celulas.index', [
            'celulas' => $celulas,
            'puedeGestionarEstructura' => $user && $user->tieneAccesoTotal(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        return view('celulas.create', [
            'personas' => $this->personasDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        $data = $this->validateData($request);

        Celula::create($data);

        return redirect()->route('celulas.index')->with('success', 'Célula creada correctamente.');
    }

    public function show(Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeVerCelula($celula)) {
            return $redirect;
        }

        $celula->load(['lider', 'coLider', 'miembros' => function ($query) {
            $query->orderBy('apellidos')->orderBy('nombres');
        }, 'reuniones.asistentes']);

        return view('celulas.show', [
            'celula' => $celula,
            'puedeAdministrar' => $this->puedeAdministrarCelula($celula),
            'puedeGestionarEstructura' => $this->usuarioActual()?->tieneAccesoTotal(),
            'personasSinAsignar' => $this->candidatosAMiembro($celula)
                ->reject(fn (Persona $persona) => $celula->miembros->contains('id', $persona->id)),
        ]);
    }

    public function edit(Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        return view('celulas.edit', [
            'celula' => $celula,
            'personas' => $this->personasDisponibles(),
        ]);
    }

    public function update(Request $request, Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        $data = $this->validateData($request, $celula);

        $celula->update($data);

        return redirect()->route('celulas.show', $celula)->with('success', 'Célula actualizada correctamente.');
    }

    public function destroy(Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        $celula->delete();

        return redirect()->route('celulas.index')->with('success', 'Célula eliminada correctamente.');
    }

    public function agregarMiembro(Request $request, Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'persona_id' => ['required', app(SedeContext::class)->existe('personas')],
        ]);

        // El líder directo es la fuente de verdad: solo pueden ser miembros de la célula las
        // personas cuyo líder directo es el líder o el co-líder de la célula.
        $persona = Persona::findOrFail($data['persona_id']);
        if (!in_array($persona->lider_id, array_filter([$celula->lider_id, $celula->co_lider_id]), true)) {
            return redirect()->route('celulas.show', $celula)
                ->with('error', 'Solo puedes agregar personas cuyo líder directo sea el líder o co-líder de la célula. Cambia primero su líder en la ficha de la persona.');
        }

        $celula->miembros()->syncWithoutDetaching([
            $persona->id => ['fecha_ingreso' => now()->format('Y-m-d')],
        ]);

        return redirect()->route('celulas.show', $celula)->with('success', 'Persona agregada a la célula.');
    }

    public function quitarMiembro(Celula $celula, Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        // La persona conserva su líder directo; solo deja de pertenecer a esta célula.
        $celula->miembros()->detach($persona->id);

        return redirect()->route('celulas.show', $celula)->with('success', 'Persona retirada de la célula.');
    }

    public function registrarReunion(Request $request, Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'tema' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'asistio' => ['array'],
            'asistio.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        $reunion = CelulaReunion::create([
            'celula_id' => $celula->id,
            'fecha' => $data['fecha'],
            'tema' => $data['tema'] ?? null,
            'notas' => $data['notas'] ?? null,
        ]);

        $asistieron = $data['asistio'] ?? [];
        $asistencia = $celula->miembros->mapWithKeys(fn (Persona $persona) => [
            $persona->id => ['asistio' => in_array($persona->id, $asistieron)],
        ]);

        $reunion->asistentes()->sync($asistencia->all());

        return redirect()->route('celulas.show', $celula)->with('success', 'Reunión registrada correctamente.');
    }

    public function eliminarReunion(Celula $celula, CelulaReunion $reunion)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        abort_unless($reunion->celula_id === $celula->id, 404);

        $reunion->delete();

        return redirect()->route('celulas.show', $celula)->with('success', 'Reunión eliminada.');
    }

    private function ensurePuedeVerCelula(Celula $celula)
    {
        if (!$this->alcance()->celulasVisibles($this->usuarioActual())->whereKey($celula->id)->exists()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta célula.');
        }

        return null;
    }

    private function puedeAdministrarCelula(Celula $celula): bool
    {
        $user = $this->usuarioActual();

        if (!$user) {
            return false;
        }

        if ($user->tieneAccesoTotal()) {
            return true;
        }

        if ($user->isLiderCelula()) {
            return $celula->lider_id === $user->persona_id || $celula->co_lider_id === $user->persona_id;
        }

        return false;
    }

    private function personasDisponibles()
    {
        return $this->alcance()->personasVisibles($this->usuarioActual())
            ->activas()
            ->orderBy('apellidos')->orderBy('nombres')
            ->get();
    }

    /**
     * Personas que pueden unirse a la célula: las que tienen como líder directo al líder
     * o al co-líder de la célula.
     */
    private function candidatosAMiembro(Celula $celula)
    {
        return Persona::query()
            ->whereIn('lider_id', array_filter([$celula->lider_id, $celula->co_lider_id]))
            ->activas()
            ->orderBy('apellidos')->orderBy('nombres')
            ->get();
    }

    private function validateData(Request $request, ?Celula $celula = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'lider_id' => ['required', app(SedeContext::class)->existe('personas')],
            'co_lider_id' => ['nullable', app(SedeContext::class)->existe('personas')],
            'sede' => ['nullable', 'string', 'max:255'],
            'dia_reunion' => ['nullable', Rule::in(Celula::DIAS)],
            'hora_reunion' => ['nullable', 'date_format:H:i'],
            'lugar_reunion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(Celula::ESTADOS)],
        ]);
    }
}
