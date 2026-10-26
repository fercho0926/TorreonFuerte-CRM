<?php

namespace Database\Factories;

use App\Models\NivelFormacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NivelFormacion>
 */
class NivelFormacionFactory extends Factory
{
    protected $model = NivelFormacion::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Nivel '.fake()->unique()->words(2, true).' '.fake()->unique()->numerify('###'),
            'descripcion' => null,
            'posicion' => (int) NivelFormacion::query()->max('posicion') + 1,
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
