<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\SedeContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Usuarios: el admin gestiona todos (con su sede); el pastor solo los de su sede, y solo cuentas
 * de líder y de usuario.
 */
class UsersController extends Controller
{
    public function __construct(private SedeContext $contexto)
    {
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('verUsuarios', User::class);

        $actor = $this->usuarioActual();

        $users = User::with(['persona', 'sede'])
            ->when($actor->esAdmin() && $request->filled('sede_id'), fn ($q) => $q->where('sede_id', $request->sede_id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('users.index', [
            'users' => $users,
            'sedes' => $actor->esAdmin() ? Sede::orderBy('nombre')->get() : collect(),
            'filtroSede' => $request->input('sede_id'),
            'enTodas' => $this->contexto->estaEnTodas(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear', User::class);

        return view('users.create', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear', User::class);

        $actor = $this->usuarioActual();
        $roles = UserPolicy::rolesAsignables($actor);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in($roles)],
            'sede_id' => [$actor->esAdmin() ? 'nullable' : 'prohibited', Rule::exists('sedes', 'id')],
            'persona_id' => ['nullable', $this->contexto->existe('personas')],
        ]);

        $crear = fn () => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'persona_id' => $data['persona_id'] ?? null,
        ]);

        if ($data['role'] === 'admin') {
            // Los administradores del sistema no pertenecen a ninguna sede.
            $this->contexto->sinFiltro($crear);
        } elseif ($actor->esAdmin()) {
            $sedeId = $data['sede_id'] ?? $this->contexto->sedeId();
            abort_unless($sedeId, 422, 'Elige la sede del usuario.');
            $this->contexto->conSede((int) $sedeId, $crear);
        } else {
            $crear();
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionar', $user);

        return view('users.edit', $this->datosFormulario() + ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionar', $user);

        $actor = $this->usuarioActual();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', Rule::in(UserPolicy::rolesAsignables($actor))],
            'persona_id' => ['nullable', $this->contexto->existe('personas')],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'] ?? $user->role;
        $user->persona_id = $data['persona_id'] ?? null;

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionar', $user);

        abort_if($user->id === $this->usuarioActual()?->id, 422, 'No puedes eliminar tu propia cuenta.');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function datosFormulario(): array
    {
        $actor = $this->usuarioActual();

        return [
            'personas' => Persona::orderBy('apellidos')->orderBy('nombres')->get(),
            'roles' => collect(User::ROLE_LABELS)->only(UserPolicy::rolesAsignables($actor)),
            'sedes' => $actor->esAdmin() ? Sede::activas()->orderBy('nombre')->get() : collect(),
            'sedeEfectivaId' => $this->contexto->sedeId(),
        ];
    }
}
