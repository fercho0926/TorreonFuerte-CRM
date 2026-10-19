<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Quien entró con una clave temporal solo puede elegir su contraseña (o cerrar sesión).
 * Si la clave temporal ya venció, se cierra la sesión.
 */
class ExigeCambioDeClave
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Controller::usuarioDeLaPeticion();

        if ($user && $user->debe_cambiar_clave && !$request->routeIs('cambiar-clave.*', 'logout')) {
            if ($user->claveTemporalVencida()) {
                Session::flush();

                return redirect()->route('login')->withErrors(['email' => 'Tu clave temporal venció; pide una nueva.']);
            }

            return redirect()->route('cambiar-clave.edit');
        }

        return $next($request);
    }
}
