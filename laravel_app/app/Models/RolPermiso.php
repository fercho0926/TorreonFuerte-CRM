<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rol_id', 'modulo', 'nivel'])]
class RolPermiso extends Model
{
    public $timestamps = false;

    protected $table = 'rol_permisos';

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
