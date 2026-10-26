<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\SedesGrilla;
use App\Grillas\UsuariosGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Http\Requests\SedeRequest;
use App\Models\Sede;
use App\Models\User;
use App\Services\OrganigramaService;
use App\Services\SedeContext;
use App\Services\SedeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Módulo exclusivo del administrador del sistema.
 */
class SedesController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    public function __construct(private SedeService $sedes, private SedeContext $contexto, private OrganigramaService $organigrama)
    {
    }

    protected function grilla(): Grilla
    {
        return app(SedesGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('ver', Sede::class);

        return view('sedes.index', $this->datosDeLaGrilla($request));
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear', Sede::class);

        return view('sedes.create');
    }

    public function store(SedeRequest $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear', Sede::class);

        $sede = $this->sedes->crear($request->validated(), $this->usuarioActual());

        return redirect()->route('sedes.show', $sede)->with('success', 'Sede creada correctamente con su pastor'.($sede->pastora_persona_id ? ' y su pastora.' : '.'));
    }

    public function show(Request $request, Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('ver', $sede);

        $sede->load(['pastor', 'pastora']);

        $pestana = $this->pestanaActiva($request, ['organigrama', 'registro']);

        $respuesta = response()->view('sedes.show', [
            'sede' => $sede,
            'pestana' => $pestana,
            'historico' => $pestana === 'historico' ? $this->historicoDeRegistro($request, $sede, route('sedes.show', $sede)) : null,
            'resumen' => $this->sedes->resumen()->get($sede->id),
            'usuarios' => $pestana === 'datos' ? UsuariosGrilla::ordenarPorMiembro(User::withoutGlobalScopes()->where('sede_id', $sede->id))->get() : collect(),
            'sedeActivaId' => $this->contexto->sedeId(),
            'organigrama' => $pestana === 'organigrama' ? $this->organigrama->construir($sede, $this->usuarioActual()) : null,
        ]);

        // El organigrama y el registro por QR listan personas: no se guardan en el dispositivo.
        return $pestana !== 'datos' ? $respuesta->header('Cache-Control', 'no-store, private') : $respuesta;
    }

    public function edit(Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('editar', $sede);

        return view('sedes.edit', ['sede' => $sede]);
    }

    public function update(SedeRequest $request, Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('editar', $sede);

        $this->sedes->editar($sede, $request->validated(), $this->usuarioActual());

        return redirect()->route('sedes.show', $sede)->with('success', 'Sede actualizada correctamente.');
    }

    public function pastores(Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('editar', $sede);

        return view('sedes.pastores', [
            'sede' => $sede->load(['pastor', 'pastora']),
            'cuentas' => UsuariosGrilla::ordenarPorMiembro(User::withoutGlobalScopes()->where('sede_id', $sede->id))->get(),
        ]);
    }

    public function updatePastores(Request $request, Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('editar', $sede);

        $data = $request->validate([
            'rol' => ['required', Rule::in(['pastor', 'pastora'])],
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'nombres' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'apellidos' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'email' => ['required_without:user_id', 'nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required_without:user_id', 'nullable', 'string', 'min:8'],
            'celular' => ['nullable', 'string', 'max:20'],
        ]);

        $rol = $data['rol'];
        unset($data['rol']);

        $this->sedes->cambiarPastor($sede, $rol, $data, $this->usuarioActual());

        return redirect()->route('sedes.show', $sede)->with('success', $rol === 'pastora' ? 'Pastora actualizada.' : 'Pastor actualizado.');
    }

    public function desactivar(Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('desactivar', $sede);

        $this->sedes->desactivar($sede, $this->usuarioActual());

        return redirect()->route('sedes.show', $sede)->with('success', 'Sede desactivada. Sus datos se conservan.');
    }

    public function reactivar(Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('desactivar', $sede);

        $this->sedes->reactivar($sede, $this->usuarioActual());

        return redirect()->route('sedes.show', $sede)->with('success', 'Sede reactivada.');
    }

    public function destroy(Sede $sede)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('eliminar', $sede);

        try {
            $this->sedes->eliminar($sede);
        } catch (ValidationException $e) {
            abort(422, 'Solo se pueden desactivar sedes con datos. Desactívala en lugar de eliminarla.');
        }

        return redirect()->route('sedes.index')->with('success', 'Sede eliminada.');
    }
}
