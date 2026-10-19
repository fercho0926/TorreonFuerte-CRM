<?php

namespace App\Services;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Define qué personas, líderes, células puede ver un usuario según su rol:
 * admin y pastor ven todo; un líder ve a sus personas y sus células; el rol "usuario" solo
 * su propio registro. Devuelve consultas (no listas de IDs) para poder paginar y reutilizarlas
 * en selectores.
 */
class AlcanceService
{
    public function tieneAccesoTotal(?User $user): bool
    {
        return $user !== null && $user->tieneAccesoTotal();
    }

    public function personasVisibles(?User $user): Builder
    {
        $query = Persona::query();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->tieneAccesoTotal()) {
            return $query;
        }

        $miId = $user->persona_id;
        if (!$miId) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isLiderCelula()) {
            $misCelulas = fn ($c) => $c->where('celulas.lider_id', $miId)->orWhere('celulas.co_lider_id', $miId);

            return $query->where(function (Builder $q) use ($miId, $misCelulas) {
                $q->where('personas.id', $miId)
                    ->orWhere('personas.lider_id', $miId)
                    ->orWhereIn('personas.id', function ($sub) use ($misCelulas) {
                        $sub->select('celula_persona.persona_id')
                            ->from('celula_persona')
                            ->join('celulas', 'celulas.id', '=', 'celula_persona.celula_id')
                            ->where($misCelulas);
                    })
                    ->orWhereIn('personas.id', function ($sub) use ($misCelulas) {
                        $sub->select('celulas.lider_id')->from('celulas')->where($misCelulas);
                    })
                    ->orWhereIn('personas.id', function ($sub) use ($misCelulas) {
                        $sub->select('celulas.co_lider_id')->from('celulas')->whereNotNull('celulas.co_lider_id')->where($misCelulas);
                    });
            });
        }

        // Rol 'user': solo su propio registro.
        return $query->where('personas.id', $miId);
    }

    public function lideresVisibles(?User $user): Builder
    {
        return $this->personasVisibles($user)->where('personas.es_lider', true);
    }

    public function puedeVerPersona(?User $user, Persona $persona): bool
    {
        return $this->personasVisibles($user)->whereKey($persona->getKey())->exists();
    }

    public function celulasVisibles(?User $user): Builder
    {
        $query = Celula::query();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->tieneAccesoTotal()) {
            return $query;
        }

        $miId = $user->persona_id;

        if ($miId && $user->isLiderCelula()) {
            return $query->where(fn (Builder $q) => $q->where('celulas.lider_id', $miId)->orWhere('celulas.co_lider_id', $miId));
        }

        return $query->whereRaw('1 = 0');
    }
}
