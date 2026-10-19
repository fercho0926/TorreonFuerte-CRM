<?php

namespace App\Http\Requests;

use App\Services\SedeContext;
use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Reglas compartidas por el formulario único de persona (con o sin rol de liderazgo).
 */
class PersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización por rol se hace en el controlador (sesión propia, no Auth).
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'es_lider' => $this->boolean('es_lider'),
            'confirmar_duplicado' => $this->boolean('confirmar_duplicado'),
        ]);

        foreach (['numero_documento', 'tipo_documento', 'celular', 'email', 'lider_id'] as $campo) {
            if ($this->has($campo) && trim((string) $this->input($campo)) === '') {
                $this->merge([$campo => null]);
            }
        }
    }

    public function rules(): array
    {
        /** @var Persona|null $persona */
        $persona = $this->route('persona');
        $creando = !$persona;

        return [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'estado' => ['required', Rule::in(array_keys(Persona::ESTADOS))],
            'celular' => array_merge(['nullable', 'string', 'max:20'], $creando ? ['required_without:email'] : []),
            'email' => array_merge(['nullable', 'email', 'max:255'], $creando ? ['required_without:celular'] : []),
            'tipo_documento' => ['nullable', 'required_with:numero_documento', 'required_if:estado,miembro_activo', 'required_if:es_lider,1', Rule::in(Persona::TIPOS_DOCUMENTO)],
            'numero_documento' => [
                'nullable',
                'required_if:estado,miembro_activo',
                'required_if:es_lider,1',
                'string',
                'max:50',
                app(SedeContext::class)->unico('personas', 'numero_documento')->ignore($persona?->id),
            ],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'lider_id' => ['nullable', app(SedeContext::class)->existe('personas')->where('es_lider', true)->whereNull('deleted_at')],
            'es_lider' => ['boolean'],
            'confirmar_duplicado' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'celular.required_without' => 'Indica al menos un medio de contacto: celular o correo.',
            'email.required_without' => 'Indica al menos un medio de contacto: celular o correo.',
            'numero_documento.required_if' => 'El documento es obligatorio para miembros activos y líderes.',
            'tipo_documento.required_if' => 'El tipo de documento es obligatorio para miembros activos y líderes.',
            'lider_id.exists' => 'El líder elegido no existe o ya no es líder.',
        ];
    }
}
