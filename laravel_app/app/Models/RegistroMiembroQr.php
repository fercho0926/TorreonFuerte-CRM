<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cada envío del formulario público del QR de miembros de una sede: lo que escribió quien se registró, el
 * resultado (nueva, actualizada, reactivada, sin cambios) y qué campos cambiaron. El cambio real vive
 * en la ficha del miembro y en su historial.
 */
#[Fillable(['sede_id', 'persona_id', 'nombres', 'apellidos', 'celular', 'resultado', 'cambios'])]
class RegistroMiembroQr extends Model
{
    use HasFactory, PerteneceASede;

    protected $table = 'registros_miembros_qr';

    public const RESULTADOS = [
        'nueva' => 'Nueva',
        'actualizada' => 'Actualizada',
        'reactivada' => 'Reactivada',
        'sin_cambios' => 'Sin cambios',
    ];

    protected function casts(): array
    {
        return ['cambios' => 'array'];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class)->withTrashed();
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombres.' '.$this->apellidos);
    }

    public function etiquetaResultado(): string
    {
        return self::RESULTADOS[$this->resultado] ?? (string) $this->resultado;
    }
}
