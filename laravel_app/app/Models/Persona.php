<?php

namespace App\Models;

use App\Support\Celular;
use App\Models\Concerns\PerteneceASede;
use App\Models\Concerns\TieneHistorial;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
    'inactivado_en',
    'trasladada_a_sede_id',
    'trasladada_en',
    'trasladada_desde_persona_id',
])]
class Persona extends Model
{
    use HasFactory, SoftDeletes, PerteneceASede, TieneHistorial;

    public const TIPOS_DOCUMENTO = ['CC', 'TI', 'CE', 'Pasaporte'];

    public const ESTADOS = [
        'visitante' => 'Visitante',
        'celula' => 'En célula',
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
     * El líder de célula de este miembro (es a su vez otro miembro).
     */
    public function lider(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'lider_id');
    }

    /**
     * Los miembros que tienen a este miembro como líder de célula.
     */
    public function miembros(): HasMany
    {
        return $this->hasMany(Persona::class, 'lider_id');
    }

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'nombres' => 'Nombres',
            'apellidos' => 'Apellidos',
            'tipo_documento' => 'Tipo de documento',
            'numero_documento' => 'Número de documento',
            'fecha_nacimiento' => ['Fecha de nacimiento', 'fecha'],
            'celular' => 'Celular',
            'email' => 'Correo',
            'direccion' => 'Dirección',
            'barrio' => 'Barrio',
            'ciudad' => 'Ciudad',
            'estado' => ['Estado', self::ESTADOS],
            'lider_id' => ['Líder', 'persona'],
            'es_lider' => ['Es líder', 'booleano'],
            'reasignado_a' => ['Miembros reasignados a', 'persona'],
            'trasladada_a_sede_id' => ['Trasladado a la sede', 'sede'],
        ];
    }

    /** @return array<int, string> */
    public function camposIgnorados(): array
    {
        return ['inactivado_en', 'trasladada_en', 'trasladada_desde_persona_id', 'celular_clave'];
    }

    protected static function booted(): void
    {
        // Forma canónica del celular para encontrar al miembro al crear su cuenta (spec 016).
        static::saving(function (Persona $persona) {
            $persona->celular_clave = Celular::clave($persona->celular);
        });
    }

    /** La cuenta de acceso del miembro, si tiene (spec 016: toda cuenta es de un miembro). */
    public function cuenta(): HasOne
    {
        return $this->hasOne(User::class)->withoutGlobalScopes();
    }

    /** Verdadero si es el pastor o la pastora de su sede: la cabeza del liderazgo, sin líder. */
    public function esPastoral(): bool
    {
        $sede = $this->sede;

        return $sede !== null && in_array($this->id, array_filter([$sede->pastor_persona_id, $sede->pastora_persona_id]), true);
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
        return Celular::limpio($this->celular);
    }

    /**
     * Número para wa.me: si es un celular local de 10 dígitos se antepone el indicativo de Colombia.
     */
    public function getCelularWhatsappAttribute(): ?string
    {
        return Celular::whatsapp($this->celular);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    /** Iniciales del primer nombre y el primer apellido, para el avatar (los miembros no tienen foto). */
    public function getInicialesAttribute(): string
    {
        $primera = fn (?string $texto) => mb_strtoupper(mb_substr(trim((string) $texto), 0, 1));

        return $primera($this->nombres).$primera($this->apellidos);
    }

    public function encuentros(): BelongsToMany
    {
        return $this->belongsToMany(Encuentro::class, 'encuentro_persona')
            ->withPivot(['asistio', 'valor_pagado'])
            ->withTimestamps();
    }

    /**
     * Inscripciones de este miembro a eventos, con su historial de abonos.
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
     * Ministerios que este miembro tiene a cargo como encargado.
     */
    public function ministeriosLiderados(): HasMany
    {
        return $this->hasMany(Ministerio::class, 'encargado_id');
    }

    /**
     * Célula(s) a las que pertenece este miembro como miembro.
     */
    public function celulas(): BelongsToMany
    {
        return $this->belongsToMany(Celula::class, 'celula_persona')
            ->withPivot(['fecha_ingreso'])
            ->withTimestamps();
    }

    /**
     * Célula(s) que este miembro lidera.
     */
    public function celulasLideradas(): HasMany
    {
        return $this->hasMany(Celula::class, 'lider_id');
    }

    /**
     * Célula(s) donde este miembro es co-líder.
     */
    public function celulasCoLideradas(): HasMany
    {
        return $this->hasMany(Celula::class, 'co_lider_id');
    }

    /** Niveles de formación que este miembro ha completado (spec 014). */
    public function avances(): HasMany
    {
        return $this->hasMany(AvanceNivel::class);
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

    /**
     * Miembros cuyo nivel actual (el de mayor posición completado entre los niveles activos) es el indicado.
     */
    public function scopeEnNivel($query, int $nivelId)
    {
        $posicion = NivelFormacion::query()->whereKey($nivelId)->value('posicion');

        if ($posicion === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($this->qualifyColumn('id'), function ($sub) use ($posicion) {
            $sub->select('a.persona_id')
                ->from('avances_nivel as a')
                ->join('niveles_formacion as n', 'n.id', '=', 'a.nivel_id')
                ->where('n.activo', true)
                ->groupBy('a.persona_id')
                ->havingRaw('max(n.posicion) = ?', [$posicion]);
        });
    }

    /** Miembros que no han completado ningún nivel activo. */
    public function scopeSinNivel($query)
    {
        return $query->whereNotIn($this->qualifyColumn('id'), function ($sub) {
            $sub->select('a.persona_id')
                ->from('avances_nivel as a')
                ->join('niveles_formacion as n', 'n.id', '=', 'a.nivel_id')
                ->where('n.activo', true);
        });
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', '!=', 'inactivo');
    }

    public function scopeSinLider($query)
    {
        // «En célula»: su vínculo es la célula, no un líder directo.
        return $query->whereNull('lider_id')->where('es_lider', false)->whereNotIn('estado', ['inactivo', 'celula']);
    }

    /**
     * Indica si el miembro tiene historial que impide eliminarlo definitivamente.
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
