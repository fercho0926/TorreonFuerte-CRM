<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\Red;
use App\Models\Zona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persona>
 */
class PersonaFactory extends Factory
{
    protected $model = Persona::class;

    public function definition(): array
    {
        return [
            'sede_id' => SedeFactory::idPorDefecto(),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName().' '.fake()->lastName(),
            'tipo_documento' => 'CC',
            'numero_documento' => (string) fake()->unique()->numerify('##########'),
            'celular' => fake()->unique()->numerify('3#########'),
            'estado' => 'miembro_activo',
            'es_lider' => false,
        ];
    }

    public function visitante(): static
    {
        return $this->state(fn () => ['estado' => 'visitante', 'tipo_documento' => null, 'numero_documento' => null]);
    }

    public function inactiva(): static
    {
        return $this->state(fn () => ['estado' => 'inactivo', 'inactivado_en' => now()]);
    }

    /**
     * Persona con rol de líder; la red y la zona se crean en su misma sede si no se indican.
     */
    public function lider(?int $redId = null, ?int $zonaId = null): static
    {
        return $this->state(fn () => [
            'es_lider' => true,
            'red_id' => $redId ?? fn (array $atributos) => Red::create([
                'sede_id' => $atributos['sede_id'],
                'nombre' => 'Red '.fake()->unique()->word().fake()->numerify('##'),
            ])->id,
            'zona_id' => $zonaId ?? fn (array $atributos) => Zona::create([
                'sede_id' => $atributos['sede_id'],
                'nombre' => 'Zona '.fake()->unique()->word().fake()->numerify('##'),
            ])->id,
        ]);
    }

    public function deSede(int $sedeId): static
    {
        return $this->state(fn () => ['sede_id' => $sedeId]);
    }
}
