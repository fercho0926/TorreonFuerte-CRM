<?php

namespace App\Http\Requests;

use App\Services\SedeContext;
use App\Models\Persona;
use App\Support\Celular;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Reglas compartidas por el formulario único de miembro (con o sin rol de liderazgo).
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

        foreach (['celular', 'email', 'lider_id'] as $campo) {
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
        $conCuenta = $persona?->cuenta()->exists() ?? false;

        return self::reglasDeAlta() + [
            'estado' => ['required', Rule::in(array_keys(Persona::ESTADOS))],
            'celular' => array_merge(
                ['nullable', 'string', 'max:20'],
                $creando ? ['required_without:email'] : [],
                // Con cuenta, el celular es el de la cuenta (claves por WhatsApp) y no se comparte (spec 016, RF-009).
                $conCuenta ? ['required', self::celularLibreEntreCuentas($persona)] : [],
            ),
            'email' => array_merge(['nullable', 'email', 'max:255'], $creando ? ['required_without:celular'] : []),
            'lider_id' => ['nullable', app(SedeContext::class)->existe('personas')->where('es_lider', true)->whereNull('deleted_at')],
            'es_lider' => ['boolean'],
            'confirmar_duplicado' => ['boolean'],
        ];
    }

    /** Rechaza un celular que ya es el de otro miembro con cuenta de usuario. */
    public static function celularLibreEntreCuentas(?Persona $persona): \Closure
    {
        return function (string $atributo, mixed $valor, \Closure $fallar) use ($persona) {
            $clave = Celular::clave((string) $valor);

            $ocupado = $clave !== null && Persona::withoutGlobalScopes()
                ->where('celular_clave', $clave)
                ->when($persona, fn ($q) => $q->whereKeyNot($persona->id))
                ->whereHas('cuenta')
                ->exists();

            if ($ocupado) {
                $fallar('Ese celular ya es el de la cuenta de otro miembro.');
            }
        };
    }

    /**
     * Datos mínimos para registrar a un miembro desde otro formulario (por ejemplo, «Nuevo usuario»,
     * spec 016). El celular, el correo y el estado los pone quien llama.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function reglasDeAlta(string $prefijo = ''): array
    {
        return [
            $prefijo.'nombres' => ['required', 'string', 'max:255'],
            $prefijo.'apellidos' => ['required', 'string', 'max:255'],
            $prefijo.'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            $prefijo.'direccion' => ['nullable', 'string', 'max:255'],
            $prefijo.'barrio' => ['nullable', 'string', 'max:255'],
            $prefijo.'ciudad' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'celular.required_without' => 'Indica al menos un medio de contacto: celular o correo.',
            'email.required_without' => 'Indica al menos un medio de contacto: celular o correo.',
            'lider_id.exists' => 'El líder elegido no existe o ya no es líder.',
        ];
    }
}
