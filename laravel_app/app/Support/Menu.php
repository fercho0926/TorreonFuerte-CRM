<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Menú lateral y migas de pan a partir de config/menu.php: filtra por permisos, marca la opción
 * activa y construye las migas por defecto (Inicio › sección › opción › pantalla).
 */
class Menu
{
    /**
     * Secciones con solo las opciones que el usuario puede usar. Cada opción añade `activa`,
     * `url` e `insignia` (contador, si aplica).
     *
     * @param  array<string, int>  $insignias
     * @return array<int, array<string, mixed>>
     */
    public static function secciones(?User $user, array $insignias = [], ?string $rutaActual = null): array
    {
        $rutaActual ??= request()->route()?->getName();
        $secciones = [];

        foreach (config('menu.secciones', []) as $seccion) {
            $opciones = [];

            foreach ($seccion['opciones'] as $opcion) {
                if (!self::permitida($user, $opcion)) {
                    continue;
                }

                $opcion['activa'] = self::activa($opcion, $rutaActual);
                $opcion['url'] = route($opcion['ruta']);
                $opcion['insignia'] = isset($opcion['insignia']) ? (int) ($insignias[$opcion['insignia']] ?? 0) : 0;
                $opciones[] = $opcion;
            }

            if ($opciones) {
                $secciones[] = ['clave' => $seccion['clave'], 'titulo' => $seccion['titulo'], 'opciones' => $opciones,
                    'abierta' => collect($opciones)->contains('activa', true)];
            }
        }

        return $secciones;
    }

    /**
     * Migas por defecto. Cada tramo: ['texto' => ..., 'url' => ?string]; el último no lleva enlace.
     *
     * @param  array<int, array<string, mixed>>  $secciones  resultado de secciones()
     * @return array<int, array{texto: string, url: ?string}>
     */
    public static function migas(array $secciones, ?string $titulo = null): array
    {
        $migas = [['texto' => 'Inicio', 'url' => route('dashboard')]];

        foreach ($secciones as $seccion) {
            foreach ($seccion['opciones'] as $opcion) {
                if (!$opcion['activa'] || $opcion['ruta'] === 'dashboard') {
                    continue;
                }

                if ($seccion['titulo']) {
                    $migas[] = ['texto' => $seccion['titulo'], 'url' => null];
                }
                $migas[] = ['texto' => $opcion['etiqueta'], 'url' => $opcion['url']];
            }
        }

        $ultima = end($migas);
        if ($titulo && $titulo !== $ultima['texto'] && !($ultima['texto'] === 'Inicio' && $titulo === 'Dashboard')) {
            $migas[] = ['texto' => $titulo, 'url' => null];
        }

        $migas[array_key_last($migas)]['url'] = null;

        return $migas;
    }

    /** @param  array<string, mixed>  $opcion */
    private static function permitida(?User $user, array $opcion): bool
    {
        if (!$user) {
            return false;
        }

        // El módulo de la opción exige al menos lectura (spec 013).
        if (!empty($opcion['modulo']) && $user->nivelEn($opcion['modulo']) < \App\Support\Niveles::LECTURA) {
            return false;
        }

        if (empty($opcion['permiso'])) {
            return true;
        }

        [$habilidad, $clase] = $opcion['permiso'] + [1 => null];

        return Gate::forUser($user)->allows($habilidad, $clase);
    }

    /** @param  array<string, mixed>  $opcion */
    private static function activa(array $opcion, ?string $rutaActual): bool
    {
        if ($rutaActual === null) {
            return false;
        }

        foreach ($opcion['activa_en'] ?? [$opcion['ruta']] as $patron) {
            if (fnmatch($patron, $rutaActual)) {
                return true;
            }
        }

        return false;
    }
}
