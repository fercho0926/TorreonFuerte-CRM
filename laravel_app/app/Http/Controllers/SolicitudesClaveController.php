<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\SolicitudesClaveGrilla;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Http\Controllers\Concerns\MuestraClave;
use App\Models\RegistroClave;
use App\Models\SolicitudClave;
use Illuminate\Http\Request;

/**
 * Panel de solicitudes de clave: administradores y pastores atienden las de su alcance.
 */
class SolicitudesClaveController extends Controller
{
    use ServicioGrilla;

    use MuestraClave;

    private const FILTROS = ['pendientes' => 'Pendientes', 'vencidas' => 'Vencidas', 'atendidas' => 'Atendidas', 'descartadas' => 'Descartadas', 'todas' => 'Todas'];

    protected function grilla(): Grilla
    {
        return app(SolicitudesClaveGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('verSolicitudesClave', \App\Models\User::class);

        return view('claves.solicitudes', $this->datosDeLaGrilla($request));
    }

    /** Genera una clave para la cuenta de la solicitud y la muestra una sola vez. */
    public function atender(SolicitudClave $solicitud)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarClaves', $solicitud->user);

        if ($solicitud->estado !== SolicitudClave::PENDIENTE) {
            return redirect()->route('solicitudes-clave.index')->with('error', 'Esta solicitud ya fue atendida o descartada.');
        }

        $actor = $this->usuarioActual();
        $respuesta = $this->mostrarClave($solicitud->user, RegistroClave::MOTIVO_RECUPERACION, $actor, $solicitud, null);

        // Si no hubo clave (la cuenta no tiene celular) la solicitud sigue pendiente.
        if (!$respuesta instanceof \Illuminate\Http\RedirectResponse) {
            app(\App\Services\Historial::class)->accion('atendida', fn () => $solicitud->update(['estado' => SolicitudClave::ATENDIDA, 'atendida_por' => $actor->id, 'atendida_en' => now()]));
        }

        return $respuesta;
    }

    public function descartar(SolicitudClave $solicitud)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarClaves', $solicitud->user);

        if ($solicitud->estado === SolicitudClave::PENDIENTE) {
            app(\App\Services\Historial::class)->accion('descartada', fn () => $solicitud->update(['estado' => SolicitudClave::DESCARTADA, 'atendida_por' => $this->usuarioActual()->id, 'atendida_en' => now()]));
        }

        return redirect()->route('solicitudes-clave.index')->with('success', 'Solicitud descartada.');
    }
}
