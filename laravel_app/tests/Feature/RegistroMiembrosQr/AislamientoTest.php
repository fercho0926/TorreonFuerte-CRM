<?php

namespace Tests\Feature\RegistroMiembrosQr;

use App\Models\Persona;
use App\Models\RegistroMiembroQr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AislamientoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        RateLimiter::clear('registro-miembros:127.0.0.1');
    }

    private function token(array $sede): string
    {
        return $sede['sede']->fresh()->registro_token;
    }

    public function test_el_token_de_una_sede_nunca_devuelve_miembros_de_otra(): void
    {
        $celularB = $this->B['persona']->celular;

        $this->postJson(route('registro-sede.buscar', $this->token($this->A)), ['celular' => $celularB])
            ->assertOk()->assertExactJson(['estado' => 'nueva']);

        RateLimiter::clear('registro-miembros:127.0.0.1');
        $this->postJson(route('registro-sede.buscar', $this->token($this->A)), ['numero_documento' => (string) $this->B['persona']->numero_documento])
            ->assertOk()->assertExactJson(['estado' => 'nueva']);
    }

    public function test_registrar_con_datos_de_otra_sede_crea_un_miembro_nuevo_sin_tocar_el_ajeno(): void
    {
        $ajeno = $this->B['persona']->fresh();

        $this->post(route('registro-sede.store', $this->token($this->A)), [
            'celular' => $ajeno->celular, 'nombres' => 'Intruso', 'apellidos' => 'Prueba', 'fecha_nacimiento' => '1990-01-01',
            'estado_inicial' => 'visitante', 'barrio' => 'Hackeado',
        ])->assertRedirect();

        $this->assertSame([$ajeno->nombres, $ajeno->barrio], [$this->B['persona']->fresh()->nombres, $this->B['persona']->fresh()->barrio]);
        $nuevo = Persona::withoutGlobalScopes()->where('nombres', 'Intruso')->firstOrFail();
        $this->assertSame($this->A['sede']->id, $nuevo->sede_id);
        $this->assertSame(0, RegistroMiembroQr::withoutGlobalScopes()->where('sede_id', $this->B['sede']->id)->count());
    }

    public function test_el_formulario_muestra_solo_la_sede_del_token(): void
    {
        $this->get(route('registro-sede.show', $this->token($this->A)))
            ->assertOk()->assertSee('Iglesia Alfa')->assertDontSee('Beta');
    }

    public function test_regenerar_una_sede_no_afecta_a_la_otra(): void
    {
        $tokenB = $this->token($this->B);

        $this->iniciarSesionComo($this->A['pastor']);
        $this->post(route('registro-miembros.regenerar', $this->A['sede']))->assertRedirect();

        $this->assertSame($tokenB, $this->token($this->B));
        $this->get(route('registro-sede.show', $tokenB))->assertOk();
    }

    public function test_la_lista_de_cada_sede_solo_muestra_sus_registros(): void
    {
        RegistroMiembroQr::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'RegistradoAlfa']);
        RegistroMiembroQr::factory()->deSede($this->B['sede']->id)->create(['nombres' => 'RegistradoBeta']);

        $this->iniciarSesionComo($this->A['pastor']);
        $this->get(route('registro-miembros.index'))
            ->assertOk()->assertSee('RegistradoAlfa')->assertDontSee('RegistradoBeta');

        $this->comoAdminEnSede($this->B['sede']);
        $this->get(route('sedes.show', ['sede' => $this->B['sede'], 'pestana' => 'registro']))
            ->assertOk()->assertSee('RegistradoBeta')->assertDontSee('RegistradoAlfa');
    }
}
