<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

/**
 * El paso 2 (confirmación) de un formulario público por QR se muestra como respuesta directa de un
 * `POST` a «buscar», sin tener su propia URL por `GET`. El `failedValidation()` por defecto de Laravel
 * redirige al `Referer` de la solicitud —esa misma URL de solo-POST— y el navegador la vuelve a pedir
 * por `GET`, lo que produce un 405 (RF-006). Se redirige en su lugar al paso 1, que sí acepta `GET`.
 */
trait RedirigeAlFormularioPublico
{
    /** Nombre de la ruta (GET) del paso 1 de este formulario público. Ej.: "registro.show". */
    abstract protected function rutaPaso1(): string;

    protected function failedValidation(Validator $validator): void
    {
        throw (new ValidationException($validator))
            ->redirectTo(route($this->rutaPaso1(), $this->route('token')))
            ->errorBag($this->errorBag);
    }
}
