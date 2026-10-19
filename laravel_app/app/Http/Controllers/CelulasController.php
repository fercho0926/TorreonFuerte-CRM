<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\CelulasGrilla;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Services\SedeContext;
use App\Models\Celula;
use App\Models\CelulaReunion;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CelulasController extends Controller
{
    use ServicioGrilla;

    protected function grilla(): Grilla
    {
        return app(CelulasGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('celulas.index', $this->datosDeLaGrilla($request) + [
            'puedeGestionarEstructura' => $this->usuarioActual()?->tieneAccesoTotal(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        return view('celulas.create', [
            'personas' => $this->personasDisponibles(),
            'integrantes' => $this->integrantesParaFormulario(null, request()),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        $data = $this->validateData($request);
        $integrantes = $this->validarIntegrantes($request, null);

        DB::transaction(function () use ($data, $integrantes) {
            $celula = Celula::create($data);
            $this->sincronizarIntegrantes($celula, $integrantes);
        });

        return redirect()->route('celulas.index')->with('success', 'Célula creada correctamente.');
    }

    public function show(Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeVerCelula($celula)) {
            return $redirect;
        }

        $celula->load(['lider', 'coLider', 'miembros' => function ($query) {
            $query->orderBy('apellidos')->orderBy('nombres');
        }, 'reuniones.asistentes']);

        return view('celulas.show', [
            'celula' => $celula,
            'puedeAdministrar' => $this->puedeAdministrarCelula($celula),
            'puedeGestionarEstructura' => $this->usuarioActual()?->tieneAccesoTotal(),
        ]);
    }

    public function edit(Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        return view('celulas.edit', [
            'celula' => $celula,
            'personas' => $this->personasDisponibles(),
            'integrantes' => $this->integrantesParaFormulario($celula, request()),
        ]);
    }

    public function update(Request $request, Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        $data = $this->validateData($request, $celula);
        $integrantes = $this->validarIntegrantes($request, $celula);

        DB::transaction(function () use ($celula, $data, $integrantes) {
            $celula->update($data);

            // Solo se sincroniza si el formulario envió la sección de integrantes.
            if ($integrantes !== null) {
                $this->sincronizarIntegrantes($celula, $integrantes);
            }
        });

        return redirect()->route('celulas.show', $celula)->with('success', 'Célula actualizada correctamente.');
    }

    public function destroy(Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($redirect = $this->ensurePuedeGestionarEstructura()) {
            return $redirect;
        }

        $celula->delete();

        return redirect()->route('celulas.index')->with('success', 'Célula eliminada correctamente.');
    }

    public function agregarMiembro(Request $request, Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        // Acepta una lista (`personas[]`) o, por compatibilidad, un solo `persona_id`.
        if ($request->filled('persona_id') && !$request->has('personas')) {
            $request->merge(['personas' => [$request->input('persona_id')]]);
        }

        $data = $request->validate([
            'personas' => ['required', 'array', 'min:1'],
            'personas.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ], ['personas.required' => 'Elige al menos una persona.', 'personas.min' => 'Elige al menos una persona.']);

        // Ser integrante de una célula no depende del líder directo y no lo cambia (spec 006).
        $anteriores = $celula->miembros()->pluck('personas.id')->all();
        $nuevas = array_diff(array_map('intval', $data['personas']), $anteriores);
        $permitidas = $nuevas
            ? $this->alcance()->personasVisibles($this->usuarioActual())->activas()->whereIn('personas.id', $nuevas)->pluck('personas.id')->all()
            : [];

        if (count($permitidas) !== count($nuevas)) {
            throw ValidationException::withMessages(['personas' => 'Algunas personas no se pueden añadir: están inactivas o fuera de tu alcance.']);
        }

        $this->sincronizarIntegrantes($celula, array_values(array_unique(array_merge($anteriores, array_map('intval', $data['personas'])))));

        $agregadas = count(array_diff($data['personas'], $anteriores));

        return redirect()->route('celulas.show', $celula)->with('success', $agregadas === 1 ? 'Persona agregada a la célula.' : "{$agregadas} personas agregadas a la célula.");
    }

    public function quitarMiembro(Celula $celula, Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        // La persona conserva su líder directo; solo deja de pertenecer a esta célula.
        $celula->miembros()->detach($persona->id);

        return redirect()->route('celulas.show', $celula)->with('success', 'Persona retirada de la célula.');
    }

    public function registrarReunion(Request $request, Celula $celula)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'tema' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'asistio' => ['array'],
            'asistio.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        $reunion = CelulaReunion::create([
            'celula_id' => $celula->id,
            'fecha' => $data['fecha'],
            'tema' => $data['tema'] ?? null,
            'notas' => $data['notas'] ?? null,
        ]);

        $asistieron = $data['asistio'] ?? [];
        $asistencia = $celula->miembros->mapWithKeys(fn (Persona $persona) => [
            $persona->id => ['asistio' => in_array($persona->id, $asistieron)],
        ]);

        $reunion->asistentes()->sync($asistencia->all());

        return redirect()->route('celulas.show', $celula)->with('success', 'Reunión registrada correctamente.');
    }

    public function eliminarReunion(Celula $celula, CelulaReunion $reunion)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if (!$this->puedeAdministrarCelula($celula)) {
            return redirect()->route('dashboard');
        }

        abort_unless($reunion->celula_id === $celula->id, 404);

        $reunion->delete();

        return redirect()->route('celulas.show', $celula)->with('success', 'Reunión eliminada.');
    }

    private function ensurePuedeVerCelula(Celula $celula)
    {
        if (!$this->alcance()->celulasVisibles($this->usuarioActual())->whereKey($celula->id)->exists()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta célula.');
        }

        return null;
    }

    private function puedeAdministrarCelula(Celula $celula): bool
    {
        $user = $this->usuarioActual();

        if (!$user) {
            return false;
        }

        if ($user->tieneAccesoTotal()) {
            return true;
        }

        if ($user->isLiderCelula()) {
            return $celula->lider_id === $user->persona_id || $celula->co_lider_id === $user->persona_id;
        }

        return false;
    }

    private function personasDisponibles()
    {
        return $this->alcance()->personasVisibles($this->usuarioActual())
            ->activas()
            ->orderBy('apellidos')->orderBy('nombres')
            ->get();
    }

    /**
     * Personas que se muestran ya seleccionadas en el formulario: lo enviado antes (si hubo un
     * error de validación) o los integrantes actuales de la célula.
     */
    private function integrantesParaFormulario(?Celula $celula, Request $request)
    {
        $ids = $request->old('integrantes', $celula ? $celula->miembros()->pluck('personas.id')->all() : []);

        return Persona::query()->whereIn('personas.id', (array) $ids)->orderBy('apellidos')->orderBy('nombres')->get();
    }

    /**
     * Valida los integrantes enviados. Devuelve null si el formulario no incluyó la sección
     * (para no borrar integrantes por error) o la lista de ids válidos.
     *
     * @return array<int, int>|null
     */
    private function validarIntegrantes(Request $request, ?Celula $celula): ?array
    {
        if (!$request->has('integrantes_presente') && !$request->has('integrantes')) {
            return null;
        }

        $request->validate([
            'integrantes' => ['nullable', 'array'],
            'integrantes.*' => ['integer', app(SedeContext::class)->existe('personas')],
        ]);

        $ids = array_values(array_unique(array_map('intval', (array) $request->input('integrantes', []))));
        $actuales = $celula ? $celula->miembros()->pluck('personas.id')->all() : [];

        // Las personas nuevas deben estar activas y dentro del alcance de quien guarda.
        $nuevas = array_diff($ids, $actuales);
        $permitidas = $nuevas
            ? $this->alcance()->personasVisibles($this->usuarioActual())->activas()->whereIn('personas.id', $nuevas)->pluck('personas.id')->all()
            : [];

        if (count($permitidas) !== count($nuevas)) {
            throw ValidationException::withMessages(['integrantes' => 'Algunas personas no se pueden añadir: están inactivas o fuera de tu alcance.']);
        }

        return $ids;
    }

    /**
     * Deja como integrantes exactamente a los ids indicados: conserva la fecha de ingreso de quien ya
     * estaba, añade a las nuevas con la fecha de hoy y retira al resto. Nunca toca el líder directo.
     *
     * @param  array<int, int>  $ids
     */
    private function sincronizarIntegrantes(Celula $celula, array $ids): void
    {
        $actuales = $celula->miembros()->pluck('personas.id')->all();

        $nuevas = array_diff($ids, $actuales);
        if ($nuevas) {
            $celula->miembros()->attach(array_fill_keys($nuevas, ['fecha_ingreso' => now()->toDateString()]));
        }

        $retiradas = array_diff($actuales, $ids);
        if ($retiradas) {
            $celula->miembros()->detach($retiradas);
        }
    }

    private function validateData(Request $request, ?Celula $celula = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'lider_id' => ['required', app(SedeContext::class)->existe('personas')],
            'co_lider_id' => ['nullable', app(SedeContext::class)->existe('personas')],
            'sede' => ['nullable', 'string', 'max:255'],
            'dia_reunion' => ['nullable', Rule::in(Celula::DIAS)],
            'hora_reunion' => ['nullable', 'date_format:H:i'],
            'lugar_reunion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(Celula::ESTADOS)],
        ]);
    }
}
