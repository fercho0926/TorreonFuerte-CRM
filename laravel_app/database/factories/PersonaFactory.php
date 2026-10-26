<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Miembro>
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

    /** Miembro que asiste a una célula y aún no es parte de la iglesia. */
    public function enCelula(): static
    {
        return $this->state(fn () => ['estado' => 'celula', 'tipo_documento' => null, 'numero_documento' => null]);
    }

    public function inactiva(): static
    {
        return $this->state(fn () => ['estado' => 'inactivo', 'inactivado_en' => now()]);
    }

    /**
     * Miembro con rol de líder.
     */
    public function lider(): static
    {
        return $this->state(fn () => ['es_lider' => true]);
    }

    public function deSede(int $sedeId): static
    {
        return $this->state(fn () => ['sede_id' => $sedeId]);
    }

    /** Pastor de la sede: líder, sin líder (es la cabeza) y registrado en `sedes.pastor_persona_id`. */
    public function pastorDe(Sede $sede): static
    {
        return $this->state(fn () => ['sede_id' => $sede->id, 'es_lider' => true, 'lider_id' => null])
            ->afterCreating(fn (Persona $persona) => $sede->forceFill(['pastor_persona_id' => $persona->id])->save());
    }
}
