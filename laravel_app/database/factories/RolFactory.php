<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\RolPermiso;
use App\Support\Modulos;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Rol>
 */
class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        $nombre = 'Rol '.fake()->unique()->word().' '.fake()->unique()->numerify('###');

        return [
            'clave' => Str::slug($nombre, '_'),
            'nombre' => $nombre,
            'descripcion' => null,
            'alcance' => 'sede',
            'es_sistema' => false,
            'asignable' => false,
        ];
    }

    public function conAlcance(string $alcance): static
    {
        return $this->state(fn () => ['alcance' => $alcance]);
    }

    public function asignable(bool $asignable = true): static
    {
        return $this->state(fn () => ['asignable' => $asignable]);
    }

    /** Crea el rol con los niveles dados (módulo → nivel); los demás módulos quedan sin acceso. */
    public function conPermisos(array $niveles): static
    {
        return $this->afterCreating(function (Rol $rol) use ($niveles) {
            foreach (Modulos::claves() as $modulo) {
                RolPermiso::create(['rol_id' => $rol->id, 'modulo' => $modulo, 'nivel' => (int) ($niveles[$modulo] ?? 0)]);
            }
        });
    }
}
