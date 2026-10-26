<?php

namespace App\Models;

use App\Models\Concerns\TieneHistorial;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Una iglesia con su propio conjunto de datos. No lleva filtro global de sede: es del módulo
 * exclusivo del administrador.
 */
#[Fillable(['nombre', 'ciudad', 'direccion', 'telefono', 'email', 'activa', 'pastor_persona_id', 'pastora_persona_id', 'registro_abierto'])]
class Sede extends Model
{
    use HasFactory, TieneHistorial;

    protected $table = 'sedes';

    protected static function booted(): void
    {
        // Enlace propio del QR de registro de miembros, no adivinable.
        static::creating(function (Sede $sede) {
            $sede->registro_token ??= Str::random(32);
        });
    }

    protected function casts(): array
    {
        return ['activa' => 'boolean', 'registro_abierto' => 'boolean'];
    }

    public function registrosMiembrosQr(): HasMany
    {
        return $this->hasMany(RegistroMiembroQr::class)->latest('created_at')->latest('id');
    }

    /** El registro de miembros está abierto si el interruptor está activo y la sede está activa. */
    public function registroMiembrosAbierto(): bool
    {
        return $this->registro_abierto && $this->activa;
    }

    public function regenerarTokenRegistro(): void
    {
        $this->forceFill(['registro_token' => Str::random(32)])->save();
    }

    public function urlRegistroMiembros(): string
    {
        // Filas creadas sin pasar por el modelo (seeders, importaciones) aún no tienen enlace: se les da uno.
        if (blank($this->registro_token)) {
            $this->regenerarTokenRegistro();
        }

        return route('registro-sede.show', $this->registro_token);
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

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'nombre' => 'Nombre',
            'ciudad' => 'Ciudad',
            'direccion' => 'Dirección',
            'telefono' => 'Teléfono',
            'email' => 'Correo',
            'activa' => ['Activa', 'booleano'],
            'pastor_persona_id' => ['Pastor', 'persona'],
            'pastora_persona_id' => ['Pastora', 'persona'],
            'registro_abierto' => ['Registro de miembros abierto', 'booleano'],
        ];
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
