<?php

namespace Tests\Feature\Interfaz;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class RespaldoIdiomaTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_clave_sin_traduccion_al_ingles_se_muestra_en_espanol(): void
    {
        App::setLocale('en');

        $this->assertFalse(Lang::has('interfaz.clave_de_prueba_sin_ingles', 'en', false));
        $this->assertTrue(Lang::has('interfaz.clave_de_prueba_sin_ingles', 'es', false));

        $this->assertSame('Texto de prueba solo en español', __('interfaz.clave_de_prueba_sin_ingles'));
    }
}
