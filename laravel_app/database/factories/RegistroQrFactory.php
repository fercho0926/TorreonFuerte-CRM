<?php

namespace Database\Factories;

use App\Models\RegistroQr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistroQr>
 */
class RegistroQrFactory extends Factory
{
    protected $model = RegistroQr::class;

    public function definition(): array
    {
        return [
            'sede_id' => SedeFactory::idPorDefecto(),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'celular' => fake()->unique()->numerify('3#########'),
            'fecha_nacimiento' => fake()->date('Y-m-d', '-18 years'),
            'resultado' => 'nueva',
        ];
    }
}
