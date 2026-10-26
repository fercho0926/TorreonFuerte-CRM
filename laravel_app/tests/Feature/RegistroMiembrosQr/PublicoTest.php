<?php

namespace Tests\Feature\RegistroMiembrosQr;

use App\Models\Persona;
use App\Models\RegistroMiembroQr;
use App\Models\Sede;
use App\Services\LiderazgoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PublicoTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private Sede $sede;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sede = $this->crearSede(['nombre' => 'Iglesia Central']);
        RateLimiter::clear('registro-miembros:127.0.0.1');
    }

    private function datos(array $extra = []): array
    {
        return $extra + ['celular' => '310 111 2233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04', 'estado_inicial' => 'visitante'];
    }

    private function enviar(array $extra = [])
    {
        return $this->post(route('registro-sede.store', $this->sede->registro_token), $this->datos($extra));
    }

    public function test_el_formulario_muestra_la_sede_y_pide_el_celular(): void
    {
        $this->get(route('registro-sede.show', $this->sede->registro_token))
            ->assertOk()
            ->assertSee('Iglesia Central')
            ->assertSee('name="celular"', false)
            ->assertSee('name="numero_documento"', false)
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_flujo_de_dos_pasos_sin_javascript_para_un_miembro_nuevo(): void
    {
        $paso2 = $this->post(route('registro-sede.buscar', $this->sede->registro_token), ['celular' => '3101112233'])
            ->assertOk()
            ->assertSee('Completa tus datos')
            ->assertSee('name="estado_inicial"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="direccion"', false)
            ->assertSee('name="barrio"', false)
            ->assertSee('name="ciudad"', false)
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertStringContainsString('registro-sede/'.$this->sede->registro_token, $paso2->getContent());

        $this->enviar(['email' => 'ana@correo.com', 'estado_inicial' => 'miembro_activo'])
            ->assertRedirect(route('registro-sede.listo', $this->sede->registro_token));

        $persona = Persona::withoutGlobalScopes()->where('sede_id', $this->sede->id)->firstOrFail();
        $this->assertSame(['3101112233', 'miembro_activo', 'ana@correo.com'], [preg_replace('/\D/', '', $persona->celular), $persona->estado, $persona->email]);

        $this->get(route('registro-sede.listo', $this->sede->registro_token))
            ->assertOk()->assertSee('quedaste registrado')->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_validaciones_por_campo_y_no_se_crea_nada(): void
    {
        $casos = [
            ['celular' => '123'], ['celular' => ''], ['nombres' => ''], ['apellidos' => ''], ['fecha_nacimiento' => ''],
            ['fecha_nacimiento' => now()->addDay()->toDateString()], ['fecha_nacimiento' => '1800-01-01'],
            ['tipo_documento' => '', 'numero_documento' => '123'], ['tipo_documento' => 'XX', 'numero_documento' => '123'],
            ['estado_inicial' => 'lider'], ['email' => 'no-es-correo'], ['nombres' => str_repeat('a', 101)],
            ['direccion' => str_repeat('a', 256)], ['barrio' => str_repeat('a', 256)], ['ciudad' => str_repeat('a', 256)],
        ];

        foreach ($casos as $caso) {
            RateLimiter::clear('registro-miembros:127.0.0.1');
            $this->enviar($caso)->assertSessionHasErrors(array_key_first($caso));
        }

        $this->assertSame(0, Persona::withoutGlobalScopes()->where('sede_id', $this->sede->id)->count());
    }

    public function test_el_estado_inicial_es_obligatorio_solo_para_nuevos(): void
    {
        $this->enviar(['estado_inicial' => null])->assertSessionHasErrors('estado_inicial');

        RateLimiter::clear('registro-miembros:127.0.0.1');
        Persona::factory()->deSede($this->sede->id)->create(['celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04']);
        $this->enviar(['estado_inicial' => null])->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_la_busqueda_json_precarga_todos_los_datos_del_miembro(): void
    {
        Persona::factory()->deSede($this->sede->id)->create([
            'celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04',
            'tipo_documento' => 'CC', 'numero_documento' => '1020304050', 'email' => 'ana@correo.com',
            'direccion' => 'Calle 1', 'barrio' => 'Centro', 'ciudad' => 'Bogotá',
        ]);

        $esperado = [
            'estado' => 'existente', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04', 'celular' => '3101112233',
            'tipo_documento' => 'CC', 'numero_documento' => '1020304050', 'email' => 'ana@correo.com',
            'direccion' => 'Calle 1', 'barrio' => 'Centro', 'ciudad' => 'Bogotá',
        ];

        $this->postJson(route('registro-sede.buscar', $this->sede->registro_token), ['celular' => '310 111 2233'])
            ->assertOk()->assertExactJson($esperado)->assertHeader('Cache-Control', 'no-store, private');

        RateLimiter::clear('registro-miembros:127.0.0.1');
        $this->postJson(route('registro-sede.buscar', $this->sede->registro_token), ['numero_documento' => '1020304050'])
            ->assertOk()->assertJson(['estado' => 'existente', 'celular' => '3101112233']);
    }

    public function test_el_paso_dos_sin_javascript_viene_con_los_datos_del_miembro(): void
    {
        Persona::factory()->deSede($this->sede->id)->create(['celular' => '3101112233', 'nombres' => 'Ana', 'barrio' => 'Centro', 'email' => 'ana@correo.com']);

        $this->post(route('registro-sede.buscar', $this->sede->registro_token), ['celular' => '3101112233'])
            ->assertOk()
            ->assertSee('Encontramos tus datos')
            ->assertSee('value="Centro"', false)
            ->assertSee('value="ana@correo.com"', false)
            ->assertDontSee('name="estado_inicial"', false);
    }

    public function test_actualizar_muestra_lo_que_cambio_y_sin_cambios_lo_dice(): void
    {
        Persona::factory()->deSede($this->sede->id)->create(['celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04', 'barrio' => 'Centro']);

        $this->enviar(['barrio' => 'Norte', 'email' => 'ana@correo.com'])->assertRedirect();
        $this->get(route('registro-sede.listo', $this->sede->registro_token))
            ->assertSee('Datos actualizados')->assertSee('Barrio')->assertSee('Correo');

        RateLimiter::clear('registro-miembros:127.0.0.1');
        $this->enviar(['barrio' => 'Norte', 'email' => 'ana@correo.com'])->assertRedirect();
        $this->get(route('registro-sede.listo', $this->sede->registro_token))->assertSee('ya estaban al día');
    }

    public function test_reactivar_se_confirma_con_bienvenida(): void
    {
        $persona = Persona::factory()->deSede($this->sede->id)->create(['celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04']);
        app(\App\Services\SedeContext::class)->conSede($this->sede->id, fn () => app(LiderazgoService::class)->inactivar($persona));

        $this->enviar()->assertRedirect();
        $this->get(route('registro-sede.listo', $this->sede->registro_token))->assertSee('Bienvenido de nuevo');
        $this->assertNotSame('inactivo', $persona->fresh()->estado);
    }

    public function test_el_conflicto_se_muestra_junto_al_celular_y_no_cambia_nada(): void
    {
        Persona::factory()->deSede($this->sede->id)->create(['celular' => '3101112233', 'numero_documento' => '111']);
        Persona::factory()->deSede($this->sede->id)->create(['celular' => '3200000000', 'numero_documento' => '222']);

        $this->enviar(['celular' => '3200000000', 'numero_documento' => '111', 'tipo_documento' => 'CC'])->assertSessionHasErrors('celular');
        $this->assertSame(0, RegistroMiembroQr::withoutGlobalScopes()->count());

        RateLimiter::clear('registro-miembros:127.0.0.1');
        $this->post(route('registro-sede.buscar', $this->sede->registro_token), ['celular' => '3200000000', 'numero_documento' => '111'])
            ->assertOk()->assertSee('pertenecen a miembros distintos');
    }

    public function test_el_limite_de_intentos_bloquea_el_sexto_en_un_minuto(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('registro-sede.buscar', $this->sede->registro_token), ['celular' => '3101112233'])->assertOk();
        }

        $this->postJson(route('registro-sede.buscar', $this->sede->registro_token), ['celular' => '3101112233'])
            ->assertStatus(429)->assertJson(['estado' => 'limite'])->assertHeader('Retry-After');
        $this->enviar()->assertStatus(429);
        $this->assertSame(0, Persona::withoutGlobalScopes()->where('sede_id', $this->sede->id)->count());

        $this->travel(61)->seconds();
        $this->postJson(route('registro-sede.buscar', $this->sede->registro_token), ['celular' => '3101112233'])->assertOk();
    }
}
