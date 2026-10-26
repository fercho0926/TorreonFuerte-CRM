<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Services\Historial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** El historial no se puede modificar ni borrar, y sobrevive a lo que nombra (spec 015, RF-008). */
class InalterableTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    private function entrada(): CambioHistorial
    {
        return app(Historial::class)->registrar(Persona::factory()->create(), 'estado', ['estado' => 'visitante'], ['estado' => 'celula']);
    }

    public function test_no_se_puede_modificar(): void
    {
        $entrada = $this->entrada();

        $this->expectException(LogicException::class);
        $entrada->update(['accion' => 'editada']);
    }

    public function test_no_se_puede_guardar_con_cambios(): void
    {
        $entrada = $this->entrada();
        $entrada->actor_nombre = 'Otro';

        $this->expectException(LogicException::class);
        $entrada->save();
    }

    public function test_no_se_puede_borrar(): void
    {
        $entrada = $this->entrada();

        $this->expectException(LogicException::class);
        $entrada->delete();
    }

    public function test_borrar_la_persona_o_el_usuario_no_toca_sus_entradas(): void
    {
        $usuario = $this->crearUsuario('pastor');
        $persona = Persona::factory()->create(['nombres' => 'Ana', 'apellidos' => 'Pérez']);
        $entrada = app(Historial::class)->registrar($persona, 'estado', ['estado' => 'visitante'], ['estado' => 'celula'], $usuario);
        $nombreUsuario = $usuario->name;

        $persona->forceDelete();
        $usuario->delete();

        $guardada = CambioHistorial::findOrFail($entrada->id);
        $this->assertSame($persona->id, $guardada->sujeto_id);
        $this->assertSame('Ana Pérez', $guardada->sujeto_nombre);
        $this->assertSame($usuario->id, $guardada->user_id);
        $this->assertSame($nombreUsuario, $guardada->actor_nombre);
    }
}
