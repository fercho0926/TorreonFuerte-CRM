<?php

namespace App\Policies;

use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;

class PersonaPolicy
{
    public function __construct(private AlcanceService $alcance)
    {
    }

    public function ver(User $user, Persona $persona): bool
    {
        return $this->alcance->puedeVerPersona($user, $persona);
    }

    public function crear(User $user): bool
    {
        return in_array($user->role, ['admin', 'pastor', 'lider_celula'], true);
    }

    public function editar(User $user, Persona $persona): bool
    {
        return $this->crear($user) && $this->ver($user, $persona);
    }

    public function inactivar(User $user, Persona $persona): bool
    {
        return $this->editar($user, $persona);
    }

    public function designarLider(User $user): bool
    {
        return in_array($user->role, ['admin', 'pastor'], true);
    }

    public function verSinLider(User $user): bool
    {
        return $this->designarLider($user);
    }

    public function eliminar(User $user, Persona $persona): bool
    {
        return $user->isAdmin();
    }

    public function gestionarCatalogos(User $user): bool
    {
        return $user->tieneAccesoTotal();
    }

    public function verBitacora(User $user): bool
    {
        return $user->tieneAccesoTotal();
    }
}
