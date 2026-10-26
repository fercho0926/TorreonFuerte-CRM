<?php

namespace Tests\Feature\Celulas;

use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 5: integrantes de una célula (FR-008 a FR-010, FR-013). */
class IntegrantesTest extends TestCase
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
        return $extra + ['nombre' => 'Célula Nueva', 'lider_id' => $this->A['lider']->id, 'estado' => 'activa'];
    }

    private function persona(array $atributos = []): Persona
    {
        return Persona::factory()->deSede($this->A['sede']->id)->create($atributos);
    }

    public function test_crear_una_celula_con_integrantes_los_sincroniza_con_fecha_de_hoy(): void
    {
        $a = $this->persona();
        $b = $this->persona(['lider_id' => null]);

        $this->post(route('celulas.store'), $this->datos(['integrantes_presente' => 1, 'integrantes' => [$a->id, $b->id]]))->assertSessionHasNoErrors();

        $celula = Celula::where('nombre', 'Célula Nueva')->firstOrFail();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $celula->miembros()->pluck('personas.id')->all());
        $this->assertSame(now()->toDateString(), substr((string) $celula->miembros()->first()->pivot->fecha_ingreso, 0, 10));
    }

    public function test_editar_conserva_la_fecha_de_los_existentes_anade_y_quita(): void
    {
        $celula = $this->A['celula'];
        $queda = $this->persona();
        $sale = $this->persona();
        $entra = $this->persona();
        $celula->miembros()->attach([$queda->id => ['fecha_ingreso' => '2026-01-15'], $sale->id => ['fecha_ingreso' => '2026-02-01']]);

        $this->put(route('celulas.update', $celula), $this->datos(['nombre' => $celula->nombre, 'integrantes_presente' => 1, 'integrantes' => [$queda->id, $entra->id]]))
            ->assertSessionHasNoErrors();

        $ids = $celula->miembros()->pluck('personas.id')->all();
        $this->assertContains($queda->id, $ids);
        $this->assertContains($entra->id, $ids);
        $this->assertNotContains($sale->id, $ids);
        $this->assertSame('2026-01-15', substr((string) $celula->miembros()->whereKey($queda->id)->first()->pivot->fecha_ingreso, 0, 10));
        $this->assertNotNull(Persona::find($sale->id), 'quitar de la célula no borra al miembro');
    }

    public function test_sin_la_seccion_de_integrantes_no_se_borra_a_nadie(): void
    {
        $celula = $this->A['celula'];
        $miembro = $this->persona();
        $celula->miembros()->attach($miembro->id, ['fecha_ingreso' => now()->toDateString()]);

        $this->put(route('celulas.update', $celula), $this->datos(['nombre' => $celula->nombre]))->assertSessionHasNoErrors();

        $this->assertTrue($celula->miembros()->whereKey($miembro->id)->exists());
    }

    public function test_enviar_la_seccion_vacia_quita_a_todos(): void
    {
        $celula = $this->A['celula'];
        $miembro = $this->persona();
        $celula->miembros()->attach($miembro->id, ['fecha_ingreso' => now()->toDateString()]);

        $this->put(route('celulas.update', $celula), $this->datos(['nombre' => $celula->nombre, 'integrantes_presente' => 1]))->assertSessionHasNoErrors();

        $this->assertSame(0, $celula->miembros()->count());
    }

    public function test_se_acepta_a_alguien_de_otro_lider_o_sin_lider_y_su_lider_directo_no_cambia(): void
    {
        $sinLider = $this->persona(['lider_id' => null]);
        $deOtro = $this->persona(['lider_id' => $this->A['lider']->id]);
        $otroLider = Persona::factory()->deSede($this->A['sede']->id)->lider()->create();
        $ajena = $this->persona(['lider_id' => $otroLider->id]);

        $this->post(route('celulas.store'), $this->datos(['integrantes' => [$sinLider->id, $deOtro->id, $ajena->id]]))->assertSessionHasNoErrors();

        $celula = Celula::where('nombre', 'Célula Nueva')->firstOrFail();
        $this->assertCount(3, $celula->miembros);
        $this->assertNull($sinLider->fresh()->lider_id);
        $this->assertSame($otroLider->id, $ajena->fresh()->lider_id);
    }

    public function test_una_persona_inactiva_nueva_o_de_otra_sede_se_rechaza(): void
    {
        $inactiva = $this->persona(['estado' => 'inactivo']);

        $this->post(route('celulas.store'), $this->datos(['integrantes' => [$inactiva->id]]))->assertSessionHasErrors('integrantes');
        $this->post(route('celulas.store'), $this->datos(['integrantes' => [$this->B['persona']->id]]))->assertSessionHasErrors('integrantes.0');
        $this->assertSame(0, Celula::where('nombre', 'Célula Nueva')->count());
    }

    public function test_un_integrante_que_se_inactivo_se_conserva_hasta_quitarlo(): void
    {
        $celula = $this->A['celula'];
        $inactiva = $this->persona(['estado' => 'inactivo']);
        $celula->miembros()->attach($inactiva->id, ['fecha_ingreso' => now()->toDateString()]);

        $this->put(route('celulas.update', $celula), $this->datos(['nombre' => $celula->nombre, 'integrantes_presente' => 1, 'integrantes' => [$inactiva->id]]))->assertSessionHasNoErrors();

        $this->assertTrue($celula->miembros()->whereKey($inactiva->id)->exists());
    }

    public function test_agregar_desde_la_ficha_acepta_varias_personas_y_no_cambia_su_lider(): void
    {
        $celula = $this->A['celula'];
        $a = $this->persona(['lider_id' => null]);
        $b = $this->persona(['lider_id' => null]);

        $this->post(route('celulas.miembros.store', $celula), ['personas' => [$a->id, $b->id]])->assertSessionHas('success');

        $this->assertTrue($celula->miembros()->whereKey($a->id)->exists());
        $this->assertTrue($celula->miembros()->whereKey($b->id)->exists());
        $this->assertNull($a->fresh()->lider_id);
        $this->post(route('celulas.miembros.store', $celula), [])->assertSessionHasErrors('personas');
    }

    public function test_la_ficha_y_el_formulario_ofrecen_el_buscador_y_el_alta_rapida(): void
    {
        $celula = $this->A['celula'];
        $integrante = $this->persona(['nombres' => 'Integrante', 'apellidos' => 'Actual']);
        $celula->miembros()->attach($integrante->id, ['fecha_ingreso' => now()->toDateString()]);

        foreach ([route('celulas.show', $celula), route('celulas.edit', $celula), route('celulas.create')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('data-fuente="'.route('personas.buscar').'"', $html, $url);
            $this->assertStringContainsString('data-crear="'.route('personas.rapida').'"', $html, $url);
        }

        $this->get(route('celulas.edit', $celula))->assertSee('Integrante Actual');
        $this->get(route('celulas.show', $celula))->assertSee('Integrante Actual')->assertSee('Agregar integrantes');
    }

    public function test_la_lista_de_asistencia_incluye_a_los_integrantes(): void
    {
        $celula = $this->A['celula'];
        $integrante = $this->persona(['nombres' => 'Asistente', 'apellidos' => 'Fiel']);
        $celula->miembros()->attach($integrante->id, ['fecha_ingreso' => now()->toDateString()]);

        $this->get(route('celulas.show', $celula))->assertSee('Asistente Fiel')->assertSee('value="'.$integrante->id.'"', false);
    }

    public function test_un_lider_agrega_integrantes_a_su_propia_celula_y_crea_personas_a_su_cargo(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);
        $celula = $this->A['celula'];

        $id = $this->postJson(route('personas.rapida'), ['nombres' => 'Nueva', 'apellidos' => 'Invitada', 'celular' => '3115550000'])->assertCreated()->json('id');
        $this->post(route('celulas.miembros.store', $celula), ['personas' => [$id]])->assertSessionHas('success');

        $this->assertTrue($celula->miembros()->whereKey($id)->exists());
        $this->assertSame('celula', Persona::withoutGlobalScopes()->find($id)->estado);
    }
}
