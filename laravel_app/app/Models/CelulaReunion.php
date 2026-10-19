<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['sede_id', 'celula_id', 'fecha', 'tema', 'notas'])]
class CelulaReunion extends Model
{
    use PerteneceASede;

    protected $table = 'celula_reuniones';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function celula(): BelongsTo
    {
        return $this->belongsTo(Celula::class);
    }

    public function asistentes(): BelongsToMany
    {
        return $this->belongsToMany(Persona::class, 'celula_reunion_asistencia', 'celula_reunion_id', 'persona_id')
            ->withPivot(['asistio'])
            ->withTimestamps();
    }

    public function totalAsistio(): int
    {
        return $this->asistentes->filter(fn (Persona $persona) => (bool) $persona->pivot->asistio)->count();
    }
}
