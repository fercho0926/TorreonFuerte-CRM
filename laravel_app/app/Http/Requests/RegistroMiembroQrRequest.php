<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\RedirigeAlFormularioPublico;
use App\Models\Persona;
use App\Models\Sede;
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
    use RedirigeAlFormularioPublico;

    public function authorize(): bool
    {
        return true;
    }

    protected function rutaPaso1(): string
    {
        return 'registro-sede.show';
    }

    protected function prepareForValidation(): void
    {
        foreach (['numero_documento', 'tipo_documento', 'estado_inicial', 'lider_id', 'email', 'barrio', 'ciudad'] as $campo) {
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
            'tipo_documento' => ['required', Rule::in(Persona::TIPOS_DOCUMENTO)],
            'numero_documento' => ['required', 'string', 'max:30'],
            'estado_inicial' => ['nullable', Rule::in(RegistroMiembrosQr::ESTADOS_INICIALES)],
            'lider_id' => [
                'nullable', 'required_if:estado_inicial,miembro_activo',
                Rule::exists('personas', 'id')->where(function ($q) {
                    $sede = Sede::where('registro_token', (string) $this->route('token'))->first();

                    $q->where('sede_id', $sede?->id)->where('es_lider', true)->whereNull('deleted_at');
                }),
            ],
            'email' => ['nullable', 'email', 'max:255'],
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
            'tipo_documento.required' => 'Elige el tipo de documento.',
            'tipo_documento.in' => 'Elige un tipo de documento válido.',
            'numero_documento.required' => 'Escribe tu número de documento.',
            'estado_inicial.in' => 'Elige si te registras como visitante o como miembro activo.',
            'lider_id.required_if' => 'Elige quién es tu líder.',
            'lider_id.exists' => 'El líder elegido no es válido.',
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
        ];
    }
}
