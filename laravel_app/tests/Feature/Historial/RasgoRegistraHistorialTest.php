<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Registro automático de altas, ediciones y bajas (rasgo RegistraHistorial, spec 015 D2). */
class RasgoRegistraHistorialTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    private function entradas()
    {
        return CambioHistorial::query()->where('sujeto_tipo', 'celula')->orderBy('id')->get();
    }

    public function test_el_alta_registra_creado_con_la_foto_del_registro(): void
    {
        $lider = Persona::factory()->create(['nombres' => 'Luis', 'apellidos' => 'Guía']);
        Celula::factory()->create(['nombre' => 'Los Olivos', 'lider_id' => $lider->id]);

        $alta = $this->entradas()->sole();
        $this->assertSame('creado', $alta->accion);
        $this->assertSame('Los Olivos', $alta->sujeto_nombre);
        $this->assertSame('Los Olivos', $alta->detalle['datos']['Nombre']);
        $this->assertSame('Luis Guía', $alta->detalle['datos']['Líder']);
    }

    public function test_una_edicion_sin_cambios_no_escribe_y_con_cambios_guarda_antes_y_despues(): void
    {
        $celula = Celula::factory()->create(['nombre' => 'Los Olivos']);

        $celula->update(['nombre' => 'Los Olivos']);
        $this->assertCount(1, $this->entradas());

        $celula->update(['nombre' => 'Las Palmas']);
        $edicion = $this->entradas()->last();
        $this->assertSame('editado', $edicion->accion);
        $this->assertSame([['Nombre', 'Los Olivos', 'Las Palmas']], collect($edicion->cambios)->map(fn ($c) => [$c['etiqueta'], $c['antes'], $c['despues']])->all());
        $this->assertSame('Los Olivos', $edicion->antes['nombre']);
        $this->assertSame('Las Palmas', $edicion->despues['nombre']);
    }

    public function test_la_baja_registra_eliminado_con_sus_datos(): void
    {
        $celula = Celula::factory()->create(['nombre' => 'Efímera']);
        $celula->delete();

        $baja = $this->entradas()->last();
        $this->assertSame('eliminado', $baja->accion);
        $this->assertSame('Efímera', $baja->detalle['datos']['Nombre']);
    }

    public function test_cambiar_solo_el_token_del_qr_dice_que_cambio_sin_guardar_el_valor(): void
    {
        $celula = Celula::factory()->create();
        $token = $celula->registro_token;

        $celula->forceFill(['registro_token' => str_repeat('x', 32)])->save();

        $edicion = $this->entradas()->last();
        $this->assertTrue($edicion->cambios[0]['secreto']);
        $this->assertStringNotContainsString($token, json_encode($edicion->getAttributes()));
        $this->assertStringNotContainsString(str_repeat('x', 32), json_encode($edicion->getAttributes()));
    }
}
