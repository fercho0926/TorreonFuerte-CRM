<?php

namespace Tests\Feature\Lideres;

use App\Models\Celula;
use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Services\SedeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 6: al cambiar el pastor de la sede se mantiene una sola cabeza (spec 016, research D10). */
class CambioDePastorTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private Sede $sede;

    private Persona $juan;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sede = $this->crearSede(['nombre' => 'Sede Norte']);
        $this->juan = Persona::factory()->pastorDe($this->sede)->create(['nombres' => 'Juan']);
        User::factory()->create(['role' => 'pastor', 'sede_id' => $this->sede->id, 'persona_id' => $this->juan->id]);
        $this->admin = $this->comoAdminEnSede($this->sede);
    }

    private function lider(?int $liderId = null): Persona
    {
        return Persona::factory()->deSede($this->sede->id)->lider()->create(['lider_id' => $liderId ?? $this->juan->id]);
    }

    public function test_los_dependientes_del_pastor_pasan_al_nuevo_sin_salir_de_sus_celulas(): void
    {
        $lideres = collect([$this->lider(), $this->lider(), $this->lider()]);
        $pedro = $this->lider();
        $cuentaPedro = User::factory()->create(['role' => 'lider_celula', 'sede_id' => $this->sede->id, 'persona_id' => $pedro->id]);
        $celula = Celula::factory()->create(['sede_id' => $this->sede->id, 'lider_id' => $this->juan->id]);
        $celula->miembros()->attach($lideres[0]->id);

        app(SedeService::class)->cambiarPastor($this->sede, 'pastor', ['user_id' => $cuentaPedro->id], $this->admin);

        $pedro->refresh();
        $this->assertNull($pedro->lider_id);
        $this->assertTrue($pedro->es_lider);
        foreach ($lideres as $lider) {
            $this->assertSame($pedro->id, $lider->fresh()->lider_id);
        }
        $this->assertTrue($celula->miembros()->whereKey($lideres[0]->id)->exists(), 'sigue en su célula');

        $juan = $this->juan->fresh();
        $this->assertTrue($juan->es_lider);
        $this->assertSame($pedro->id, $juan->lider_id);
        $this->assertSame('lider_celula', User::withoutGlobalScopes()->where('persona_id', $juan->id)->value('role'));

        $this->assertTrue(CambioHistorial::query()->de('persona', $lideres[1]->id)->where('accion', 'lider')->exists());
    }

    public function test_al_cambiar_la_pastora_nadie_se_mueve_y_la_anterior_queda_bajo_el_pastor(): void
    {
        $marta = Persona::factory()->deSede($this->sede->id)->lider()->create(['lider_id' => null]);
        $this->sede->update(['pastora_persona_id' => $marta->id]);
        $lider = $this->lider();

        app(SedeService::class)->cambiarPastor($this->sede->fresh(), 'pastora', [
            'nombres' => 'Lucía', 'apellidos' => 'Nueva', 'email' => 'lucia@iglesia.co', 'password' => 'secreto-123',
        ], $this->admin);

        $lucia = Persona::where('nombres', 'Lucía')->firstOrFail();
        $this->assertNull($lucia->lider_id);
        $this->assertTrue($lucia->es_lider);
        $this->assertSame($this->juan->id, $lider->fresh()->lider_id);
        $this->assertSame($this->juan->id, $marta->fresh()->lider_id);
    }
}
