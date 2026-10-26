<?php

namespace App\Policies;

use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;
use App\Services\AutorizacionRoles;
use App\Support\Modulos;

/**
 * Acciones sobre miembros (spec 013): el nivel del rol en el módulo decide si puede ver o cambiar, y el alcance
 * de datos del rol decide sobre qué registros. Eliminar queda reservado al administrador.
 */
class PersonaPolicy
{
    public function __construct(private AlcanceService $alcance, private AutorizacionRoles $autorizacion)
    {
    }

    public function ver(User $user, Persona $persona): bool
    {
        return $this->autorizacion->puedeLeer($user, Modulos::MIEMBROS) && $this->alcance->puedeVerPersona($user, $persona);
    }

    public function crear(User $user): bool
    {
        return $this->autorizacion->puedeEscribir($user, Modulos::MIEMBROS);
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
        return $this->autorizacion->puedeEscribir($user, Modulos::LIDERES);
    }

    public function verSinLider(User $user): bool
    {
        return $this->autorizacion->puedeLeer($user, Modulos::SIN_LIDER);
    }

    /** Reservada al administrador, y nunca sobre un miembro con cuenta de usuario (spec 016, RF-010). */
    public function eliminar(User $user, Persona $persona): bool
    {
        return $user->esAdmin() && !$persona->cuenta()->exists();
    }

    public function gestionarCatalogos(User $user): bool
    {
        return $user->tieneAccesoTotal();
    }
}
