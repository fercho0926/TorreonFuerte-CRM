<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Models\Concerns\RegistraHistorial;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Petición de nueva clave hecha desde el inicio de sesión. Una sola pendiente por cuenta; la
 * atiende un administrador o pastor de la sede de la cuenta.
 */
#[Fillable(['user_id', 'sede_id', 'estado', 'atendida_por', 'atendida_en', 'ultima_solicitud_en', 'origen_hash'])]
class SolicitudClave extends Model
{
    use PerteneceASede, RegistraHistorial;

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'user_id' => ['Usuario', 'usuario'],
            'estado' => ['Estado', ['pendiente' => 'Pendiente', 'atendida' => 'Atendida', 'descartada' => 'Descartada']],
            'atendida_por' => ['Atendida por', 'usuario'],
            'atendida_en' => ['Atendida el', 'fecha_hora'],
        ];
    }

    /** Volver a pedir la clave solo renueva la fecha: no es un cambio que contar. */
    public function camposIgnorados(): array
    {
        return ['ultima_solicitud_en'];
    }

    public function nombreHistorial(): string
    {
        $usuario = User::query()->withoutGlobalScopes()->find($this->user_id);

        return 'Solicitud de '.($usuario?->name ?? 'usuario #'.$this->user_id);
    }

    public const PENDIENTE = 'pendiente';

    public const ATENDIDA = 'atendida';

    public const DESCARTADA = 'descartada';

    protected $table = 'solicitudes_clave';

    protected function casts(): array
    {
        return ['atendida_en' => 'datetime', 'ultima_solicitud_en' => 'datetime'];
    }

    /** Las cuentas de administradores del sistema no tienen sede; sus solicitudes solo las ve el admin. */
    public function exigeSede(): bool
    {
        return $this->sede_id !== null;
    }

    public function sedeEsObligatoria(): bool
    {
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function atendidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendida_por')->withoutGlobalScopes();
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroClave::class, 'solicitud_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::PENDIENTE);
    }

    /** Pendiente con más de la vigencia configurada (se calcula al consultar, sin tareas programadas). */
    public function estaVencida(): bool
    {
        return $this->estado === self::PENDIENTE
            && $this->ultima_solicitud_en->lt(now()->subHours(config('claves.solicitud_vence_horas')));
    }

    /** Estado para mostrar: «vencida» se deduce de la fecha. */
    public function estadoVigente(): string
    {
        return $this->estaVencida() ? 'vencida' : $this->estado;
    }
}
