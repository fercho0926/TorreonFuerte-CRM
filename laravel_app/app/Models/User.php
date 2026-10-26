<?php

namespace App\Models;

use App\Models\Concerns\PerteneceASede;
use App\Models\Concerns\RegistraHistorial;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Persona;
use App\Services\AutorizacionRoles;
use App\Support\Niveles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['sede_id', 'email', 'password', 'debe_cambiar_clave', 'clave_vence_en', 'role', 'persona_id', 'revision_vinculo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use PerteneceASede, RegistraHistorial;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** El nombre y el celular salen del miembro: se carga siempre con la cuenta. */
    protected $with = ['persona'];

    /**
     * Campos del historial (spec 015). La contraseña y el token de sesión nunca se guardan: solo que cambiaron.
     *
     * @return array<string, string|array<int|string, mixed>>
     */
    public function camposHistorial(): array
    {
        // Nombre y celular son del miembro: sus cambios quedan en el historial del miembro (spec 016).
        return [
            'email' => 'Correo',
            'role' => ['Rol', 'rol'],
            'sede_id' => ['Sede', 'sede'],
            'persona_id' => ['Ficha de miembro', 'persona'],
            'debe_cambiar_clave' => ['Debe cambiar la clave', 'booleano'],
            'password' => 'Contraseña',
        ];
    }

    /** @return array<int, string> */
    public function camposIgnorados(): array
    {
        return ['clave_vence_en', 'email_verified_at', 'remember_token', 'revision_vinculo', 'name', 'celular'];
    }

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
            'revision_vinculo' => 'array',
        ];
    }

    /**
     * El miembro dueño de la cuenta (spec 016). Sin el filtro de sede: el miembro de un administrador
     * pertenece a una sede concreta y el administrador navega por todas.
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class)->withoutGlobalScopes();
    }

    /** Nombre de la cuenta: siempre el del miembro, nunca una copia (spec 016). */
    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->persona?->nombre_completo);
    }

    /** Celular de la cuenta (claves por WhatsApp): siempre el del miembro. */
    protected function celular(): Attribute
    {
        return Attribute::get(fn () => $this->persona?->celular);
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

    /** Solo por compatibilidad: las reglas nuevas no deben depender del nombre del rol. */
    public function isPastor(): bool
    {
        return $this->role === 'pastor';
    }

    /** Su rol limita los datos a su rama (él, sus miembros y sus células). */
    public function isLiderCelula(): bool
    {
        return $this->alcance() === Niveles::ALCANCE_RAMA;
    }

    /** Su rol da acceso a todos los datos de su sede (el administrador, a todas las sedes). */
    public function tieneAccesoTotal(): bool
    {
        return $this->alcance() === Niveles::ALCANCE_SEDE;
    }

    /** Rol configurable del usuario (por `users.role` = `roles.clave`). */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'role', 'clave');
    }

    /** Alcance de datos de su rol: `sede`, `rama` o `registro`. */
    public function alcance(): string
    {
        return app(AutorizacionRoles::class)->alcance($this);
    }

    /** Nivel de acceso a un módulo: 0 sin acceso, 1 lectura, 2 escritura. */
    public function nivelEn(string $modulo): int
    {
        return app(AutorizacionRoles::class)->nivel($this, $modulo);
    }

    /** Nombre del rol para mostrar. */
    public function getRolEtiquetaAttribute(): string
    {
        return app(AutorizacionRoles::class)->rolDe($this)?->nombre ?? (string) $this->role;
    }

    /** Nombre de un rol por su clave (para listas y celdas). */
    public static function etiquetaDeRol(?string $clave): string
    {
        if ($clave === null) {
            return '';
        }

        return app(AutorizacionRoles::class)->nombres()[$clave] ?? $clave;
    }
}
