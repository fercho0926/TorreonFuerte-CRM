<?php

namespace App\Services;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\CambioHistorial;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiderazgoService
{
    /**
     * Deja constancia de una acción sobre un miembro en el historial único (spec 015). Se conserva como atajo para los
     * servicios de miembros; quien necesite registro relacionado o detalle extra usa `Historial::registrar()`.
     *
     * @param  array<string, mixed>  $detalle
     */
    public function registrarCambio(Persona $persona, string $accion, ?array $antes = null, ?array $despues = null, ?User $por = null, array $detalle = [], ?\Illuminate\Database\Eloquent\Model $relacionado = null): ?CambioHistorial
    {
        return app(Historial::class)->registrar($persona, $accion, $antes, $despues, $por, $detalle, $relacionado);
    }

    /**
     * Cambia el líder directo de un miembro. Impide que sea su propio líder o que se
     * forme un ciclo, y la retira de las células del líder anterior.
     *
     * @return array<int, string> nombres de las células de las que fue retirada
     */
    public function cambiarLider(Persona $persona, ?Persona $nuevoLider, ?User $por = null): array
    {
        if ($persona->lider_id === $nuevoLider?->id) {
            return [];
        }

        if ($nuevoLider) {
            $this->validarLider($persona, $nuevoLider);
        } elseif ($persona->es_lider && !$persona->esPastoral()) {
            // Solo el pastor (y la pastora) no tienen líder: es la cabeza de la sede (spec 016).
            throw ValidationException::withMessages(['lider_id' => self::LIDER_OBLIGATORIO]);
        }

        return DB::transaction(function () use ($persona, $nuevoLider, $por) {
            $anterior = $persona->lider_id;

            $retiradas = $this->retirarDeCelulasDelLider($persona, $anterior);

            $persona->lider_id = $nuevoLider?->id;
            $persona->save();

            $this->registrarCambio($persona, 'lider', ['lider_id' => $anterior], ['lider_id' => $nuevoLider?->id], $por);

            return $retiradas;
        });
    }

    /**
     * Líder que recibe todo líder nuevo o designado: el pastor de la sede del miembro, que es la cabeza
     * del liderazgo (spec 016).
     *
     * @throws ValidationException si la sede aún no tiene pastor
     */
    public function liderPorDefecto(Persona $persona): Persona
    {
        return $this->pastorDeSede($persona->sede_id);
    }

    /** @throws ValidationException si la sede aún no tiene pastor */
    public function pastorDeSede(?int $sedeId): Persona
    {
        $pastor = $sedeId ? \App\Models\Sede::find($sedeId)?->pastor : null;

        if (!$pastor) {
            throw ValidationException::withMessages(['lider_id' => self::SIN_PASTOR]);
        }

        return $pastor;
    }

    public const SIN_PASTOR = 'Primero asigna el pastor de la sede.';

    public const LIDER_OBLIGATORIO = 'Todo líder depende del pastor o de otro líder.';

    public const PASTOR_SIN_LIDER = 'El pastor es la cabeza de la sede y no tiene líder.';

    /**
     * Corrige los datos existentes para que el pastor sea la cabeza de cada sede (spec 016, research D11):
     * pastor y pastora quedan líderes y sin líder, y todo líder sin líder pasa a depender del pastor.
     * Se puede repetir sin efecto. Las sedes sin pastor se omiten.
     *
     * @return array{corregidos: int, sedes_sin_pastor: array<int, string>}
     */
    public function corregirCabezas(): array
    {
        $corregidos = 0;
        $sinPastor = [];

        foreach (\App\Models\Sede::query()->orderBy('id')->get() as $sede) {
            $pastor = $sede->pastor;
            if (!$pastor) {
                $sinPastor[] = $sede->nombre;

                continue;
            }

            $pastorales = array_values(array_filter([$sede->pastor_persona_id, $sede->pastora_persona_id]));

            app(Historial::class)->comoActor('sistema', 'Sistema', function () use ($pastorales, $pastor, &$corregidos) {
                foreach (Persona::withoutGlobalScopes()->whereKey($pastorales)->get() as $cabeza) {
                    if (!$cabeza->es_lider || $cabeza->lider_id) {
                        $antes = $cabeza->lider_id;
                        $cabeza->forceFill(['es_lider' => true, 'lider_id' => null])->save();
                        $this->registrarCambio($cabeza, 'lider', ['lider_id' => $antes], ['lider_id' => null]);
                        $corregidos++;
                    }
                }

                $sueltos = Persona::withoutGlobalScopes()->whereNull('deleted_at')
                    ->where('sede_id', $pastor->sede_id)->where('es_lider', true)->whereNull('lider_id')
                    ->whereNotIn('id', $pastorales)->get();

                foreach ($sueltos as $lider) {
                    $lider->forceFill(['lider_id' => $pastor->id])->save();
                    $this->registrarCambio($lider, 'lider', ['lider_id' => null], ['lider_id' => $pastor->id]);
                    $corregidos++;
                }
            });
        }

        return ['corregidos' => $corregidos, 'sedes_sin_pastor' => $sinPastor];
    }

