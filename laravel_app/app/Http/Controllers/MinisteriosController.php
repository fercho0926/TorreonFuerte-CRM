<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\MinisteriosGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Services\Historial;
use App\Services\SedeContext;
use App\Models\Ministerio;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MinisteriosController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    /** Integrantes que entraron o salieron: resumen en el ministerio y una entrada en cada miembro (spec 015). */
    private function registrarIntegrantes(Ministerio $ministerio, array $resultado): void
    {
        app(Historial::class)->sincronizacion($ministerio, $resultado, 'integrantes_actualizados', 'ingreso_ministerio', 'salida_ministerio');
    }

    protected function grilla(): Grilla
    {
        return app(MinisteriosGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('ministerios.index', $this->datosDeLaGrilla($request));
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('ministerios.create', [
            'personas' => $this->personasDisponibles(),
            'integrantes' => $this->integrantesParaFormulario(null, request()),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $personaIds = $this->integrantesPermitidos($request, null);

        DB::transaction(function () use ($data, $personaIds) {
            $ministerio = Ministerio::create($data);
            $this->registrarIntegrantes($ministerio, $ministerio->integrantes()->sync($personaIds));
        });

        return redirect()->route('ministerios.index')->with('success', 'Ministerio creado correctamente.');
    }

    public function show(Request $request, Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($this->pestanaActiva($request) === 'historico') {
            return view('ministerios.show', [
                'ministerio' => $ministerio,
                'pestana' => 'historico',
                'historico' => $this->historicoDeRegistro($request, $ministerio, route('ministerios.show', $ministerio)),
            ]);
        }

        $visibles = $this->idsDePersonasVisibles();

        $ministerio->load(['encargado', 'integrantes' => function ($query) use ($visibles) {
            $query->orderBy('apellidos')->orderBy('nombres')
                ->when($visibles !== null, fn ($q) => $q->whereIn('personas.id', $visibles));
        }]);

        return view('ministerios.show', [
            'ministerio' => $ministerio,
        ]);
    }

    public function edit(Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('ministerios.edit', [
            'ministerio' => $ministerio,
            'personas' => $this->personasDisponibles(),
            'integrantes' => $this->integrantesParaFormulario($ministerio, request()),
        ]);
    }

    public function update(Request $request, Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $personaIds = $this->integrantesPermitidos($request, $ministerio);

        DB::transaction(function () use ($ministerio, $data, $personaIds) {
            $ministerio->update($data);
            // sync() con IDs simples conserva el rol/fecha_ingreso de quienes ya estaban.
            $this->registrarIntegrantes($ministerio, $ministerio->integrantes()->sync($personaIds));
        });

        return redirect()->route('ministerios.index')->with('success', 'Ministerio actualizado correctamente.');
    }

    public function destroy(Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $ministerio->delete();

        return redirect()->route('ministerios.index')->with('success', 'Ministerio eliminado correctamente.');
    }

    /**
     * Actualiza, desde el detalle del ministerio, el rol y la fecha de ingreso de cada integrante.
     */
    public function actualizarIntegrantes(Request $request, Ministerio $ministerio)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $request->validate([
            'rol' => ['array'],
            'rol.*' => ['nullable', 'string', 'max:255'],
            'fecha_ingreso' => ['array'],
            'fecha_ingreso.*' => ['nullable', 'date'],
        ]);

        $roles = $data['rol'] ?? [];
        $fechas = $data['fecha_ingreso'] ?? [];

        DB::transaction(function () use ($ministerio, $roles, $fechas) {
            $historial = app(Historial::class);
            $lote = (string) Str::uuid();
            $cambiados = [];

            foreach ($ministerio->integrantes as $persona) {
                $antes = ['rol' => $persona->pivot->rol, 'fecha_ingreso' => $persona->pivot->fecha_ingreso];
                $despues = ['rol' => $roles[$persona->id] ?? null, 'fecha_ingreso' => $fechas[$persona->id] ?? null];

                $ministerio->integrantes()->updateExistingPivot($persona->id, $despues);

                $cambios = [];
                if ((string) $antes['rol'] !== (string) $despues['rol']) {
                    $cambios[] = ['campo' => 'rol', 'etiqueta' => 'Rol en el ministerio', 'antes' => $antes['rol'] ?: '(vacío)', 'despues' => $despues['rol'] ?: '(vacío)', 'largo' => false, 'secreto' => false, 'modulo_sensible' => null];
                }
                if (substr((string) $antes['fecha_ingreso'], 0, 10) !== substr((string) $despues['fecha_ingreso'], 0, 10)) {
                    $cambios[] = ['campo' => 'fecha_ingreso', 'etiqueta' => 'Fecha de ingreso', 'largo' => false, 'secreto' => false, 'modulo_sensible' => null,
                        'antes' => $historial->valorLegible(Persona::class, 'fecha_nacimiento', $antes['fecha_ingreso']),
                        'despues' => $historial->valorLegible(Persona::class, 'fecha_nacimiento', $despues['fecha_ingreso'])];
                }
                if ($cambios) {
                    $historial->registrar($persona, 'cambio_ministerio', null, null, null, ['cambios' => $cambios], $ministerio, $lote);
                    $cambiados[] = ['id' => $persona->id, 'nombre' => $persona->nombre_completo];
                }
            }

            if ($cambiados) {
                $historial->registrar($ministerio, 'integrantes_actualizados', null, null, null, ['agregados' => $cambiados, 'etiquetas' => ['Rol o fecha actualizados', '']], null, $lote);
            }
        });

        return redirect()->route('ministerios.show', $ministerio)->with('success', 'Integrantes actualizados correctamente.');
    }

    /**
     * Miembros que se muestran ya seleccionadas: lo enviado antes (si hubo un error de validación)
     * o los integrantes actuales. El resto se busca con el autocompletado.
     */
    private function integrantesParaFormulario(?Ministerio $ministerio, Request $request)
    {
        $ids = $request->old('personas', $ministerio ? $ministerio->integrantes()->pluck('personas.id')->all() : []);

        return Persona::query()->whereIn('personas.id', (array) $ids)->orderBy('apellidos')->orderBy('nombres')->get();
    }

    /**
     * Ids de integrantes que se pueden guardar: los miembros nuevas deben estar activas y dentro del
     * alcance de quien guarda; quienes ya eran integrantes se conservan aunque hoy estén inactivas.
     *
     * @return array<int, int>
     */
    private function integrantesPermitidos(Request $request, ?Ministerio $ministerio): array
    {
        $ids = array_values(array_unique(array_map('intval', (array) $request->input('personas', []))));
        $actuales = $ministerio ? $ministerio->integrantes()->pluck('personas.id')->all() : [];
        $nuevas = array_diff($ids, $actuales);

        $permitidas = $nuevas
            ? $this->alcance()->personasVisibles($this->usuarioActual())->activas()->whereIn('personas.id', $nuevas)->pluck('personas.id')->all()
            : [];

        if (count($permitidas) !== count($nuevas)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['personas' => 'Algunos miembros no se pueden añadir: están inactivas o fuera de tu alcance.']);
        }

        return $ids;
    }

    private function personasDisponibles()
    {
        return $this->alcance()->personasVisibles($this->usuarioActual())->orderBy('apellidos')->orderBy('nombres')->get();
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'encargado_id' => ['nullable', app(SedeContext::class)->existe('personas')],
            'estado' => ['required', Rule::in(Ministerio::ESTADOS)],
            'personas' => ['nullable', 'array'],
            'personas.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        $data['encargado_id'] = ($data['encargado_id'] ?? null) ?: null;
        unset($data['personas']);

        return $data;
    }
}
