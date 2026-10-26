<?php

namespace Tests\Feature\Interfaz;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** El marco común de AdminLTE 4 aparece en toda pantalla autenticada (FR-001 a FR-005, FR-013). */
class MarcoDePaginaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private const RUTAS = ['dashboard', 'personas.index', 'lideres.index', 'celulas.index', 'eventos.index', 'encuentros.index',
        'ministerios.index', 'sin-lider.index', 'users.index', 'sedes.index'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    public function test_toda_pantalla_comparte_barra_menu_migas_pie_y_titulo_con_el_sistema(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        foreach (self::RUTAS as $ruta) {
            $html = $this->get(route($ruta))->assertOk()->getContent();

            foreach (['app-header', 'app-sidebar', 'app-content-header', 'breadcrumb', 'app-footer', 'data-lte-toggle="sidebar"'] as $marca) {
                $this->assertStringContainsString($marca, $html, "{$ruta} no tiene {$marca}");
            }

            $this->assertMatchesRegularExpression('/<title>[^<]+ · '.preg_quote(config('app.sistema'), '/').' · Iglesia Alfa<\/title>/u', $html, "{$ruta}: título de pestaña");
        }
    }

    public function test_el_usuario_ve_su_nombre_rol_y_sede_y_puede_cerrar_sesion(): void
    {
        $user = $this->comoPastorDe($this->A['sede']);

        $this->get(route('dashboard'))
            ->assertSee($user->name)
            ->assertSee('Pastor')
            ->assertSee('Cerrar sesión')
            ->assertSee('Iglesia Alfa');
    }

    public function test_el_menu_se_agrupa_en_secciones_y_resalta_la_opcion_activa(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('personas.index'))->getContent();

        foreach (['CONGREGACIÓN', 'ACTIVIDADES', 'ADMINISTRACIÓN'] as $seccion) {
            $this->assertStringContainsString($seccion, $html);
        }
        $this->assertMatchesRegularExpression('/<a href="[^"]*\/personas" class="nav-link active"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<a href="[^"]*\/lideres" class="nav-link active"/', $html);
    }

    public function test_el_lider_solo_ve_las_opciones_permitidas_y_no_las_secciones_vacias(): void
    {
        $this->comoLiderDe($this->A['sede'], $this->A['lider']);

        $html = $this->get(route('personas.index'))->getContent();

        $this->assertStringContainsString('Miembros', $html);
        $this->assertStringNotContainsString('ADMINISTRACIÓN', $html);
        $this->assertStringNotContainsString('Sin líder asignado', $html);
        $this->assertStringNotContainsString('>Usuarios<', $html);
    }

    public function test_las_migas_muestran_inicio_seccion_y_pantalla_y_un_nombre_de_sede_largo_se_recorta(): void
    {
        $this->A['sede']->update(['nombre' => 'Iglesia con un nombre de sede demasiado largo para caber en la barra superior']);
        $this->comoAdminEnSede($this->A['sede']->fresh());

        $html = $this->get(route('celulas.index'))->getContent();

        $this->assertMatchesRegularExpression('/breadcrumb-item"><a href="[^"]*\/dashboard">Inicio<\/a>.*Congregación.*Células/s', $html);
        $this->assertStringContainsString('nombre-sede', $html);
    }

    public function test_el_admin_en_todas_las_sedes_ve_el_selector_y_el_aviso_de_solo_consulta(): void
    {
        $this->comoAdminEnTodas();

        $this->get(route('personas.index'))
            ->assertSee('Todas las sedes: solo consulta')
            ->assertSee('id="sede-activa"', false)
            ->assertDontSee('marca-agua-sede', false);
    }
}
