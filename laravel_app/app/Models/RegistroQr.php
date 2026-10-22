<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Registro hecho por una persona desde el formulario público del QR de un evento, encuentro o célula.
 * Deja la traza del origen QR (fecha y hora) y lo que la persona escribió; la inscripción real vive
 * en las tablas existentes. Las personas de otra sede solo quedan aquí, sin enlace a su ficha.
 */
#[Fillable([
    'sede_id', 'registrable_type', 'registrable_id', 'persona_id', 'nombres', 'apellidos', 'celular',
    'fecha_nacimiento', 'tipo_documento', 'numero_documento', 'resultado', 'nota_correccion',
])]
class RegistroQr extends Model
{
    use HasFactory, PerteneceASede;

    protected $table = 'registros_qr';

    public const RESULTADOS = ['nueva', 'existente', 'de_otra_sede', 'pendiente'];

    protected function casts(): array
    {
        return ['fecha_nacimiento' => 'date'];
    }

    public function registrable(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScopes();
    }

    /** Persona de la sede del destino (nula si es de otra sede o está pendiente). */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class)->withTrashed();
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombres.' '.$this->apellidos);
    }

    public function etiquetaResultado(): string
    {
        return [
            'nueva' => 'Nueva persona',
            'existente' => 'Ya existía',
            'de_otra_sede' => 'De otra sede',
            'pendiente' => 'Pendiente',
        ][$this->resultado] ?? $this->resultado;
    }
}
