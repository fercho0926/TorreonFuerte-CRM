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

/** Tarjeta «Miembros por nivel» del tablero (spec 014, HU5, RF-018). */
class TableroNivelesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function completar(Persona $p, int ...$posiciones): void
    {
        foreach ($posiciones as $posicion) {
            AvanceNivel::factory()->de($p, NivelFormacion::where('posicion', $posicion)->first())->create();
        }
    }

    public function test_con_lectura_en_niveles_aparece_la_tarjeta_con_conteos_y_enlaces(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $this->completar($this->A['persona'], 1, 2);

        $html = $this->get(route('dashboard'))->assertOk()
            ->assertSee('Miembros por nivel')
            ->assertSee('Libres para servir')
            ->assertSee('Sin iniciar')
            ->assertSee(route('niveles.miembros.index', ['nivel' => NivelFormacion::where('posicion', 2)->value('id')]), false)
            ->assertSee(route('niveles.index'), false)
            ->getContent();

        $this->assertMatchesRegularExpression('/Aliviando el fuego<\/span>.*?<strong class="tarjeta-nivel-total">1<\/strong>/s', $html);
    }

    public function test_los_conteos_coinciden_con_la_vista_de_niveles(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $this->completar($this->A['persona'], 1);
        $resumen = app(\App\Services\NivelesService::class)->resumen($this->A['pastor']);

        $html = $this->get(route('dashboard'))->getContent();

        $this->assertMatchesRegularExpression('/Libres para servir<\/span>.*?<strong class="tarjeta-nivel-total">'.$resumen['niveles']->first()['total'].'<\/strong>/s', $html);
        $this->assertMatchesRegularExpression('/Sin iniciar<\/span>.*?<strong class="tarjeta-nivel-total">'.$resumen['sin_iniciar'].'<\/strong>/s', $html);
    }

    public function test_sin_acceso_al_modulo_la_tarjeta_no_aparece(): void
    {
        $rol = Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'miembros' => 1])->create();
        $this->iniciarSesionComo($this->crearUsuario($rol->clave, $this->A['lider'], $this->A['sede']->id));

        $this->get(route('dashboard'))->assertOk()->assertDontSee('Miembros por nivel');
    }

    public function test_los_totales_respetan_el_alcance_del_usuario(): void
    {
        $this->completar($this->A['persona'], 1);
        $this->completar($this->B['persona'], 1);
        $this->iniciarSesionComo($this->B['pastor']);

        $html = $this->get(route('dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/Libres para servir<\/span>.*?<strong class="tarjeta-nivel-total">1<\/strong>/s', $html, 'solo el de su sede');
    }

    public function test_el_administrador_en_todas_las_sedes_ve_el_tablero_sin_errores(): void
    {
        $this->comoAdminEnTodas();

        $this->get(route('dashboard'))->assertOk();
    }
}
