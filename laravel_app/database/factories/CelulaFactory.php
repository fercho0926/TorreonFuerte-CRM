<?php

namespace Database\Factories;

use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Celula>
 */
class CelulaFactory extends Factory
{
    protected $model = Celula::class;

    public function definition(): array
    {
        return [
            'sede_id' => SedeFactory::idPorDefecto(),
            'nombre' => 'Célula '.fake()->unique()->word().fake()->numerify('##'),
            'lider_id' => fn (array $atributos) => Persona::factory()->lider()->create(['sede_id' => $atributos['sede_id']])->id,
            'estado' => 'activa',
        ];
    }
}
