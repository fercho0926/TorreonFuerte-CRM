<?php

namespace App\Policies;

use App\Models\Sede;
use App\Models\User;

/**
 * Módulo de sedes: exclusivo del administrador del sistema.
 */
class SedePolicy
{
    public function ver(User $user, ?Sede $sede = null): bool
    {
        return $user->esAdmin();
    }

    public function crear(User $user): bool
    {
        return $user->esAdmin();
    }

    public function editar(User $user, ?Sede $sede = null): bool
    {
        return $user->esAdmin();
    }

    public function desactivar(User $user, ?Sede $sede = null): bool
    {
        return $user->esAdmin();
    }

    public function eliminar(User $user, ?Sede $sede = null): bool
    {
        return $user->esAdmin();
    }

    public function trasladar(User $user, ?Sede $sede = null): bool
    {
        return $user->esAdmin();
    }
}
