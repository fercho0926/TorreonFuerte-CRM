<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\RolesGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Http\Requests\RolRequest;
use App\Models\Rol;
use App\Services\RolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Pestaña «Roles» del módulo Usuarios (spec 013). Es del administrador: ningún otro rol la ve ni la abre.
 * Las respuestas llevan datos de acceso, así que nunca se guardan en el dispositivo.
 */
class RolesController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    public function __construct(private RolService $roles)
    {
    }

    protected function grilla(): Grilla
    {
        return app(RolesGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->soloAdministrador()) {
            return $redirect;
        }

        return $this->sinCache(response()->view('roles.index', $this->datosDeLaGrilla($request)));
    }

    public function create(Request $request)
    {
        if ($redirect = $this->soloAdministrador()) {
            return $redirect;
        }

        // «Duplicar» parte de los permisos y el alcance de otro rol y pide un nombre nuevo.
        $origen = $request->filled('duplicar') ? Rol::with('permisos')->where('clave', $request->query('duplicar'))->first() : null;

        return $this->sinCache(response()->view('roles.create', [
            'rol' => $origen,
            'niveles' => $origen?->niveles() ?? [],
            'duplicado' => $origen !== null,
        ]));
    }

    public function store(RolRequest $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $duplicado = $request->filled('duplicado_de') && Rol::where('clave', $request->input('duplicado_de'))->exists();
        $rol = $this->roles->crear($request->safe()->only(['nombre', 'descripcion', 'alcance', 'asignable']), $request->niveles(), $this->usuarioActual(), $duplicado ? 'duplicado' : 'creado');

        return redirect()->route('roles.show', $rol)->with('success', 'Rol «'.$rol->nombre.'» creado correctamente.');
    }

    public function edit(Rol $rol)
    {
        if ($redirect = $this->soloAdministrador()) {
            return $redirect;
        }

        abort_if($rol->esAdministrador(), 403, 'Rol protegido: el Administrador no se puede modificar.');

        return $this->sinCache(response()->view('roles.edit', [
            'rol' => $rol->load('permisos'),
            'niveles' => $rol->niveles(),
            'afectados' => $this->roles->usuariosAfectados($rol),
        ]));
    }

    public function update(RolRequest $request, Rol $rol)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_if($rol->esAdministrador(), 403, 'Rol protegido: el Administrador no se puede modificar.');

        $datos = $request->safe()->only(['nombre', 'descripcion', 'alcance', 'asignable']);
        $niveles = $request->niveles();
        $afectados = $this->roles->usuariosAfectados($rol);

        if ($afectados > 0 && !$request->boolean('confirmo_afectados') && $this->roles->diferencias($rol, $datos, $niveles)['antes'] !== []) {
            return back()->withInput()->withErrors([
                'confirmo_afectados' => 'Confirma el cambio: '.$afectados.($afectados === 1 ? ' usuario se verá afectado.' : ' usuarios se verán afectados.'),
            ]);
        }

        $rol = $this->roles->actualizar($rol, $datos, $niveles, $this->usuarioActual());

        return redirect()->route('roles.show', $rol)->with('success', 'Rol «'.$rol->nombre.'» actualizado correctamente.');
    }

    public function destroy(Rol $rol)
    {
        if ($redirect = $this->soloAdministrador()) {
            return $redirect;
        }

        abort_if($rol->esAdministrador(), 403, 'Rol protegido: el Administrador no se puede eliminar.');

        try {
            $this->roles->eliminar($rol, $this->usuarioActual());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('roles.index')->with('success', 'Rol «'.$rol->nombre.'» eliminado.');
    }

    public function show(Request $request, Rol $rol)
    {
        if ($redirect = $this->soloAdministrador()) {
            return $redirect;
        }

        $rol->load(['permisos']);
        $pestana = $this->pestanaActiva($request);

        return $this->sinCache(response()->view('roles.show', [
            'rol' => $rol,
            'pestana' => $pestana,
            'historico' => $pestana === 'historico' ? $this->historicoDeRegistro($request, $rol, route('roles.show', $rol)) : null,
            'niveles' => $rol->niveles(),
            'usuarios' => \App\Grillas\UsuariosGrilla::ordenarPorMiembro($rol->usuarios()->with('sede'))->get(),
        ]));
    }

    /** Redirige al inicio de sesión sin sesión; 403 si no es administrador. */
    private function soloAdministrador()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        abort_unless(Gate::forUser($this->usuarioActual())->allows('administrador'), 403, self::MENSAJE_SIN_PERMISO);

        return null;
    }

    private function sinCache($respuesta)
    {
        $respuesta->headers->set('Cache-Control', 'no-store, private');

        return $respuesta;
    }
}
