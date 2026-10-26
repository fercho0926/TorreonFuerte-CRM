<?php

namespace App\Policies;

use App\Models\User;
use App\Services\AutorizacionRoles;
use App\Support\Modulos;

/**
 * Gestión de cuentas (spec 013): el administrador gestiona todas; cualquier otro usuario con escritura en
 * Usuarios gestiona las de su sede y solo con roles que puede asignar (marcados «asignables» y sin más
 * permisos ni alcance que él). Nadie cambia su propia cuenta desde aquí.
 */
class UserPolicy
{
    private AutorizacionRoles $autorizacion;

    public function __construct(?AutorizacionRoles $autorizacion = null)
    {
        $this->autorizacion = $autorizacion ?? app(AutorizacionRoles::class);
    }

    public function verUsuarios(User $actor): bool
    {
        return $this->autorizacion->puedeLeer($actor, Modulos::USUARIOS);
    }

    public function crear(User $actor): bool
    {
        return $this->autorizacion->puedeEscribir($actor, Modulos::USUARIOS);
    }

    public function gestionar(User $actor, User $objetivo): bool
    {
        if ($actor->esAdmin()) {
            return true;
        }

        $rol = $this->autorizacion->rolDe($objetivo);

        return $this->crear($actor)
            && $actor->sede_id !== null
            && $objetivo->sede_id === $actor->sede_id
            && $actor->id !== $objetivo->id
            && $rol !== null
            && $this->autorizacion->puedeAsignar($actor, $rol);
    }

    /**
     * Generar y enviar una clave temporal (también al atender una solicitud): quien tiene escritura en Usuarios o en
     * Solicitudes de clave, en su sede, para cuentas cuyo rol está dentro de su techo y nunca la propia.
     */
    public function gestionarClaves(User $actor, User $objetivo): bool
    {
        if ($actor->esAdmin()) {
            return true;
        }

        $rol = $this->autorizacion->rolDe($objetivo);

        return ($this->crear($actor) || $this->autorizacion->puedeEscribir($actor, Modulos::SOLICITUDES_CLAVE))
            && $actor->sede_id !== null
            && $objetivo->sede_id === $actor->sede_id
            && $actor->id !== $objetivo->id
            && $rol !== null
            && $this->autorizacion->dentroDelTecho($actor, $rol);
    }

    /** Ver y atender las solicitudes de clave de su alcance. */
    public function verSolicitudesClave(User $actor): bool
    {
        return $this->autorizacion->puedeLeer($actor, Modulos::SOLICITUDES_CLAVE);
    }

    /**
     * Claves de los roles que el actor puede asignar al crear o editar una cuenta.
     *
     * @return array<int, string>
     */
    public static function rolesAsignables(User $actor): array
    {
        return app(AutorizacionRoles::class)->rolesAsignables($actor)->pluck('clave')->all();
    }
}
