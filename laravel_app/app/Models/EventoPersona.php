<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa la inscripción de una persona a un evento, junto con el
 * historial de abonos (pagos) que haya hecho para cubrir el costo.
 */
#[Fillable(['sede_id', 'evento_id', 'persona_id', 'costo_individual'])]
class EventoPersona extends Model
{
    use PerteneceASede;

    protected $table = 'evento_persona';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'costo_individual' => 'decimal:2',
        ];
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(EventoPago::class);
    }

    /**
     * Costo que le aplica a esta persona: el individual si fue definido,
     * o el costo general del evento en caso contrario.
     */
    public function costoAplicable(): float
    {
        return (float) ($this->costo_individual ?? $this->evento->costo);
    }

    public function totalPagado(): float
    {
        return (float) ($this->relationLoaded('pagos') ? $this->pagos->sum('monto') : $this->pagos()->sum('monto'));
    }

    public function saldoPendiente(): float
    {
        return max(0, $this->costoAplicable() - $this->totalPagado());
    }

    public function estaPagado(): bool
    {
        return $this->saldoPendiente() <= 0;
    }
}
