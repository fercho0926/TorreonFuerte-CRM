<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Persona;
use App\Models\User;
use App\Services\ClavesTemporales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Session::get('is_logged_in')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Límite de intentos fallidos por correo y origen (spec 005, research D6).
        $limite = 'login:'.Str::lower($request->email).'|'.$request->ip();
        $maximo = (int) config('claves.login_fallos_por_minuto');

        if (RateLimiter::tooManyAttempts($limite, $maximo)) {
            return back()->withErrors(['email' => 'Demasiados intentos; espera un minuto antes de volver a intentarlo.']);
        }

        $user = User::withoutGlobalScopes()->where('email', $request->email)->first();

        if ($user && $this->claveCorrecta($user, (string) $request->password)) {
            if ($user->claveTemporalVencida()) {
                return back()->withErrors(['email' => 'Tu clave temporal venció; pide una nueva.']);
            }

            if (!$user->esAdmin() && (!$user->sede_id || !$user->sede()->value('activa'))) {
                return back()->withErrors([
                    'email' => $user->sede_id
                        ? 'Tu sede está inactiva; contacta al administrador.'
                        : 'Tu cuenta aún no está asociada a una sede; contacta al administrador.',
                ]);
            }

            RateLimiter::clear($limite);
            Session::regenerate();
            Session::put('is_logged_in', true);
            Session::put('user_name', $user->name);
            Session::put('user_id', $user->id);

            // Con clave temporal, el middleware lleva a «Elige tu contraseña».
            return redirect()->route('dashboard');
        }

        RateLimiter::hit($limite, 60);

        return back()->withErrors([
            'email' => 'Credenciales incorrectas. Verifica tu correo y contraseña.',
        ]);
    }

    /**
     * Valida la contraseña. Con una clave temporal vigente también se acepta escrita sin guion y
     * en minúsculas (ABCDE-FGHJK = abcdefghjk).
     */
    private function claveCorrecta(User $user, string $clave): bool
    {
        if (Hash::check($clave, $user->password)) {
            return true;
        }

        return $user->debe_cambiar_clave
            && Hash::check(app(ClavesTemporales::class)->normalizar($clave), $user->password);
    }

    public function logout()
    {
        Session::flush();

        return redirect()->route('login');
    }

    public function dashboard()
    {
        if (!Session::get('is_logged_in')) {
            return redirect()->route('login');
        }

        $actor = $this->usuarioActual();
        $esAdmin = (bool) $actor?->esAdmin();
        $contexto = app(\App\Services\SedeContext::class);
        $global = $esAdmin && $contexto->estaEnTodas();

        $alcance = app(\App\Services\AlcanceService::class);
        $lee = fn (string $modulo) => $actor->nivelEn($modulo) >= \App\Support\Niveles::LECTURA;

        // Cada total del tablero depende del permiso del módulo al que pertenece y se calcula dentro del alcance del rol.
        $userCount = $lee(\App\Support\Modulos::USUARIOS) ? User::count() : 0;
        // El total de sesiones abiertas mezcla todas las sedes: solo se muestra al administrador.
        $activeSessions = $esAdmin ? DB::table('sessions')->count() : null;
        $personaCount = $alcance->personasVisibles($actor)->count();
        $liderCount = $alcance->lideresVisibles($actor)->count();

        $eventos = $lee(\App\Support\Modulos::EVENTOS) ? Evento::with('inscripciones.pagos')->get() : collect();
        $eventoCount = $eventos->count();
        $totalEsperadoEventos = $eventos->sum(fn (Evento $evento) => $evento->totalEsperado());
        $totalRecaudadoEventos = $eventos->sum(fn (Evento $evento) => $evento->totalRecaudado());

        $proximosEventos = $eventos
            ->where('estado', '!=', 'cancelado')
            ->filter(fn (Evento $evento) => $evento->fecha->greaterThanOrEqualTo(today()))
            ->sortBy('fecha')
            ->take(5);

        // Bloques informativos del tablero: solo datos existentes y dentro del ámbito del usuario.
        $personasNuevas = $lee(\App\Support\Modulos::MIEMBROS)
            ? $alcance->personasVisibles($actor)->orderByDesc('created_at')->orderByDesc('id')->limit(5)->get()
            : collect();
        $puedeVerSinLider = \App\Support\Permisos::puede('verSinLider');
        $sinLider = $puedeVerSinLider ? $alcance->personasVisibles($actor)->sinLider() : null;

        // Gráficas y metas del tablero (diseño «Dashboard v2» de AdminLTE).
        $tablero = app(\App\Services\TableroService::class);
        $leeMiembros = $lee(\App\Support\Modulos::MIEMBROS);
        $porEstado = $leeMiembros ? $tablero->porEstado($actor) : [];

        return view('dashboard', [
            'mensual' => $leeMiembros ? $tablero->mensual($actor, $lee(\App\Support\Modulos::EVENTOS)) : null,
            'porEstado' => $porEstado,
            'celulaCount' => $lee(\App\Support\Modulos::CELULAS) ? $alcance->celulasVisibles($actor)->count() : null,
            'personasNuevas' => $personasNuevas,
            'personasSinLider' => $sinLider ? (clone $sinLider)->orderByDesc('created_at')->limit(5)->get() : null,
            'sinLiderTotal' => $sinLider ? (clone $sinLider)->count() : 0,
            'userName' => Session::get('user_name', 'Usuario'),
            'userCount' => $userCount,
            'activeSessions' => $activeSessions,
            'personaCount' => $personaCount,
            'liderCount' => $liderCount,
            'eventoCount' => $eventoCount,
            'totalEsperadoEventos' => $totalEsperadoEventos,
            'totalRecaudadoEventos' => $totalRecaudadoEventos,
            'proximosEventos' => $proximosEventos,
            // Resumen por sede y totales globales rotulados (solo admin en "Todas las sedes").
            'resumenNiveles' => $lee(\App\Support\Modulos::NIVELES) ? app(\App\Services\NivelesService::class)->resumen($actor) : null,
            'resumenSedes' => $global ? app(\App\Services\SedeService::class)->resumen() : null,
        ]);
    }
}
