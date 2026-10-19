<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonaRequest;
use App\Models\Persona;
use App\Models\Red;
use App\Services\LiderazgoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonasController extends Controller
{
    private const POR_PAGINA = 25;

    public function __construct(private LiderazgoService $liderazgo)
    {
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $user = $this->usuarioActual();
        $estado = $request->input('estado');
        $incluirInactivos = $request->boolean('inactivos') || $estado === 'inactivo';

        $personas = $this->alcance()->personasVisibles($user)
            ->with(['lider', 'redLider'])
            ->when($request->filled('q'), fn ($query) => $query->buscar($request->q))
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->when(!$incluirInactivos, fn ($query) => $query->activas())
            ->when($request->filled('lider_id'), fn ($query) => $query->where('lider_id', $request->lider_id))
            ->when($request->filled('red_id'), function ($query) use ($request) {
                $query->where(fn ($q) => $q->where('red_id', $request->red_id)
                    ->orWhereHas('lider', fn ($lider) => $lider->where('red_id', $request->red_id)));
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(self::POR_PAGINA)
            ->withQueryString();

        return view('personas.index', [
            'personas' => $personas,
            'lideres' => $this->alcance()->lideresVisibles($user)->orderBy('apellidos')->orderBy('nombres')->get(),
            'redes' => $this->alcance()->redesVisibles($user)->orderBy('nombre')->get(),
            'filtros' => [
                'q' => $request->input('q'),
                'estado' => $estado,
                'red_id' => $request->input('red_id'),
                'lider_id' => $request->input('lider_id'),
                'inactivos' => $incluirInactivos,
            ],
            'sinPersonaVinculada' => $user && !$user->tieneAccesoTotal() && !$user->persona_id,
        ]);
    }

    public function show(Persona $persona)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->persona($persona);

        $persona->load([
            'lider',
            'redLider',
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

        if ($this->puede('verBitacora')) {
            $persona->load('cambios.usuario');
        }

        return view('personas.show', ['persona' => $persona]);
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

        // Un líder que registra personas las deja a su cargo; si no, quedarían fuera de su ámbito.
        if (empty($data['lider_id']) && $user->isLiderCelula() && $user->persona_id) {
            $data['lider_id'] = $user->persona_id;
        }

        $lider = $this->resolverLider($data['lider_id'] ?? null);
        if ($lider) {
            $this->liderazgo->validarLider(new Persona(), $lider);
        }

        if ($respuesta = $this->avisarDuplicado($request, $data, null)) {
            return $respuesta;
        }

        $persona = DB::transaction(function () use ($data, $user) {
            $persona = Persona::create($data);
            $this->liderazgo->registrarCambio($persona, 'creada', null, ['estado' => $persona->estado, 'lider_id' => $persona->lider_id], $user);

            return $persona;
        });

        return redirect()->route('personas.show', $persona)->with('success', 'Persona creada correctamente.');
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
                ->with('error', 'Para quitar el rol de líder primero decide qué pasará con sus personas y células.');
        }

        $nuevoLiderId = $data['lider_id'] ?? null;
        unset($data['lider_id']);
        $cambiaLider = (int) $nuevoLiderId !== (int) $persona->lider_id;

        $nuevoLider = $cambiaLider ? $this->resolverLider($nuevoLiderId) : null;
        if ($nuevoLider) {
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
                $this->liderazgo->registrarCambio($persona, 'rol_lider', ['es_lider' => false], ['es_lider' => true, 'red_id' => $persona->red_id], $user);
            }

            return $cambiaLider ? $this->liderazgo->cambiarLider($persona, $nuevoLider, $user) : [];
        });

        $mensaje = 'Persona actualizada correctamente.';
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

        return redirect()->route('personas.show', $persona)->with('success', 'Persona inactivada. Su historial se conserva.');
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

        return redirect()->route('personas.show', $persona)->with('success', 'Persona reactivada.');
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
            'Esta persona tiene historial (encuentros, eventos, células, ministerios o personas a cargo) y no se puede eliminar. Inactívala en su lugar.'
        );

        $this->liderazgo->registrarCambio($persona, 'eliminada', ['estado' => $persona->estado], null, $this->usuarioActual());
        $persona->delete();

        return redirect()->route('personas.index')->with('success', 'Persona eliminada correctamente.');
    }

    /**
     * Resuelve la persona y comprueba su ámbito (404 si está fuera) y la habilidad pedida (403).
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
     * líderes controla el rol y la red.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function datosGuardables(array $data, ?Persona $actual): array
    {
        unset($data['confirmar_duplicado']);

        if (!$this->puede('designarLider')) {
            unset($data['es_lider'], $data['red_id']);

            return $data;
        }

        if (!empty($data['es_lider'])) {
            $data['lider_id'] = $data['lider_id'] ?? null;

            return $data;
        }

        $data['red_id'] = null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function avisarDuplicado(PersonaRequest $request, array $data, ?Persona $actual)
    {
        if ($request->boolean('confirmar_duplicado') || empty($data['celular'])) {
            return null;
        }

        $duplicada = Persona::query()
            ->whereRaw('lower(nombres) = ?', [mb_strtolower(trim($data['nombres']))])
            ->whereRaw('lower(apellidos) = ?', [mb_strtolower(trim($data['apellidos']))])
            ->where('celular', $data['celular'])
            ->when($actual, fn ($q) => $q->whereKeyNot($actual->id))
            ->first();

        if (!$duplicada) {
            return null;
        }

        return back()->withInput()->with('duplicado', [
            'id' => $duplicada->id,
            'nombre' => $duplicada->nombre_completo,
            'puedeVer' => $this->alcance()->puedeVerPersona($this->usuarioActual(), $duplicada),
        ]);
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
            'redes' => $puedeDesignar ? Red::activas()->orderBy('nombre')->get() : collect(),
        ];
    }
}
