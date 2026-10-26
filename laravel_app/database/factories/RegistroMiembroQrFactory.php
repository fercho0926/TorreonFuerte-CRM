<?php

namespace Database\Factories;

use App\Models\RegistroMiembroQr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistroMiembroQr>
 */
class RegistroMiembroQrFactory extends Factory
{
    protected $model = RegistroMiembroQr::class;

    public function definition(): array
    {
        return [
            'sede_id' => SedeFactory::idPorDefecto(),
            'persona_id' => null,
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'celular' => fake()->numerify('3#########'),
            'resultado' => 'nueva',
            'cambios' => null,
        ];
    }

    public function deSede(int $sedeId): static
    {
        return $this->state(fn () => ['sede_id' => $sedeId]);
    }
}
