<?php

namespace App\Services;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\PersonaCambio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiderazgoService
{
    public function registrarCambio(Persona $persona, string $accion, ?array $antes = null, ?array $despues = null, ?User $por = null): PersonaCambio
    {
        return PersonaCambio::create([
            'persona_id' => $persona->id,
            'user_id' => $por?->id,
            'accion' => $accion,
            'antes' => $antes,
            'despues' => $despues,
        ]);
    }

    /**
     * Cambia el líder directo de una persona. Impide que sea su propio líder o que se
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

    public function validarLider(Persona $persona, Persona $lider): void
    {
        if ($persona->exists && $lider->id === $persona->id) {
            throw ValidationException::withMessages(['lider_id' => 'Una persona no puede ser su propio líder.']);
        }

        if (!$lider->es_lider) {
            throw ValidationException::withMessages(['lider_id' => 'La persona elegida no es líder.']);
        }

        // Recorre la cadena de líderes del candidato: si llega a la persona se formaría un ciclo.
        $visitados = [];
        $actual = $lider;
        while ($actual && $persona->exists) {
            if ($actual->id === $persona->id) {
                throw ValidationException::withMessages(['lider_id' => 'Ese líder depende de esta persona; no se pueden crear ciclos de liderazgo.']);
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
        }

        return $celulas->pluck('nombre')->all();
    }

    public function designarLider(Persona $persona, ?User $por = null): Persona
    {
        if (!$persona->numero_documento) {
            throw ValidationException::withMessages(['persona_id' => 'Para designar un líder la persona debe tener documento de identidad registrado.']);
        }

        return DB::transaction(function () use ($persona, $por) {
            $antes = ['es_lider' => $persona->es_lider];

            $persona->update(['es_lider' => true]);

            $this->registrarCambio($persona, 'rol_lider', $antes, ['es_lider' => true], $por);

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
     * tiene personas, basta con reasignarlas o confirmar que queden sin líder.
     *
     * @throws ValidationException cuando faltan decisiones del usuario
     */
    public function quitarRol(Persona $lider, ?Persona $reasignarA = null, bool $confirmarSinLider = false, ?User $por = null): void
    {
        $impacto = $this->impactoDeQuitarRol($lider);

        if ($reasignarA) {
            if ($reasignarA->id === $lider->id || !$reasignarA->es_lider) {
                throw ValidationException::withMessages(['reasignar_a' => 'Elige otro líder válido para recibir a las personas y células.']);
            }
        } elseif ($impacto['celulas'] > 0) {
            throw ValidationException::withMessages(['reasignar_a' => "Este líder tiene {$impacto['celulas']} célula(s) y {$impacto['personas']} persona(s) a cargo. Elige un líder que las reciba."]);
        } elseif ($impacto['personas'] > 0 && !$confirmarSinLider) {
            throw ValidationException::withMessages(['reasignar_a' => "Este líder tiene {$impacto['personas']} persona(s) a cargo. Reasígnalas o confirma que queden sin líder."]);
        }

        DB::transaction(function () use ($lider, $reasignarA, $por) {
            $antes = ['es_lider' => true];

            if ($reasignarA) {
                $lider->miembros()->update(['lider_id' => $reasignarA->id]);
                Celula::where('lider_id', $lider->id)->update(['lider_id' => $reasignarA->id]);
                Celula::where('co_lider_id', $lider->id)->update(['co_lider_id' => null]);
            } else {
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

    public function reactivar(Persona $persona, ?User $por = null): void
    {
        $previo = PersonaCambio::where('persona_id', $persona->id)
            ->where('accion', 'inactivada')
            ->latest('id')
            ->first()?->antes['estado'] ?? null;

        $estado = $previo && $previo !== 'inactivo' ? $previo : 'miembro_activo';

        $persona->update(['estado' => $estado, 'inactivado_en' => null]);

        $this->registrarCambio($persona, 'reactivada', ['estado' => 'inactivo'], ['estado' => $estado], $por);
    }
}
