<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sede_id' => fn (array $atributos) => ($atributos['role'] ?? 'user') === 'admin' ? null : SedeFactory::idPorDefecto(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Toda cuenta es de un miembro (spec 016): si no se indica `persona_id`, se crea el miembro en la
     * sede de la cuenta (el del administrador, en la sede por defecto). `name` y `celular`, si se pasan,
     * van al miembro.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $usuario) {
            $atributos = $usuario->getAttributes();
            $usuario->setRawAttributes(Arr::except($atributos, ['name', 'celular', '_miembro']));

            if ($usuario->persona_id) {
                return;
            }

            $datos = $atributos['_miembro'] ?? [];

            if (!empty($atributos['name'])) {
                $partes = preg_split('/\s+/', trim($atributos['name']), 2);
                $datos += ['nombres' => $partes[0], 'apellidos' => $partes[1] ?? '-'];
            }

            if (array_key_exists('celular', $atributos)) {
                $datos += ['celular' => $atributos['celular']];
            }

            $persona = Persona::factory()->create($datos + ['sede_id' => $usuario->sede_id ?? SedeFactory::idPorDefecto()]);

            $usuario->persona_id = $persona->id;
            $usuario->setRelation('persona', $persona);
        });
    }

    /** Cuenta cuyo miembro tiene estos datos (`nombres`, `apellidos`, `celular`, …). */
    public function conMiembro(array $datos): static
    {
        return $this->state(fn () => ['_miembro' => $datos]);
    }

    /** Cuenta con una clave temporal vigente: debe elegir su contraseña al entrar. */
    public function conClaveTemporal(?\DateTimeInterface $venceEn = null): static
    {
        return $this->state(fn () => ['debe_cambiar_clave' => true, 'clave_vence_en' => $venceEn ?? now()->addDay()]);
    }

    /** Cuenta con una clave temporal que ya venció. */
    public function conClaveVencida(): static
    {
        return $this->conClaveTemporal(now()->subMinute());
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
