<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensureIsAdmin()) {
            return $redirect;
        }

        $users = User::with('persona')->orderBy('created_at', 'desc')->get();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensureIsAdmin()) {
            return $redirect;
        }

        return view('users.create', [
            'personas' => $this->personasDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensureIsAdmin()) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
            'persona_id' => ['nullable', Rule::exists('personas', 'id')],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'persona_id' => $data['persona_id'] ?: null,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensureIsAdmin()) {
            return $redirect;
        }

        return view('users.edit', [
            'user' => $user,
            'personas' => $this->personasDisponibles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensureIsAdmin()) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', Rule::in(User::ROLES)],
            'persona_id' => ['nullable', Rule::exists('personas', 'id')],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'] ?? $user->role;
        $user->persona_id = $data['persona_id'] ?: null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensureIsAdmin()) {
            return $redirect;
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function personasDisponibles()
    {
        return Persona::orderBy('apellidos')->orderBy('nombres')->get();
    }
}