    /**
     * Pasa a `$a` todos los que dependían directamente de `$de` (cambio de pastor), sin retirarlos de
     * sus células: la estructura se conserva (spec 016, research D10).
     *
     * @return int cuántos miembros cambiaron de líder
     */
    public function reasignarDependientes(Persona $de, Persona $a, ?User $por = null): int
    {
        $dependientes = Persona::withoutGlobalScopes()->whereNull('deleted_at')
            ->where('lider_id', $de->id)->whereKeyNot($a->id)->get();

        foreach ($dependientes as $persona) {
            $persona->forceFill(['lider_id' => $a->id])->save();
            $this->registrarCambio($persona, 'lider', ['lider_id' => $de->id], ['lider_id' => $a->id], $por);
        }

        return $dependientes->count();
    }

    public function validarLider(Persona $persona, Persona $lider): void
    {
        if ($persona->exists && $lider->id === $persona->id) {
            throw ValidationException::withMessages(['lider_id' => 'Un miembro no puede ser su propio líder.']);
        }

        if ($persona->exists && $persona->esPastoral()) {
            throw ValidationException::withMessages(['lider_id' => self::PASTOR_SIN_LIDER]);
        }

        // El pastor y la pastora siempre pueden recibir miembros, aunque sus datos antiguos no los marquen como líderes.
        if (!$lider->es_lider && !$lider->esPastoral()) {
            throw ValidationException::withMessages(['lider_id' => 'El miembro elegido no es líder.']);
        }

        // Recorre la cadena de líderes del candidato: si llega al miembro se formaría un ciclo.
        $visitados = [];
        $actual = $lider;
        while ($actual && $persona->exists) {
            if ($actual->id === $persona->id) {
                throw ValidationException::withMessages(['lider_id' => 'Ese líder depende de este miembro; no se pueden crear ciclos de liderazgo.']);
            }
            if (isset($visitados[$actual->id])) {
                break;
            }
            $visitados[$actual->id] = true;
            $actual = $actual->lider_id ? Persona::find($actual->lider_id) : null;
        }
    }

    /**
     * @return array<int, string>
     */
    private function retirarDeCelulasDelLider(Persona $persona, ?int $liderId): array
    {
        if (!$liderId) {
            return [];
        }

        $celulas = Celula::query()
            ->where(fn ($q) => $q->where('lider_id', $liderId)->orWhere('co_lider_id', $liderId))
            ->whereHas('miembros', fn ($q) => $q->whereKey($persona->id))
            ->get();

        foreach ($celulas as $celula) {
            $celula->miembros()->detach($persona->id);
            app(Historial::class)->sincronizacion($celula, ['detached' => [$persona->id]], 'miembros_actualizados', 'ingreso_celula', 'salida_celula');
        }

        return $celulas->pluck('nombre')->all();
    }

    /**
     * Designa líder a un miembro y lo pone bajo el pastor de su sede, aunque antes tuviera otro líder
     * (spec 016, clarificación 1). El pastor y la pastora quedan sin líder: son la cabeza.
     *
     * @throws ValidationException si la sede no tiene pastor (no cambia nada)
     */
    public function designarLider(Persona $persona, ?User $por = null): Persona
    {
        $pastoral = $persona->esPastoral();
        $pastor = $pastoral ? null : $this->liderPorDefecto($persona);

        return DB::transaction(function () use ($persona, $por, $pastor) {
            $antes = ['es_lider' => $persona->es_lider];

            $persona->update(['es_lider' => true]);

            $this->registrarCambio($persona, 'rol_lider', $antes, ['es_lider' => true], $por);

            if ($pastor) {
                $this->cambiarLider($persona, $pastor, $por);
            } elseif ($persona->lider_id) {
                $persona->update(['lider_id' => null]);
            }

            return $persona;
        });
    }

