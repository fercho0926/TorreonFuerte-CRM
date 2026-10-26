<?php

namespace Tests\Unit\Historial;

use App\Models\Persona;
use App\Models\User;
use App\Services\Historial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Diferencia campo a campo y valores legibles (spec 015, RF-003, RF-007). */
class DiferenciaTest extends TestCase
{
    use RefreshDatabase;

    private function cambios(string $modelo, array $antes, array $despues, array $secretos = []): array
    {
        return collect(app(Historial::class)->cambios($modelo, $antes, $despues, $secretos))->keyBy('campo')->all();
    }

    public function test_solo_lista_los_campos_que_cambiaron_con_su_etiqueta(): void
    {
        $cambios = $this->cambios(Persona::class,
            ['direccion' => 'Calle 10 # 5-20', 'celular' => '3001112233', 'nombres' => 'Ana'],
            ['direccion' => 'Carrera 8 # 3-15', 'celular' => '3001112233', 'nombres' => 'Ana'],
        );

        $this->assertSame(['direccion'], array_keys($cambios));
        $this->assertSame('Dirección', $cambios['direccion']['etiqueta']);
        $this->assertSame('Calle 10 # 5-20', $cambios['direccion']['antes']);
        $this->assertSame('Carrera 8 # 3-15', $cambios['direccion']['despues']);
    }

    public function test_traduce_claves_foraneas_estados_fechas_y_booleanos(): void
    {
        $lider = Persona::factory()->create(['nombres' => 'Luis', 'apellidos' => 'Guía']);

        $cambios = $this->cambios(Persona::class,
            ['lider_id' => null, 'estado' => 'visitante', 'fecha_nacimiento' => '1990-03-12', 'es_lider' => false],
            ['lider_id' => $lider->id, 'estado' => 'miembro_activo', 'fecha_nacimiento' => '1991-03-12', 'es_lider' => true],
        );

        $this->assertSame('(vacío)', $cambios['lider_id']['antes']);
        $this->assertSame('Luis Guía', $cambios['lider_id']['despues']);
        $this->assertSame('Visitante', $cambios['estado']['antes']);
        $this->assertSame('Miembro activo', $cambios['estado']['despues']);
        $this->assertSame('12 mar. 1991', $cambios['fecha_nacimiento']['despues']);
        $this->assertSame('No', $cambios['es_lider']['antes']);
        $this->assertSame('Sí', $cambios['es_lider']['despues']);
    }

    public function test_marca_los_textos_largos(): void
    {
        $cambios = $this->cambios(Persona::class, ['direccion' => 'corta'], ['direccion' => str_repeat('a', 121)]);

        $this->assertTrue($cambios['direccion']['largo']);
    }

    public function test_los_secretos_solo_dicen_que_cambiaron(): void
    {
        $cambios = $this->cambios(User::class, [], [], ['password']);

        $this->assertTrue($cambios['password']['secreto']);
        $this->assertSame('', $cambios['password']['antes']);
        $this->assertSame('', $cambios['password']['despues']);
    }

    public function test_reconoce_los_campos_secretos_por_su_nombre(): void
    {
        $historial = app(Historial::class);

        foreach (['password', 'remember_token', 'clave_temporal', 'token', 'registro_token', 'codigo_recuperacion', 'origen_hash'] as $campo) {
            $this->assertTrue($historial->esSecreto(User::class, $campo), $campo);
        }
        foreach (['nombres', 'celular', 'debe_cambiar_clave'] as $campo) {
            $this->assertFalse($historial->esSecreto(User::class, $campo), $campo);
        }
    }

    public function test_ignora_los_campos_tecnicos(): void
    {
        $cambios = $this->cambios(Persona::class, ['updated_at' => '2026-01-01', 'inactivado_en' => null], ['updated_at' => '2026-02-01', 'inactivado_en' => '2026-02-01']);

        $this->assertSame([], $cambios);
    }
}
