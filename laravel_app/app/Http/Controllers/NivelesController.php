<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\NivelesMiembrosGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Http\Requests\NivelRequest;
use App\Models\CambioHistorial;
use App\Models\NivelFormacion;
use App\Services\NivelesService;
use Illuminate\Http\Request;

/** Módulo Niveles (spec 014): plan de niveles, orden y vista de quién está en cada nivel. */
class NivelesController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    public function __construct(private NivelesService $niveles)
    {
    }

    protected function grilla(): Grilla
    {
        return app(NivelesMiembrosGrilla::class);
    }

    /** Miembros de un nivel (o sin iniciar) con la grilla estándar: nivel actual, completaron o les toca. */
    public function miembros(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $nivel = $request->input('nivel');
        $elegido = null;
        if (filled($nivel) && $nivel !== 'sin') {
            $elegido = NivelFormacion::findOrFail((int) $nivel);
        }

        return view('niveles.miembros', $this->datosDeLaGrilla($request) + [
            'nivel' => $elegido,
            'sin' => $nivel === 'sin',
            'vista' => NivelesMiembrosGrilla::vistaDe($request->input('vista')),
            'vistas' => NivelesMiembrosGrilla::VISTAS,
        ]);
    }

    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('niveles.index', [
            'plan' => NivelFormacion::query()->ordenados()->get(),
            'resumen' => $this->niveles->resumen($this->usuarioActual()),
            'cambios' => CambioHistorial::query()->where('sujeto_tipo', 'nivel')->recientes()->limit(15)->get(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('niveles.create');
    }

    public function store(NivelRequest $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $nivel = $this->niveles->crear($request->validated(), $this->usuarioActual());

        return redirect()->route('niveles.index')->with('success', "Nivel «{$nivel->nombre}» creado al final del plan.");
    }

    public function edit(Request $request, NivelFormacion $nivel)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $pestana = $this->pestanaActiva($request);

        return view('niveles.edit', [
            'nivel' => $nivel,
            'pestana' => $pestana,
            'historico' => $pestana === 'historico' ? $this->historicoDeRegistro($request, $nivel, route('niveles.edit', $nivel)) : null,
        ]);
    }

    public function update(NivelRequest $request, NivelFormacion $nivel)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->niveles->actualizar($nivel, $request->validated(), $this->usuarioActual());

        return redirect()->route('niveles.index')->with('success', 'Nivel actualizado.');
    }

    public function subir(NivelFormacion $nivel)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->niveles->subir($nivel, $this->usuarioActual());

        return redirect()->route('niveles.index')->with('success', "«{$nivel->nombre}» subió una posición.");
    }

    public function bajar(NivelFormacion $nivel)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->niveles->bajar($nivel, $this->usuarioActual());

        return redirect()->route('niveles.index')->with('success', "«{$nivel->nombre}» bajó una posición.");
    }

    public function desactivar(NivelFormacion $nivel)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->niveles->desactivar($nivel, $this->usuarioActual());

        return redirect()->route('niveles.index')->with('success', "Nivel «{$nivel->nombre}» desactivado: ya no aparece en el camino.");
    }

    public function reactivar(NivelFormacion $nivel)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->niveles->reactivar($nivel, $this->usuarioActual());

        return redirect()->route('niveles.index')->with('success', "Nivel «{$nivel->nombre}» reactivado.");
    }

    public function destroy(NivelFormacion $nivel)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        try {
            $this->niveles->eliminar($nivel, $this->usuarioActual());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('niveles.index')->with('error', $e->errors()['nivel'][0]);
        }

        return redirect()->route('niveles.index')->with('success', 'Nivel eliminado.');
    }
}
