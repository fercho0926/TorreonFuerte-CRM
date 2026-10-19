<?php

namespace App\Support;

/**
 * Normalización de celulares para llamadas y WhatsApp: solo dígitos y, si son exactamente 10,
 * se antepone el indicativo de Colombia (57) para los enlaces wa.me.
 */
class Celular
{
    /** Solo los dígitos del celular, o nulo si no hay ninguno. */
    public static function limpio(?string $celular): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $celular);

        return $digitos !== '' ? $digitos : null;
    }

    /** Número para wa.me: 10 dígitos → «57» + dígitos; en otro caso, los dígitos tal cual. */
    public static function whatsapp(?string $celular): ?string
    {
        $digitos = self::limpio($celular);

        return $digitos !== null && strlen($digitos) === 10 ? '57'.$digitos : $digitos;
    }

    /** Un celular válido para WhatsApp: entre 10 y 15 dígitos (con o sin indicativo). */
    public static function esValido(?string $celular): bool
    {
        $largo = strlen(self::limpio($celular) ?? '');

        return $largo >= 10 && $largo <= 15;
    }

    /** Regla de validación para formularios. */
    public static function regla(): \Closure
    {
        return function (string $atributo, mixed $valor, \Closure $fallar): void {
            if (!self::esValido((string) $valor)) {
                $fallar('El celular debe tener al menos 10 dígitos (por ejemplo 310 555 9999).');
            }
        };
    }

    /** Formato legible para verificar el número: «310 555 9999» (10 dígitos) o los dígitos sin más. */
    public static function formatoLegible(?string $celular): string
    {
        $digitos = self::limpio($celular) ?? '';

        if (strlen($digitos) === 10) {
            return substr($digitos, 0, 3).' '.substr($digitos, 3, 3).' '.substr($digitos, 6);
        }

        return $digitos;
    }
}
