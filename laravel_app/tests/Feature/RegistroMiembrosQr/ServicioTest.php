<?php

namespace Tests\Feature\RegistroMiembrosQr;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\CambioHistorial;
use App\Models\RegistroMiembroQr;
use App\Models\Sede;
use App\Services\LiderazgoService;
use App\Services\RegistroMiembrosQr;
use App\Services\SedeContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class ServicioTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private Sede $sede;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sede = $this->crearSede();
    }

    private function registrar(array $datos, ?Sede $sede = null): array
    {
        return app(RegistroMiembrosQr::class)->registrar($sede ?? $this->sede, $datos + [
            'celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04', 'estado_inicial' => 'visitante',
        ]);
    }

    private function persona(array $atributos = []): Persona
    {
        return Persona::factory()->deSede($this->sede->id)->create($atributos + ['celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04']);
    }

    private function inactivar(Persona $persona): void
    {
        app(SedeContext::class)->conSede($this->sede->id, fn () => app(LiderazgoService::class)->inactivar($persona));
    }

    private function cambios(Persona $persona, string $accion)
    {
        return CambioHistorial::de('persona', $persona->id)->where('accion', $accion)->get();
    }

    // ---- Miembro nuevo ----

    public function test_crea_un_miembro_nuevo_con_el_estado_elegido_y_todos_los_datos(): void
    {
        foreach (['visitante', 'miembro_activo'] as $i => $estado) {
            $resultado = $this->registrar([
                'celular' => '31011122'.$i.'0', 'estado_inicial' => $estado, 'email' => 'ana@correo.com',
                'direccion' => 'Calle 1', 'barrio' => 'Centro', 'ciudad' => 'Bogotá',
            ]);

            $persona = $resultado['persona']->fresh();
            $this->assertSame('nueva', $resultado['resultado']);
            $this->assertSame($estado, $persona->estado);
            $this->assertSame($this->sede->id, $persona->sede_id);
            $this->assertSame(['ana@correo.com', 'Calle 1', 'Centro', 'Bogotá'], [$persona->email, $persona->direccion, $persona->barrio, $persona->ciudad]);
            $this->assertCount(1, $this->cambios($persona, 'creada_qr'));
            $this->assertNull($this->cambios($persona, 'creada_qr')->first()->user_id);
        }

        $this->assertSame(2, RegistroMiembroQr::withoutGlobalScopes()->where('resultado', 'nueva')->count());
    }

    public function test_los_datos_opcionales_vacios_quedan_nulos(): void
    {
        $persona = $this->registrar(['email' => '', 'direccion' => '  ', 'barrio' => null])['persona']->fresh();

        $this->assertNull($persona->email);
        $this->assertNull($persona->direccion);
        $this->assertNull($persona->barrio);
    }

    public function test_un_miembro_nuevo_sin_estado_inicial_se_rechaza(): void
    {
        $this->expectException(ValidationException::class);

        try {
            $this->registrar(['estado_inicial' => null]);
        } finally {
            $this->assertSame(0, Persona::withoutGlobalScopes()->where('sede_id', $this->sede->id)->count());
        }
    }

    public function test_el_doble_envio_no_duplica_el_miembro(): void
    {
        $primero = $this->registrar([]);
        $segundo = $this->registrar([]);

        $this->assertSame('nueva', $primero['resultado']);
        $this->assertSame('sin_cambios', $segundo['resultado']);
        $this->assertSame(1, Persona::withoutGlobalScopes()->where('sede_id', $this->sede->id)->count());
        $this->assertCount(1, $this->cambios($primero['persona'], 'creada_qr'));
    }

    // ---- Actualizar ----

    public function test_actualiza_solo_los_campos_que_cambiaron(): void
    {
        $persona = $this->persona(['direccion' => 'Calle vieja', 'barrio' => 'Centro', 'estado' => 'miembro_activo']);

        $resultado = $this->registrar(['direccion' => 'Calle nueva 123', 'email' => 'ana@correo.com', 'barrio' => 'centro']);

        $this->assertSame('actualizada', $resultado['resultado']);
        $this->assertEqualsCanonicalizing(['direccion', 'email'], array_keys($resultado['cambios']));
        $persona->refresh();
        $this->assertSame('Calle nueva 123', $persona->direccion);
        $this->assertSame('ana@correo.com', $persona->email);
        $this->assertSame('Centro', $persona->barrio); // solo cambió mayúsculas: no es cambio

        $historial = $this->cambios($persona, 'actualizada_qr');
        $this->assertCount(1, $historial);
        $this->assertSame('Calle vieja', $historial->first()->antes['direccion']);
        $this->assertSame('Calle nueva 123', $historial->first()->despues['direccion']);
        $this->assertSame('QR de miembros', $historial->first()->despues['origen']);

        $registro = RegistroMiembroQr::withoutGlobalScopes()->latest('id')->first();
        $this->assertSame('actualizada', $registro->resultado);
        $this->assertSame(['Calle vieja', 'Calle nueva 123'], $registro->cambios['direccion']);
        $this->assertSame($persona->id, $registro->persona_id);
    }

    public function test_un_campo_vacio_nunca_borra_un_dato_guardado(): void
    {
        $persona = $this->persona(['barrio' => 'Centro', 'email' => 'ana@correo.com', 'direccion' => 'Calle 1']);

        $resultado = $this->registrar(['barrio' => '', 'email' => null, 'direccion' => '   ']);

        $this->assertSame('sin_cambios', $resultado['resultado']);
        $persona->refresh();
        $this->assertSame(['Centro', 'ana@correo.com', 'Calle 1'], [$persona->barrio, $persona->email, $persona->direccion]);
    }

    public function test_sin_cambios_no_escribe_en_la_ficha_ni_en_el_historial(): void
    {
        $persona = $this->persona(['fecha_nacimiento' => '1990-05-04']);
        $actualizada = $persona->fresh()->updated_at;
        $antes = CambioHistorial::where('sujeto_tipo', 'persona')->count();

        $resultado = $this->registrar([]);

        $this->assertSame('sin_cambios', $resultado['resultado']);
        $this->assertEquals($actualizada, $persona->fresh()->updated_at);
        $this->assertSame($antes, CambioHistorial::where('sujeto_tipo', 'persona')->count());
    }

    public function test_dos_sin_cambios_seguidos_no_duplican_el_registro(): void
    {
        $this->persona(['fecha_nacimiento' => '1990-05-04']);

        $this->registrar([]);
        $this->registrar([]);

        $this->assertSame(1, RegistroMiembroQr::withoutGlobalScopes()->where('resultado', 'sin_cambios')->count());
    }

    public function test_no_toca_celular_estado_lider_celulas_ni_sede(): void
    {
        $lider = Persona::factory()->deSede($this->sede->id)->lider()->create();
        $persona = $this->persona(['estado' => 'nuevo_creyente', 'lider_id' => $lider->id]);
        $celula = Celula::factory()->create(['sede_id' => $this->sede->id, 'lider_id' => $lider->id]);
        $celula->miembros()->attach($persona->id);

        $this->registrar(['estado_inicial' => 'visitante', 'nombres' => 'Ana María']);

        $persona->refresh();
        $this->assertSame('Ana María', $persona->nombres);
        $this->assertSame('nuevo_creyente', $persona->estado);
        $this->assertSame($lider->id, $persona->lider_id);
        $this->assertSame('3101112233', $persona->celular);
        $this->assertSame($this->sede->id, $persona->sede_id);
        $this->assertTrue($celula->miembros()->whereKey($persona->id)->exists());
    }

    public function test_la_cedula_solo_se_escribe_si_la_ficha_no_tiene(): void
    {
        $sinCedula = $this->persona(['tipo_documento' => null, 'numero_documento' => null]);
        $this->registrar(['tipo_documento' => 'CC', 'numero_documento' => '1020304050']);
        $this->assertSame(['CC', '1020304050'], [$sinCedula->fresh()->tipo_documento, $sinCedula->fresh()->numero_documento]);

        $conCedula = $this->persona(['celular' => '3209998877', 'tipo_documento' => 'CC', 'numero_documento' => '555']);
        $resultado = $this->registrar(['celular' => '3209998877', 'tipo_documento' => 'CE', 'numero_documento' => '555']);
        $this->assertSame('sin_cambios', $resultado['resultado']);
        $this->assertSame(['CC', '555'], [$conCedula->fresh()->tipo_documento, $conCedula->fresh()->numero_documento]);
    }

    public function test_un_celular_con_formato_distinto_encuentra_al_mismo_miembro(): void
    {
        $persona = $this->persona(['celular' => '310 111 2233']);

        $resultado = $this->registrar(['celular' => '+57 (310) 111-2233', 'barrio' => 'Norte']);

        $this->assertSame($persona->id, $resultado['persona']->id);
        $this->assertSame('actualizada', $resultado['resultado']);
        $this->assertSame(1, Persona::withoutGlobalScopes()->where('sede_id', $this->sede->id)->count());
    }

    // ---- Casos especiales ----

    public function test_cedula_y_celular_de_miembros_distintos_se_rechaza_sin_escribir(): void
    {
        $this->persona(['numero_documento' => '111']);
        $otro = $this->persona(['celular' => '3200000000', 'numero_documento' => '222']);

        try {
            $this->registrar(['celular' => '3200000000', 'numero_documento' => '111', 'tipo_documento' => 'CC', 'barrio' => 'X']);
            $this->fail('Debió rechazar el conflicto');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('celular', $e->errors());
        }

        $this->assertNull($otro->fresh()->barrio);
        $this->assertSame(0, RegistroMiembroQr::withoutGlobalScopes()->count());
    }

    public function test_celular_repetido_en_dos_fichas_se_rechaza(): void
    {
        $this->persona();
        $this->persona(['numero_documento' => '999']);

        $this->expectException(ValidationException::class);
        $this->registrar(['barrio' => 'X']);
    }

    public function test_un_miembro_inactivo_se_reactiva_con_su_estado_anterior(): void
    {
        $persona = $this->persona(['estado' => 'miembro_activo']);
        $this->inactivar($persona);

        $resultado = $this->registrar(['barrio' => 'Norte']);

        $this->assertSame('reactivada', $resultado['resultado']);
        $persona->refresh();
        $this->assertSame('miembro_activo', $persona->estado);
        $this->assertNull($persona->inactivado_en);
        $this->assertSame('Norte', $persona->barrio);
        $historial = $this->cambios($persona, 'reactivada_qr');
        $this->assertCount(1, $historial);
        $this->assertSame('QR de miembros', $historial->first()->despues['origen']);
        $this->assertSame('miembro_activo', $historial->first()->despues['estado']);
        $this->assertCount(0, $this->cambios($persona, 'actualizada_qr'));
    }

    public function test_un_miembro_eliminado_se_restaura_y_reactiva(): void
    {
        $persona = $this->persona(['estado' => 'visitante']);
        $persona->delete();

        $resultado = $this->registrar(['barrio' => 'Norte']);

        $this->assertSame('reactivada', $resultado['resultado']);
        $persona = Persona::withoutGlobalScopes()->findOrFail($persona->id);
        $this->assertNull($persona->deleted_at);
        $this->assertSame('Norte', $persona->barrio);
        $this->assertSame(1, Persona::withoutGlobalScopes()->withTrashed()->where('sede_id', $this->sede->id)->count());
    }

    public function test_un_miembro_solo_de_otra_sede_no_se_toca_y_se_crea_uno_nuevo(): void
    {
        $otraSede = $this->crearSede();
        $ajeno = Persona::factory()->deSede($otraSede->id)->create(['celular' => '3101112233', 'nombres' => 'Ajeno', 'barrio' => 'Original']);

        $resultado = $this->registrar(['barrio' => 'Cambiado']);

        $this->assertSame('nueva', $resultado['resultado']);
        $this->assertNotSame($ajeno->id, $resultado['persona']->id);
        $this->assertSame($this->sede->id, $resultado['persona']->sede_id);
        $ajeno->refresh();
        $this->assertSame(['Ajeno', 'Original'], [$ajeno->nombres, $ajeno->barrio]);
    }
}
