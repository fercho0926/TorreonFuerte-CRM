<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PersonasRegistroTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_registra_un_visitante_con_nombres_apellidos_celular_y_documento(): void
    {
        $this->comoAdmin();

        $this->post(route('personas.store'), [
            'nombres' => 'Vera',
            'apellidos' => 'Visitante',
            'celular' => '3001234567',
            'estado' => 'visitante',
            'tipo_documento' => 'CC',
            'numero_documento' => '3001234567',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $persona = Persona::where('nombres', 'Vera')->firstOrFail();
        $this->assertSame('visitante', $persona->estado);
        $this->assertSame('3001234567', $persona->numero_documento);
        $this->assertDatabaseHas('historial_cambios', ['sujeto_tipo' => 'persona', 'sujeto_id' => $persona->id, 'accion' => 'creada']);
    }

    public function test_exige_celular_tipo_y_numero_de_documento(): void
    {
        $this->comoAdmin();

        $this->post(route('personas.store'), ['nombres' => 'Sin', 'apellidos' => 'Datos', 'estado' => 'visitante'])
            ->assertSessionHasErrors(['celular', 'tipo_documento', 'numero_documento']);
    }

    public function test_avisa_de_posible_duplicado_y_permite_confirmar(): void
    {
        $this->comoAdmin();
        $existente = Persona::factory()->create(['nombres' => 'Ana', 'apellidos' => 'Pérez', 'celular' => '3105550000']);

        $datos = [
            'nombres' => 'ana', 'apellidos' => 'pérez', 'celular' => '3105550000', 'estado' => 'visitante',
            'tipo_documento' => 'CC', 'numero_documento' => '3105550000',
        ];

        $antes = Persona::count(); // incluye el miembro dueño de la cuenta del administrador (spec 016)

        $this->post(route('personas.store'), $datos)
            ->assertSessionHas('duplicado', fn ($d) => $d['id'] === $existente->id);
        $this->assertSame($antes, Persona::count());

        $this->post(route('personas.store'), $datos + ['confirmar_duplicado' => 1])->assertSessionHasNoErrors();
        $this->assertSame($antes + 1, Persona::count());
    }

    public function test_varios_visitantes_sin_documento_pueden_coexistir_a_nivel_de_datos(): void
    {
        // El formulario de administración exige documento (regla de negocio), pero la unicidad por
        // sede sigue permitiendo varios registros sin número (p. ej. altas rápidas desde una célula).
        Persona::factory()->visitante()->create(['nombres' => 'Uno', 'apellidos' => 'Visita']);
        Persona::factory()->visitante()->create(['nombres' => 'Dos', 'apellidos' => 'Visita']);

        $this->assertSame(2, Persona::whereNull('numero_documento')->whereIn('nombres', ['Uno', 'Dos'])->count());
    }
}
