<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sede_id', 'persona_id', 'user_id', 'accion', 'antes', 'despues'])]
class PersonaCambio extends Model
{
    use PerteneceASede;

    public const UPDATED_AT = null;

    protected $table = 'persona_cambios';

    public const ACCIONES = [
        'creada' => 'Creada',
        'editada' => 'Datos editados',
        'estado' => 'Cambio de estado',
        'lider' => 'Cambio de líder',
        'rol_lider' => 'Rol de líder',
        'inactivada' => 'Inactivada',
        'reactivada' => 'Reactivada',
        'eliminada' => 'Eliminada',
    ];

    protected function casts(): array
    {
        return ['antes' => 'array', 'despues' => 'array'];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEtiquetaAttribute(): string
    {
        return self::ACCIONES[$this->accion] ?? $this->accion;
    }
}
