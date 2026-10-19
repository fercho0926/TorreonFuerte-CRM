<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Persona;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['sede_id', 'name', 'email', 'celular', 'password', 'debe_cambiar_clave', 'clave_vence_en', 'role', 'persona_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use PerteneceASede;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'pastor', 'lider_celula', 'user'];

    public const ROLE_LABELS = [
        'admin' => 'Administrador del sistema',
        'pastor' => 'Pastor (ve todo)',
        'lider_celula' => 'Líder',
        'user' => 'Usuario',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'debe_cambiar_clave' => 'boolean',
            'clave_vence_en' => 'datetime',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function registrosClave(): HasMany
    {
        return $this->hasMany(RegistroClave::class)->withoutGlobalScopes();
    }

    /** Último registro de clave temporal generado para la cuenta. */
    public function ultimoRegistroClave(): HasOne
    {
        return $this->hasOne(RegistroClave::class)->withoutGlobalScopes()->latestOfMany();
    }

    /** Verdadero si la contraseña vigente es una clave temporal que ya venció. */
    public function claveTemporalVencida(): bool
    {
        return $this->debe_cambiar_clave && $this->clave_vence_en !== null && $this->clave_vence_en->isPast();
    }

    /**
     * Los administradores del sistema no pertenecen a ninguna sede: ven todas.
     */
    public function exigeSede(): bool
    {
        return $this->role !== 'admin';
    }

    public function sedeEsObligatoria(): bool
    {
        return false;
    }

    public function esAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPastor(): bool
    {
        return $this->role === 'pastor';
    }

    public function isLiderCelula(): bool
    {
        return $this->role === 'lider_celula';
    }

    /**
     * Admin y pastor ven todos los datos, sin restricción jerárquica.
     */
    public function tieneAccesoTotal(): bool
    {
        return in_array($this->role, ['admin', 'pastor'], true);
    }
}
