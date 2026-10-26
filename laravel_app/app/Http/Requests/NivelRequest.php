<?php

namespace App\Http\Requests;

use App\Models\NivelFormacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** Datos de un nivel del plan (spec 014): nombre único en todo el plan y descripción opcional. */
class NivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $propio = $this->route('nivel');
        $propioId = $propio instanceof NivelFormacion ? $propio->id : null;

        return [
            'nombre' => ['required', 'string', 'max:80', function (string $atributo, mixed $valor, Closure $fallo) use ($propioId) {
                $repetido = NivelFormacion::query()
                    ->whereRaw('lower(nombre) = ?', [mb_strtolower(trim((string) $valor))])
                    ->when($propioId, fn ($q) => $q->where('id', '!=', $propioId))
                    ->exists();

                if ($repetido) {
                    $fallo('Ya existe un nivel con ese nombre.');
                }
            }],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['nombre' => 'nombre', 'descripcion' => 'descripción'];
    }
}
