<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PersonasBusquedaTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_busca_por_parte_de_nombre_apellido_documento_y_celular(): void
    {
        $this->comoAdmin();
        Persona::factory()->create(['nombres' => 'Ana María', 'apellidos' => 'Pérez Soto', 'numero_documento' => '5551234', 'celular' => '3105559999']);
        Persona::factory()->create(['nombres' => 'Luis', 'apellidos' => 'Gómez Rey', 'numero_documento' => '7771111', 'celular' => '3200000000']);

        foreach (['ana ma', 'pérez', 'ana soto', '5551', '559999'] as $texto) {
            $this->get(route('personas.index', ['q' => $texto]))
                ->assertOk()
                ->assertSee('Ana María Pérez Soto')
                ->assertDontSee('Luis Gómez Rey');
        }
    }

    public function test_combina_busqueda_con_filtros_y_pagina_de_25_en_25(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create(['nombres' => 'Lidia', 'apellidos' => 'Guía']);
        Persona::factory()->count(30)->create(['lider_id' => $lider->id, 'estado' => 'miembro_activo']);
        Persona::factory()->visitante()->create(['nombres' => 'Vera', 'apellidos' => 'Visitante', 'lider_id' => $lider->id]);

        $this->get(route('personas.index', ['lider_id' => $lider->id]))
            ->assertOk()
            ->assertViewHas('pagina', fn ($p) => $p->perPage() === 25 && $p->total() === 31 && $p->count() === 25);

        $this->get(route('personas.index', ['lider_id' => $lider->id, 'estado' => 'visitante', 'q' => 'vera']))
            ->assertViewHas('pagina', fn ($p) => $p->total() === 1);
    }

    public function test_los_inactivos_no_aparecen_por_defecto(): void
    {
        $this->comoAdmin();
        Persona::factory()->create(['nombres' => 'Activa', 'apellidos' => 'Uno']);
        Persona::factory()->inactiva()->create(['nombres' => 'Inactiva', 'apellidos' => 'Dos']);

        $this->get(route('personas.index'))->assertSee('Activa Uno')->assertDontSee('Inactiva Dos');
        $this->get(route('personas.index', ['inactivos' => 1]))->assertSee('Inactiva Dos');
    }

    public function test_el_listado_no_muestra_identificadores_tecnicos(): void
    {
        $this->comoAdmin();
        Persona::factory()->create();

        $this->get(route('personas.index'))->assertDontSee('<th>ID</th>', false);
    }
}
