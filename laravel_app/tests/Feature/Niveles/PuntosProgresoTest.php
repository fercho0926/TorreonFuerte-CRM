<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Progreso resumido (puntos) en la grilla de miembros (spec 014, RF-005, RF-017). */
class PuntosProgresoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function completar(Persona $p, int ...$posiciones): void
    {
        foreach ($posiciones as $posicion) {
            AvanceNivel::factory()->de($p, NivelFormacion::where('posicion', $posicion)->firstOrFail())->create();
        }
    }

    public function test_el_componente_pinta_un_punto_por_nivel_con_etiqueta_accesible(): void
    {
        $p = $this->A['persona'];
        $this->completar($p, 1, 2);
        $p->load('avances');

        $html = view('components.niveles.puntos', ['miembro' => $p])->render();

        $this->assertSame(4, substr_count($html, 'class="punto punto--'));
        $this->assertSame(2, substr_count($html, 'punto--completado'));
        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('aria-label="2 de 4 niveles; actual: Aliviando el fuego"', $html);
        $this->assertStringContainsString('Aliviando el fuego', strip_tags($html));
    }

    public function test_sin_avances_dice_sin_iniciar(): void
    {
        $p = $this->A['persona']->load('avances');

        $html = view('components.niveles.puntos', ['miembro' => $p])->render();

        $this->assertStringContainsString('Sin iniciar', $html);
        $this->assertStringContainsString('aria-label="0 de 4 niveles; sin iniciar"', $html);
    }

    public function test_la_grilla_de_miembros_muestra_la_columna_nivel_con_los_puntos(): void
    {
        $this->completar($this->A['persona'], 1, 2, 3);

        $json = $this->getJson(route('personas.datos', ['length' => 50]))->assertOk()->json('data');
        $texto = json_encode($json, JSON_UNESCAPED_UNICODE);

        $this->assertStringContainsString('Caminando en santidad', $texto);
        $this->assertStringContainsString('Sin iniciar', $texto);
    }

    public function test_los_avances_se_cargan_de_una_vez_no_por_fila(): void
    {
        foreach (Persona::factory()->count(25)->deSede($this->A['sede']->id)->create() as $p) {
            $this->completar($p, 1);
        }

        DB::enableQueryLog();
        $this->getJson(route('personas.datos', ['length' => 50]))->assertOk();
        $consultasDeAvances = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'avances_nivel') || str_contains($q['query'], 'niveles_formacion'))->count();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(3, $consultasDeAvances, 'niveles y avances: unas pocas consultas, sin importar cuántos miembros haya');
    }

    public function test_sin_lectura_en_niveles_la_columna_no_aparece(): void
    {
        $rol = \App\Models\Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'miembros' => 1])->create();
        $usuario = $this->crearUsuario($rol->clave, $this->A['lider'], $this->A['sede']->id);
        $this->iniciarSesionComo($usuario);

        $this->get(route('personas.index'))->assertOk()->assertDontSee('data-columna="nivel"', false);
    }
}
