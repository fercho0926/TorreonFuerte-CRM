<?php

namespace Tests\Feature\Interfaz;

use PHPUnit\Framework\TestCase;

/**
 * Select2 necesita jQuery global antes de iniciar los selectores. Desde la spec 008 (ligereza en celular)
 * jQuery y Select2 se descargan bajo demanda: solo las pantallas con selectores con buscador los piden, y
 * `jquery-global.js` se importa (y espera) justo antes de `iniciarSelectores`.
 */
class OrdenDeCargaJsTest extends TestCase
{
    private function js(string $ruta): string
    {
        return file_get_contents(dirname(__DIR__, 3).'/resources/js/'.$ruta);
    }

    public function test_jquery_ya_no_se_descarga_en_todas_las_pantallas(): void
    {
        $app = $this->js('app.js');

        $this->assertStringNotContainsString("import './jquery-global.js'", $app, 'jQuery se carga solo cuando hace falta');
        $this->assertStringNotContainsString('window.jQuery', $app, 'el código suelto de app.js corre después de los import');
    }

    public function test_los_formularios_esperan_a_jquery_antes_de_iniciar_los_selectores(): void
    {
        $formularios = $this->js('forms/index.js');

        $carga = strpos($formularios, "await import('../jquery-global.js')");
        $inicio = strpos($formularios, 'iniciarSelectores(form);');

        $this->assertNotFalse($carga, 'forms/index.js debe esperar la carga de jquery-global.js');
        $this->assertNotFalse($inicio);
        $this->assertLessThan($inicio, $carga, 'jQuery debe estar cargado antes de iniciar los selectores');
    }

    public function test_solo_las_pantallas_que_lo_necesitan_descargan_select2_el_calendario_las_grillas_y_los_dialogos(): void
    {
        $this->assertStringContainsString("if (!necesitaSelect2(form)) return;", $this->js('forms/index.js'));
        $this->assertStringContainsString("import('flatpickr')", $this->js('forms/fechas.js'));
        $this->assertStringContainsString("import('sweetalert2')", $this->js('forms/swal.js'));
        $this->assertStringContainsString("import('./grilla/grilla.js')", $this->js('app.js'));

        foreach (['forms/avisos.js', 'forms/confirmar.js', 'forms/alta-rapida.js'] as $archivo) {
            $this->assertStringNotContainsString("from 'sweetalert2'", $this->js($archivo), "$archivo no debe importar SweetAlert2 de forma estática");
        }
        $this->assertStringNotContainsString("from 'flatpickr'", $this->js('forms/fechas.js'));
    }
}
