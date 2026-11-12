<?php

namespace App\Support;

/**
 * Normaliza texto para que buscar sea insensible a mayúsculas y a tildes/diéresis: "gomez" encuentra
 * "Gómez". Útil tanto en SQL (sobre una columna) como en PHP (sobre el texto que escribe el usuario).
 */
class BusquedaTexto
{
    /** Letra con tilde/diéresis u otro signo => su letra base, en minúscula (incluye mayúsculas). */
    private const EQUIVALENCIAS = [
        'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'Á' => 'a', 'À' => 'a', 'Ä' => 'a', 'Â' => 'a',
        'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'É' => 'e', 'È' => 'e', 'Ë' => 'e', 'Ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'Í' => 'i', 'Ì' => 'i', 'Ï' => 'i', 'Î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'Ó' => 'o', 'Ò' => 'o', 'Ö' => 'o', 'Ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'Ú' => 'u', 'Ù' => 'u', 'Ü' => 'u', 'Û' => 'u',
        'ñ' => 'n', 'Ñ' => 'n',
    ];

    /** El texto tal como lo escribe quien busca: sin tildes y en minúscula. */
    public static function normalizar(?string $texto): string
    {
        return strtr(mb_strtolower((string) $texto), self::EQUIVALENCIAS);
    }

    /** Expresión SQL (SQLite y MySQL) que deja una columna sin tildes y en minúscula, para comparar con `normalizar()`. */
    public static function columnaSql(string $columna): string
    {
        $expresion = $columna;
        foreach (self::EQUIVALENCIAS as $con => $sin) {
            $expresion = "REPLACE({$expresion}, '{$con}', '{$sin}')";
        }

        return "LOWER({$expresion})";
    }
}
