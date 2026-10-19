<?php

namespace App\Models\Scopes;

use App\Services\SedeContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtro global de sede (falla cerrado). Reglas en `SedeContext::modo()`:
 *  - sede efectiva → solo registros de esa sede;
 *  - sin sede para un usuario no admin, o sin usuario en la web → no se ve nada;
 *  - admin en "Todas las sedes" y consola → sin filtro.
 *
 * Usos permitidos de `withoutGlobalScopes()` / `withoutGlobalScope(SedeScope::class)`:
 * SedesController, SedeService, TrasladoService, las relaciones de Sede y SedeCambio, el
 * inicio de sesión y la carga del usuario autenticado (AuthController y Controller), y las pruebas. Cualquier otro uso es un riesgo
 * de fuga entre sedes y debe revisarse.
 */
class SedeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $modo = app(SedeContext::class)->modo();

        if ($modo['tipo'] === SedeContext::MODO_SEDE) {
            $builder->where($model->qualifyColumn('sede_id'), $modo['id']);
        } elseif ($modo['tipo'] === SedeContext::MODO_CERRADO) {
            $builder->whereRaw('1 = 0');
        }
    }
}
