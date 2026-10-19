<?php

namespace Tests\Feature\Interfaz;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Inicio de sesión y páginas de error con el estilo del panel (FR-012). */
class AccesoYErroresTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_el_inicio_de_sesion_muestra_la_tarjeta_de_acceso_con_el_nombre_del_sistema(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('login-box', false)
            ->assertSee('login-card-body', false)
            ->assertSee('Torreón Fuerte')
            ->assertSee(config('app.sistema').'</title>', false)
            ->assertDontSee('hold-transition', false);
    }

    public function test_una_ruta_inexistente_muestra_404_con_boton_para_volver(): void
    {
        $this->get('/ruta-que-no-existe')
            ->assertNotFound()
            ->assertSee('404')
            ->assertSee('Volver al inicio')
            ->assertSee('login-box', false);
    }

    public function test_sin_permiso_muestra_403_con_el_marco_del_panel_cuando_hay_sesion(): void
    {
        $this->armarDosSedes();
        $this->comoLiderDe($this->A['sede'], $this->A['lider']);

        $this->get(route('sedes.index'))
            ->assertForbidden()
            ->assertSee('403')
            ->assertSee('Volver al inicio')
            ->assertSee('app-sidebar', false);
    }

    public function test_las_paginas_de_error_existen_para_403_404_419_422_y_500(): void
    {
        foreach ([403, 404, 419, 422, 500] as $codigo) {
            $this->assertFileExists(resource_path("views/errors/{$codigo}.blade.php"));
        }
    }

    public function test_el_error_419_y_el_500_se_dibujan_sin_romper_el_layout(): void
    {
        $this->assertStringContainsString('Tu sesión expiró', view('errors.419')->render());
        $this->assertStringContainsString('Volver al inicio', view('errors.500')->render());
    }
}
