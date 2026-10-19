<?php

namespace Database\Factories;

use App\Models\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sede>
 */
class SedeFactory extends Factory
{
    protected $model = Sede::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Iglesia '.fake()->unique()->city().' '.fake()->unique()->numerify('###'),
            'ciudad' => fake()->city(),
            'direccion' => fake()->streetAddress(),
            'telefono' => fake()->numerify('3#########'),
            'email' => fake()->unique()->safeEmail(),
            'activa' => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(fn () => ['activa' => false]);
    }

    /**
     * Sede compartida por defecto en las pruebas: la primera existente o una nueva.
     */
    public static function idPorDefecto(): int
    {
        return Sede::query()->orderBy('id')->value('id')
            ?? Sede::factory()->create(['nombre' => 'Sede de prueba'])->id;
    }
}
