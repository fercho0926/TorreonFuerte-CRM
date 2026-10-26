<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AlcanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

abstract class Controller
{
    public const MENSAJE_SIN_PERMISO = 'No tienes permiso para esta acción';

    protected function ensureLoggedIn(): ?RedirectResponse
    {
        if (!Session::get('is_logged_in')) {
            return redirect()->route('login');
        }

        return null;
    }

    protected function ensureIsAdmin(): ?RedirectResponse
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return null;
    }

    /**
     * Restringe la gestión (crear/editar/eliminar) de estructura de liderazgo
     * (células, etc.) a quienes tienen visión amplia: admin o pastor.
     */
    protected function ensurePuedeGestionarEstructura(): ?RedirectResponse
    {
        $user = $this->usuarioActual();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$this->puedeGestionarEstructura()) {
            return redirect()->route('dashboard');
        }

        return null;
    }

    /**
     * Crear, editar y eliminar células (estructura) exige escritura en Células y alcance de toda la sede; agregar
     * miembros y registrar reuniones basta con escritura dentro del alcance del rol.
     */
    protected function puedeGestionarEstructura(): bool
    {
        $user = $this->usuarioActual();

        return $user !== null && $user->tieneAccesoTotal() && $user->nivelEn(\App\Support\Modulos::CELULAS) >= \App\Support\Niveles::ESCRITURA;
    }

    /**
     * Usuario autenticado; se consulta una sola vez por petición.
     */
    protected function usuarioActual(): ?User
    {
        return self::usuarioDeLaPeticion();
    }

    public static function usuarioDeLaPeticion(): ?User
    {
        $atributos = request()->attributes;
        $userId = Session::get('user_id');

        if ($atributos->has('usuario_actual') && $atributos->get('usuario_actual_id') === $userId) {
            return $atributos->get('usuario_actual');
        }

        // Se carga sin filtro de sede: el propio filtro necesita saber quién es el usuario.
        $user = $userId
            ? User::withoutGlobalScopes()->with(['persona' => fn ($q) => $q->withoutGlobalScopes()])->find($userId)
            : null;
        $atributos->set('usuario_actual', $user);
        $atributos->set('usuario_actual_id', $userId);

        return $user;
    }

    /**
     * Ids de los miembros que el rol del usuario puede ver, o nulo si ve toda la sede (sin filtrar). Se usa para
     * listar solo esos miembros dentro de módulos sin dueño (eventos, encuentros, ministerios).
     *
     * @return array<int, int>|null
     */
    protected function idsDePersonasVisibles(): ?array
    {
        $user = $this->usuarioActual();

        if (!$user || $user->tieneAccesoTotal()) {
            return null;
        }

        return $this->alcance()->personasVisibles($user)->pluck('personas.id')->all();
    }

    protected function alcance(): AlcanceService
    {
        return app(AlcanceService::class);
    }

    protected function puede(string $habilidad, mixed $argumento = Persona::class): bool
    {
        $user = $this->usuarioActual();
        if (!$user) {
            return false;
        }

        return Gate::forUser($user)->allows($habilidad, $argumento);
    }

    /**
     * Responde 403 con un mensaje claro cuando el usuario no tiene la habilidad.
     */
    protected function autorizar(string $habilidad, mixed $argumento = Persona::class): void
    {
        abort_unless($this->puede($habilidad, $argumento), 403, self::MENSAJE_SIN_PERMISO);
    }
}
