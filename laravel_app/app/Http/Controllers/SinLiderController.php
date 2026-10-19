<?php

namespace App\Http\Controllers;

use App\Services\SedeContext;
use App\Models\Persona;
use App\Services\LiderazgoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SinLiderController extends Controller
{
    public function __construct(private LiderazgoService $liderazgo)
    {
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('verSinLider');

        $user = $this->usuarioActual();

        $personas = $this->alcance()->personasVisibles($user)
            ->sinLider()
            ->when($request->filled('q'), fn ($query) => $query->buscar($request->q))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(25)
            ->withQueryString();

        return view('sin-lider.index', [
            'personas' => $personas,
            'lideres' => $this->alcance()->lideresVisibles($user)->orderBy('apellidos')->orderBy('nombres')->get(),
            'q' => $request->input('q'),
        ]);
    }

    public function asignar(Request $request, Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('verSinLider');

        $user = $this->usuarioActual();
        abort_unless($this->alcance()->puedeVerPersona($user, $persona), 404);

        $data = $request->validate([
            'lider_id' => ['required', app(SedeContext::class)->existe('personas')->where('es_lider', true)],
        ]);

        $lider = $this->alcance()->lideresVisibles($user)->whereKey($data['lider_id'])->first();
        abort_unless($lider, 403, self::MENSAJE_SIN_PERMISO);

        $this->liderazgo->cambiarLider($persona, $lider, $user);

        return redirect()->route('sin-lider.index')->with('success', "{$persona->nombre_completo} quedó a cargo de {$lider->nombre_completo}.");
    }
}
