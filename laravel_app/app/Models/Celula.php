<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sede_id',
    'nombre',
    'lider_id',
    'co_lider_id',
    'sede',
    'dia_reunion',
    'hora_reunion',
    'lugar_reunion',
    'estado',
])]
class Celula extends Model
{
    use HasFactory, PerteneceASede;

    public const ESTADOS = ['activa', 'inactiva'];

    public const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    public function lider(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'lider_id');
    }

    public function coLider(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'co_lider_id');
    }

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'celula_persona')
            ->withPivot(['fecha_ingreso'])
            ->withTimestamps();
    }

    public function reuniones(): HasMany
    {
        return $this->hasMany(CelulaReunion::class)->orderByDesc('fecha');
    }
}
