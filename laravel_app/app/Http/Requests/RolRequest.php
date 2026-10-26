<?php

namespace App\Http\Requests;

use App\Models\Rol;
use App\Support\Modulos;
use App\Support\Niveles;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Formulario de rol (spec 013): nombre único sin distinguir mayúsculas ni acentos, alcance y un nivel por módulo.
 * Solo el administrador puede enviarlo.
 */
class RolRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = \App\Http\Controllers\Controller::usuarioDeLaPeticion();

        $rol = $this->route('rol');

        return $usuario !== null
            && Gate::forUser($usuario)->allows('administrador')
            && !($rol instanceof Rol && $rol->esAdministrador());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => preg_replace('/\s+/', ' ', trim((string) $this->input('nombre'))),
            'descripcion' => ($d = trim((string) $this->input('descripcion'))) === '' ? null : $d,
            'asignable' => $this->boolean('asignable'),
        ]);
    }

    public function rules(): array
    {
        /** @var Rol|null $rol */
        $rol = $this->route('rol');

        $reglas = [
            'nombre' => ['required', 'string', 'max:60', $this->nombreUnico($rol?->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'alcance' => ['required', Rule::in(array_keys(Niveles::ALCANCES))],
            'asignable' => ['boolean'],
            'permisos' => ['nullable', 'array', $this->sinModulosDesconocidos()],
        ];

        foreach (Modulos::claves() as $modulo) {
            $reglas['permisos.'.$modulo] = ['nullable', 'integer', 'between:0,'.Modulos::nivelMaximo($modulo)];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Escribe el nombre del rol.',
            'nombre.max' => 'El nombre no puede pasar de 60 caracteres.',
            'descripcion.max' => 'La descripción no puede pasar de 255 caracteres.',
            'alcance.required' => 'Elige el alcance de datos del rol.',
            'alcance.in' => 'Elige un alcance válido.',
            'permisos.*.between' => 'El nivel elegido no es válido para este módulo.',
            'permisos.*.integer' => 'El nivel elegido no es válido.',
        ];
    }

    /**
     * Niveles por módulo del catálogo; los módulos ausentes quedan sin acceso.
     *
     * @return array<string, int>
     */
    public function niveles(): array
    {
        $enviados = (array) $this->input('permisos', []);
        $niveles = [];

        foreach (Modulos::claves() as $modulo) {
            $niveles[$modulo] = (int) ($enviados[$modulo] ?? Niveles::SIN_ACCESO);
        }

        return $niveles;
    }

    /** El nombre no se repite aunque cambien mayúsculas o acentos. */
    private function nombreUnico(?int $ignorarId): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fallar) use ($ignorarId): void {
            $buscado = Str::lower(Str::ascii((string) $valor));

            $repetido = Rol::query()
                ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->pluck('nombre')
                ->contains(fn (string $nombre) => Str::lower(Str::ascii($nombre)) === $buscado);

            if ($repetido) {
                $fallar('Ya existe un rol con ese nombre.');
            }
        };
    }

    /** Solo se aceptan módulos del catálogo (Sedes y Roles son exclusivos del administrador). */
    private function sinModulosDesconocidos(): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fallar): void {
            $desconocidos = array_diff(array_keys((array) $valor), Modulos::claves());

            if ($desconocidos) {
                $fallar('Hay módulos que no se pueden configurar: '.implode(', ', $desconocidos).'.');
            }
        };
    }
}
