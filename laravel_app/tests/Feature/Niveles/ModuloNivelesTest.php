<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\Encuentro;
use App\Models\NivelFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Módulo Niveles: plan, alta, edición, orden, desactivación y eliminación (spec 014, HU2). */
class ModuloNivelesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function nivel(int $posicion): NivelFormacion
    {
        return NivelFormacion::where('posicion', $posicion)->firstOrFail();
    }

    private function nombresEnOrden(): array
    {
        return NivelFormacion::query()->ordenados()->pluck('nombre')->all();
    }

    public function test_la_lista_trae_los_cuatro_niveles_numerados(): void
    {
        $this->get(route('niveles.index'))->assertOk()
            ->assertSeeInOrder(['Libres para servir', 'Aliviando el fuego', 'Caminando en santidad', 'Viviendo en el espíritu'])
            ->assertSee('Plan de niveles')
            ->assertSee('Sin iniciar');
    }

    public function test_crear_agrega_el_nivel_al_final_y_aparece_en_el_camino(): void
    {
        $this->post(route('niveles.store'), ['nombre' => 'Nivel cinco', 'descripcion' => 'Más adelante'])->assertRedirect(route('niveles.index'));

        $nuevo = NivelFormacion::where('nombre', 'Nivel cinco')->firstOrFail();
        $this->assertSame(5, $nuevo->posicion);
        $this->assertTrue($nuevo->activo);
        $this->get(route('personas.show', $this->A['persona']))->assertSee('Nivel cinco')->assertSee('0 de 5 niveles');
    }

    public function test_el_nombre_es_obligatorio_y_no_se_repite_sin_distinguir_mayusculas(): void
    {
        $this->post(route('niveles.store'), ['nombre' => ''])->assertSessionHasErrors('nombre');
        $this->post(route('niveles.store'), ['nombre' => 'aliviando EL FUEGO'])->assertSessionHasErrors('nombre');
        $this->post(route('niveles.store'), ['nombre' => str_repeat('x', 81)])->assertSessionHasErrors('nombre');
        $this->post(route('niveles.store'), ['nombre' => 'ok', 'descripcion' => str_repeat('y', 501)])->assertSessionHasErrors('descripcion');
        $this->assertSame(4, NivelFormacion::count());
    }

    public function test_editar_cambia_nombre_y_descripcion_sin_chocar_consigo_mismo(): void
    {
        $nivel = $this->nivel(2);

        $this->put(route('niveles.update', $nivel), ['nombre' => 'Aliviando el fuego', 'descripcion' => 'Nueva descripción'])->assertSessionHasNoErrors();
        $this->assertSame('Nueva descripción', $nivel->fresh()->descripcion);

        $this->put(route('niveles.update', $nivel), ['nombre' => 'Libres para servir'])->assertSessionHasErrors('nombre');
        $this->put(route('niveles.update', $nivel), ['nombre' => 'Fuego renovado'])->assertSessionHasNoErrors();
        $this->assertSame('Fuego renovado', $nivel->fresh()->nombre);
    }

    public function test_subir_y_bajar_intercambian_con_el_vecino_y_en_los_extremos_no_hacen_nada(): void
    {
        $this->post(route('niveles.subir', $this->nivel(2)))->assertRedirect();
        $this->assertSame(['Aliviando el fuego', 'Libres para servir', 'Caminando en santidad', 'Viviendo en el espíritu'], $this->nombresEnOrden());

        $this->post(route('niveles.bajar', $this->nivel(2)))->assertRedirect();
        $this->assertSame(['Aliviando el fuego', 'Caminando en santidad', 'Libres para servir', 'Viviendo en el espíritu'], $this->nombresEnOrden());

        $primero = $this->nivel(1);
        $this->post(route('niveles.subir', $primero))->assertRedirect();
        $ultimo = $this->nivel(4);
        $this->post(route('niveles.bajar', $ultimo))->assertRedirect();
        $this->assertSame([1, 2, 3, 4], NivelFormacion::query()->ordenados()->pluck('posicion')->all());
        $this->assertSame($primero->nombre, $this->nivel(1)->nombre);
    }

    public function test_reordenar_conserva_los_avances_y_el_camino_sigue_el_nuevo_orden(): void
    {
        $libres = $this->nivel(1);
        AvanceNivel::factory()->de($this->A['persona'], $libres)->create();

        $this->post(route('niveles.bajar', $libres));

        $this->assertSame(2, $libres->fresh()->posicion);
        $this->assertSame(1, AvanceNivel::where('persona_id', $this->A['persona']->id)->count());
        $this->get(route('personas.show', $this->A['persona']))
            ->assertSeeInOrder(['Aliviando el fuego', 'Libres para servir', 'Caminando en santidad'])
            ->assertSee('Nivel actual: Libres para servir');
    }

    public function test_desactivar_oculta_del_camino_y_reactivar_lo_devuelve(): void
    {
        $nivel = $this->nivel(2);
        AvanceNivel::factory()->de($this->A['persona'], $nivel)->create();

        $this->post(route('niveles.desactivar', $nivel))->assertRedirect();
        $this->assertFalse($nivel->fresh()->activo);
        $estacion = '<strong class="estacion-nombre">Aliviando el fuego</strong>';
        $this->get(route('personas.show', $this->A['persona']))->assertDontSee($estacion, false)->assertSee('0 de 3 niveles');
        $this->assertSame(1, AvanceNivel::count(), 'el avance se conserva');

        $this->post(route('niveles.reactivar', $nivel))->assertRedirect();
        $this->get(route('personas.show', $this->A['persona']))->assertSee($estacion, false)->assertSee('1 de 4 niveles');
    }

    public function test_eliminar_solo_sin_avances_ni_encuentros_y_renumera(): void
    {
        $conAvance = $this->nivel(1);
        AvanceNivel::factory()->de($this->A['persona'], $conAvance)->create();
        $conEncuentro = $this->nivel(2);
        Encuentro::create(['sede_id' => $this->A['sede']->id, 'nombre' => 'Retiro', 'fecha_realizacion' => '2026-03-12', 'valor' => 0, 'estado' => 'realizado', 'nivel_id' => $conEncuentro->id]);

        $this->delete(route('niveles.destroy', $conAvance))->assertRedirect()->assertSessionHas('error');
        $this->delete(route('niveles.destroy', $conEncuentro))->assertRedirect()->assertSessionHas('error');
        $this->assertSame(4, NivelFormacion::count());

        $libre = $this->nivel(3);
        $this->delete(route('niveles.destroy', $libre))->assertRedirect()->assertSessionHas('success');
        $this->assertNull(NivelFormacion::find($libre->id));
        $this->assertSame([1, 2, 3], NivelFormacion::query()->ordenados()->pluck('posicion')->all(), 'posiciones consecutivas');
        $this->assertSame('Viviendo en el espíritu', $this->nivel(3)->nombre);
    }

    public function test_el_plan_es_el_mismo_en_otra_sede(): void
    {
        $this->post(route('niveles.store'), ['nombre' => 'Común a todas']);

        $this->iniciarSesionComo($this->B['pastor']);

        $this->get(route('niveles.index'))->assertOk()->assertSee('Común a todas');
    }
}
