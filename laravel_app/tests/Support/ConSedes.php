<?php

namespace Tests\Support;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;

/**
 * Utilitarios para pruebas con varias sedes. Requiere el trait IniciaSesion.
 */
trait ConSedes
{
    protected function crearSede(array $atributos = []): Sede
    {
        return Sede::factory()->create($atributos);
    }

    protected function comoPastorDe(Sede $sede): User
    {
        $persona = Persona::factory()->deSede($sede->id)->create();

        return $this->iniciarSesionComo($this->crearUsuario('pastor', $persona, $sede->id));
    }

    protected function comoLiderDe(Sede $sede, ?Persona $persona = null): User
    {
        $persona ??= Persona::factory()->deSede($sede->id)->lider()->create();

        return $this->iniciarSesionComo($this->crearUsuario('lider_celula', $persona, $sede->id));
    }

    protected function comoAdminEnSede(Sede $sede): User
    {
        return $this->iniciarSesionComo($this->crearUsuario('admin'), $sede->id);
    }

    /**
     * Admin sin sede activa ("Todas las sedes": solo consulta).
     */
    protected function comoAdminEnTodas(): User
    {
        $user = $this->crearUsuario('admin');
        $this->withSession(['is_logged_in' => true, 'user_id' => $user->id, 'user_name' => $user->name]);

        return $user;
    }
}