    /**
     * Resume lo que se afectaría al quitar el rol de líder.
     *
     * @return array{personas:int, celulas:int}
     */
    public function impactoDeQuitarRol(Persona $lider): array
    {
        return [
            'personas' => $lider->miembros()->count(),
            'celulas' => Celula::where('lider_id', $lider->id)->orWhere('co_lider_id', $lider->id)->count(),
        ];
    }

    /**
     * Quita el rol de líder. Si el líder tiene células se debe indicar un nuevo líder; si solo
     * tiene miembros, basta con reasignarlos o confirmar que queden sin líder.
     *
     * @throws ValidationException cuando faltan decisiones del usuario
     */
    public function quitarRol(Persona $lider, ?Persona $reasignarA = null, bool $confirmarSinLider = false, ?User $por = null): void
    {
        $impacto = $this->impactoDeQuitarRol($lider);

        if ($reasignarA) {
            if ($reasignarA->id === $lider->id || !$reasignarA->es_lider) {
                throw ValidationException::withMessages(['reasignar_a' => 'Elige otro líder válido para recibir a los miembros y células.']);
            }
        } elseif ($impacto['celulas'] > 0) {
            throw ValidationException::withMessages(['reasignar_a' => "Este líder tiene {$impacto['celulas']} célula(s) y {$impacto['personas']} miembro(s) a cargo. Elige un líder que las reciba."]);
        } elseif ($impacto['personas'] > 0 && !$confirmarSinLider) {
            throw ValidationException::withMessages(['reasignar_a' => "Este líder tiene {$impacto['personas']} miembro(s) a cargo. Reasígnalos o confirma que queden sin líder."]);
        }

        DB::transaction(function () use ($lider, $reasignarA, $por) {
            $antes = ['es_lider' => true];

            if ($reasignarA) {
                $lider->miembros()->update(['lider_id' => $reasignarA->id]);
                Celula::where('lider_id', $lider->id)->update(['lider_id' => $reasignarA->id]);
                Celula::where('co_lider_id', $lider->id)->update(['co_lider_id' => null]);
            } else {
                // Los líderes a su cargo nunca quedan sin líder: suben a su líder o al pastor (spec 016).
                $arriba = $lider->lider_id ?? $lider->sede?->pastor_persona_id;
                $lider->miembros()->where('es_lider', true)->update(['lider_id' => $arriba]);
                $lider->miembros()->update(['lider_id' => null]);
            }

            $lider->update(['es_lider' => false]);

            $this->registrarCambio($lider, 'rol_lider', $antes, ['es_lider' => false, 'reasignado_a' => $reasignarA?->id], $por);
        });
    }

    public function inactivar(Persona $persona, ?User $por = null): void
    {
        $antes = ['estado' => $persona->estado];

        $persona->update(['estado' => 'inactivo', 'inactivado_en' => now()]);

        $this->registrarCambio($persona, 'inactivada', $antes, ['estado' => 'inactivo'], $por);
    }

    /** Estado que tenía el miembro antes de inactivarse (o miembro activo si no consta). */
    public function estadoAlReactivar(Persona $persona): string
    {
        $previo = CambioHistorial::query()->de('persona', $persona->id)
            ->where('accion', 'inactivada')
            ->latest('id')
            ->first()?->antes['estado'] ?? null;

        return $previo && $previo !== 'inactivo' ? $previo : 'miembro_activo';
    }

    public function reactivar(Persona $persona, ?User $por = null): void
    {
        $estado = $this->estadoAlReactivar($persona);

        $persona->update(['estado' => $estado, 'inactivado_en' => null]);

        $this->registrarCambio($persona, 'reactivada', ['estado' => 'inactivo'], ['estado' => $estado], $por);
    }
}
