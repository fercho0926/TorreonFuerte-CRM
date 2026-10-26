<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\LideresGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Services\SedeContext;
use App\Models\Celula;
use App\Models\Persona;
use App\Services\BuscadorPersonas;
use App\Services\LiderazgoService;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LideresController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    public function __construct(private LiderazgoService $liderazgo)
    {
    }

    protected function grilla(): Grilla
    {
        return app(LideresGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('lideres.index', $this->datosDeLaGrilla($request));
    }

    public function show(Request $request, Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->lider($lider);

        // El líder es un miembro: su histórico es el de su ficha, con las mismas secciones (spec 015).
        if ($this->pestanaActiva($request) === 'historico') {
            return view('lideres.show', [
                'lider' => $lider,
                'pestana' => 'historico',
                'historico' => $this->historicoDePersona($request, $lider, route('lideres.show', $lider)),
            ]);
        }

        $miembros = $lider->miembros()
            ->with('celulas')
            ->activas()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $celulas = Celula::query()
            ->where(fn ($q) => $q->where('lider_id', $lider->id)->orWhere('co_lider_id', $lider->id))
            ->withCount('miembros')
            ->withMax('reuniones', 'fecha')
            ->orderBy('nombre')
            ->get();

        return view('lideres.show', [
            'lider' => $lider,
            'miembros' => $miembros,
            'celulas' => $celulas,
            'ultimaReunion' => $celulas->max('reuniones_max_fecha'),
        ]);
    }

    public function designar(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('designarLider');

        // Tras un error de validación se conserva el miembro elegido en el autocompletado.
        $elegida = $request->old('persona_id')
            ? $this->alcance()->personasVisibles($this->usuarioActual())->whereKey($request->old('persona_id'))->first()
            : null;

        return view('lideres.designar', [
            'opcionElegida' => $elegida ? [['id' => $elegida->id, 'texto' => $elegida->nombre_completo, 'apoyo' => BuscadorPersonas::formato($elegida)['apoyo']]] : [],
        ]);
    }

    public function storeDesignar(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('designarLider');

        $data = $request->validate([
            'persona_id' => ['required', app(SedeContext::class)->existe('personas')],
        ], ['persona_id.required' => 'Elige al miembro que será líder.']);

        $user = $this->usuarioActual();
        $persona = $this->alcance()->personasVisibles($user)->whereKey($data['persona_id'])->first();
        abort_unless($persona, 404);

        if ($persona->estado === 'inactivo') {
            throw ValidationException::withMessages(['persona_id' => 'No se puede designar a un miembro inactiva.']);
        }

        if ($persona->es_lider) {
            throw ValidationException::withMessages(['persona_id' => 'Ese miembro ya es líder.']);
        }

        try {
            $this->liderazgo->designarLider($persona, $user);
        } catch (ValidationException $e) {
            // La sede sin pastor (o el pastor mismo) se informa en el campo del miembro elegido.
            throw ValidationException::withMessages(['persona_id' => collect($e->errors())->flatten()->first()]);
        }

        $pastor = $persona->fresh()->lider;
        $mensaje = $pastor
            ? "{$persona->nombre_completo} ahora es líder, bajo el pastor {$pastor->nombre_completo}."
            : "{$persona->nombre_completo} ahora es líder.";

        return redirect()->route('lideres.show', $persona)->with('success', $mensaje);
    }

    public function quitar(Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->lider($lider);
        $this->autorizar('designarLider');

        return view('lideres.quitar', $this->datosQuitar($lider));
    }

    public function destroy(Request $request, Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->lider($lider);
        $this->autorizar('designarLider');

        $data = $request->validate([
            'reasignar_a' => ['nullable', app(SedeContext::class)->existe('personas')->where('es_lider', true)],
            'confirmar_sin_lider' => ['nullable', 'boolean'],
        ]);

        $nuevo = null;
        if (!empty($data['reasignar_a'])) {
            $nuevo = $this->alcance()->lideresVisibles($this->usuarioActual())->whereKey($data['reasignar_a'])->first();
            abort_unless($nuevo, 403, self::MENSAJE_SIN_PERMISO);
        }

        try {
            $this->liderazgo->quitarRol($lider, $nuevo, $request->boolean('confirmar_sin_lider'), $this->usuarioActual());
        } catch (ValidationException $e) {
            // 422 mostrando cuántas miembros y células quedarían sin líder.
            return response()->view('lideres.quitar', $this->datosQuitar($lider) + [
                'mensajeError' => collect($e->errors())->flatten()->first(),
            ], 422);
        }

        return redirect()->route('lideres.index')->with('success', 'Se quitó el rol de líder; no se eliminó al miembro.');
    }

    /**
     * @return array<string, mixed>
     */
    private function datosQuitar(Persona $lider): array
    {
        return [
            'lider' => $lider,
            'impacto' => $this->liderazgo->impactoDeQuitarRol($lider),
            'personas' => $lider->miembros()->orderBy('apellidos')->orderBy('nombres')->get(),
            'celulas' => Celula::where('lider_id', $lider->id)->orWhere('co_lider_id', $lider->id)->orderBy('nombre')->get(),
            'otrosLideres' => $this->alcance()->lideresVisibles($this->usuarioActual())->whereKeyNot($lider->id)->orderBy('apellidos')->orderBy('nombres')->get(),
        ];
    }

    private function lider(Persona $lider): void
    {
        abort_unless($lider->es_lider, 404);
        abort_unless($this->alcance()->puedeVerPersona($this->usuarioActual(), $lider), 404);
    }
}
