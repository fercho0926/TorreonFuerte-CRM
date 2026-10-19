<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

/**
 * El administrador elige en qué sede trabaja (o "Todas las sedes", solo para consultar).
 */
class SedeActivaController extends Controller
{
    public function update(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless($this->usuarioActual()?->esAdmin(), 403, self::MENSAJE_SIN_PERMISO);

        $data = $request->validate([
            'sede_id' => ['nullable', Rule::exists('sedes', 'id')],
        ]);

        if (!empty($data['sede_id'])) {
            Session::put('sede_activa_id', (int) $data['sede_id']);
            $mensaje = 'Trabajando en la sede '.Sede::find($data['sede_id'])->nombre.'.';
        } else {
            Session::forget('sede_activa_id');
            $mensaje = 'Viendo todas las sedes (solo consulta).';
        }

        return redirect()->back()->with('success', $mensaje);
    }
}
