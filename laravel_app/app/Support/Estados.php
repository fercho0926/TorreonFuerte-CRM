<?php

namespace App\Support;

/**
 * Esquema único de estados para las insignias de toda la interfaz: etiqueta y color de Bootstrap.
 * Todo estado se dibuja con <x-ui.estado>, que usa esta clase.
 */
class Estados
{
    /** @var array<string, array{0: string, 1: string}> clave => [etiqueta, color] */
    private const MAPA = [
        // Éxito
        'activo' => ['Activo', 'success'],
        'activa' => ['Activa', 'success'],
        'miembro_activo' => ['Miembro activo', 'success'],
        'realizado' => ['Realizado', 'success'],
        'pagado' => ['Pagado', 'success'],
        'si' => ['Sí', 'success'],
        // Información
        'planificado' => ['Planificado', 'info'],
        'en_curso' => ['En curso', 'info'],
        'nuevo_creyente' => ['Nuevo creyente', 'info'],
        // Atención
        'visitante' => ['Visitante', 'warning'],
        'pendiente' => ['Pendiente', 'warning'],
        // Neutro
        'inactivo' => ['Inactivo', 'secondary'],
        'inactiva' => ['Inactiva', 'secondary'],
        'no' => ['No', 'secondary'],
        // Peligro
        'cancelado' => ['Cancelado', 'danger'],
        // Rol
        'lider' => ['Líder', 'primary'],
    ];

    public static function color(?string $estado): string
    {
        return self::MAPA[self::clave($estado)][1] ?? 'secondary';
    }

    public static function etiqueta(?string $estado): string
    {
        return self::MAPA[self::clave($estado)][0] ?? (string) ucfirst(str_replace('_', ' ', (string) $estado));
    }

    private static function clave(?string $estado): string
    {
        return strtolower((string) $estado);
    }
}
