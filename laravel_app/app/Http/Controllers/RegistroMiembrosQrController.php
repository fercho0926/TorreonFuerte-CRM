<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use App\Services\GeneradorQr;
use App\Services\SedeContext;
use Illuminate\Support\Facades\Gate;

/**
 * Gestión del QR de miembros de una sede: pantalla del pastor, descarga, abrir/cerrar y regenerar.
 * El administrador usa la pestaña «Registro de miembros» del detalle de sede (SedesController).
 */
class RegistroMiembrosQrController extends Controller
{
    /** Pantalla del pastor: el QR de su sede. */
    public function index(SedeContext $contexto)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('verRegistroMiembros', Sede::class);

        $sede = $contexto->sede();
        abort_unless($sede, 404);

        return response()->view('registro-miembros.index', ['sede' => $sede])
            ->header('Cache-Control', 'no-store, private');
    }

    public function svg(GeneradorQr $generador, Sede $sede)
    {
        $this->sede($sede);

        return response($generador->svg($sede->urlRegistroMiembros(), 640), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-miembros-sede-'.$sede->id.'.svg"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function abrir(Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($sede) {
            $this->sede($sede)->update(['registro_abierto' => !$sede->registro_abierto]);
            app(\App\Services\Historial::class)->registrar($sede, $sede->registro_abierto ? 'registro_abierto' : 'registro_cerrado');
        });

        return back()->with('success', $sede->registro_abierto ? 'Registro de miembros abierto.' : 'Registro de miembros cerrado.');
    }

    public function regenerar(Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($sede) {
            $this->sede($sede)->regenerarTokenRegistro();
            app(\App\Services\Historial::class)->registrar($sede, 'registro_regenerado');
        });

        return back()->with('success', 'Se generó un enlace nuevo; el anterior ya no funciona.');
    }

    private function sede(Sede $sede): Sede
    {
        $user = $this->usuarioActual();
        abort_unless($user, 403, self::MENSAJE_SIN_PERMISO);
        abort_unless(Gate::forUser($user)->allows('gestionarRegistroMiembros', $sede), 403, self::MENSAJE_SIN_PERMISO);

        return $sede;
    }
}
