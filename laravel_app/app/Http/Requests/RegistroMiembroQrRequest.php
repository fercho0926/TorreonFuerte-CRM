<?php

namespace App\Http\Requests;

use App\Models\Persona;
use App\Services\RegistroMiembrosQr;
use App\Support\Celular;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos del formulario público del QR de miembros (sin sesión: la autorización es el token de la ruta).
 * `estado_inicial` solo se exige en el servicio cuando quien se registra es nuevo en la sede.
 */
class RegistroMiembroQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['numero_documento', 'tipo_documento', 'estado_inicial', 'email', 'direccion', 'barrio', 'ciudad'] as $campo) {
            if ($this->has($campo)) {
                $valor = trim((string) $this->input($campo));
                $this->merge([$campo => $valor === '' ? null : $valor]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'celular' => ['required', 'string', 'max:20', Celular::regla()],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:1900-01-01'],
            'tipo_documento' => ['nullable', 'required_with:numero_documento', Rule::in(Persona::TIPOS_DOCUMENTO)],
            'numero_documento' => ['nullable', 'string', 'max:30'],
            'estado_inicial' => ['nullable', Rule::in(RegistroMiembrosQr::ESTADOS_INICIALES)],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'celular.required' => 'Escribe tu celular.',
            'nombres.required' => 'Escribe tus nombres.',
            'nombres.max' => 'Los nombres no pueden pasar de 100 caracteres.',
            'apellidos.required' => 'Escribe tus apellidos.',
            'apellidos.max' => 'Los apellidos no pueden pasar de 100 caracteres.',
            'fecha_nacimiento.required' => 'Escribe tu fecha de nacimiento.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'fecha_nacimiento.after_or_equal' => 'La fecha de nacimiento no es válida.',
            'tipo_documento.required_with' => 'Elige el tipo de documento.',
            'tipo_documento.in' => 'Elige un tipo de documento válido.',
            'estado_inicial.in' => 'Elige si te registras como visitante o como miembro activo.',
            'email.email' => 'Escribe un correo válido (por ejemplo ana@correo.com).',
        ];
    }

    public function attributes(): array
    {
        return [
            'celular' => 'celular',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'numero_documento' => 'cédula',
            'email' => 'correo',
            'direccion' => 'dirección',
        ];
    }
}
