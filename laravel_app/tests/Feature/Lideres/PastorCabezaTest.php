<?php

namespace Tests\Feature\Lideres;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 5: todo líder nuevo o designado queda bajo el pastor de la sede (spec 016). */
class PastorCabezaTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private Sede $sede;

    private Persona $pastor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sede = $this->crearSede(['nombre' => 'Sede Norte']);
        $this->pastor = Persona::factory()->pastorDe($this->sede)->create(['nombres' => 'Juan', 'apellidos' => 'Pastor']);
        $this->comoAdminEnSede($this->sede);
    }

    private function miembro(array $datos = []): Persona
    {
        return Persona::factory()->deSede($this->sede->id)->create($datos);
    }

    private function datos(Persona $p, array $cambios = []): array
    {
        return $cambios + [
            'nombres' => $p->nombres, 'apellidos' => $p->apellidos, 'estado' => $p->estado,
            'celular' => $p->celular, 'email' => $p->email, 'lider_id' => $p->lider_id, 'es_lider' => $p->es_lider ? 1 : 0,
        ];
    }

    public function test_designar_lider_lo_pone_bajo_el_pastor_aunque_tuviera_otro_lider(): void
    {
        $otroLider = $this->miembro()->forceFill(['es_lider' => true, 'lider_id' => $this->pastor->id]);
        $otroLider->save();
        $rosa = $this->miembro(['nombres' => 'Rosa', 'apellidos' => 'Gómez', 'lider_id' => $otroLider->id]);

        $this->post(route('lideres.designar.store'), ['persona_id' => $rosa->id])
            ->assertRedirect(route('lideres.show', $rosa))
            ->assertSessionHas('success', 'Rosa Gómez ahora es líder, bajo el pastor Juan Pastor.');

        $rosa->refresh();
        $this->assertTrue($rosa->es_lider);
        $this->assertSame($this->pastor->id, $rosa->lider_id);
        $acciones = CambioHistorial::query()->de('persona', $rosa->id)->pluck('accion')->all();
        $this->assertContains('rol_lider', $acciones);
        $this->assertContains('lider', $acciones);
    }

    public function test_crear_un_miembro_como_lider_lo_pone_bajo_el_pastor(): void
    {
        $this->post(route('personas.store'), ['nombres' => 'Nuevo', 'apellidos' => 'Lider', 'estado' => 'miembro_activo', 'celular' => '3001112233', 'es_lider' => 1])
            ->assertSessionHasNoErrors();

        $nuevo = Persona::where('nombres', 'Nuevo')->firstOrFail();
        $this->assertTrue($nuevo->es_lider);
        $this->assertSame($this->pastor->id, $nuevo->lider_id);
    }

    public function test_editar_a_un_miembro_para_hacerlo_lider_lo_pone_bajo_el_pastor(): void
    {
        $miembro = $this->miembro();

        $this->put(route('personas.update', $miembro), $this->datos($miembro, ['es_lider' => 1]))->assertSessionHasNoErrors();

        $this->assertSame($this->pastor->id, $miembro->fresh()->lider_id);
    }

    public function test_un_lider_no_puede_quedar_sin_lider(): void
    {
        $lider = $this->miembro(['es_lider' => true, 'lider_id' => $this->pastor->id]);

        $this->put(route('personas.update', $lider), $this->datos($lider, ['lider_id' => '']))
            ->assertSessionHasErrors(['lider_id' => 'Todo líder depende del pastor o de otro líder.']);

        $this->assertSame($this->pastor->id, $lider->fresh()->lider_id);
    }

    public function test_al_pastor_no_se_le_asigna_lider(): void
    {
        $lider = $this->miembro(['es_lider' => true, 'lider_id' => $this->pastor->id]);

        $this->put(route('personas.update', $this->pastor), $this->datos($this->pastor, ['lider_id' => $lider->id]))
            ->assertSessionHasErrors(['lider_id' => 'El pastor es la cabeza de la sede y no tiene líder.']);

        $this->assertNull($this->pastor->fresh()->lider_id);
    }

    public function test_en_una_sede_sin_pastor_no_se_crean_ni_designan_lideres(): void
    {
        $sinPastor = $this->crearSede(['nombre' => 'Sede Sin Pastor']);
        $this->comoAdminEnSede($sinPastor);
        $miembro = Persona::factory()->deSede($sinPastor->id)->create();

        $this->post(route('lideres.designar.store'), ['persona_id' => $miembro->id])
            ->assertSessionHasErrors(['persona_id' => 'Primero asigna el pastor de la sede.']);
        $this->assertFalse($miembro->fresh()->es_lider);

        $this->post(route('personas.store'), ['nombres' => 'Otro', 'apellidos' => 'Lider', 'estado' => 'miembro_activo', 'celular' => '3001112244', 'es_lider' => 1])
            ->assertSessionHasErrors(['lider_id' => 'Primero asigna el pastor de la sede.']);
        $this->assertSame(0, Persona::where('nombres', 'Otro')->count());
    }

    public function test_un_lider_se_puede_mover_bajo_otro_lider_sin_ciclos(): void
    {
        $ana = $this->miembro(['es_lider' => true, 'lider_id' => $this->pastor->id]);
        $luis = $this->miembro(['es_lider' => true, 'lider_id' => $this->pastor->id]);

        $this->put(route('personas.update', $luis), $this->datos($luis, ['lider_id' => $ana->id]))->assertSessionHasNoErrors();
        $this->assertSame($ana->id, $luis->fresh()->lider_id);

        $ana->refresh();
        $this->put(route('personas.update', $ana), $this->datos($ana, ['lider_id' => $luis->id]))->assertSessionHasErrors('lider_id');
    }

    public function test_al_quitar_el_rol_sus_lideres_suben_y_no_quedan_sin_lider(): void
    {
        $ana = $this->miembro(['es_lider' => true, 'lider_id' => $this->pastor->id]);
        $luis = $this->miembro(['es_lider' => true, 'lider_id' => $ana->id]);
        $eva = $this->miembro(['lider_id' => $ana->id]);

        $this->delete(route('lideres.destroy', $ana), ['confirmar_sin_lider' => 1])->assertRedirect(route('lideres.index'));

        $this->assertSame($this->pastor->id, $luis->fresh()->lider_id);
        $this->assertNull($eva->fresh()->lider_id);
        $this->assertSame($this->pastor->id, $ana->fresh()->lider_id, 'quien deja de ser líder conserva su líder');
    }
}
