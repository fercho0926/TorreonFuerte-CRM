<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Models\Concerns\TieneRegistroQr;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'sede_id',
    'registro_abierto',
    'nombre',
    'fecha_realizacion',
    'lugar',
    'valor',
    'estado',
    'notas',
])]
class Encuentro extends Model
{
    use HasFactory, PerteneceASede, TieneRegistroQr;

    public const ESTADOS = ['planificado', 'realizado', 'cancelado'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_realizacion' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function personas(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'encuentro_persona')
            ->withPivot(['asistio', 'valor_pagado'])
            ->withTimestamps();
    }

    public function totalRecaudado(): float
    {
        return (float) $this->personas->sum(fn (Persona $persona) => $persona->pivot->valor_pagado ?? 0);
    }
}
