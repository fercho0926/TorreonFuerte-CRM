<?php

namespace Tests\Feature\Sedes;

use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class SedesAdminTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_solo_el_admin_accede_al_modulo_de_sedes(): void
    {
        $sede = $this->crearSede();

        $this->comoPastorDe($sede);
        $this->get(route('sedes.index'))->assertForbidden()->assertSee('No tienes permiso para esta acción');
        $this->get(route('sedes.create'))->assertForbidden();
        $this->post(route('sedes.store'), ['nombre' => 'X'])->assertForbidden();

        $this->comoLiderDe($sede);
        $this->get(route('sedes.index'))->assertForbidden();

        $this->iniciarSesionComo($this->crearUsuario('user', null, $sede->id));
        $this->get(route('sedes.index'))->assertForbidden();

        $this->comoAdmin();
        $this->get(route('sedes.index'))->assertOk();
    }

    public function test_el_menu_de_sedes_solo_aparece_para_el_admin(): void
    {
        $sede = $this->crearSede();

        $this->comoPastorDe($sede);
        $this->get(route('dashboard'))->assertOk()->assertDontSee(route('sedes.index'), false);

        $this->comoAdmin();
        $this->get(route('dashboard'))->assertOk()->assertSee(route('sedes.index'), false);
    }

    public function test_el_listado_muestra_pastor_pastora_y_conteos(): void
    {
        $sede = $this->crearSede(['nombre' => 'Iglesia Central']);
        $pastor = Persona::factory()->deSede($sede->id)->create(['nombres' => 'Pablo', 'apellidos' => 'Pastor']);
        $pastora = Persona::factory()->deSede($sede->id)->create(['nombres' => 'Paula', 'apellidos' => 'Pastora']);
        $sede->update(['pastor_persona_id' => $pastor->id, 'pastora_persona_id' => $pastora->id]);
        Persona::factory()->count(3)->deSede($sede->id)->create();

        $this->comoAdminEnTodas();

        $this->get(route('sedes.index'))
            ->assertOk()
            ->assertSee('Iglesia Central')
            ->assertSee('Pablo Pastor')
            ->assertSee('Paula Pastora')
            // 5 miembros más el dueño de la cuenta del administrador, que es miembro de la primera sede (spec 016).
            ->assertViewHas('pagina', fn ($p) => collect($p->items())->first(fn ($f) => $f['sede']->id === $sede->id)['personas'] === 6);
    }

    public function test_el_admin_puede_listar_sedes_estando_en_todas_las_sedes_y_en_una_activa(): void
    {
        $a = $this->crearSede(['nombre' => 'Sede A']);
        $this->crearSede(['nombre' => 'Sede B']);

        $this->comoAdminEnSede($a);
        $this->get(route('sedes.index'))->assertOk()->assertSee('Sede A')->assertSee('Sede B');
        $this->assertSame(2, Sede::count());
    }
}
