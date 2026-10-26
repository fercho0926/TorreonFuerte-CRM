<?php

namespace Tests\Feature\RegistroMiembrosQr;

use App\Models\Persona;
use App\Models\RegistroMiembroQr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class CierreTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private function datos(array $extra = []): array
    {
        return $extra + ['celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04', 'estado_inicial' => 'visitante'];
    }

    public function test_con_el_registro_cerrado_se_muestra_el_aviso_y_no_se_guarda_nada(): void
    {
        $sede = $this->crearSede(['registro_abierto' => false]);

        $this->get(route('registro-sede.show', $sede->registro_token))->assertOk()->assertSee('Registro cerrado');
        $this->post(route('registro-sede.store', $sede->registro_token), $this->datos())->assertOk()->assertSee('Registro cerrado');
        $this->postJson(route('registro-sede.buscar', $sede->registro_token), ['celular' => '3101112233'])
            ->assertStatus(409)->assertJson(['estado' => 'cerrado']);

        $this->assertSame(0, Persona::withoutGlobalScopes()->where('sede_id', $sede->id)->count());
        $this->assertSame(0, RegistroMiembroQr::withoutGlobalScopes()->count());
    }

    public function test_una_sede_inactiva_cierra_el_registro(): void
    {
        $sede = $this->crearSede(['activa' => false]);

        $this->get(route('registro-sede.show', $sede->registro_token))->assertOk()->assertSee('Registro cerrado');
    }

    public function test_un_token_inexistente_responde_404(): void
    {
        $this->get(route('registro-sede.show', str_repeat('a', 32)))->assertNotFound();
        $this->get(route('registro-sede.show', 'corto'))->assertNotFound();
        $this->postJson(route('registro-sede.buscar', str_repeat('a', 32)), ['celular' => '3101112233'])
            ->assertNotFound()->assertJson(['estado' => 'no_valido']);
    }

    public function test_las_sedes_nuevas_tienen_su_propio_token(): void
    {
        $a = $this->crearSede();
        $b = $this->crearSede();

        $this->assertSame(32, strlen($a->registro_token));
        $this->assertNotSame($a->registro_token, $b->registro_token);
    }
}
