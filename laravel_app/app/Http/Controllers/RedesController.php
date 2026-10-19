<?php

namespace App\Http\Controllers;

use App\Services\SedeContext;
use App\Models\Persona;
use App\Models\Red;
use App\MigracionRedesZonas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RedesController extends Controller
{
    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        return view('redes.index', [
            'redes' => Red::withCount('lideres')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        Red::create(['nombre' => $this->validarNombre($request), 'activa' => true]);

        return redirect()->route('redes.index')->with('success', 'Red creada correctamente.');
    }

    public function update(Request $request, Red $red)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        $red->update([
            'nombre' => $this->validarNombre($request, $red),
            'activa' => $request->boolean('activa'),
        ]);

        return redirect()->route('redes.index')->with('success', 'Red actualizada correctamente.');
    }

    public function fusionar(Request $request, Red $red)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        $data = $request->validate([
            'destino_id' => ['required', app(SedeContext::class)->existe('redes'), Rule::notIn([$red->id])],
        ]);

        Persona::where('red_id', $red->id)->update(['red_id' => $data['destino_id']]);
        $red->delete();

        return redirect()->route('redes.index')->with('success', 'Las redes se fusionaron; los líderes pasaron a la red elegida.');
    }

    public function destroy(Red $red)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        abort_if($red->lideres()->exists(), 422, 'La red tiene líderes asignados. Desactívala o fusiónala con otra en lugar de eliminarla.');

        $red->delete();

        return redirect()->route('redes.index')->with('success', 'Red eliminada correctamente.');
    }

    private function validarNombre(Request $request, ?Red $actual = null): string
    {
        $nombre = trim((string) $request->validate(['nombre' => ['required', 'string', 'max:255']])['nombre']);

        $existente = Red::where('clave', \App\Support\MigracionRedesZonas::clave($nombre))
            ->when($actual, fn ($q) => $q->whereKeyNot($actual->id))
            ->first();

        if ($existente) {
            throw ValidationException::withMessages([
                'nombre' => "Ya existe la red \"{$existente->nombre}\". Usa esa en lugar de crear una duplicada.",
            ]);
        }

        return $nombre;
    }
}
