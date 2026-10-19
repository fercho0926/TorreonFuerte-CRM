<?php

namespace Tests\Feature\Ministerios;

use App\Models\Ministerio;
use App\Models\Persona;
use App\Support\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 7: Ministerios en Congregación y selección de integrantes con buscador (FR-011, FR-012). */
class IntegrantesBuscadorTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function datos(array $extra = []): array
    {
        return $extra + ['nombre' => 'Alabanza Nueva', 'estado' => 'activo'];
    }

    private function persona(array $atributos = []): Persona
    {
        return Persona::factory()->deSede($this->A['sede']->id)->create($atributos);
    }

    public function test_el_menu_tiene_ministerios_en_congregacion_y_no_en_actividades(): void
    {
        $secciones = collect(Menu::secciones($this->A['pastor'], [], 'ministerios.index'))->keyBy('clave');

        $this->assertContains('Ministerios', collect($secciones['congregacion']['opciones'])->pluck('etiqueta')->all());
        $this->assertNotContains('Ministerios', collect($secciones['actividades']['opciones'])->pluck('etiqueta')->all());
        $this->assertTrue($secciones['congregacion']['abierta']);

        $html = $this->get(route('ministerios.index'))->getContent();
        $this->assertLessThan(strpos($html, 'ACTIVIDADES'), strpos($html, '>Ministerios<'), 'Ministerios aparece antes que Actividades');
    }

    public function test_el_formulario_usa_el_buscador_y_carga_los_integrantes_actuales(): void
    {
        $ministerio = $this->A['ministerio'];
        $integrante = $this->persona(['nombres' => 'Integrante', 'apellidos' => 'Musical']);
        $ministerio->integrantes()->attach($integrante->id, ['rol' => 'Bajista', 'fecha_ingreso' => '2026-03-01']);

        foreach ([route('ministerios.create'), route('ministerios.edit', $ministerio)] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('data-fuente="'.route('personas.buscar').'"', $html, $url);
            $this->assertStringNotContainsString('data-crear', $html, 'el alta rápida es solo de células');
        }

        $this->get(route('ministerios.edit', $ministerio))->assertSee('Integrante Musical');
    }

    public function test_guardar_sincroniza_los_integrantes_y_conserva_rol_y_fecha_de_los_existentes(): void
    {
        $ministerio = $this->A['ministerio'];
        $queda = $this->persona();
        $sale = $this->persona();
        $entra = $this->persona();
        $ministerio->integrantes()->attach([$queda->id => ['rol' => 'Bajista', 'fecha_ingreso' => '2026-03-01'], $sale->id => ['rol' => 'Voz', 'fecha_ingreso' => '2026-03-02']]);

        $this->put(route('ministerios.update', $ministerio), $this->datos(['nombre' => $ministerio->nombre, 'personas' => [$queda->id, $entra->id]]))->assertSessionHasNoErrors();

        $ids = $ministerio->integrantes()->pluck('personas.id')->all();
        $this->assertEqualsCanonicalizing([$queda->id, $entra->id], $ids);
        $pivot = $ministerio->integrantes()->whereKey($queda->id)->first()->pivot;
        $this->assertSame('Bajista', $pivot->rol);
        $this->assertSame('2026-03-01', substr((string) $pivot->fecha_ingreso, 0, 10));
    }

    public function test_una_persona_nueva_inactiva_o_de_otra_sede_se_rechaza(): void
    {
        $inactiva = $this->persona(['estado' => 'inactivo']);

        $this->post(route('ministerios.store'), $this->datos(['personas' => [$inactiva->id]]))->assertSessionHasErrors('personas');
        $this->post(route('ministerios.store'), $this->datos(['personas' => [$this->B['persona']->id]]))->assertSessionHasErrors('personas.0');
        $this->assertSame(0, Ministerio::where('nombre', 'Alabanza Nueva')->count());
    }

    public function test_un_integrante_que_se_inactivo_se_conserva(): void
    {
        $ministerio = $this->A['ministerio'];
        $inactiva = $this->persona(['estado' => 'inactivo']);
        $ministerio->integrantes()->attach($inactiva->id, ['fecha_ingreso' => now()->toDateString()]);

        $this->put(route('ministerios.update', $ministerio), $this->datos(['nombre' => $ministerio->nombre, 'personas' => [$inactiva->id]]))->assertSessionHasNoErrors();

        $this->assertTrue($ministerio->integrantes()->whereKey($inactiva->id)->exists());
    }

    public function test_crear_con_integrantes_funciona(): void
    {
        $a = $this->persona();
        $b = $this->persona();

        $this->post(route('ministerios.store'), $this->datos(['personas' => [$a->id, $b->id]]))->assertSessionHasNoErrors();

        $this->assertCount(2, Ministerio::where('nombre', 'Alabanza Nueva')->firstOrFail()->integrantes);
    }
}
