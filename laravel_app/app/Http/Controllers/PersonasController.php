<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonasController extends Controller
{
    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $visiblesIds = $this->personasVisiblesIds();

        $personas = Persona::with('lider')
            ->when($visiblesIds !== null, fn ($query) => $query->whereIn('id', $visiblesIds))
            ->when($request->filled('lider_id'), fn ($query) => $query->where('lider_id', $request->lider_id))
            ->when($request->filled('red'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('red', $request->red)
                        ->orWhereHas('lider', fn ($lider) => $lider->where('red', $request->red));
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('personas.index', [
            'personas' => $personas,
            'lideres' => Persona::lideres()->orderBy('apellidos')->orderBy('nombres')->get(),
            'redes' => Persona::lideres()->pluck('red')->unique()->sort()->values(),
            'filtroLiderId' => $request->lider_id,
            'filtroRed' => $request->red,
        ]);
    }

    public function show(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeVerPersona($persona)) {
            return $redirect;
        }

        $persona->load([
            'lider',
            'miembros' => fn ($query) => $query->orderBy('apellidos')->orderBy('nombres'),
            'celulas.lider',
            'celulasLideradas.miembros',
            'celulasCoLideradas.miembros',
            'encuentros' => fn ($query) => $query->orderByDesc('fecha_realizacion'),
            'inscripcionesEventos.evento',
            'inscripcionesEventos.pagos',
            'ministerios',
            'ministeriosLiderados',
        ]);

        return view('personas.show', [
            'persona' => $persona,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('personas.create', [
            'lideres' => $this->lideresDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);

        Persona::create($data);

        return redirect()->route('personas.index')->with('success', 'Persona creada correctamente.');
    }

    public function edit(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeVerPersona($persona)) {
            return $redirect;
        }

        return view('personas.edit', [
            'persona' => $persona,
            'lideres' => $this->lideresDisponibles($persona),
        ]);
    }

    public function update(Request $request, Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeVerPersona($persona)) {
            return $redirect;
        }

        $data = $this->validateData($request, $persona);

        $persona->update($data);

        return redirect()->route('personas.index')->with('success', 'Persona actualizada correctamente.');
    }

    public function destroy(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeVerPersona($persona)) {
            return $redirect;
        }

        $persona->delete();

        return redirect()->route('personas.index')->with('success', 'Persona eliminada correctamente.');
    }

    private function ensurePuedeVerPersona(Persona $persona)
    {
        $visiblesIds = $this->personasVisiblesIds();

        if ($visiblesIds !== null && !in_array($persona->id, $visiblesIds, true)) {
            return redirect()->route('personas.index')->with('error', 'No tienes acceso a esta persona.');
        }

        return null;
    }

    /**
     * Personas disponibles para asignar como líder de célula (excluye a la propia persona
     * al editar, para que nadie pueda ser su propio líder).
     */
    private function lideresDisponibles(?Persona $persona = null)
    {
        return Persona::lideres()
            ->when($persona, fn ($query) => $query->where('id', '!=', $persona->id))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    private function validateData(Request $request, ?Persona $persona = null): array
    {
        $liderRules = ['nullable', Rule::exists('personas', 'id')];
        if ($persona) {
            $liderRules[] = Rule::notIn([$persona->id]);
        }

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', Rule::in(Persona::TIPOS_DOCUMENTO)],
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('personas', 'numero_documento')->ignore($persona?->id),
            ],
            'fecha_nacimiento' => ['nullable', 'date'],
            'celular' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'lider_id' => $liderRules,
        ], [
            'lider_id.not_in' => 'Una persona no puede ser su propio líder de célula.',
        ]);

        $data['lider_id'] = $data['lider_id'] ?: null;

        return $data;
    }
}
