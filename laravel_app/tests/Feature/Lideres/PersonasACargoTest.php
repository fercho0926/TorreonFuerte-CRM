<?php

namespace Tests\Feature\Lideres;

use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 4: grilla «Miembros a su cargo» en la ficha del líder (FR-007). */
class PersonasACargoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function aCargo(array $atributos = []): Persona
    {
        return Persona::factory()->deSede($this->A['sede']->id)->create($atributos + ['lider_id' => $this->A['lider']->id]);
    }

    public function test_muestra_nombre_estado_celular_y_celula_ordenados_por_apellido(): void
    {
        $zeta = $this->aCargo(['nombres' => 'Ana', 'apellidos' => 'Zeta', 'celular' => '3112223344']);
        $alfa = $this->aCargo(['nombres' => 'Beto', 'apellidos' => 'Alfa']);
        $celula = Celula::create(['sede_id' => $this->A['sede']->id, 'nombre' => 'Célula del Valle', 'lider_id' => $this->A['lider']->id, 'estado' => 'activa']);
        $celula->miembros()->attach($zeta->id, ['fecha_ingreso' => now()->toDateString()]);

        $html = $this->get(route('lideres.show', $this->A['lider']))->assertOk()->getContent();

        $this->assertStringContainsString('Miembros a su cargo', $html);
        $this->assertStringContainsString('personas-a-cargo', $html);
        $this->assertStringContainsString('Ana Zeta', $html);
        $this->assertStringContainsString('3112223344', $html);
        $this->assertStringContainsString('Célula del Valle', $html);
        $this->assertStringContainsString('href="'.route('personas.show', $zeta).'"', $html);
        $this->assertLessThan(strpos($html, 'Ana Zeta'), strpos($html, 'Beto Alfa'), 'ordenadas por apellido');
        $this->assertStringContainsString('Miembro activo', $html);
    }

    public function test_un_lider_sin_personas_muestra_el_mensaje_vacio(): void
    {
        $libre = Persona::factory()->deSede($this->A['sede']->id)->lider()->create(['nombres' => 'Libre']);

        $this->get(route('lideres.show', $libre))->assertOk()->assertSee('Aún no lidera a nadie');
    }

    public function test_no_incluye_a_integrantes_de_su_celula_cuyo_lider_directo_es_otro(): void
    {
        $otroLider = Persona::factory()->deSede($this->A['sede']->id)->lider()->create();
        $ajena = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Ajena', 'apellidos' => 'Aotro', 'lider_id' => $otroLider->id]);
        $celula = Celula::create(['sede_id' => $this->A['sede']->id, 'nombre' => 'Mixta', 'lider_id' => $this->A['lider']->id, 'estado' => 'activa']);
        $celula->miembros()->attach($ajena->id, ['fecha_ingreso' => now()->toDateString()]);

        $html = $this->get(route('lideres.show', $this->A['lider']))->getContent();

        $this->assertStringNotContainsString('Ajena Aotro', $html);
        // La célula sí la muestra entre sus integrantes.
        $this->get(route('celulas.show', $celula))->assertSee('Ajena Aotro');
    }

    public function test_no_lista_a_las_inactivas(): void
    {
        $this->aCargo(['nombres' => 'Inactiva', 'apellidos' => 'Fuera', 'estado' => 'inactivo']);

        $this->get(route('lideres.show', $this->A['lider']))->assertDontSee('Inactiva Fuera');
    }

    public function test_con_cien_personas_no_aumenta_las_consultas_y_limita_la_altura(): void
    {
        $this->get(route('lideres.show', $this->A['lider']))->assertOk(); // calienta
        DB::enableQueryLog();
        $this->get(route('lideres.show', $this->A['lider']))->assertOk();
        $base = count(DB::getQueryLog());
        DB::flushQueryLog();

        Persona::factory()->count(100)->deSede($this->A['sede']->id)->create(['lider_id' => $this->A['lider']->id]);
        DB::flushQueryLog();
        $html = $this->get(route('lideres.show', $this->A['lider']))->assertOk()->getContent();
        $con100 = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual($base + 3, $con100, 'las células de cada miembro se cargan de una vez');
        $this->assertStringContainsString('max-height', $html);
    }

    public function test_un_pastor_de_otra_sede_no_ve_a_este_lider(): void
    {
        $this->iniciarSesionComo($this->B['pastor']);

        $this->get(route('lideres.show', $this->A['lider']))->assertNotFound();
    }
}
