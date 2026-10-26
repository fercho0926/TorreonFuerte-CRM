<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 4: el administrador revisa los vínculos dudosos de la unificación (spec 016, research D7). */
class RevisionVinculoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private User $cuenta;

    private Persona $automatico;

    private Persona $candidato;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $sede = $this->A['sede']->id;
        $this->candidato = Persona::factory()->deSede($sede)->create(['nombres' => 'Milton', 'apellidos' => 'Pérez', 'celular' => '3001234567']);
        $this->automatico = Persona::factory()->deSede($sede)->create(['nombres' => 'Milton', 'apellidos' => 'P', 'celular' => '3001234567']);
        $this->cuenta = User::factory()->create(['role' => 'user', 'sede_id' => $sede, 'persona_id' => $this->automatico->id]);
        $this->cuenta->forceFill(['revision_vinculo' => [
            'candidatos' => [$this->candidato->id], 'motivo' => 'nombre_distinto', 'creado_automatico' => $this->automatico->id,
        ]])->save();
    }

    public function test_el_filtro_por_revisar_solo_lista_las_cuentas_marcadas(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $filas = $this->getJson(route('users.datos', ['length' => 25, 'vinculo' => 'revisar']))->assertOk()->json('data');

        $this->assertCount(1, $filas);
        $this->assertStringContainsString($this->cuenta->email, json_encode($filas));
    }

    public function test_elegir_un_candidato_vincula_y_elimina_el_miembro_automatico_sin_datos(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->get(route('users.edit', $this->cuenta))->assertOk()->assertSee('name="vincular_a"', false)->assertSee('Milton Pérez');

        $this->put(route('users.update', $this->cuenta), ['email' => $this->cuenta->email, 'role' => 'user', 'vincular_a' => $this->candidato->id])
            ->assertRedirect(route('users.index'));

        $cuenta = $this->cuenta->fresh();
        $this->assertSame($this->candidato->id, $cuenta->persona_id);
        $this->assertNull($cuenta->revision_vinculo);
        $this->assertSoftDeleted('personas', ['id' => $this->automatico->id]);
    }

    public function test_el_miembro_automatico_con_datos_se_conserva_con_aviso(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        Celula::factory()->create(['sede_id' => $this->A['sede']->id])->miembros()->attach($this->automatico->id);

        $this->put(route('users.update', $this->cuenta), ['email' => $this->cuenta->email, 'role' => 'user', 'vincular_a' => $this->candidato->id])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('warning');

        $this->assertNotSoftDeleted('personas', ['id' => $this->automatico->id]);
    }

    public function test_un_pastor_no_ve_ni_usa_la_revision(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get(route('users.edit', $this->cuenta))->assertOk()->assertDontSee('name="vincular_a"', false);
        $this->put(route('users.update', $this->cuenta), ['email' => $this->cuenta->email, 'role' => 'user', 'vincular_a' => $this->candidato->id]);

        $this->assertSame($this->automatico->id, $this->cuenta->fresh()->persona_id);
    }
}
