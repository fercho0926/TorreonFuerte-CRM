<?php

namespace App\Http\Controllers;

use App\Grillas\CambiosGrilla;
use App\Grillas\Grilla;
use App\Http\Controllers\Concerns\ServicioGrilla;
use Illuminate\Http\Request;

/**
 * «Control de cambios» (spec 015, historia 4): todo lo que cambió en la plataforma, solo lectura y sin exportación.
 * Por defecto solo lo abre el administrador; el resto de roles, si se les concede el módulo desde Roles.
 */
class ControlCambiosController extends Controller
{
    use ServicioGrilla;

    protected function grilla(): Grilla
    {
        return app(CambiosGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        // El historial reúne datos de toda la plataforma: no se guarda en el dispositivo.
        return response()->view('control-cambios.index', $this->datosDeLaGrilla($request))->header('Cache-Control', 'no-store, private');
    }
}
