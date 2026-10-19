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
    public static function puede(string $habilidad, mixed $argumento = Persona::class): bool
    {
        $user = Controller::usuarioDeLaPeticion();

        return $user && Gate::forUser($user)->allows($habilidad, $argumento);
    }
}
