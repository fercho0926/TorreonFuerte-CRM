<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Services\Historial;
use App\Support\Modulos;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sede_id', 'evento_persona_id', 'monto', 'fecha_pago', 'notas'])]
class EventoPago extends Model
{
    use PerteneceASede;

    protected $table = 'evento_pagos';

    /** Cada abono registrado o eliminado queda en el histórico del evento y en el del miembro (spec 015). */
    protected static function booted(): void
    {
        $registrar = function (EventoPago $pago, string $accion): void {
            $historial = app(Historial::class);
            $inscripcion = EventoPersona::query()->withoutGlobalScopes()->find($pago->evento_persona_id);
            $evento = $inscripcion ? Evento::query()->withoutGlobalScopes()->find($inscripcion->evento_id) : null;
            $persona = $inscripcion ? Persona::query()->withoutGlobalScopes()->withTrashed()->find($inscripcion->persona_id) : null;

            if (!$evento || !$persona) {
                return;
            }

            $detalle = [
                'fecha_evento' => $pago->fecha_pago?->toDateString(),
                'cambios' => array_values(array_filter([
                    ['campo' => 'monto', 'etiqueta' => 'Valor', 'antes' => '', 'despues' => '$ '.number_format((float) $pago->monto, 0, ',', '.'), 'largo' => false, 'secreto' => false, 'modulo_sensible' => Modulos::EVENTOS, 'solo' => true],
                    $pago->notas ? ['campo' => 'notas', 'etiqueta' => 'Notas', 'antes' => '', 'despues' => $pago->notas, 'largo' => mb_strlen($pago->notas) > 120, 'secreto' => false, 'modulo_sensible' => null, 'solo' => true] : null,
                ])),
            ];
            $lote = (string) Str::uuid();
            $historial->registrar($evento, $accion, null, null, null, $detalle, $persona, $lote);
            $historial->registrar($persona, $accion, null, null, null, $detalle, $evento, $lote);
        };

        static::created(fn (EventoPago $p) => $registrar($p, 'abono_registrado'));
        static::deleted(fn (EventoPago $p) => $registrar($p, 'abono_eliminado'));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_pago' => 'date',
        ];
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(EventoPersona::class, 'evento_persona_id');
    }
}
