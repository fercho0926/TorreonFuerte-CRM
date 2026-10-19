<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PresentacionPersonasTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_la_ficha_muestra_enlaces_de_llamada_y_whatsapp_con_celular(): void
    {
        $this->comoAdmin();
        $persona = Persona::factory()->create(['celular' => '310 555 9999']);

        $this->get(route('personas.show', $persona))
            ->assertOk()
            ->assertSee('href="tel:3105559999"', false)
            ->assertSee('href="https://wa.me/573105559999"', false);
    }

    public function test_sin_celular_no_se_muestran_los_enlaces(): void
    {
        $this->comoAdmin();
        $persona = Persona::factory()->create(['celular' => null, 'email' => 'a@b.co']);

        $this->get(route('personas.show', $persona))
            ->assertOk()
            ->assertDontSee('tel:', false)
            ->assertDontSee('wa.me', false);
    }

    public function test_la_fecha_de_nacimiento_se_muestra_en_espanol_con_la_edad(): void
    {
        $this->comoAdmin();
        Carbon::setTestNow('2026-10-19');
        $persona = Persona::factory()->create(['fecha_nacimiento' => '1990-03-05']);

        $this->get(route('personas.show', $persona))
            ->assertOk()
            ->assertSee('5 de marzo de 1990')
            ->assertSee('36 años');

        Carbon::setTestNow();
    }

    public function test_el_listado_incluye_la_vista_de_tarjetas_para_telefono(): void
    {
        $this->comoAdmin();
        Persona::factory()->create();

        $this->get(route('personas.index'))
            ->assertOk()
            ->assertSee('d-md-none', false)
            ->assertSee('d-none d-md-block', false);
    }
}
