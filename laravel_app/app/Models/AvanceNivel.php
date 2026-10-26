<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Constancia de que un miembro completó un nivel (spec 014). A lo sumo uno por miembro y nivel. */
#[Fillable(['sede_id', 'persona_id', 'nivel_id', 'fecha', 'origen', 'encuentro_id', 'registrado_por', 'nota'])]
class AvanceNivel extends Model
{
    use HasFactory, PerteneceASede;

    public const ORIGEN_ENCUENTRO = 'encuentro';

    public const ORIGEN_MANUAL = 'manual';

    public const ORIGENES = [
        self::ORIGEN_ENCUENTRO => 'Encuentro',
        self::ORIGEN_MANUAL => 'Registro manual',
    ];

    protected $table = 'avances_nivel';

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class)->withoutGlobalScopes();
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelFormacion::class, 'nivel_id');
    }

    public function encuentro(): BelongsTo
    {
        return $this->belongsTo(Encuentro::class)->withoutGlobalScopes();
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por')->withoutGlobalScopes();
    }

    public function getOrigenEtiquetaAttribute(): string
    {
        return self::ORIGENES[$this->origen] ?? (string) $this->origen;
    }

    public function esManual(): bool
    {
        return $this->origen === self::ORIGEN_MANUAL;
    }
}
