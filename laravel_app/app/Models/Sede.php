<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Una iglesia con su propio conjunto de datos. No lleva filtro global de sede: es del módulo
 * exclusivo del administrador.
 */
#[Fillable(['nombre', 'ciudad', 'direccion', 'telefono', 'email', 'activa', 'pastor_persona_id', 'pastora_persona_id'])]
class Sede extends Model
{
    use HasFactory;

    protected $table = 'sedes';

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function pastor(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'pastor_persona_id')->withoutGlobalScopes();
    }

    public function pastora(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'pastora_persona_id')->withoutGlobalScopes();
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'sede_id')->withoutGlobalScopes();
    }

    public function cambios(): HasMany
    {
        return $this->hasMany(SedeCambio::class)->latest('created_at')->latest('id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
