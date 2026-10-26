<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\User;
use App\Support\Modulos;
use App\Support\Niveles;
use Illuminate\Support\Collection;

/**
 * Nivel de acceso de un usuario en cada módulo y alcance de sus datos (spec 013). Los permisos del rol se leen
 * una vez por petición (atributos de la petición) y nunca se guardan en sesión ni entre peticiones, así un
 * cambio del administrador se aplica en la siguiente acción del usuario.
 */
class AutorizacionRoles
{
    private const ATRIBUTO = 'autorizacion_roles';

    /** Rol vigente del usuario (nulo si no tiene o fue eliminado). */
    public function rolDe(?User $usuario): ?Rol
    {
        if (!$usuario || !$usuario->role) {
            return null;
        }

        $cache = $this->cache();
        $clave = $usuario->role;

        if (!array_key_exists($clave, $cache['roles'])) {
            $cache['roles'][$clave] = Rol::with('permisos')->where('clave', $clave)->first();
            $this->guardar($cache);
        }

        return $cache['roles'][$clave];
    }

    /** 0 sin acceso, 1 lectura, 2 escritura; el administrador siempre tiene el máximo del módulo. */
    public function nivel(?User $usuario, string $modulo): int
    {
        $rol = $this->rolDe($usuario);

        return $rol ? $rol->nivelEn($modulo) : Niveles::SIN_ACCESO;
    }

    public function puedeLeer(?User $usuario, string $modulo): bool
    {
        return $this->nivel($usuario, $modulo) >= Niveles::LECTURA;
    }

    public function puedeEscribir(?User $usuario, string $modulo): bool
    {
        return $this->nivel($usuario, $modulo) >= Niveles::ESCRITURA;
    }

    /** Alcance de datos del rol; `sede` para el administrador y `registro` si no hay rol vigente. */
    public function alcance(?User $usuario): string
    {
        $rol = $this->rolDe($usuario);

        if (!$rol) {
            return Niveles::ALCANCE_REGISTRO;
        }

        return $rol->esAdministrador() ? Niveles::ALCANCE_SEDE : $rol->alcance;
    }

    public function esAdmin(?User $usuario): bool
    {
        // El rol Administrador está protegido (no se elimina ni se edita): basta con su clave, sin consultar la base.
        return $usuario !== null && $usuario->role === Rol::CLAVE_ADMIN;
    }

    /**
     * Roles que el actor puede asignar: administrador, todos; otros, los marcados «asignables» cuyo alcance
     * y cuyo nivel en cada módulo no superen los suyos.
     *
     * @return Collection<int, Rol>
     */
    public function rolesAsignables(User $actor): Collection
    {
        $roles = Rol::with('permisos')->orderBy('nombre')->get();

        if ($this->esAdmin($actor)) {
            return $roles;
        }

        if (!$this->puedeEscribir($actor, Modulos::USUARIOS)) {
            return collect();
        }

        return $roles->filter(fn (Rol $rol) => $this->puedeAsignar($actor, $rol))->values();
    }

    public function puedeAsignar(User $actor, Rol $rol): bool
    {
        if ($this->esAdmin($actor)) {
            return true;
        }

        return $this->puedeEscribir($actor, Modulos::USUARIOS) && $this->dentroDelTecho($actor, $rol);
    }

    /**
     * Techo de asignación (spec 013, D10): el rol es asignable, no es el de administrador y no da más alcance
     * ni más nivel en ningún módulo que los del actor. También limita sobre qué cuentas se pueden generar claves.
     */
    public function dentroDelTecho(User $actor, Rol $rol): bool
    {
        if ($this->esAdmin($actor)) {
            return true;
        }

        if (!$rol->asignable || $rol->esAdministrador()) {
            return false;
        }

        if (Niveles::ordenAlcance($rol->alcance) > Niveles::ordenAlcance($this->alcance($actor))) {
            return false;
        }

        foreach ($rol->niveles() as $modulo => $nivel) {
            if ($nivel > $this->nivel($actor, $modulo)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Nombre de cada rol por clave (una consulta por petición).
     *
     * @return array<string, string>
     */
    public function nombres(): array
    {
        $cache = $this->cache();

        if (!isset($cache['nombres'])) {
            $cache['nombres'] = Rol::pluck('nombre', 'clave')->all();
            $this->guardar($cache);
        }

        return $cache['nombres'];
    }

    /** Olvida lo resuelto en esta petición (después de cambiar roles o permisos). */
    public function olvidar(): void
    {
        $this->guardar(['roles' => [], 'nombres' => null]);
    }

    /** @return array{roles: array<string, ?Rol>, nombres?: ?array<string, string>} */
    private function cache(): array
    {
        return request()->attributes->get(self::ATRIBUTO, ['roles' => []]);
    }

    /** @param array{roles: array<string, ?Rol>, nombres?: ?array<string, string>} $cache */
    private function guardar(array $cache): void
    {
        request()->attributes->set(self::ATRIBUTO, $cache);
    }
}
