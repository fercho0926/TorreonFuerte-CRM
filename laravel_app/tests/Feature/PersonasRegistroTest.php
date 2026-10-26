<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PersonasRegistroTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_registra_un_visitante_solo_con_nombres_apellidos_y_celular(): void
    {
        $this->comoAdmin();

        $this->post(route('personas.store'), [
            'nombres' => 'Vera',
            'apellidos' => 'Visitante',
            'celular' => '3001234567',
            'estado' => 'visitante',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $persona = Persona::where('nombres', 'Vera')->firstOrFail();
        $this->assertSame('visitante', $persona->estado);
        $this->assertNull($persona->numero_documento);
        $this->assertDatabaseHas('historial_cambios', ['sujeto_tipo' => 'persona', 'sujeto_id' => $persona->id, 'accion' => 'creada']);
    }

    public function test_exige_al_menos_un_medio_de_contacto(): void
    {
        $this->comoAdmin();

        $this->post(route('personas.store'), ['nombres' => 'Sin', 'apellidos' => 'Contacto', 'estado' => 'visitante'])
            ->assertSessionHasErrors(['celular', 'email']);
    }

    public function test_avisa_de_posible_duplicado_y_permite_confirmar(): void
    {
        $this->comoAdmin();
        $existente = Persona::factory()->create(['nombres' => 'Ana', 'apellidos' => 'Pérez', 'celular' => '3105550000']);

        $datos = ['nombres' => 'ana', 'apellidos' => 'pérez', 'celular' => '3105550000', 'estado' => 'visitante'];

        $antes = Persona::count(); // incluye el miembro dueño de la cuenta del administrador (spec 016)

        $this->post(route('personas.store'), $datos)
            ->assertSessionHas('duplicado', fn ($d) => $d['id'] === $existente->id);
        $this->assertSame($antes, Persona::count());

        $this->post(route('personas.store'), $datos + ['confirmar_duplicado' => 1])->assertSessionHasNoErrors();
        $this->assertSame($antes + 1, Persona::count());
    }

    public function test_varios_visitantes_sin_documento_pueden_coexistir(): void
    {
        $this->comoAdmin();

        foreach (['Uno', 'Dos'] as $i => $nombre) {
            $this->post(route('personas.store'), ['nombres' => $nombre, 'apellidos' => 'Visita', 'celular' => '300000000'.$i, 'estado' => 'visitante'])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(2, Persona::whereNull('numero_documento')->count());
    }
}
