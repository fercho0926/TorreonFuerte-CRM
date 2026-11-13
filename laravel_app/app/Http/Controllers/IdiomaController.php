<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * El usuario elige el idioma de la interfaz (español o inglés); se guarda en su perfil (spec 020).
 */
class IdiomaController extends Controller
{
    public function update(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $request->validate([
            'idioma' => ['required', Rule::in(['es', 'en'])],
        ]);

        $this->usuarioActual()->update(['idioma' => $data['idioma']]);

        return redirect()->back();
    }
}
