<?php

namespace App\Policies;

use App\Models\User;

/**
 * Gestión de cuentas: el admin gestiona todas; el pastor (y la pastora) solo las de su sede y
 * solo con rol de líder o de usuario (las cuentas de pastor y admin las controla el admin).
 */
class UserPolicy
{
    /** Roles que puede crear y modificar un pastor. */
    public const ROLES_DEL_PASTOR = ['lider_celula', 'user'];

    public function verUsuarios(User $actor): bool
    {
        return $actor->esAdmin() || $actor->role === 'pastor';
    }

    public function crear(User $actor): bool
    {
        return $this->verUsuarios($actor);
    }

    public function gestionar(User $actor, User $objetivo): bool
    {
        if ($actor->esAdmin()) {
            return true;
        }

        return $actor->role === 'pastor'
            && $actor->sede_id !== null
            && $objetivo->sede_id === $actor->sede_id
            && $actor->id !== $objetivo->id
            && in_array($objetivo->role, self::ROLES_DEL_PASTOR, true);
    }

    /** Generar y enviar una clave temporal: mismo alcance que gestionar la cuenta. */
    public function gestionarClaves(User $actor, User $objetivo): bool
    {
        return $this->gestionar($actor, $objetivo);
    }

    /** Ver y atender las solicitudes de clave de su alcance. */
    public function verSolicitudesClave(User $actor): bool
    {
        return $this->verUsuarios($actor);
    }

    /**
     * Roles que el actor puede asignar al crear o editar una cuenta.
     *
     * @return array<int, string>
     */
    public static function rolesAsignables(User $actor): array
    {
        return $actor->esAdmin() ? User::ROLES : self::ROLES_DEL_PASTOR;
    }
}
