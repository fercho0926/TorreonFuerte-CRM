<?php

namespace App\Models\Concerns;

use App\Models\Scopes\SedeScope;
use App\Models\Sede;
use App\Services\SedeContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hace que un modelo pertenezca a una sede: aplica el filtro global y asigna la sede
 * automáticamente al crear. Debe usarlo todo modelo cuya tabla tenga `sede_id`.
 */
trait PerteneceASede
{
    public static function bootPerteneceASede(): void
    {
        static::addGlobalScope(new SedeScope());

        static::creating(function ($modelo) {
            $contexto = app(SedeContext::class);

            if (!$modelo->exigeSede()) {
                $modelo->sede_id = null;

                return;
            }

            $modo = $contexto->modo();

            if ($modelo->sede_id === null) {
                $modelo->sede_id = $contexto->sedeParaCrear($modelo->sedeEsObligatoria());

                return;
            }

            // Un registro nunca puede crearse en una sede distinta de la efectiva.
            abort_if(
                $contexto->estaEnPeticionWeb()
                    && $modo['tipo'] === SedeContext::MODO_SEDE
                    && (int) $modelo->sede_id !== $modo['id'],
                403,
                'No tienes permiso para esta acción'
            );
        });
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class)->withoutGlobalScopes();
    }

    /**
     * Si el modelo debe llevar sede (los administradores del sistema no).
     */
    public function exigeSede(): bool
    {
        return true;
    }

    /**
     * Si crear sin una sede efectiva en la web debe responder 403.
     */
    public function sedeEsObligatoria(): bool
    {
        return true;
    }

    public function scopeDeSede($query, int $sedeId)
    {
        return $query->where($this->qualifyColumn('sede_id'), $sedeId);
    }
}
