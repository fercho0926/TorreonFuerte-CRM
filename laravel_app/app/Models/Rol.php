<?php

namespace App\Models;

use App\Support\Modulos;
use App\Support\Niveles;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rol configurable con un nivel por módulo y un alcance de datos. Es global (sin filtro de sede) y se
 * relaciona con los usuarios por `users.role` = `roles.clave`.
 */
#[Fillable(['nombre', 'descripcion', 'alcance', 'asignable'])]
class Rol extends Model
{
    use HasFactory, \App\Models\Concerns\TieneHistorial;

    public const CLAVE_ADMIN = 'admin';

    protected $table = 'roles';

    protected function casts(): array
    {
        return ['es_sistema' => 'boolean', 'asignable' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'clave';
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(RolPermiso::class, 'rol_id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'clave')->withoutGlobalScopes();
    }

    /** @return array<string, string|array<int|string, mixed>> */
    public function camposHistorial(): array
    {
        return [
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'alcance' => ['Alcance', Niveles::ALCANCES],
            'asignable' => ['Asignable', 'booleano'],
        ];
    }

    public function esAdministrador(): bool
    {
        return $this->clave === self::CLAVE_ADMIN;
    }

    /**
     * Nivel de cada módulo del catálogo (0 si no hay fila). El administrador siempre tiene el máximo.
     *
     * @return array<string, int>
     */
    public function niveles(): array
    {
        $guardados = $this->relationLoaded('permisos')
            ? $this->permisos->pluck('nivel', 'modulo')->all()
            : $this->permisos()->pluck('nivel', 'modulo')->all();

        $niveles = [];
        foreach (Modulos::claves() as $modulo) {
            $niveles[$modulo] = $this->esAdministrador() ? Modulos::nivelMaximo($modulo) : (int) ($guardados[$modulo] ?? Niveles::SIN_ACCESO);
        }

        return $niveles;
    }

    public function nivelEn(string $modulo): int
    {
        return $this->niveles()[$modulo] ?? Niveles::SIN_ACCESO;
    }

    public function etiquetaAlcance(): string
    {
        return Niveles::ALCANCES[$this->alcance] ?? (string) $this->alcance;
    }
}
