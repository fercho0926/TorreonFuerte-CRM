<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Services\Historial;
use App\Support\Modulos;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa la inscripción de un miembro a un evento, junto con el
 * historial de abonos (pagos) que haya hecho para cubrir el costo.
 */
#[Fillable(['sede_id', 'evento_id', 'persona_id', 'costo_individual'])]
class EventoPersona extends Model
{
    use PerteneceASede;

    protected $table = 'evento_persona';

    /**
     * Cada inscripción, retiro y cambio de costo queda en el histórico del evento y en el del miembro (spec 015).
     * El costo es sensible: solo lo ve quien tiene lectura en Eventos.
     */
    protected static function booted(): void
    {
        $registrar = function (EventoPersona $inscripcion, string $accion, array $detalle = []): void {
            $historial = app(Historial::class);
            $evento = Evento::query()->withoutGlobalScopes()->find($inscripcion->evento_id);
            $persona = Persona::query()->withoutGlobalScopes()->withTrashed()->find($inscripcion->persona_id);
            $detalle += ['fecha_evento' => $evento?->fecha?->toDateString()];

            if ($evento && $persona) {
                $lote = (string) Str::uuid();
                $historial->registrar($evento, $accion, null, null, null, $detalle, $persona, $lote);
                $historial->registrar($persona, $accion, null, null, null, $detalle, $evento, $lote);
            }
        };
        $costo = fn ($valor) => $valor === null ? '(costo general)' : '$ '.number_format((float) $valor, 0, ',', '.');

        static::created(fn (EventoPersona $i) => $registrar($i, 'inscrito_evento'));
        static::deleted(fn (EventoPersona $i) => $registrar($i, 'quitado_evento'));
        static::updated(function (EventoPersona $i) use ($registrar, $costo) {
            if ($i->wasChanged('costo_individual')) {
                $registrar($i, 'costo_evento', ['cambios' => [[
                    'campo' => 'costo_individual', 'etiqueta' => 'Costo individual', 'largo' => false, 'secreto' => false,
                    'modulo_sensible' => Modulos::EVENTOS,
                    'antes' => $costo($i->getRawOriginal('costo_individual')), 'despues' => $costo($i->costo_individual),
                ]]]);
            }
        });
    }

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
     * Costo que le aplica a este miembro: el individual si fue definido,
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
