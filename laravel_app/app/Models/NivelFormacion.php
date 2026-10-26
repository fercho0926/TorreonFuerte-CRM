<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nivel del plan de formación de la iglesia (spec 014). Es común a todas las sedes: no lleva sede ni filtro global;
 * lo que se aísla por sede son los avances de los miembros. No confundir con `App\Support\Niveles` (niveles de permiso).
 */
#[Fillable(['nombre', 'descripcion', 'posicion', 'activo'])]
class NivelFormacion extends Model
{
    use HasFactory, \App\Models\Concerns\TieneHistorial;

    protected $table = 'niveles_formacion';

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'posicion' => 'Posición',
            'activo' => ['Activo', 'booleano'],
        ];
    }

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'posicion' => 'integer'];
    }

    public function avances(): HasMany
    {
        return $this->hasMany(AvanceNivel::class, 'nivel_id')->withoutGlobalScopes();
    }

    public function encuentros(): HasMany
    {
        return $this->hasMany(Encuentro::class, 'nivel_id')->withoutGlobalScopes();
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('posicion');
    }

    /** ¿Se puede eliminar? Solo si no tiene avances ni encuentros vinculados (si no, se desactiva). */
    public function sePuedeEliminar(): bool
    {
        return !$this->avances()->exists() && !$this->encuentros()->exists();
    }
}
