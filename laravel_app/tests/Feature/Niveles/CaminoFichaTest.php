<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Camino de niveles en la ficha del miembro (spec 014, HU1). */
class CaminoFichaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function completar(Persona $p, string $fecha, int ...$posiciones): void
    {
        foreach ($posiciones as $posicion) {
            AvanceNivel::factory()->de($p, NivelFormacion::where('posicion', $posicion)->firstOrFail())->create(['fecha' => $fecha]);
        }
    }

    private function ficha(?Persona $p = null)
    {
        return $this->get(route('personas.show', $p ?? $this->A['persona']))->assertOk();
    }

    public function test_muestra_las_estaciones_en_orden_con_su_estado_y_fecha_en_texto(): void
    {
        $this->completar($this->A['persona'], '2026-03-12', 1, 2);

        $this->ficha()
            ->assertSee('Camino de niveles')
            ->assertSee('Libres para servir')
            ->assertSee('Aliviando el fuego')
            ->assertSee('Completado el 12 de marzo de 2026')
            ->assertSee('Siguiente')
            ->assertSee('Pendiente')
            ->assertSee('2 de 4 niveles')
            ->assertSee('Nivel actual: Aliviando el fuego')
            ->assertSeeInOrder(['Libres para servir', 'Aliviando el fuego', 'Caminando en santidad', 'Viviendo en el espíritu']);
    }

    public function test_cada_estacion_indica_su_estado_con_una_clase_y_texto_no_solo_color(): void
    {
        $this->completar($this->A['persona'], '2026-03-12', 1, 2);

        $html = $this->ficha()->getContent();

        $this->assertSame(2, substr_count($html, 'estacion--completado'));
        $this->assertSame(1, substr_count($html, 'estacion--siguiente'));
        $this->assertSame(1, substr_count($html, 'estacion--pendiente'));
        $this->assertStringContainsString('<ol class="camino-estaciones"', $html);
        $this->assertStringContainsString('aria-label="Camino de niveles', $html);
    }

    public function test_sin_avances_dice_que_aun_no_inicia_y_el_primero_es_el_siguiente(): void
    {
        $html = $this->ficha()->assertSee('Aún no inicia su camino')->getContent();

        $this->assertSame(1, substr_count($html, 'estacion--siguiente'));
        $this->assertSame(3, substr_count($html, 'estacion--pendiente'));
    }

    public function test_con_los_cuatro_niveles_el_camino_esta_terminado(): void
    {
        $this->completar($this->A['persona'], '2026-03-12', 1, 2, 3, 4);

        $this->ficha()->assertSee('Camino terminado')->assertDontSee('estacion--siguiente', false);
    }

    public function test_un_nivel_saltado_se_marca_como_saltado(): void
    {
        $this->completar($this->A['persona'], '2026-03-12', 1, 4);

        // El 2 es el siguiente a hacer y el 3 sigue pendiente; ambos quedaron atrás del 4.
        $this->ficha()->assertSee('Siguiente (saltado)')->assertSee('Pendiente (saltado)');
    }

    public function test_sin_niveles_activos_explica_que_el_plan_esta_vacio(): void
    {
        NivelFormacion::query()->update(['activo' => false]);

        $this->ficha()->assertSee('El plan de niveles aún no tiene niveles');
    }

    public function test_sin_lectura_en_niveles_no_se_muestra_el_camino(): void
    {
        $rol = Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'miembros' => 1])->create();
        $usuario = $this->crearUsuario($rol->clave, $this->A['lider'], $this->A['sede']->id);
        $this->iniciarSesionComo($usuario);

        $this->ficha()->assertDontSee('Camino de niveles');
    }

    public function test_con_solo_lectura_se_ve_el_camino_sin_botones_de_registro(): void
    {
        $rol = Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'miembros' => 1, 'niveles' => 1])->create();
        $usuario = $this->crearUsuario($rol->clave, $this->A['lider'], $this->A['sede']->id);
        $this->iniciarSesionComo($usuario);
        $this->completar($this->A['persona'], '2026-03-12', 1);

        $this->ficha()
            ->assertSee('Camino de niveles')
            ->assertDontSee('Marcar completado')
            ->assertDontSee(route('niveles.avances.store'), false);
    }

    public function test_con_escritura_aparece_el_registro_de_avance_y_quitar_en_los_manuales(): void
    {
        $this->completar($this->A['persona'], '2026-03-12', 1);

        $this->ficha()
            ->assertSee('Marcar completado')
            ->assertSee(route('niveles.avances.store'), false)
            ->assertSee('Quitar');
    }
}
