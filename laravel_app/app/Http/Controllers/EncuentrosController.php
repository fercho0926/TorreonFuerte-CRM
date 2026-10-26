<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\EncuentrosGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Services\SedeContext;
use App\Models\Encuentro;
use App\Models\Persona;
use App\Services\AvancesNivelService;
use App\Services\Historial;
use App\Support\Modulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EncuentrosController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    /** Inscritos que entraron o salieron: resumen en el encuentro y una entrada en cada miembro (spec 015). */
    private function registrarInscritos(Encuentro $encuentro, array $resultado): void
    {
        app(Historial::class)->sincronizacion($encuentro, $resultado, 'inscritos_actualizados', 'inscrito_encuentro', 'quitado_encuentro', [
            'fecha_evento' => $encuentro->fecha_realizacion?->toDateString(),
        ]);
    }

    protected function grilla(): Grilla
    {
        return app(EncuentrosGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('encuentros.index', $this->datosDeLaGrilla($request));
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('encuentros.create', [
            'personas' => $this->personasDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $personaIds = $request->input('personas', []);

        DB::transaction(function () use ($data, $personaIds) {
            $encuentro = Encuentro::create($data);
            $this->registrarInscritos($encuentro, $encuentro->personas()->sync($personaIds));
            app(AvancesNivelService::class)->recalcularEncuentro($encuentro, null, $this->usuarioActual());
        });

        return redirect()->route('encuentros.index')->with('success', 'Encuentro creado correctamente.');
    }

    public function show(Request $request, Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($this->pestanaActiva($request) === 'historico') {
            return view('encuentros.show', [
                'encuentro' => $encuentro,
                'pestana' => 'historico',
                'historico' => $this->historicoDeRegistro($request, $encuentro, route('encuentros.show', $encuentro)),
            ]);
        }

        $visibles = $this->idsDePersonasVisibles();

        $encuentro->load(['personas' => function ($query) use ($visibles) {
            $query->orderBy('apellidos')->orderBy('nombres')
                ->when($visibles !== null, fn ($q) => $q->whereIn('personas.id', $visibles));
        }]);

        return view('encuentros.show', [
            'encuentro' => $encuentro,
        ]);
    }

    public function edit(Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('encuentros.edit', [
            'encuentro' => $encuentro,
            'personas' => $this->personasDisponibles(),
            'personasSeleccionadas' => $encuentro->personas()->pluck('personas.id')->all(),
        ]);
    }

    public function update(Request $request, Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $personaIds = $request->input('personas', []);

        DB::transaction(function () use ($data, $personaIds, $encuentro) {
            $nivelAnterior = $encuentro->nivel_id;
            $encuentro->update($data);
            $this->registrarInscritos($encuentro, $encuentro->personas()->sync($personaIds));
            // Los avances de niveles siguen a la asistencia: se recalculan con el nivel, el estado y los inscritos nuevos.
            app(AvancesNivelService::class)->recalcularEncuentro($encuentro->fresh(), $nivelAnterior, $this->usuarioActual());
        });

        return redirect()->route('encuentros.index')->with('success', 'Encuentro actualizado correctamente.');
    }

    public function destroy(Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        DB::transaction(function () use ($encuentro) {
            app(AvancesNivelService::class)->retirarDeEncuentro($encuentro, $this->usuarioActual());
            $encuentro->delete();
        });

        return redirect()->route('encuentros.index')->with('success', 'Encuentro eliminado correctamente.');
    }

    /**
     * Actualiza, desde el detalle del encuentro, quién asistió y cuánto pagó cada miembro.
     */
    public function actualizarAsistencia(Request $request, Encuentro $encuentro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $data = $request->validate([
            'asistio' => ['array'],
            'asistio.*' => ['integer', app(SedeContext::class)->existe('personas')],
            'valor_pagado' => ['array'],
            'valor_pagado.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $asistieron = $data['asistio'] ?? [];
        $valoresPagados = $data['valor_pagado'] ?? [];

        DB::transaction(function () use ($encuentro, $asistieron, $valoresPagados) {
            $historial = app(Historial::class);
            $lote = (string) Str::uuid();
            $fecha = ['fecha_evento' => $encuentro->fecha_realizacion?->toDateString()];
            $marcados = $retirados = [];

            foreach ($encuentro->personas as $persona) {
                $antes = ['asistio' => (bool) $persona->pivot->asistio, 'valor_pagado' => $persona->pivot->valor_pagado];
                $despues = ['asistio' => in_array($persona->id, $asistieron), 'valor_pagado' => $valoresPagados[$persona->id] ?? null];

                $encuentro->personas()->updateExistingPivot($persona->id, $despues);

                // Una entrada por cambio real en el histórico de cada miembro (RF-006).
                if ($antes['asistio'] !== $despues['asistio']) {
                    $historial->registrar($persona, $despues['asistio'] ? 'asistio_encuentro' : 'no_asistio_encuentro', null, null, null, $fecha, $encuentro, $lote);
                    $despues['asistio'] ? $marcados[] = $persona : $retirados[] = $persona;
                }
                if ((float) $antes['valor_pagado'] !== (float) $despues['valor_pagado']) {
                    $historial->registrar($persona, 'pago_encuentro', null, null, null, $fecha + ['cambios' => [[
                        'campo' => 'valor_pagado', 'etiqueta' => 'Valor pagado', 'largo' => false, 'secreto' => false, 'modulo_sensible' => Modulos::ENCUENTROS,
                        'antes' => $historial->valorLegible($encuentro, 'valor', $antes['valor_pagado']),
                        'despues' => $historial->valorLegible($encuentro, 'valor', $despues['valor_pagado']),
                    ]]], $encuentro, $lote);
                }
            }

            if ($marcados || $retirados) {
                $nombre = fn ($p) => ['id' => $p->id, 'nombre' => $p->nombre_completo];
                $historial->registrar($encuentro, 'asistencia_actualizada', null, null, null, $fecha + [
                    'agregados' => array_map($nombre, $marcados),
                    'quitados' => array_map($nombre, $retirados),
                    'etiquetas' => ['Asistieron', 'Asistencia retirada'],
                ], null, $lote);
            }

            app(AvancesNivelService::class)->recalcularEncuentro($encuentro->fresh(), null, $this->usuarioActual());
        });

        return redirect()->route('encuentros.show', $encuentro)->with('success', 'Asistencia y pagos actualizados.');
    }

    private function personasDisponibles()
    {
        return $this->alcance()->personasVisibles($this->usuarioActual())->orderBy('apellidos')->orderBy('nombres')->get();
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_realizacion' => ['required', 'date'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', Rule::in(Encuentro::ESTADOS)],
            'notas' => ['nullable', 'string'],
            'nivel_id' => ['nullable', 'integer', 'exists:niveles_formacion,id'],
            'personas' => ['nullable', 'array'],
            'personas.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        unset($data['personas']);

        return $data;
    }
}
