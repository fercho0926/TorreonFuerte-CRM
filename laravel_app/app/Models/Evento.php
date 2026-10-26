<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Models\Concerns\RegistraHistorial;
use App\Models\Concerns\TieneRegistroQr;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sede_id',
    'registro_abierto',
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
    use HasFactory, PerteneceASede, RegistraHistorial, TieneRegistroQr;

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'nombre' => 'Nombre',
            'tipo' => 'Tipo',
            'fecha' => ['Fecha', 'fecha'],
            'lugar' => 'Lugar',
            'costo' => ['Costo', 'dinero', 'sensible' => \App\Support\Modulos::EVENTOS],
            'estado' => ['Estado', ['planificado' => 'Planificado', 'realizado' => 'Realizado', 'cancelado' => 'Cancelado']],
            'descripcion' => 'Descripción',
            'registro_abierto' => ['Registro por QR abierto', 'booleano'],
        ];
    }

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
