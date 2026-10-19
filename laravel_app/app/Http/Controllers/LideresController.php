<?php

namespace App\Http\Controllers;

use App\Services\SedeContext;
use App\Models\Celula;
use App\Models\Persona;
use App\Models\Red;
use App\Services\LiderazgoService;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LideresController extends Controller
{
    public function __construct(private LiderazgoService $liderazgo)
    {
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $user = $this->usuarioActual();

        $lideres = $this->alcance()->lideresVisibles($user)
            ->with('redLider')
            ->withCount(['miembros', 'celulasLideradas'])
            ->when($request->filled('q'), fn ($query) => $query->buscar($request->q))
            ->when($request->filled('red_id'), fn ($query) => $query->where('red_id', $request->red_id))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(25)
            ->withQueryString();

        return view('lideres.index', [
            'lideres' => $lideres,
            'redes' => $this->alcance()->redesVisibles($user)->orderBy('nombre')->get(),
            'filtros' => $request->only(['q', 'red_id']),
        ]);
    }

    public function show(Persona $lider)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->lider($lider);

        $miembros = $lider->miembros()
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
            'lider' => $lider->load('redLider'),
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

        $user = $this->usuarioActual();
        $candidatas = collect();

        if ($request->filled('q')) {
            $candidatas = $this->alcance()->personasVisibles($user)
                ->where('es_lider', false)
                ->activas()
                ->buscar($request->q)
                ->orderBy('apellidos')->orderBy('nombres')
                ->limit(25)
                ->get();
        }

        return view('lideres.designar', [
            'q' => $request->input('q'),
            'candidatas' => $candidatas,
            'redes' => Red::activas()->orderBy('nombre')->get(),
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
            'red_id' => ['required', app(SedeContext::class)->existe('redes')],
        ]);

        $user = $this->usuarioActual();
        $persona = $this->alcance()->personasVisibles($user)->whereKey($data['persona_id'])->first();
        abort_unless($persona, 404);

        $this->liderazgo->designarLider($persona, Red::findOrFail($data['red_id']), $user);

        return redirect()->route('lideres.show', $persona)->with('success', 'La persona ahora es líder.');
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
            // 422 mostrando cuántas personas y células quedarían sin líder.
            return response()->view('lideres.quitar', $this->datosQuitar($lider) + [
                'mensajeError' => collect($e->errors())->flatten()->first(),
            ], 422);
        }

        return redirect()->route('lideres.index')->with('success', 'Se quitó el rol de líder; no se eliminó a la persona.');
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
