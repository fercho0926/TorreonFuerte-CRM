<?php

namespace App\Support;

/**
 * Niveles de acceso a un módulo (acumulativos: escritura incluye lectura) y alcances de datos de un rol.
 */
class Niveles
{
    public const SIN_ACCESO = 0;

    public const LECTURA = 1;

    public const ESCRITURA = 2;

    public const ETIQUETAS = [
        self::SIN_ACCESO => 'Sin acceso',
        self::LECTURA => 'Lectura',
        self::ESCRITURA => 'Escritura',
    ];

    public const ALCANCE_SEDE = 'sede';

    public const ALCANCE_RAMA = 'rama';

    public const ALCANCE_REGISTRO = 'registro';

    /** De menor a mayor alcance. */
    public const ALCANCES = [
        self::ALCANCE_REGISTRO => 'Solo su registro',
        self::ALCANCE_RAMA => 'Solo su rama',
        self::ALCANCE_SEDE => 'Toda la sede',
    ];

    public static function ordenAlcance(string $alcance): int
    {
        $posicion = array_search($alcance, array_keys(self::ALCANCES), true);

        return $posicion === false ? 0 : $posicion;
    }

    /** Nivel pedido por nombre («lectura», «escritura») o por número. */
    public static function aNumero(int|string $nivel): int
    {
        return match ($nivel) {
            'lectura', 1, '1' => self::LECTURA,
            'escritura', 2, '2' => self::ESCRITURA,
            default => self::SIN_ACCESO,
        };
    }
}
