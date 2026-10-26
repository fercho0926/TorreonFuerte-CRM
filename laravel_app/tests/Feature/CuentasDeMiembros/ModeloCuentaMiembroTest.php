<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Services\SedeContext;
use Database\Factories\SedeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Relación cuenta ↔ miembro (spec 016, fundamentos). */
class ModeloCuentaMiembroTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_miembro_calcula_su_clave_de_celular_al_guardar(): void
    {
        $persona = Persona::factory()->create(['celular' => '300-123-4567']);
        $this->assertSame('3001234567', $persona->fresh()->celular_clave);

        $persona->update(['celular' => '+57 311 000 0000']);
        $this->assertSame('3110000000', $persona->fresh()->celular_clave);

        $persona->update(['celular' => null]);
        $this->assertNull($persona->fresh()->celular_clave);
    }

    public function test_la_fabrica_de_usuarios_siempre_crea_su_miembro_en_la_sede_de_la_cuenta(): void
    {
        $sede = Sede::factory()->create();
        $usuario = User::factory()->create(['role' => 'pastor', 'sede_id' => $sede->id]);

        $this->assertNotNull($usuario->persona_id);
        $this->assertSame($sede->id, Persona::withoutGlobalScopes()->find($usuario->persona_id)->sede_id);
        $this->assertTrue($usuario->persona->cuenta->is($usuario));
    }

    public function test_el_miembro_del_administrador_queda_en_la_sede_por_defecto(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertNull($admin->sede_id);
        $this->assertSame(SedeFactory::idPorDefecto(), $admin->persona->sede_id);
    }

    public function test_la_fabrica_pasa_nombre_y_celular_al_miembro(): void
    {
        $usuario = User::factory()->conMiembro(['nombres' => 'Milton', 'apellidos' => 'Pérez', 'celular' => '3001234567'])->create();

        $this->assertSame('Milton', $usuario->persona->nombres);
        $this->assertSame('3001234567', $usuario->persona->celular);
    }

    public function test_el_miembro_de_la_cuenta_se_lee_aunque_la_sede_activa_sea_otra(): void
    {
        $otra = Sede::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $persona = app(SedeContext::class)->conSede($otra->id, fn () => User::withoutGlobalScopes()->find($admin->id)->persona);

        $this->assertNotNull($persona);
        $this->assertSame($admin->persona_id, $persona->id);
    }

    public function test_pastor_y_pastora_son_pastorales(): void
    {
        $sede = Sede::factory()->create();
        $pastor = Persona::factory()->deSede($sede->id)->pastorDe($sede)->create();
        $pastora = Persona::factory()->deSede($sede->id)->create();
        $sede->update(['pastora_persona_id' => $pastora->id]);
        $otro = Persona::factory()->deSede($sede->id)->create();

        $this->assertTrue($pastor->fresh()->esPastoral());
        $this->assertTrue($pastora->fresh()->esPastoral());
        $this->assertFalse($otro->fresh()->esPastoral());
        $this->assertTrue($pastor->fresh()->es_lider);
        $this->assertSame($pastor->id, $sede->fresh()->pastor_persona_id);
    }
}
