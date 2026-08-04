<?php

namespace App\Http\Controllers;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

abstract class Controller
{
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
     * (células, etc.) a quienes tienen visión amplia: admin, pastor o líder de zona.
     */
    protected function ensurePuedeGestionarEstructura(): ?RedirectResponse
    {
        $user = $this->usuarioActual();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->tieneAccesoTotal() && !$user->isLiderZona()) {
            return redirect()->route('dashboard');
        }

        return null;
    }

    protected function usuarioActual(): ?User
    {
        $userId = Session::get('user_id');

        return $userId ? User::with('persona')->find($userId) : null;
    }

    /**
     * IDs de personas que el usuario autenticado puede ver, según su rol en la
     * jerarquía (pastor/admin ven todo, líder de zona ve sus células, líder de
     * célula ve solo la suya). Devuelve null cuando no hay restricción.
     *
     * @return array<int>|null
     */
    protected function personasVisiblesIds(): ?array
    {
        $user = $this->usuarioActual();

        if (!$user || $user->tieneAccesoTotal()) {
            return null;
        }

        if (!$user->persona_id) {
            return [];
        }

        $miPersonaId = $user->persona_id;

        if ($user->isLiderCelula()) {
            $celulas = Celula::where('lider_id', $miPersonaId)
                ->orWhere('co_lider_id', $miPersonaId)
                ->with('miembros:id')
                ->get();

            $ids = $celulas->flatMap(fn (Celula $celula) => $celula->miembros->pluck('id'))
                ->push($miPersonaId)
                ->merge($celulas->pluck('lider_id'))
                ->merge($celulas->pluck('co_lider_id'));

            return $ids->filter()->unique()->values()->all();
        }

        if ($user->isLiderZona()) {
            $liderIds = Persona::where('lider_id', $miPersonaId)->pluck('id');

            $celulas = Celula::whereIn('lider_id', $liderIds)
                ->orWhereIn('co_lider_id', $liderIds)
                ->with('miembros:id')
                ->get();

            $ids = $celulas->flatMap(fn (Celula $celula) => $celula->miembros->pluck('id'))
                ->merge($celulas->pluck('lider_id'))
                ->merge($celulas->pluck('co_lider_id'))
                ->merge($liderIds)
                ->push($miPersonaId);

            return $ids->filter()->unique()->values()->all();
        }

        // Rol 'user' sin liderazgo asignado: solo su propio registro, si existe.
        return [$miPersonaId];
    }

    /**
     * IDs de células visibles para el usuario autenticado. Null = sin restricción.
     *
     * @return array<int>|null
     */
    protected function celulasVisiblesIds(): ?array
    {
        $user = $this->usuarioActual();

        if (!$user || $user->tieneAccesoTotal()) {
            return null;
        }

        if (!$user->persona_id) {
            return [];
        }

        $miPersonaId = $user->persona_id;

        if ($user->isLiderCelula()) {
            return Celula::where('lider_id', $miPersonaId)
                ->orWhere('co_lider_id', $miPersonaId)
                ->pluck('id')
                ->all();
        }

        if ($user->isLiderZona()) {
            $liderIds = Persona::where('lider_id', $miPersonaId)->pluck('id');

            return Celula::whereIn('lider_id', $liderIds)
                ->orWhereIn('co_lider_id', $liderIds)
                ->pluck('id')
                ->all();
        }

        return [];
    }
}
