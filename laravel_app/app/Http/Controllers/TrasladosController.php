<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Sede;
use App\Services\TrasladoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Traslado de personas entre sedes: solo administrador (con una sede activa).
 */
class TrasladosController extends Controller
{
    public function __construct(private TrasladoService $traslados)
    {
    }

    public function create(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('trasladar', Sede::class);

        return view('traslados.create', [
            'persona' => $persona,
            'pendientes' => $this->traslados->pendientes($persona),
            'sedes' => Sede::activas()->whereKeyNot($persona->sede_id)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('trasladar', Sede::class);

        $data = $request->validate([
            'sede_destino_id' => ['required', Rule::exists('sedes', 'id')],
        ]);

        abort_if($this->traslados->hayPendientes($persona), 422, $this->traslados->mensajePendientes($persona));

        $destino = Sede::findOrFail($data['sede_destino_id']);
        $this->traslados->trasladar($persona, $destino, $this->usuarioActual());

        return redirect()->route('personas.show', $persona)
            ->with('success', "{$persona->nombre_completo} fue trasladada a {$destino->nombre}. Su registro aquí quedó inactivo con su historial; en la nueva sede aparece sin líder, célula ni ministerios.");
    }
}
