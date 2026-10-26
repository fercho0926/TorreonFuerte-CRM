<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Services\AutorizacionRoles;
use App\Support\Modulos;
use App\Support\Niveles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Red de seguridad por módulo (spec 013): `modulo:{clave}[,lectura|escritura]`.
 *
 * Sin nivel explícito, GET/HEAD exigen lectura y los demás métodos exigen escritura; además, las pantallas de
 * alta o cambio (`*.create`, `*.edit`, `lideres.designar`, `lideres.quitar`) exigen escritura aunque sean GET.
 * Las reglas finas por registro siguen en las políticas.
 */
class AutorizaModulo
{
    /** Pantallas GET que preparan un cambio. */
    private const PANTALLAS_DE_CAMBIO = ['*.create', '*.edit', 'lideres.designar', 'lideres.quitar'];

    public function __construct(private AutorizacionRoles $autorizacion)
    {
    }

    public function handle(Request $request, Closure $next, string $modulo, ?string $nivel = null): Response
    {
        if (!Session::get('is_logged_in')) {
            return $request->expectsJson()
                ? response()->json(['mensaje' => 'Inicia sesión'], 401)
                : redirect()->route('login');
        }

        abort_unless(Modulos::existe($modulo), 500, 'Módulo desconocido: '.$modulo);

        $exigido = $nivel !== null ? Niveles::aNumero($nivel) : $this->nivelExigido($request);

        if ($this->autorizacion->nivel(Controller::usuarioDeLaPeticion(), $modulo) < $exigido) {
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['mensaje' => Controller::MENSAJE_SIN_PERMISO], 403)
                : abort(403, Controller::MENSAJE_SIN_PERMISO);
        }

        return $next($request);
    }

    private function nivelExigido(Request $request): int
    {
        if (!$request->isMethodSafe()) {
            return Niveles::ESCRITURA;
        }

        $nombre = (string) $request->route()?->getName();
        foreach (self::PANTALLAS_DE_CAMBIO as $patron) {
            if (fnmatch($patron, $nombre)) {
                return Niveles::ESCRITURA;
            }
        }

        return Niveles::LECTURA;
    }
}
