<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LideresController extends Controller
{
    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $visiblesIds = $this->personasVisiblesIds();

        $lideres = Persona::lideres()
            ->when($visiblesIds !== null, fn ($query) => $query->whereIn('id', $visiblesIds))
            ->withCount('miembros')
            ->orderBy('red')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $user = $this->usuarioActual();

        return view('lideres.index', [
            'lideres' => $lideres,
            'puedeGestionarEstructura' => $user && ($user->tieneAccesoTotal() || $user->isLiderZona()),
        ]);
    }

    public function show(Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($lider->es_lider, 404);

        if ($redirect = $this->ensurePuedeVerPersona($lider)) {
            return $redirect;
        }

        $miembros = $lider->miembros()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('lideres.show', [
            'lider' => $lider,
            'miembros' => $miembros,
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

        return view('lideres.create');
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
        $data['es_lider'] = true;

        Persona::create($data);

        return redirect()->route('lideres.index')->with('success', 'Líder creado correctamente.');
    }

    public function edit(Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($lider->es_lider, 404);

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        return view('lideres.edit', [
            'lider' => $lider,
        ]);
    }

    public function update(Request $request, Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($lider->es_lider, 404);

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        $data = $this->validateData($request, $lider);

        $lider->update($data);

        return redirect()->route('lideres.index')->with('success', 'Líder actualizado correctamente.');
    }

    public function destroy(Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($lider->es_lider, 404);

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        // Un líder es un miembro con el rol de líder: al quitarle el rol no se borra
        // a la persona, solo deja de ser líder de red y sus miembros quedan sin líder asignado.
        $lider->miembros()->update(['lider_id' => null]);
        $lider->update(['es_lider' => false, 'red' => null]);

        return redirect()->route('lideres.index')->with('success', 'Se quitó el rol de líder a esta persona.');
    }

    private function ensurePuedeVerPersona(Persona $persona)
    {
        $visiblesIds = $this->personasVisiblesIds();

        if ($visiblesIds !== null && !in_array($persona->id, $visiblesIds, true)) {
            return redirect()->route('lideres.index')->with('error', 'No tienes acceso a este líder.');
        }

        return null;
    }

    private function validateData(Request $request, ?Persona $lider = null): array
    {
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', Rule::in(Persona::TIPOS_DOCUMENTO)],
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('personas', 'numero_documento')->ignore($lider?->id),
            ],
            'fecha_nacimiento' => ['nullable', 'date'],
            'celular' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'red' => ['required', 'string', 'max:255'],
        ]);

        return $data;
    }
}
