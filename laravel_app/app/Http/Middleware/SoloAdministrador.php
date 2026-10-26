<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rutas reservadas al administrador (Sedes, Roles, traslados, selector de sede): ningún nivel de ningún
 * rol las concede (spec 013, research D4).
 */
class SoloAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::get('is_logged_in')) {
            return $request->expectsJson()
                ? response()->json(['mensaje' => 'Inicia sesión'], 401)
                : redirect()->route('login');
        }

        if (!Controller::usuarioDeLaPeticion()?->esAdmin()) {
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['mensaje' => Controller::MENSAJE_SIN_PERMISO], 403)
                : abort(403, Controller::MENSAJE_SIN_PERMISO);
        }

        return $next($request);
    }
}
