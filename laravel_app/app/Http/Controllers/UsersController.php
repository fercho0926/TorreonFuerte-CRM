<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\UsuariosGrilla;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Http\Controllers\Concerns\MuestraClave;
use App\Models\Persona;
use App\Models\RegistroClave;
use App\Models\Sede;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\SedeContext;
use App\Support\Celular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Usuarios: el admin gestiona todos (con su sede); el pastor solo los de su sede, y solo cuentas
 * de líder y de usuario.
 */
class UsersController extends Controller
{
    use ServicioGrilla;

    use MuestraClave;

    public function __construct(private SedeContext $contexto)
    {
    }

    protected function grilla(): Grilla
    {
        return app(UsuariosGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('verUsuarios', User::class);

        return view('users.index', $this->datosDeLaGrilla($request) + [
            'sedes' => $this->usuarioActual()->esAdmin() ? Sede::orderBy('nombre')->get() : collect(),
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

        // Si no se escribió celular, se propone el de la persona vinculada.
        if (!filled($request->input('celular')) && filled($request->input('persona_id'))) {
            $request->merge(['celular' => Persona::find($request->input('persona_id'))?->celular]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'celular' => ['required', 'string', 'max:20', Celular::regla()],
            'role' => ['required', Rule::in($roles)],
            'sede_id' => [$actor->esAdmin() ? 'nullable' : 'prohibited', Rule::exists('sedes', 'id')],
            'persona_id' => ['nullable', $this->contexto->existe('personas')],
        ]);

        // La cuenta nace con una contraseña aleatoria que nadie conoce; enseguida se reemplaza por
        // la clave temporal que se entrega por WhatsApp.
        $crear = fn () => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'celular' => $data['celular'],
            'password' => Str::random(40),
            'role' => $data['role'],
            'persona_id' => $data['persona_id'] ?? null,
        ]);

        if ($data['role'] === 'admin') {
            // Los administradores del sistema no pertenecen a ninguna sede.
            $user = $this->contexto->sinFiltro($crear);
        } elseif ($actor->esAdmin()) {
            $sedeId = $data['sede_id'] ?? $this->contexto->sedeId();
            abort_unless($sedeId, 422, 'Elige la sede del usuario.');
            $user = $this->contexto->conSede((int) $sedeId, $crear);
        } else {
            $user = $crear();
        }

        // La clave se muestra en esta misma respuesta (sin redirección ni sesión): ver research D4.
        return $this->mostrarClave($user, RegistroClave::MOTIVO_ALTA, $actor);
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
            'celular' => ['nullable', 'string', 'max:20', Celular::regla()],
            'role' => ['nullable', Rule::in(UserPolicy::rolesAsignables($actor))],
            'persona_id' => ['nullable', $this->contexto->existe('personas')],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->celular = $data['celular'] ?? null;
        $user->role = $data['role'] ?? $user->role;
        $user->persona_id = $data['persona_id'] ?? null;

        // Las contraseñas de otras personas no se escriben: se generan claves temporales (spec 005).
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
