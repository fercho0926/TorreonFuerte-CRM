<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'nombre',
    'descripcion',
    'encargado_id',
    'estado',
])]
class Ministerio extends Model
{
    use HasFactory;

    public const ESTADOS = ['activo', 'inactivo'];

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
