<?php

namespace Tests\Feature;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\Red;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Comprueba que los módulos que dependen de Personas siguen funcionando tras el cambio de modelo.
 */
class ModulosRelacionadosTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_el_dashboard_agrupa_personas_por_red_del_catalogo(): void
    {
        $this->comoAdmin();
        $red = Red::create(['nombre' => 'Matrimonios']);
        $lider = Persona::factory()->lider($red->id)->create();
        Persona::factory()->count(2)->create(['lider_id' => $lider->id]);
        Persona::factory()->create();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Matrimonios')
            ->assertSee('Sin red asignada');
    }

    public function test_las_pantallas_de_celulas_y_catalogos_de_personas_cargan(): void
    {
        $this->comoAdmin();
        $celula = Celula::factory()->create();

        foreach ([route('celulas.index'), route('celulas.show', $celula), route('celulas.create'), route('lideres.designar'), route('zonas.index'), route('redes.index'), route('personas.create'), route('personas.create', ['es_lider' => 1])] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
