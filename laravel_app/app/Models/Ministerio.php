<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Models\Concerns\RegistraHistorial;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'sede_id',
    'nombre',
    'descripcion',
    'encargado_id',
    'estado',
])]
class Ministerio extends Model
{
    use HasFactory, PerteneceASede, RegistraHistorial;

    public const ESTADOS = ['activo', 'inactivo'];

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'encargado_id' => ['Encargado', 'persona'],
            'estado' => ['Estado', ['activo' => 'Activo', 'inactivo' => 'Inactivo']],
        ];
    }

    public function encargado(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'encargado_id');
    }

    public function integrantes(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'ministerio_persona')
            ->withPivot(['rol', 'fecha_ingreso'])
            ->withTimestamps();
    }
}
