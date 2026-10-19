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
        $this->assertDatabaseHas('persona_cambios', ['persona_id' => $persona->id, 'accion' => 'creada']);
    }

    public function test_exige_al_menos_un_medio_de_contacto(): void
    {
        $this->comoAdmin();

        $this->post(route('personas.store'), ['nombres' => 'Sin', 'apellidos' => 'Contacto', 'estado' => 'visitante'])
            ->assertSessionHasErrors(['celular', 'email']);
    }

    public function test_exige_documento_al_pasar_a_miembro_activo(): void
    {
        $this->comoAdmin();

        $this->post(route('personas.store'), [
            'nombres' => 'Mario', 'apellidos' => 'Miembro', 'celular' => '3001112222', 'estado' => 'miembro_activo',
        ])->assertSessionHasErrors(['numero_documento', 'tipo_documento']);
    }

    public function test_avisa_de_posible_duplicado_y_permite_confirmar(): void
    {
        $this->comoAdmin();
        $existente = Persona::factory()->create(['nombres' => 'Ana', 'apellidos' => 'Pérez', 'celular' => '3105550000']);

        $datos = ['nombres' => 'ana', 'apellidos' => 'pérez', 'celular' => '3105550000', 'estado' => 'visitante'];

        $this->post(route('personas.store'), $datos)
            ->assertSessionHas('duplicado', fn ($d) => $d['id'] === $existente->id);
        $this->assertSame(1, Persona::count());

        $this->post(route('personas.store'), $datos + ['confirmar_duplicado' => 1])->assertSessionHasNoErrors();
        $this->assertSame(2, Persona::count());
    }

    public function test_rechaza_un_documento_duplicado(): void
    {
        $this->comoAdmin();
        Persona::factory()->create(['numero_documento' => '999']);

        $this->post(route('personas.store'), [
            'nombres' => 'Otra', 'apellidos' => 'Persona', 'celular' => '3000000001', 'estado' => 'miembro_activo',
            'tipo_documento' => 'CC', 'numero_documento' => '999',
        ])->assertSessionHasErrors('numero_documento');
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
