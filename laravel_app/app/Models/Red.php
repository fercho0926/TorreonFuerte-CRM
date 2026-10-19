<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Support\MigracionRedesZonas;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sede_id', 'nombre', 'clave', 'activa'])]
class Red extends Model
{
    use PerteneceASede;

    protected $table = 'redes';

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Red $red) {
            $red->nombre = trim(preg_replace('/\s+/', ' ', $red->nombre));
            $red->clave = MigracionRedesZonas::clave($red->nombre);
        });
    }

    public function lideres(): HasMany
    {
        return $this->hasMany(Persona::class, 'red_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
