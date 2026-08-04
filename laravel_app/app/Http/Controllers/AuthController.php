<?php

namespace App\Http\Controllers;

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

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
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

        $userCount = User::count();
        $activeSessions = DB::table('sessions')->count();

        return view('dashboard', [
            'userName' => Session::get('user_name', 'Usuario'),
            'userCount' => $userCount,
            'activeSessions' => $activeSessions,
        ]);
    }
}
