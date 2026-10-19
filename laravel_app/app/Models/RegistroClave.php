<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Auditoría de una clave temporal generada. No contiene la clave ni su hash.
 */
#[Fillable(['user_id', 'sede_id', 'solicitud_id', 'motivo', 'solicitada_por', 'preparada_por', 'estado', 'vence_en', 'enviada_en', 'usada_en'])]
class RegistroClave extends Model
{
    use PerteneceASede;

    public const MOTIVO_ALTA = 'alta';

    public const MOTIVO_RECUPERACION = 'recuperacion';

    public const MOTIVO_REENVIO = 'reenvio';

    public const PENDIENTE_ENVIO = 'pendiente_envio';

    public const ENVIADA = 'enviada';

    public const USADA = 'usada';

    public const REEMPLAZADA = 'reemplazada';

    public const VENCIDA = 'vencida';

    protected $table = 'registros_clave';

    protected function casts(): array
    {
        return ['vence_en' => 'datetime', 'enviada_en' => 'datetime', 'usada_en' => 'datetime'];
    }

    public function exigeSede(): bool
    {
        return $this->sede_id !== null;
    }

    public function sedeEsObligatoria(): bool
    {
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function preparadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preparada_por')->withoutGlobalScopes();
    }

    public function solicitadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitada_por')->withoutGlobalScopes();
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudClave::class, 'solicitud_id')->withoutGlobalScopes();
    }

    /** Estado para mostrar: pendiente de envío o enviada con la fecha vencida se muestra «vencida». */
    public function estadoVigente(): string
    {
        $abierto = in_array($this->estado, [self::PENDIENTE_ENVIO, self::ENVIADA], true);

        return $abierto && $this->vence_en->isPast() ? self::VENCIDA : $this->estado;
    }
}
