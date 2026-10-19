<?php

namespace App\Http\Middleware;

use App\Models\Sede;
use App\Services\SedeContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida la sede del usuario en cada petición y bloquea las escrituras cuando no hay una
 * sede activa (admin en "Todas las sedes") o la sede está inactiva.
 */
class ResuelveSede
{
    /** Rutas (por nombre) que pueden escribir aunque no haya sede activa. */
    private const EXENTAS = ['login.post', 'logout', 'sede-activa.update', 'sedes.*', 'users.*'];

    public function __construct(private SedeContext $contexto)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->contexto->iniciarPeticionWeb();

        $user = $this->contexto->usuario();

        if ($user && !$user->esAdmin()) {
            $sede = $user->sede_id ? Sede::find($user->sede_id) : null;

            if (!$sede || !$sede->activa) {
                Session::flush();

                return redirect()->route('login')->withErrors([
                    'email' => $sede
                        ? 'Tu sede está inactiva; contacta al administrador.'
                        : 'Tu cuenta aún no está asociada a una sede; contacta al administrador.',
                ]);
            }
        }

        if ($user && $user->esAdmin() && ($id = Session::get('sede_activa_id')) && !Sede::whereKey($id)->exists()) {
            Session::forget('sede_activa_id');
        }

        if ($user && !$request->isMethodSafe() && !$request->routeIs(...self::EXENTAS)) {
            $this->bloquearEscrituraSinSede();
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->contexto->terminarPeticionWeb();
    }

    private function bloquearEscrituraSinSede(): void
    {
        $modo = $this->contexto->modo();

        if ($modo['tipo'] !== SedeContext::MODO_SEDE) {
            abort(403, 'Elige una sede activa para crear o editar');
        }

        abort_unless($this->contexto->puedeEscribir(), 403, 'La sede está inactiva');
    }
}
