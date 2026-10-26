<?php

namespace App\Models;

use App\Support\AccionesHistorial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Una entrada del historial único de la plataforma (spec 015): constancia inalterable de una acción.
 *
 * - Solo se escribe con `App\Services\Historial`; una vez creada no se puede modificar ni borrar (RF-008).
 * - La tabla no tiene claves foráneas a propósito: borrar el registro afectado o el usuario no toca la entrada, que
 *   conserva sus nombres como fotos (`actor_nombre`, `sujeto_nombre`, `relacionado_nombre`).
 * - No lleva el filtro global de sede: la pestaña «Histórico» se autoriza con la ficha del registro (y debe mostrar
 *   la cadena de traslados de un miembro), y «Control de cambios» filtra la sede de forma explícita en `LecturaHistorial`.
 */
class CambioHistorial extends Model
{
    public const UPDATED_AT = null;

    public const ACTOR_USUARIO = 'usuario';

    public const ACTOR_QR = 'qr';

    public const ACTOR_AUTOMATICO = 'automatico';

    public const ACTOR_SISTEMA = 'sistema';

    protected $table = 'historial_cambios';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Las entradas del historial no se pueden modificar.'));
        static::deleting(fn () => throw new LogicException('Las entradas del historial no se pueden borrar.'));
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'antes' => 'array',
            'despues' => 'array',
            'detalle' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function getEtiquetaAttribute(): string
    {
        return AccionesHistorial::existe($this->sujeto_tipo, $this->accion)
            ? AccionesHistorial::etiqueta($this->sujeto_tipo, $this->accion)
            : $this->accion;
    }

    public function getIconoAttribute(): string
    {
        return AccionesHistorial::existe($this->sujeto_tipo, $this->accion)
            ? AccionesHistorial::icono($this->sujeto_tipo, $this->accion)
            : 'fa-history';
    }

    /** Entrada copiada de los historiales anteriores a la spec 015 (sin detalle por campo legible). */
    public function getEsLegadoAttribute(): bool
    {
        return isset($this->detalle['legado']);
    }

    /** @return array<int, array<string, mixed>> */
    public function getCambiosAttribute(): array
    {
        return $this->detalle['cambios'] ?? [];
    }

    public function scopeDe($query, string $tipo, int|array $ids)
    {
        return $query->where('sujeto_tipo', $tipo)->whereIn('sujeto_id', (array) $ids);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
