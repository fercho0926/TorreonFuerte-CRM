<?php

namespace Tests\Feature;

use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Comprueba que los módulos que dependen de Miembros siguen funcionando tras el cambio de modelo.
 */
class ModulosRelacionadosTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_las_pantallas_de_celulas_y_catalogos_de_personas_cargan(): void
    {
        $this->comoAdmin();
        $celula = Celula::factory()->create();

        foreach ([route('celulas.index'), route('celulas.show', $celula), route('celulas.create'), route('lideres.designar'), route('personas.create'), route('personas.create', ['es_lider' => 1])] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
