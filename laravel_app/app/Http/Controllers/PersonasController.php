<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\PersonasGrilla;
use App\Http\Controllers\Concerns\PestanaHistorico;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Http\Requests\PersonaRequest;
use App\Models\Persona;
use App\Services\BuscadorPersonas;
use App\Services\LiderazgoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PersonasController extends Controller
{
    use PestanaHistorico, ServicioGrilla;

    public function __construct(private LiderazgoService $liderazgo)
    {
    }

    protected function grilla(): Grilla
    {
        return app(PersonasGrilla::class);
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $user = $this->usuarioActual();

        return view('personas.index', $this->datosDeLaGrilla($request) + [
            'sinPersonaVinculada' => $user && !$user->tieneAccesoTotal() && !$user->persona_id,
        ]);
    }

    /** Búsqueda con autocompletado (JSON). Ver contracts/rutas-y-componentes.md. */
    public function buscar(Request $request, BuscadorPersonas $buscador)
    {
        if (!Session::get('is_logged_in')) {
            return response()->json(['mensaje' => 'Inicia sesión'], 401);
        }

        $excluir = $request->input('excluir', []);
        $excluir = is_array($excluir) ? $excluir : explode(',', (string) $excluir);

        $resultado = $buscador->buscar($this->usuarioActual(), [
            'q' => $request->input('q'),
            'activas' => $request->boolean('activas', true),
            'sin_lideres' => $request->boolean('sin_lideres'),
            'excluir' => $excluir,
            'pagina' => $request->integer('pagina', 1),
        ]);

        return response()->json($resultado)->header('Cache-Control', 'no-store, private');
    }

    /** Alta rápida (por ejemplo desde una célula): queda en estado «En célula». */
    public function rapida(Request $request)
    {
        if (!Session::get('is_logged_in')) {
            return response()->json(['mensaje' => 'Inicia sesión'], 401);
        }

        $this->autorizar('crear');

        // Este endpoint responde siempre JSON (el manejador global solo lo hace para /api).
        $validador = Validator::make($request->all(), [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'celular' => ['nullable', 'string', 'max:20', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:celular'],
        ], [
            'celular.required_without' => 'Indica al menos un medio de contacto: celular o correo.',
            'email.required_without' => 'Indica al menos un medio de contacto: celular o correo.',
        ]);

        if ($validador->fails()) {
            return response()->json(['errors' => $validador->errors()->toArray()], 422);
        }

        $data = $validador->validated();

        $data = array_map(fn ($v) => is_string($v) && trim($v) === '' ? null : $v, $data) + ['estado' => 'celula'];
        $data['estado'] = 'celula';

        if (!$request->boolean('confirmar_duplicado') && ($duplicada = $this->buscarDuplicada($data, null))) {
            return response()->json(['duplicado' => ['id' => $duplicada->id, 'nombre' => $duplicada->nombre_completo]], 409);
        }

        $user = $this->usuarioActual();

        // Un líder que registra miembros los deja a su cargo; si no, quedarían fuera de su ámbito.
        if ($user->isLiderCelula() && $user->persona_id) {
            $data['lider_id'] = $user->persona_id;
        }

        $persona = DB::transaction(function () use ($data, $user) {
            $persona = Persona::create($data);
            $this->liderazgo->registrarCambio($persona, 'creada', null, ['estado' => $persona->estado, 'lider_id' => $persona->lider_id], $user);

            return $persona;
        });

        return response()->json(BuscadorPersonas::formato($persona), 201);
    }

    public function show(Request $request, Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->persona($persona);

        // Quien ve la ficha ve su histórico (spec 015, RF-021).
        $pestana = $this->pestanaActiva($request);
        if ($pestana === 'historico') {
            return view('personas.show', [
                'persona' => $persona->load('lider')->loadCount(['celulas', 'celulasLideradas', 'celulasCoLideradas', 'encuentros', 'inscripcionesEventos']),
                'pestana' => $pestana,
                'historico' => $this->historicoDePersona($request, $persona, route('personas.show', $persona)),
            ]);
        }

        $persona->load([
            'lider',
            'miembros' => fn ($query) => $query->orderBy('apellidos')->orderBy('nombres'),
            'celulas.lider',
            'celulasLideradas.miembros',
            'celulasCoLideradas.miembros',
            'encuentros' => fn ($query) => $query->orderByDesc('fecha_realizacion'),
            'inscripcionesEventos.evento',
            'inscripcionesEventos.pagos',
            'ministerios',
            'ministeriosLiderados',
        ]);

        return view('personas.show', ['persona' => $persona, 'pestana' => $pestana]);
    }

    public function create(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear');

        return view('personas.create', $this->datosFormulario() + [
            'esLiderInicial' => $request->boolean('es_lider') && $this->puede('designarLider'),
        ]);
    }

    public function store(PersonaRequest $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('crear');

        $user = $this->usuarioActual();
        $data = $this->datosGuardables($request->validated(), null);

        // Un líder que registra miembros los deja a su cargo; si no, quedarían fuera de su ámbito.
        if (empty($data['lider_id']) && $user->isLiderCelula() && $user->persona_id) {
            $data['lider_id'] = $user->persona_id;
        }

        if (!empty($data['es_lider'])) {
            // Todo líder nuevo queda bajo el pastor de la sede (spec 016); sin pastor no hay líderes.
            $data['lider_id'] = $this->liderazgo->pastorDeSede(app(\App\Services\SedeContext::class)->sedeParaCrear())->id;
        } else {
            $lider = $this->resolverLider($data['lider_id'] ?? null);
            if ($lider) {
                $this->liderazgo->validarLider(new Persona(), $lider);
            }
        }

        if ($respuesta = $this->avisarDuplicado($request, $data, null)) {
            return $respuesta;
        }

        $persona = DB::transaction(function () use ($data, $user) {
            $persona = Persona::create($data);
            $this->liderazgo->registrarCambio($persona, 'creada', null, ['estado' => $persona->estado, 'lider_id' => $persona->lider_id], $user);

            return $persona;
        });

        return redirect()->route('personas.show', $persona)->with('success', 'Miembro creado correctamente.');
    }

    public function edit(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->persona($persona, 'editar');

        return view('personas.edit', $this->datosFormulario($persona) + ['persona' => $persona]);
    }

    public function update(PersonaRequest $request, Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->persona($persona, 'editar');

        $user = $this->usuarioActual();
        $data = $this->datosGuardables($request->validated(), $persona);

        if ($persona->es_lider && array_key_exists('es_lider', $data) && !$data['es_lider']) {
            return redirect()->route('lideres.quitar', $persona)
                ->with('error', 'Para quitar el rol de líder primero decide qué pasará con sus miembros y células.');
        }

        $nuevoLiderId = $data['lider_id'] ?? null;
        unset($data['lider_id']);
        $seraLider = (bool) ($data['es_lider'] ?? $persona->es_lider);
        $pastoral = $persona->esPastoral();

        // El pastor es la cabeza: no tiene líder. Quien pasa a ser líder queda bajo el pastor; un líder
        // nunca queda sin líder (spec 016).
        if ($pastoral && $nuevoLiderId) {
            throw ValidationException::withMessages(['lider_id' => LiderazgoService::PASTOR_SIN_LIDER]);
        }

        if ($seraLider && !$pastoral && !$persona->es_lider) {
            $nuevoLider = $this->liderazgo->liderPorDefecto($persona);
            $cambiaLider = $nuevoLider->id !== $persona->lider_id;
        } else {
            if ($seraLider && !$pastoral && !$nuevoLiderId) {
                throw ValidationException::withMessages(['lider_id' => LiderazgoService::LIDER_OBLIGATORIO]);
            }

            $cambiaLider = (int) $nuevoLiderId !== (int) $persona->lider_id;
            $nuevoLider = $cambiaLider ? $this->resolverLider($nuevoLiderId) : null;
        }

        if ($nuevoLider && $cambiaLider) {
            $this->liderazgo->validarLider($persona, $nuevoLider);
        }

        if ($respuesta = $this->avisarDuplicado($request, $data + ['lider_id' => $nuevoLiderId], $persona)) {
            return $respuesta;
        }

        $retiradas = DB::transaction(function () use ($data, $persona, $user, $cambiaLider, $nuevoLider) {
            $eraLider = $persona->es_lider;
            $estadoAnterior = $persona->estado;

            if (($data['estado'] ?? $estadoAnterior) === 'inactivo') {
                $data['inactivado_en'] = $persona->inactivado_en ?? now();
            } else {
                $data['inactivado_en'] = null;
            }

            $persona->fill($data);
            $cambios = $persona->getDirty();
            unset($cambios['inactivado_en'], $cambios['updated_at']);
            $antes = array_intersect_key($persona->getOriginal(), $cambios);
            $persona->save();

            if ($cambios) {
                $this->liderazgo->registrarCambio($persona, 'editada', $antes, $cambios, $user);
            }
            if ($estadoAnterior !== $persona->estado) {
                $this->liderazgo->registrarCambio($persona, 'estado', ['estado' => $estadoAnterior], ['estado' => $persona->estado], $user);
            }
            if (!$eraLider && $persona->es_lider) {
                $this->liderazgo->registrarCambio($persona, 'rol_lider', ['es_lider' => false], ['es_lider' => true], $user);
            }

            return $cambiaLider ? $this->liderazgo->cambiarLider($persona, $nuevoLider, $user) : [];
        });

        $mensaje = 'Miembro actualizado correctamente.';
        if ($retiradas) {
            $mensaje .= ' Se retiró de la(s) célula(s): '.implode(', ', $retiradas).'; elige una célula de su nuevo líder si corresponde.';
        }

        return redirect()->route('personas.show', $persona)->with('success', $mensaje);
    }

    public function inactivar(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->persona($persona, 'inactivar');

        if ($persona->estado !== 'inactivo') {
            $this->liderazgo->inactivar($persona, $this->usuarioActual());
        }

        return redirect()->route('personas.show', $persona)->with('success', 'Miembro inactivado. Su historial se conserva.');
    }

    public function reactivar(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->persona($persona, 'inactivar');

        if ($persona->estado === 'inactivo') {
            $this->liderazgo->reactivar($persona, $this->usuarioActual());
        }

        return redirect()->route('personas.show', $persona)->with('success', 'Miembro reactivado.');
    }

    public function destroy(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->persona($persona);
        $this->autorizar('eliminar', $persona);

        abort_if(
            $persona->tieneHistorial(),
            422,
            'Este miembro tiene historial (encuentros, eventos, células, ministerios o miembros a cargo) y no se puede eliminar. Inactívalo en su lugar.'
        );

        $this->liderazgo->registrarCambio($persona, 'eliminada', ['estado' => $persona->estado], null, $this->usuarioActual());
        $persona->delete();

        return redirect()->route('personas.index')->with('success', 'Miembro eliminado correctamente.');
    }

    /**
     * Resuelve el miembro y comprueba su ámbito (404 si está fuera) y la habilidad pedida (403).
     */
    private function persona(Persona $persona, string $habilidad = 'ver'): Persona
    {
        abort_unless($this->alcance()->puedeVerPersona($this->usuarioActual(), $persona), 404);

        if ($habilidad !== 'ver') {
            $this->autorizar($habilidad, $persona);
        }

        return $persona;
    }

    private function resolverLider(mixed $id): ?Persona
    {
        if (!$id) {
            return null;
        }

        // El líder elegido debe estar dentro del ámbito de quien guarda (no se asigna a líderes ajenos).
        $lider = $this->alcance()->lideresVisibles($this->usuarioActual())->whereKey($id)->first();
        abort_unless($lider, 403, self::MENSAJE_SIN_PERMISO);

        return $lider;
    }

    /**
     * Ajusta los datos validados según los permisos de quien guarda: solo quien puede designar
     * líderes controla el rol.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function datosGuardables(array $data, ?Persona $actual): array
    {
        unset($data['confirmar_duplicado']);

        if (!$this->puede('designarLider')) {
            unset($data['es_lider']);

            return $data;
        }

        if (!empty($data['es_lider'])) {
            $data['lider_id'] = $data['lider_id'] ?? null;

            return $data;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function avisarDuplicado(PersonaRequest $request, array $data, ?Persona $actual)
    {
        if ($request->boolean('confirmar_duplicado')) {
            return null;
        }

        $duplicada = $this->buscarDuplicada($data, $actual);

        if (!$duplicada) {
            return null;
        }

        return back()->withInput()->with('duplicado', [
            'id' => $duplicada->id,
            'nombre' => $duplicada->nombre_completo,
            'puedeVer' => $this->alcance()->puedeVerPersona($this->usuarioActual(), $duplicada),
        ]);
    }

    /** Miembro con el mismo nombre, apellidos y celular (posible duplicado). */
    private function buscarDuplicada(array $data, ?Persona $actual): ?Persona
    {
        if (empty($data['celular'])) {
            return null;
        }

        return Persona::query()
            ->whereRaw('lower(nombres) = ?', [mb_strtolower(trim($data['nombres']))])
            ->whereRaw('lower(apellidos) = ?', [mb_strtolower(trim($data['apellidos']))])
            ->where('celular', $data['celular'])
            ->when($actual, fn ($q) => $q->whereKeyNot($actual->id))
            ->first();
    }

    /**
     * Opciones de los selectores del formulario, limitadas al ámbito del usuario.
     *
     * @return array<string, mixed>
     */
    private function datosFormulario(?Persona $persona = null): array
    {
        $user = $this->usuarioActual();
        $puedeDesignar = $this->puede('designarLider');

        $lideres = $this->alcance()->lideresVisibles($user)
            ->when($persona, fn ($q) => $q->whereKeyNot($persona->id))
            ->orderBy('apellidos')->orderBy('nombres')
            ->get();

        return [
            'lideres' => $lideres,
            'puedeDesignarLider' => $puedeDesignar,
        ];
    }
}
