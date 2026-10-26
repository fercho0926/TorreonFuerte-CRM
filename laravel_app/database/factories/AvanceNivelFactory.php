<?php

namespace Database\Factories;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvanceNivel>
 */
class AvanceNivelFactory extends Factory
{
    protected $model = AvanceNivel::class;

    public function definition(): array
    {
        return [
            'sede_id' => SedeFactory::idPorDefecto(),
            'persona_id' => Persona::factory(),
            'nivel_id' => fn () => NivelFormacion::query()->orderBy('posicion')->value('id'),
            'fecha' => now()->subMonth()->toDateString(),
            'origen' => AvanceNivel::ORIGEN_MANUAL,
        ];
    }

    public function de(Persona $persona, NivelFormacion $nivel): static
    {
        return $this->state(fn () => ['persona_id' => $persona->id, 'sede_id' => $persona->sede_id, 'nivel_id' => $nivel->id]);
    }
}
