<?php

namespace Database\Factories;

use App\Models\Persona;
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

    /** Persona que asiste a una célula y aún no es parte de la iglesia. */
    public function enCelula(): static
    {
        return $this->state(fn () => ['estado' => 'celula', 'tipo_documento' => null, 'numero_documento' => null]);
    }

    public function inactiva(): static
    {
        return $this->state(fn () => ['estado' => 'inactivo', 'inactivado_en' => now()]);
    }

    /**
     * Persona con rol de líder.
     */
    public function lider(): static
    {
        return $this->state(fn () => ['es_lider' => true]);
    }

    public function deSede(int $sedeId): static
    {
        return $this->state(fn () => ['sede_id' => $sedeId]);
    }
}
