<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\UsuariosGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Http\Controllers\Concerns\MuestraClave;
use App\Models\Persona;
use App\Models\RegistroClave;
use App\Models\Sede;
use App\Models\User;
use App\Http\Requests\PersonaRequest;
use App\Policies\UserPolicy;
use App\Services\CuentasDeMiembros;
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
    use PestanaHistorico, ServicioGrilla;

    use MuestraClave;

    public function __construct(private SedeContext $contexto, private CuentasDeMiembros $cuentas)
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

    /**
     * «Nuevo usuario» en dos pasos (spec 016): primero el celular; con él se busca al miembro y se
     * muestra la tarjeta del encontrado, la lista para elegir, o los datos para registrarlo.
     */
    public function create(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear', User::class);

        $actor = $this->usuarioActual();
        $celular = trim((string) $request->query('celular', ''));
        $sedeId = $actor->esAdmin() ? ((int) $request->query('sede_id') ?: $this->contexto->sedeId()) : null;
        $busqueda = null;

        if ($celular !== '') {
            if ($actor->esAdmin() && !$sedeId) {
                return redirect()->route('users.create')->withInput()->withErrors(['sede_id' => 'Elige la sede del miembro.']);
            }

            $busqueda = $this->cuentas->buscarPorCelular($celular, $actor, $sedeId);

            if ($busqueda['estado'] === CuentasDeMiembros::INVALIDO) {
                return redirect()->route('users.create', $sedeId ? ['sede_id' => $sedeId] : [])->withInput()
                    ->withErrors(['celular' => 'Escribe un celular válido de WhatsApp (10 dígitos, por ejemplo 310 555 9999).']);
            }
        }

        return view('users.create', $this->datosFormulario() + [
            'celular' => $celular,
            'sedeElegidaId' => $sedeId,
            'busqueda' => $busqueda,
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear', User::class);

        $actor = $this->usuarioActual();
        $vinculaExistente = filled($request->input('persona_id'));

        $data = $request->validate([
            'celular' => ['required', 'string', 'max:20', Celular::regla()],
            'persona_id' => ['nullable', 'integer'],
            'miembro' => [$vinculaExistente ? 'prohibited' : 'required', 'array'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(UserPolicy::rolesAsignables($actor))],
            // El administrador elige la sede del miembro; los demás usan su sede (spec 016, RF-012a).
            'sede_id' => [$actor->esAdmin() ? 'required' : 'prohibited', Rule::exists('sedes', 'id')],
        ] + ($vinculaExistente ? [] : PersonaRequest::reglasDeAlta('miembro.')), [
            'miembro.required' => 'Completa los datos del miembro.',
            'sede_id.required' => 'Elige la sede del miembro.',
        ]);

        $persona = $vinculaExistente
            ? Persona::withoutGlobalScopes()->find($data['persona_id'])
            : null;

        if ($vinculaExistente && !$persona) {
            throw \Illuminate\Validation\ValidationException::withMessages(['persona_id' => 'El miembro elegido no existe.']);
        }

        $user = $this->cuentas->crear(
            ['celular' => $data['celular'], 'email' => $data['email'], 'role' => $data['role']],
            $persona,
            $data['miembro'] ?? null,
            $actor,
            isset($data['sede_id']) ? (int) $data['sede_id'] : null,
        );

        // La clave se muestra en esta misma respuesta (sin redirección ni sesión): ver research D4.
        return $this->mostrarClave($user, RegistroClave::MOTIVO_ALTA, $actor);
    }

    public function edit(Request $request, User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionar', $user);

        $pestana = $this->pestanaActiva($request);

        // Vínculo dudoso de la unificación (spec 016): solo el administrador elige el miembro correcto.
        $candidatos = $this->usuarioActual()->esAdmin() && $user->revision_vinculo
            ? Persona::withoutGlobalScopes()->with('sede')->whereKey($user->revision_vinculo['candidatos'] ?? [])->whereNull('deleted_at')->orderBy('apellidos')->get()
            : collect();

        return view('users.edit', $this->datosFormulario() + [
            'user' => $user,
            'pestana' => $pestana,
            'historico' => $pestana === 'historico' ? $this->historicoDeRegistro($request, $user, route('users.edit', $user)) : null,
            'candidatos' => $candidatos,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionar', $user);

        $actor = $this->usuarioActual();

        // Solo lo propio de la cuenta: nombre y celular son del miembro y se editan en su ficha (spec 016).
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['nullable', Rule::in(UserPolicy::rolesAsignables($actor))],
        ]);

        // Nunca puede quedar el sistema sin un usuario administrador.
        if ($user->esAdmin() && ($data['role'] ?? $user->role) !== $user->role && $this->esElUnicoAdministrador($user)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['role' => 'No se puede cambiar el rol del único administrador del sistema.']);
        }

        $user->email = $data['email'];
        $user->role = $data['role'] ?? $user->role;

        // Las contraseñas de otros miembros no se escriben: se generan claves temporales (spec 005).
        $user->save();

        $respuesta = redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');

        if ($actor->esAdmin() && $user->revision_vinculo && $request->filled('vincular_a')) {
            $elegido = Persona::withoutGlobalScopes()->whereNull('deleted_at')->find((int) $request->input('vincular_a'));
            if (!$elegido) {
                throw \Illuminate\Validation\ValidationException::withMessages(['vincular_a' => 'Elige uno de los miembros propuestos.']);
            }

            if ($this->cuentas->resolverRevision($user, $elegido, $actor)) {
                $respuesta->with('warning', 'La cuenta quedó vinculada. El miembro creado durante la unificación tiene datos propios y se conservó; revísalo en Miembros.');
            }
        }

        return $respuesta;
    }

    public function destroy(User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionar', $user);

        abort_if($user->id === $this->usuarioActual()?->id, 422, 'No puedes eliminar tu propia cuenta.');
        abort_if($user->esAdmin() && $this->esElUnicoAdministrador($user), 422, 'No se puede eliminar al único administrador del sistema.');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function esElUnicoAdministrador(User $user): bool
    {
        return User::withoutGlobalScopes()->where('role', \App\Models\Rol::CLAVE_ADMIN)->where('id', '!=', $user->id)->doesntExist();
    }

    /**
     * @return array<string, mixed>
     */
    private function datosFormulario(): array
    {
        $actor = $this->usuarioActual();

        return [
            'roles' => \App\Models\Rol::whereIn('clave', UserPolicy::rolesAsignables($actor))->orderBy('nombre')->pluck('nombre', 'clave'),
            'sedes' => $actor->esAdmin() ? Sede::activas()->orderBy('nombre')->get() : collect(),
            'sedeEfectivaId' => $this->contexto->sedeId(),
        ];
    }
}
