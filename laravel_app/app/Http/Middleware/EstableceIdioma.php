<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica el idioma del usuario autenticado (o el predeterminado) a cada petición web. La
 * aplicación no usa el Auth de Laravel: el usuario de la sesión se resuelve igual que en el
 * resto del proyecto, con Controller::usuarioDeLaPeticion().
 */
class EstableceIdioma
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Controller::usuarioDeLaPeticion();

        App::setLocale($usuario->idioma ?? config('app.locale'));

        return $next($request);
    }
}
