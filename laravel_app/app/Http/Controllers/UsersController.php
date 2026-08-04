<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $users = User::orderBy('created_at', 'desc')->get();

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

        return view('users.create');
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
            'role' => ['required', 'in:admin,user'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
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
            'role' => ['nullable', 'in:admin,user'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

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
}
