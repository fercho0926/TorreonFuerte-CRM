<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Persona;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'persona_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'pastor', 'lider_zona', 'lider_celula', 'user'];

    public const ROLE_LABELS = [
        'admin' => 'Administrador del sistema',
        'pastor' => 'Pastor (ve todo)',
        'lider_zona' => 'Líder de zona/red',
        'lider_celula' => 'Líder de célula',
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
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPastor(): bool
    {
        return $this->role === 'pastor';
    }

    public function isLiderZona(): bool
    {
        return $this->role === 'lider_zona';
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
