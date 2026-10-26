<?php

namespace App\Services;

use App\Models\Celula;
use App\Models\Evento;
use App\Models\EventoPago;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Operaciones del módulo Sedes (solo administrador): alta atómica con pastor y pastora,
 * edición, cambio de pastores, desactivación y resumen global.
 */
class SedeService
{
    public function __construct(private SedeContext $contexto)
    {
    }

    /**
     * Crea la sede y, en la misma transacción, al pastor y (opcionalmente) a la pastora, cada
     * uno como miembro de la sede y como usuario con rol "pastor". Si algo falla no queda nada.
     *
     * @param  array<string, mixed>  $datos  nombre, ciudad, direccion, telefono, email, pastor[...], pastora[...]
     */
    public function crear(array $datos, ?User $por = null): Sede
    {
        return DB::transaction(function () use ($datos, $por) {
            $sede = Sede::create([
                'nombre' => trim($datos['nombre']),
                'ciudad' => trim($datos['ciudad']),
                'direccion' => $datos['direccion'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'email' => $datos['email'] ?? null,
                'activa' => true,
            ]);

            $personaPastor = $this->crearCuentaPastoral($sede, $datos['pastor']);
            $sede->pastor_persona_id = $personaPastor->id;

            if (!empty($datos['pastora'])) {
                $sede->pastora_persona_id = $this->crearCuentaPastoral($sede, $datos['pastora'])->id;
            }

            $sede->save();

            $this->registrar($sede, 'creada', null, [
                'nombre' => $sede->nombre,
                'pastor' => $personaPastor->nombre_completo,
                'pastora' => $sede->pastora_persona_id ? trim($datos['pastora']['nombres'].' '.$datos['pastora']['apellidos']) : null,
            ], $por);

            return $sede;
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function editar(Sede $sede, array $datos, ?User $por = null): Sede
    {
        $campos = ['nombre', 'ciudad', 'direccion', 'telefono', 'email'];
        $sede->fill(array_intersect_key($datos, array_flip($campos)));

        $cambios = $sede->getDirty();
        $antes = array_intersect_key($sede->getOriginal(), $cambios);

        if ($cambios) {
            $sede->save();
            $this->registrar($sede, 'editada', $antes, $cambios, $por);
        }

        return $sede;
    }

    public function desactivar(Sede $sede, ?User $por = null): void
    {
        if (!$sede->activa) {
            return;
        }

        $sede->update(['activa' => false]);
        $this->registrar($sede, 'desactivada', ['activa' => true], ['activa' => false], $por);
    }

    public function reactivar(Sede $sede, ?User $por = null): void
    {
        if ($sede->activa) {
            return;
        }

        $sede->update(['activa' => true]);
        $this->registrar($sede, 'reactivada', ['activa' => false], ['activa' => true], $por);
    }

    /**
     * Cambia al pastor o a la pastora: crea la cuenta nueva (o designa una cuenta existente de
     * la misma sede) y la anterior pasa a líder conservando su miembro.
     *
     * @param  'pastor'|'pastora'  $rol
     * @param  array<string, mixed>  $datos  datos de la cuenta nueva, o ['user_id' => id] de una cuenta de la sede
     */
    public function cambiarPastor(Sede $sede, string $rol, array $datos, ?User $por = null): Sede
    {
        $campo = $rol === 'pastora' ? 'pastora_persona_id' : 'pastor_persona_id';

        return DB::transaction(function () use ($sede, $rol, $campo, $datos, $por) {
            $anteriorPersonaId = $sede->{$campo};

            $nuevaPersona = $this->contexto->conSede($sede->id, function () use ($sede, $datos) {
                if (!empty($datos['user_id'])) {
                    $usuario = User::withoutGlobalScopes()->where('sede_id', $sede->id)->find($datos['user_id']);

                    if (!$usuario) {
                        throw ValidationException::withMessages(['user_id' => 'La cuenta elegida no pertenece a esta sede.']);
                    }

                    $usuario->update(['role' => 'pastor']);

                    // Toda cuenta es de un miembro (spec 016).
                    return $usuario->persona;
                }

                return $this->crearCuentaPastoral($sede, $datos);
            });

            if ($anteriorPersonaId && $anteriorPersonaId !== $nuevaPersona->id) {
                // La cuenta anterior pierde el rol de pastor pero conserva su miembro y sus datos.
                User::withoutGlobalScopes()
                    ->where('sede_id', $sede->id)
                    ->where('persona_id', $anteriorPersonaId)
                    ->where('role', 'pastor')
                    ->update(['role' => 'lider_celula']);
            }

            $sede->update([$campo => $nuevaPersona->id]);

            $this->registrar($sede, $rol === 'pastora' ? 'pastora_cambiada' : 'pastor_cambiado',
                [$campo => $anteriorPersonaId], [$campo => $nuevaPersona->id], $por);

            $this->mantenerCabeza($sede->fresh(), $rol, $nuevaPersona, $anteriorPersonaId, $por);

            return $sede;
        });
    }

    /**
     * Tras cambiar al pastor o a la pastora, la sede sigue con una sola cabeza (spec 016, research D10):
     * el nuevo queda líder y sin líder; si cambió el pastor, quienes dependían del anterior pasan al
     * nuevo; y el anterior queda como líder bajo el pastor.
     */
    private function mantenerCabeza(Sede $sede, string $rol, Persona $nueva, ?int $anteriorId, ?User $por): void
    {
        $liderazgo = app(LiderazgoService::class);
        $nueva = Persona::withoutGlobalScopes()->find($nueva->id);

        $antes = ['es_lider' => $nueva->es_lider, 'lider_id' => $nueva->lider_id];
        if ($antes !== ['es_lider' => true, 'lider_id' => null]) {
            $nueva->forceFill(['es_lider' => true, 'lider_id' => null])->save();
            $liderazgo->registrarCambio($nueva, 'lider', ['lider_id' => $antes['lider_id']], ['lider_id' => null], $por);
        }

        if (!$anteriorId || $anteriorId === $nueva->id) {
            return;
        }

        $anterior = Persona::withoutGlobalScopes()->find($anteriorId);
        $pastor = $rol === 'pastor' ? $nueva : $sede->pastor;

        if ($rol === 'pastor' && $anterior) {
            $liderazgo->reasignarDependientes($anterior, $nueva, $por);
        }

        if ($anterior && $pastor && $anterior->id !== $pastor->id && !$anterior->esPastoral()) {
            $anterior->forceFill(['es_lider' => true])->save();
            $liderazgo->cambiarLider($anterior, $pastor, $por);
        }
    }

    /**
     * Resumen por sede para el administrador: miembros, líderes, células, eventos próximos y
     * recaudo (siempre por sede; los montos no se mezclan sin rotular).
     *
     * @return Collection<int, array<string, mixed>> indexada por id de sede
     */
    public function resumen(): Collection
    {
        return $this->contexto->sinFiltro(function () {
            $porSede = fn ($consulta) => $consulta->selectRaw('sede_id, count(*) as total')->groupBy('sede_id')->pluck('total', 'sede_id');

            $personas = $porSede(Persona::query()->where('estado', '!=', 'inactivo'));
            $lideres = $porSede(Persona::query()->where('es_lider', true)->where('estado', '!=', 'inactivo'));
            $celulas = $porSede(Celula::query());
            $eventos = $porSede(Evento::query()->where('estado', '!=', 'cancelado')->whereDate('fecha', '>=', today()));
            $recaudo = EventoPago::query()->selectRaw('sede_id, sum(monto) as total')->groupBy('sede_id')->pluck('total', 'sede_id');

            return Sede::query()->orderBy('nombre')->get()->mapWithKeys(fn (Sede $sede) => [
                $sede->id => [
                    'sede' => $sede,
                    'personas' => (int) ($personas[$sede->id] ?? 0),
                    'lideres' => (int) ($lideres[$sede->id] ?? 0),
                    'celulas' => (int) ($celulas[$sede->id] ?? 0),
                    'eventos_proximos' => (int) ($eventos[$sede->id] ?? 0),
                    'recaudo' => (float) ($recaudo[$sede->id] ?? 0),
                ],
            ]);
        });
    }

    /**
     * Indica si la sede tiene datos propios de la iglesia más allá de las cuentas del pastor y
     * la pastora (miembros, células, eventos, etc.), lo que impide eliminarla.
     */
    public function tieneDatos(Sede $sede): bool
    {
        return $this->contexto->sinFiltro(function () use ($sede) {
            $pastorales = array_filter([$sede->pastor_persona_id, $sede->pastora_persona_id]);

            if (Persona::query()->withTrashed()->where('sede_id', $sede->id)->whereNotIn('id', $pastorales)->exists()) {
                return true;
            }

            foreach ([Celula::class, Evento::class, \App\Models\Encuentro::class, \App\Models\Ministerio::class] as $modelo) {
                if ($modelo::query()->where('sede_id', $sede->id)->exists()) {
                    return true;
                }
            }

            return User::query()->where('sede_id', $sede->id)->whereNotIn('persona_id', $pastorales ?: [0])->exists();
        });
    }

    /**
     * Elimina una sede sin datos (solo quedan sus cuentas pastorales, que se eliminan con ella).
     *
     * @throws ValidationException si la sede tiene datos
     */
    public function eliminar(Sede $sede): void
    {
        if ($this->tieneDatos($sede)) {
            throw ValidationException::withMessages(['sede' => 'Solo se pueden desactivar sedes con datos.']);
        }

        DB::transaction(function () use ($sede) {
            // La sede deja de existir; su historial y la foto de sus datos se conservan (spec 015, RF-004).
            app(Historial::class)->registrar($sede, 'eliminada');

            $this->contexto->sinFiltro(function () use ($sede) {
                $personas = array_filter([$sede->pastor_persona_id, $sede->pastora_persona_id]);
                $sede->update(['pastor_persona_id' => null, 'pastora_persona_id' => null]);
                User::query()->where('sede_id', $sede->id)->delete();
                Persona::query()->withTrashed()->whereIn('id', $personas)->forceDelete();
                $sede->delete();
            });
        });
    }

    /**
     * Crea el miembro y el usuario (rol pastor) de una cuenta pastoral dentro de la sede.
     *
     * @param  array<string, mixed>  $datos
     */
    private function crearCuentaPastoral(Sede $sede, array $datos): Persona
    {
        return $this->contexto->conSede($sede->id, function () use ($sede, $datos) {
            // El pastor y la pastora son líderes sin líder: la cabeza de la sede (spec 016).
            $persona = Persona::create([
                'sede_id' => $sede->id,
                'nombres' => trim($datos['nombres']),
                'apellidos' => trim($datos['apellidos']),
                'celular' => $datos['celular'] ?? null,
                'email' => $datos['email'],
                'estado' => 'miembro_activo',
                'es_lider' => true,
                'lider_id' => null,
            ]);

            User::create([
                'sede_id' => $sede->id,
                'email' => $datos['email'],
                'password' => $datos['password'],
                'role' => 'pastor',
                'persona_id' => $persona->id,
            ]);

            return $persona;
        });
    }

    /**
     * @param  array<string, mixed>|null  $antes
     * @param  array<string, mixed>|null  $despues
     */
    private function registrar(Sede $sede, string $accion, ?array $antes, ?array $despues, ?User $por): void
    {
        app(Historial::class)->registrar($sede, $accion, $antes, $despues, $por);
    }
}
