<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sede_id', 'user_id', 'accion', 'antes', 'despues'])]
class SedeCambio extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'sede_cambios';

    public const ACCIONES = [
        'creada' => 'Sede creada',
        'editada' => 'Datos editados',
        'desactivada' => 'Sede desactivada',
        'reactivada' => 'Sede reactivada',
        'pastor_cambiado' => 'Cambio de pastor',
        'pastora_cambiada' => 'Cambio de pastora',
    ];

    protected function casts(): array
    {
        return ['antes' => 'array', 'despues' => 'array'];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function getEtiquetaAttribute(): string
    {
        return self::ACCIONES[$this->accion] ?? $this->accion;
    }
}
