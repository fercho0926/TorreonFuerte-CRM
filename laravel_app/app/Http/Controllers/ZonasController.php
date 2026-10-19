<?php

namespace App\Http\Controllers;

use App\Models\Zona;
use App\Support\MigracionRedesZonas;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ZonasController extends Controller
{
    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        return view('zonas.index', [
            'zonas' => Zona::withCount('lideres')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        Zona::create(['nombre' => $this->validarNombre($request), 'activa' => true]);

        return redirect()->route('zonas.index')->with('success', 'Zona creada correctamente.');
    }

    public function update(Request $request, Zona $zona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        $zona->update(['nombre' => $this->validarNombre($request, $zona), 'activa' => $request->boolean('activa')]);

        return redirect()->route('zonas.index')->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy(Zona $zona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarCatalogos');

        abort_if($zona->lideres()->exists(), 422, 'La zona tiene líderes asignados. Desactívala o muévelos a otra zona antes de eliminarla.');

        $zona->delete();

        return redirect()->route('zonas.index')->with('success', 'Zona eliminada correctamente.');
    }

    private function validarNombre(Request $request, ?Zona $actual = null): string
    {
        $nombre = trim($request->validate(['nombre' => ['required', 'string', 'max:255']])['nombre']);

        $existente = Zona::where('clave', MigracionRedesZonas::clave($nombre))
            ->when($actual, fn ($q) => $q->whereKeyNot($actual->id))
            ->first();

        if ($existente) {
            throw ValidationException::withMessages(['nombre' => "Ya existe la zona \"{$existente->nombre}\"."]);
        }

        return $nombre;
    }
}
