<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sede_id',
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
    'estado',
    'red_id',
    'inactivado_en',
    'trasladada_a_sede_id',
    'trasladada_en',
])]
class Persona extends Model
{
    use HasFactory, SoftDeletes, PerteneceASede;

    public const TIPOS_DOCUMENTO = ['CC', 'TI', 'CE', 'Pasaporte'];

    public const ESTADOS = [
        'visitante' => 'Visitante',
        'nuevo_creyente' => 'Nuevo creyente',
        'miembro_activo' => 'Miembro activo',
        'inactivo' => 'Inactivo',
    ];

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
            'inactivado_en' => 'datetime',
            'trasladada_en' => 'datetime',
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

    public function redLider(): BelongsTo
    {
        return $this->belongsTo(Red::class, 'red_id');
    }

    public function cambios(): HasMany
    {
        return $this->hasMany(PersonaCambio::class)->latest('created_at')->latest('id');
    }

    public function getEstadoEtiquetaAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? (string) $this->estado;
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento?->age;
    }

    public function getFechaNacimientoLegibleAttribute(): ?string
    {
        return $this->fecha_nacimiento?->locale('es')->translatedFormat('j \d\e F \d\e Y');
    }

    /**
     * Celular solo con dígitos, para los enlaces de llamada y de WhatsApp.
     */
    public function getCelularLimpioAttribute(): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $this->celular);

        return $digitos !== '' ? $digitos : null;
    }

    /**
     * Número para wa.me: si es un celular local de 10 dígitos se antepone el indicativo de Colombia.
     */
    public function getCelularWhatsappAttribute(): ?string
    {
        $digitos = $this->celular_limpio;

        return $digitos !== null && strlen($digitos) === 10 ? '57'.$digitos : $digitos;
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

    /**
     * Busca por texto parcial en nombres, apellidos, documento y celular. Cada palabra debe
     * coincidir con alguno de esos campos, por lo que "ana pérez" encuentra a Ana Pérez.
     */
    public function scopeBuscar($query, ?string $texto)
    {
        foreach (preg_split('/\s+/', trim((string) $texto), -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
            $like = '%'.str_replace(['%', '_'], '', $palabra).'%';

            $query->where(function ($q) use ($like) {
                $q->where('nombres', 'like', $like)
                    ->orWhere('apellidos', 'like', $like)
                    ->orWhere('numero_documento', 'like', $like)
                    ->orWhere('celular', 'like', $like);
            });
        }

        return $query;
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', '!=', 'inactivo');
    }

    public function scopeSinLider($query)
    {
        return $query->whereNull('lider_id')->where('es_lider', false)->where('estado', '!=', 'inactivo');
    }

    /**
     * Indica si la persona tiene historial que impide eliminarla definitivamente.
     */
    public function tieneHistorial(): bool
    {
        return $this->encuentros()->exists()
            || $this->inscripcionesEventos()->exists()
            || $this->celulas()->exists()
            || $this->ministerios()->exists()
            || $this->celulasLideradas()->exists()
            || $this->celulasCoLideradas()->exists()
            || $this->ministeriosLiderados()->exists()
            || $this->miembros()->exists();
    }
}
