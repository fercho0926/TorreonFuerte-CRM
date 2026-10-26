<?php

namespace App\Http\Controllers;

use App\Models\SolicitudClave;
use App\Models\User;
use App\Services\SedeContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * «¿Olvidaste tu contraseña?»: el miembro pide una nueva clave con su correo. La respuesta es
 * siempre la misma, exista o no la cuenta; si existe, se registra una solicitud para quien
 * administra su sede (el envío por WhatsApp es asistido).
 */
class RecuperacionClaveController extends Controller
{
    public const MENSAJE = 'Si la cuenta existe, avisaremos a quien administra tu sede para que te envíe una nueva clave por WhatsApp.';

    public function __construct(private SedeContext $contexto)
    {
    }

    public function create()
    {
        if (Session::get('is_logged_in')) {
            return redirect()->route('dashboard');
        }

        return view('auth.olvide-clave');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        $correo = Str::lower((string) $request->input('email'));
        $porOrigen = 'recuperacion-origen:'.$request->ip();
        $porCuenta = 'recuperacion-cuenta:'.$correo;

        if (RateLimiter::tooManyAttempts($porOrigen, (int) config('claves.solicitudes_por_origen_hora'))
            || RateLimiter::tooManyAttempts($porCuenta, (int) config('claves.solicitudes_por_cuenta_hora'))) {
            abort(429, 'Espera unos minutos antes de volver a intentarlo.');
        }

        RateLimiter::hit($porOrigen, 3600);
        RateLimiter::hit($porCuenta, 3600);

        // Siempre se hace la misma consulta para que la respuesta no delate si la cuenta existe.
        $user = User::withoutGlobalScopes()->where('email', $correo)->first();

        if ($user && $this->habilitada($user)) {
            $this->registrarSolicitud($user, $request);
        }

        return redirect()->route('login')->with('status', self::MENSAJE);
    }

    /** Cuenta que puede recibir claves: administradores, o usuarios de una sede activa. */
    private function habilitada(User $user): bool
    {
        return $user->esAdmin() || ($user->sede_id && $user->sede()->value('activa'));
    }

    private function registrarSolicitud(User $user, Request $request): void
    {
        $registrar = function () use ($user, $request) {
            $pendiente = SolicitudClave::pendientes()->where('user_id', $user->id)->first();

            if ($pendiente) {
                $pendiente->update(['ultima_solicitud_en' => now()]);

                return;
            }

            SolicitudClave::create([
                'user_id' => $user->id,
                'sede_id' => $user->sede_id,
                'estado' => SolicitudClave::PENDIENTE,
                'ultima_solicitud_en' => now(),
                'origen_hash' => hash('sha256', $request->ip().config('app.key')),
            ]);
        };

        $user->sede_id
            ? $this->contexto->conSede((int) $user->sede_id, $registrar)
            : $this->contexto->sinFiltro($registrar);
    }
}
