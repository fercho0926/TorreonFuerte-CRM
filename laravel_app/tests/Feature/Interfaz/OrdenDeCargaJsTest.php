<?php

namespace Tests\Feature\Interfaz;

use PHPUnit\Framework\TestCase;

/**
 * Select2 necesita jQuery global antes de que arranquen los formularios. Los import de ES se
 * evalúan en orden, por eso jQuery se carga en su propio módulo importado antes que los formularios.
 */
class OrdenDeCargaJsTest extends TestCase
{
    public function test_jquery_global_se_importa_antes_que_los_formularios(): void
    {
        $app = file_get_contents(dirname(__DIR__, 3).'/resources/js/app.js');

        $jquery = strpos($app, "import './jquery-global.js'");
        $formularios = strpos($app, "import './forms/index.js'");

        $this->assertNotFalse($jquery, 'app.js debe importar jquery-global.js');
        $this->assertNotFalse($formularios);
        $this->assertLessThan($formularios, $jquery, 'jQuery debe cargarse antes que los formularios');
        $this->assertStringNotContainsString('window.jQuery', $app, 'el código suelto de app.js corre después de los import');
    }
}
