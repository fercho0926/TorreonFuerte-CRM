<?php

namespace App\Support;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use Illuminate\Support\Facades\Gate;

/**
 * Consulta de permisos para las vistas (la app usa sesión propia, no Auth de Laravel).
 * Es lo que usa la directiva @puede y los componentes de presentación.
 */
class Permisos
{
    /** ¿El rol del usuario llega al nivel indicado («lectura» o «escritura») en el módulo? */
    public static function modulo(string $modulo, string $nivel = 'lectura'): bool
    {
        $user = Controller::usuarioDeLaPeticion();

        return $user && $user->nivelEn($modulo) >= \App\Support\Niveles::aNumero($nivel);
    }

    public static function puede(string $habilidad, mixed $argumento = Persona::class): bool
    {
        $user = Controller::usuarioDeLaPeticion();

        return $user && Gate::forUser($user)->allows($habilidad, $argumento);
    }
}
