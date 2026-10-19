<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class MarcaDeSedeTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private const MARCA = '<div class="marca-agua-sede" aria-hidden="true"><span>Cocorná</span></div>';

    public function test_el_nombre_de_la_sede_aparece_en_el_menu_y_como_marca_de_agua_en_todas_las_paginas(): void
    {
        $sede = $this->crearSede(['nombre' => 'Cocorná']);
        $this->comoPastorDe($sede);

        foreach (['dashboard', 'personas.index', 'lideres.index', 'celulas.index', 'eventos.index', 'ministerios.index', 'encuentros.index', 'sin-lider.index', 'users.index'] as $ruta) {
            $this->get(route($ruta))
                ->assertOk()
                ->assertSee('CRM Admin - <strong class="nombre-sede">Cocorná</strong>', false)
                ->assertSee(self::MARCA, false);
        }
    }

    public function test_tambien_en_las_paginas_de_error_con_sesion(): void
    {
        $sede = $this->crearSede(['nombre' => 'Cocorná']);
        $this->comoPastorDe($sede);

        $this->get(route('sedes.index'))->assertForbidden()->assertSee(self::MARCA, false);
    }

    public function test_el_admin_la_ve_con_sede_activa_y_no_en_todas_las_sedes(): void
    {
        $sede = $this->crearSede(['nombre' => 'Cocorná']);

        $this->comoAdminEnSede($sede);
        $this->get(route('personas.index'))->assertSee('CRM Admin - <strong class="nombre-sede">Cocorná</strong>', false)->assertSee(self::MARCA, false);

        $this->flushSession();
        $this->comoAdminEnTodas();
        $this->get(route('personas.index'))->assertDontSee('marca-agua-sede', false)->assertDontSee('CRM Admin - <strong class="nombre-sede">', false);
    }
}
