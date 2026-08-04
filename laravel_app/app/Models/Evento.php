<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombre',
    'tipo',
    'fecha',
    'lugar',
    'costo',
    'estado',
    'descripcion',
])]
class Evento extends Model
{
    use HasFactory;

    public const TIPOS = [
        'Cena de varones',
        'Cena de mujeres',
        'Bautizo',
        'Aniversario',
        'Otro',
    ];

    public const ESTADOS = ['planificado', 'realizado', 'cancelado'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'costo' => 'decimal:2',
        ];
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(EventoPersona::class);
    }

    public function totalRecaudado(): float
    {
        return (float) $this->inscripciones->sum(fn (EventoPersona $inscripcion) => $inscripcion->totalPagado());
    }

    public function totalEsperado(): float
    {
        return (float) $this->inscripciones->sum(fn (EventoPersona $inscripcion) => $inscripcion->costoAplicable());
    }
}
