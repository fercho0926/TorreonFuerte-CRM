<?php

namespace Tests\Support;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use Database\Factories\SedeFactory;

/**
 * Simula la sesión propia de la aplicación (no usa el Auth de Laravel).
 * Los usuarios que no son admin se crean en la sede de prueba por defecto; el admin inicia con
 * esa sede como sede activa (salvo que se use `comoAdminEnTodas()`).
 */
trait IniciaSesion
{
    protected function iniciarSesionComo(User $user, ?int $sedeActivaId = null): User
    {
        $sesion = ['is_logged_in' => true, 'user_id' => $user->id, 'user_name' => $user->name];

        if ($user->esAdmin()) {
            $sesion['sede_activa_id'] = $sedeActivaId ?? SedeFactory::idPorDefecto();
        }

        $this->withSession($sesion);

        return $user;
    }

    protected function crearUsuario(string $rol, ?Persona $persona = null, ?int $sedeId = null): User
    {
        // La cuenta de pastor es la del pastor de la sede si esta aún no tiene uno (spec 016: es la cabeza).
        if ($persona === null && $rol === 'pastor') {
            $sede = Sede::query()->withoutGlobalScopes()->findOrFail($sedeId ?? SedeFactory::idPorDefecto());
            $persona = $sede->pastor_persona_id ? null : Persona::factory()->pastorDe($sede)->create();
        }

        $atributos = ['role' => $rol, 'persona_id' => $persona?->id];

        if ($sedeId !== null && $rol !== 'admin') {
            $atributos['sede_id'] = $sedeId;
        } elseif ($persona !== null && $rol !== 'admin') {
            $atributos['sede_id'] = $persona->sede_id;
        }

        // Un miembro tiene una sola cuenta (spec 016): si ya la tiene, se usa esa con el rol pedido.
        if ($persona !== null && ($existente = User::withoutGlobalScopes()->where('persona_id', $persona->id)->first())) {
            $existente->forceFill(['role' => $rol, 'sede_id' => $rol === 'admin' ? null : ($atributos['sede_id'] ?? $existente->sede_id)])->save();

            return $existente->fresh();
        }

        return User::factory()->create($atributos);
    }

    protected function comoAdmin(): User
    {
        return $this->iniciarSesionComo($this->crearUsuario('admin'));
    }

    protected function comoPastor(): User
    {
        return $this->iniciarSesionComo($this->crearUsuario('pastor'));
    }

    protected function comoLider(?Persona $persona = null): User
    {
        $persona ??= Persona::factory()->lider()->create();

        return $this->iniciarSesionComo($this->crearUsuario('lider_celula', $persona));
    }
}
