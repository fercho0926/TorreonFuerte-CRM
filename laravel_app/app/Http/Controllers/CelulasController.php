<?php

namespace App\Http\Controllers;

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

        $visiblesIds = $this->celulasVisiblesIds();

        $celulas = Celula::with(['lider', 'coLider'])
            ->withCount('miembros')
            ->when($visiblesIds !== null, fn ($query) => $query->whereIn('id', $visiblesIds))
            ->orderBy('nombre')
            ->get();

        $user = $this->usuarioActual();

        return view('celulas.index', [
            'celulas' => $celulas,
            'puedeGestionarEstructura' => $user && ($user->tieneAccesoTotal() || $user->isLiderZona()),
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
            'puedeGestionarEstructura' => $this->usuarioActual()?->tieneAccesoTotal() || $this->usuarioActual()?->isLiderZona(),
            'personasSinAsignar' => $this->personasDisponibles()
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
            'persona_id' => ['required', Rule::exists('personas', 'id')],
        ]);

        $celula->miembros()->syncWithoutDetaching([
            $data['persona_id'] => ['fecha_ingreso' => now()->format('Y-m-d')],
        ]);

        // Mantiene sincronizado el "líder de célula" clásico de Personas con la célula real.
        Persona::whereKey($data['persona_id'])->update(['lider_id' => $celula->lider_id]);

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

        $celula->miembros()->detach($persona->id);

        if ($persona->lider_id === $celula->lider_id) {
            $persona->update(['lider_id' => null]);
        }

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
            'asistio.*' => ['integer', Rule::exists('personas', 'id')],
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
        $visiblesIds = $this->celulasVisiblesIds();

        if ($visiblesIds !== null && !in_array($celula->id, $visiblesIds, true)) {
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

        if ($user->isLiderZona()) {
            $visiblesIds = $this->celulasVisiblesIds() ?? [];

            return in_array($celula->id, $visiblesIds, true);
        }

        if ($user->isLiderCelula()) {
            return $celula->lider_id === $user->persona_id || $celula->co_lider_id === $user->persona_id;
        }

        return false;
    }

    private function personasDisponibles()
    {
        return Persona::orderBy('apellidos')->orderBy('nombres')->get();
    }

    private function validateData(Request $request, ?Celula $celula = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'lider_id' => ['required', Rule::exists('personas', 'id')],
            'co_lider_id' => ['nullable', Rule::exists('personas', 'id')],
            'sede' => ['nullable', 'string', 'max:255'],
            'dia_reunion' => ['nullable', Rule::in(Celula::DIAS)],
            'hora_reunion' => ['nullable', 'date_format:H:i'],
            'lugar_reunion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(Celula::ESTADOS)],
        ]);
    }
}
