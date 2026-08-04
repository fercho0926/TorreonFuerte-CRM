<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombres',
    'apellidos',
    'tipo_documento',
    'numero_documento',
    'fecha_nacimiento',
    'celular',
    'email',
    'direccion',
    'barrio',
    'ciudad',
    'lider_id',
    'es_lider',
    'red',
])]
class Persona extends Model
{
    use HasFactory;

    public const TIPOS_DOCUMENTO = ['CC', 'TI', 'CE', 'Pasaporte'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'es_lider' => 'boolean',
        ];
    }

    /**
     * El líder de célula de esta persona (es a su vez otra persona).
     */
    public function lider(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'lider_id');
    }

    /**
     * Las personas que tienen a esta persona como líder de célula.
     */
    public function miembros(): HasMany
    {
        return $this->hasMany(Persona::class, 'lider_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    public function encuentros(): BelongsToMany
    {
        return $this->belongsToMany(Encuentro::class, 'encuentro_persona')
            ->withPivot(['asistio', 'valor_pagado'])
            ->withTimestamps();
    }

    /**
     * Inscripciones de esta persona a eventos, con su historial de abonos.
     */
    public function inscripcionesEventos(): HasMany
    {
        return $this->hasMany(EventoPersona::class);
    }

    public function ministerios(): BelongsToMany
    {
        return $this->belongsToMany(Ministerio::class, 'ministerio_persona')
            ->withPivot(['rol', 'fecha_ingreso'])
            ->withTimestamps();
    }

    /**
     * Ministerios que esta persona tiene a cargo como encargado.
     */
    public function ministeriosLiderados(): HasMany
    {
        return $this->hasMany(Ministerio::class, 'encargado_id');
    }

    /**
     * Célula(s) a las que pertenece esta persona como miembro.
     */
    public function celulas(): BelongsToMany
    {
        return $this->belongsToMany(Celula::class, 'celula_persona')
            ->withPivot(['fecha_ingreso'])
            ->withTimestamps();
    }

    /**
     * Célula(s) que esta persona lidera.
     */
    public function celulasLideradas(): HasMany
    {
        return $this->hasMany(Celula::class, 'lider_id');
    }

    /**
     * Célula(s) donde esta persona es co-líder.
     */
    public function celulasCoLideradas(): HasMany
    {
        return $this->hasMany(Celula::class, 'co_lider_id');
    }

    public function scopeLideres($query)
    {
        return $query->where('es_lider', true);
    }
}
