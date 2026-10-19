<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Support\MigracionRedesZonas;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sede_id', 'nombre', 'clave', 'activa'])]
class Zona extends Model
{
    use PerteneceASede;

    protected $table = 'zonas';

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Zona $zona) {
            $zona->nombre = trim(preg_replace('/\s+/', ' ', $zona->nombre));
            $zona->clave = MigracionRedesZonas::clave($zona->nombre);
        });
    }

    public function lideres(): HasMany
    {
        return $this->hasMany(Persona::class, 'zona_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
