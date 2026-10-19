<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

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

        $user = User::withoutGlobalScopes()->where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if (!$user->esAdmin() && (!$user->sede_id || !$user->sede()->value('activa'))) {
                return back()->withErrors([
                    'email' => $user->sede_id
                        ? 'Tu sede está inactiva; contacta al administrador.'
                        : 'Tu cuenta aún no está asociada a una sede; contacta al administrador.',
                ]);
            }

            Session::put('is_logged_in', true);
            Session::put('user_name', $user->name);
            Session::put('user_id', $user->id);

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciales incorrectas. Verifica tu correo y contraseña.',
        ]);
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

        $userCount = User::count();
        // El total de sesiones abiertas mezcla todas las sedes: solo se muestra al administrador.
        $activeSessions = $esAdmin ? DB::table('sessions')->count() : null;
        $personaCount = Persona::count();
        $liderCount = Persona::lideres()->count();

        $personasPorRed = Persona::query()
            ->selectRaw('redes.id as red_id, redes.nombre as red_grupo, sedes_red.nombre as sede_nombre, COUNT(*) as total')
            ->leftJoin('personas as lideres', 'personas.lider_id', '=', 'lideres.id')
            ->leftJoin('redes', 'redes.id', '=', DB::raw('COALESCE(personas.red_id, lideres.red_id)'))
            ->leftJoin('sedes as sedes_red', 'sedes_red.id', '=', 'redes.sede_id')
            ->groupBy('redes.id', 'redes.nombre', 'sedes_red.nombre')
            ->orderByRaw('redes.nombre IS NULL, redes.nombre')
            ->get();

        $eventos = Evento::with('inscripciones.pagos')->get();
        $eventoCount = $eventos->count();
        $totalEsperadoEventos = $eventos->sum(fn (Evento $evento) => $evento->totalEsperado());
        $totalRecaudadoEventos = $eventos->sum(fn (Evento $evento) => $evento->totalRecaudado());

        $proximosEventos = $eventos
            ->where('estado', '!=', 'cancelado')
            ->filter(fn (Evento $evento) => $evento->fecha->greaterThanOrEqualTo(today()))
            ->sortBy('fecha')
            ->take(5);

        // Bloques informativos del tablero: solo datos existentes y dentro del ámbito del usuario.
        $alcance = app(\App\Services\AlcanceService::class);
        $personasNuevas = $alcance->personasVisibles($actor)->orderByDesc('created_at')->orderByDesc('id')->limit(5)->get();
        $puedeVerSinLider = \App\Support\Permisos::puede('verSinLider');
        $sinLider = $puedeVerSinLider ? $alcance->personasVisibles($actor)->sinLider() : null;

        return view('dashboard', [
            'personasNuevas' => $personasNuevas,
            'personasSinLider' => $sinLider ? (clone $sinLider)->orderByDesc('created_at')->limit(5)->get() : null,
            'sinLiderTotal' => $sinLider ? (clone $sinLider)->count() : 0,
            'userName' => Session::get('user_name', 'Usuario'),
            'userCount' => $userCount,
            'activeSessions' => $activeSessions,
            'personaCount' => $personaCount,
            'liderCount' => $liderCount,
            'personasPorRed' => $personasPorRed,
            'eventoCount' => $eventoCount,
            'totalEsperadoEventos' => $totalEsperadoEventos,
            'totalRecaudadoEventos' => $totalRecaudadoEventos,
            'proximosEventos' => $proximosEventos,
            // Resumen por sede y totales globales rotulados (solo admin en "Todas las sedes").
            'resumenSedes' => $global ? app(\App\Services\SedeService::class)->resumen() : null,
        ]);
    }
}
