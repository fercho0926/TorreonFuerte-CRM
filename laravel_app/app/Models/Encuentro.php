<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Models\Concerns\RegistraHistorial;
use App\Models\Concerns\TieneRegistroQr;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'sede_id',
    'nivel_id',
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
    use HasFactory, PerteneceASede, RegistraHistorial, TieneRegistroQr;

    public const ESTADOS = ['planificado', 'realizado', 'cancelado'];

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'nombre' => 'Nombre',
            'fecha_realizacion' => ['Fecha', 'fecha'],
            'lugar' => 'Lugar',
            'valor' => ['Valor', 'dinero'],
            'estado' => ['Estado', ['planificado' => 'Planificado', 'realizado' => 'Realizado', 'cancelado' => 'Cancelado']],
            'notas' => 'Notas',
            'nivel_id' => ['Nivel del plan', 'nivel'],
            'registro_abierto' => ['Registro por QR abierto', 'booleano'],
        ];
    }

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

    /** Nivel del plan de formación al que pertenece este encuentro (opcional, spec 014). */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelFormacion::class, 'nivel_id');
    }

    public function totalRecaudado(): float
    {
        return (float) $this->personas->sum(fn (Persona $persona) => $persona->pivot->valor_pagado ?? 0);
    }
}
