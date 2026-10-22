<?php

namespace Tests\Feature\Movil;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Cada tipo de campo abre el teclado adecuado en el celular (spec 008, RF-008). */
class TeclasFormularioTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private function vista(string $plantilla)
    {
        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());

        return $this->blade($plantilla);
    }

    public function test_el_tipo_celular_pide_el_marcador_del_telefono(): void
    {
        $this->vista('<x-form.input name="celular" label="Celular" type="celular" />')
            ->assertSee('type="tel"', false)
            ->assertSee('inputmode="tel"', false)
            ->assertSee('autocomplete="tel"', false);
    }

    public function test_el_tipo_correo_pide_el_teclado_con_arroba(): void
    {
        $this->vista('<x-form.input name="email" label="Correo" type="correo" />')
            ->assertSee('type="email"', false)
            ->assertSee('inputmode="email"', false)
            ->assertSee('autocomplete="email"', false);
    }

    public function test_el_tipo_numero_pide_el_teclado_numerico(): void
    {
        $this->vista('<x-form.input name="cupo" label="Cupo" type="numero" />')
            ->assertSee('type="text"', false)
            ->assertSee('inputmode="numeric"', false);
    }

    public function test_los_valores_en_dinero_piden_el_teclado_decimal(): void
    {
        $this->vista('<x-form.money name="costo" label="Costo" />')->assertSee('inputmode="decimal"', false);
    }

    public function test_los_tipos_anteriores_siguen_funcionando(): void
    {
        $this->vista('<x-form.input name="a" label="A" type="tel" />')->assertSee('inputmode="tel"', false);
        $this->vista('<x-form.input name="b" label="B" type="email" />')->assertSee('inputmode="email"', false);
        $this->vista('<x-form.input name="c" label="C" />')->assertDontSee('inputmode=', false);
    }

    public function test_los_formularios_reales_usan_los_tipos_con_teclado(): void
    {
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);

        $persona = $this->get(route('personas.create'))->assertOk()->getContent();
        $this->assertStringContainsString('inputmode="tel"', $persona, 'celular');
        $this->assertStringContainsString('inputmode="email"', $persona, 'correo');

        $this->comoAdminEnTodas();
        $usuario = $this->get(route('users.create'))->assertOk()->getContent();
        $this->assertStringContainsString('inputmode="email"', $usuario);
        $this->assertStringContainsString('inputmode="tel"', $usuario);
    }
}
