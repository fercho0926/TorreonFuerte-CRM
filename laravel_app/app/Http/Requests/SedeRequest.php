<?php

namespace App\Http\Requests;

use App\Models\Sede;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos de una sede y, al crearla, de su pastor y su pastora (opcional).
 */
class SedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el administrador (sesión propia, no Auth). Se comprueba antes de validar para que
        // un usuario sin permiso reciba 403 y no errores de validación.
        return (bool) \App\Http\Controllers\Controller::usuarioDeLaPeticion()?->esAdmin();
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'No tienes permiso para esta acción');
    }

    protected function prepareForValidation(): void
    {
        // Si la pastora viene totalmente vacía se interpreta como "sin pastora".
        $pastora = (array) $this->input('pastora', []);
        if (collect($pastora)->every(fn ($v) => trim((string) $v) === '')) {
            $this->request->remove('pastora');
        }
    }

    public function rules(): array
    {
        /** @var Sede|null $sede */
        $sede = $this->route('sede');

        $reglas = [
            'nombre' => ['required', 'string', 'max:255', Rule::unique('sedes', 'nombre')->ignore($sede?->id)],
            'ciudad' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ];

        if ($sede) {
            return $reglas;
        }

        return $reglas + $this->reglasDeCuenta('pastor', true) + $this->reglasDeCuenta('pastora', false) + [
            'pastora.email' => ['required_with:pastora', 'email', 'max:255', 'unique:users,email', 'different:pastor.email'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function reglasDeCuenta(string $prefijo, bool $obligatoria): array
    {
        $requerido = $obligatoria ? 'required' : 'required_with:'.$prefijo;

        return [
            $prefijo => $obligatoria ? ['required', 'array'] : ['nullable', 'array'],
            "{$prefijo}.nombres" => [$requerido, 'string', 'max:255'],
            "{$prefijo}.apellidos" => [$requerido, 'string', 'max:255'],
            "{$prefijo}.email" => [$requerido, 'email', 'max:255', 'unique:users,email'],
            "{$prefijo}.password" => [$requerido, 'string', 'min:8'],
            "{$prefijo}.celular" => ['nullable', 'string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        $atributos = [];
        foreach (['pastor' => 'del pastor', 'pastora' => 'de la pastora'] as $clave => $de) {
            $atributos["{$clave}.nombres"] = "nombres {$de}";
            $atributos["{$clave}.apellidos"] = "apellidos {$de}";
            $atributos["{$clave}.email"] = "correo {$de}";
            $atributos["{$clave}.password"] = "contraseña {$de}";
            $atributos["{$clave}.celular"] = "celular {$de}";
        }

        return $atributos + ['nombre' => 'nombre de la sede', 'ciudad' => 'ciudad'];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe una sede con ese nombre.',
            'pastor.email.unique' => 'Ese correo ya está en uso por otro usuario.',
            'pastora.email.unique' => 'Ese correo ya está en uso por otro usuario.',
            'pastora.email.different' => 'La pastora debe tener un correo distinto al del pastor.',
        ];
    }
}
