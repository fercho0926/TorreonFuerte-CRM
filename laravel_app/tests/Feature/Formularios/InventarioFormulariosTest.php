<?php

namespace Tests\Feature\Formularios;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\Support\RecorreFormularios;
use Tests\TestCase;

/**
 * Garantiza que ningún formulario de la aplicación quede fuera de los componentes (FR-017, SC-006).
 * Si esta prueba falla, hay una vista con <form> escrito a mano: usa <x-form.layout> o <x-form.confirm>.
 */
class InventarioFormulariosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RecorreFormularios, RefreshDatabase;

    /** Archivos que pueden contener <form> escrito a mano y por qué. */
    private const EXCEPCIONES = [
        'components/form/layout.blade.php' => 'es el propio componente',
        'components/form/confirm.blade.php' => 'es el propio componente de confirmación',
        'components/form/accion.blade.php' => 'es el propio componente de acciones de un botón',
        'layouts/admin.blade.php' => 'barra superior: cerrar sesión y cambiar de sede activa (formularios compactos)',
    ];

    public function test_ninguna_vista_tiene_formularios_escritos_a_mano_fuera_de_las_excepciones(): void
    {
        $sueltas = [];
        foreach (File::allFiles(resource_path('views')) as $archivo) {
            $ruta = str_replace('\\', '/', $archivo->getRelativePathname());

            if (isset(self::EXCEPCIONES[$ruta])) {
                continue;
            }

            if (preg_match('/<form[\s>]/', $archivo->getContents())) {
                $sueltas[] = $ruta;
            }
        }

        $this->assertSame([], $sueltas, 'Vistas con <form> fuera de los componentes: '.implode(', ', $sueltas));
    }

    public function test_el_layout_admin_solo_conserva_los_formularios_de_la_barra_superior(): void
    {
        $contenido = file_get_contents(resource_path('views/layouts/admin.blade.php'));

        $this->assertSame(2, preg_match_all('/<form[\s>]/', $contenido), 'La barra superior debe tener solo cerrar sesión y sede activa');
        $this->assertStringContainsString("route('logout')", $contenido);
        $this->assertStringContainsString("route('sede-activa.update')", $contenido);
    }

    public function test_cada_pantalla_del_inventario_responde_con_formularios_de_componentes(): void
    {
        $this->prepararFormularios();

        foreach ($this->pantallasDeFormulario() + $this->pantallasConFormulariosIncrustados() as $etiqueta => $url) {
            $html = $this->get($url)->assertOk()->getContent();

            // Todo <form> de la pantalla (salvo los de la barra superior) debe llevar data-form o data-confirm.
            preg_match_all('/<form\b[^>]*>/', $html, $formularios);
            foreach ($formularios[0] as $etiquetaForm) {
                if (str_contains($etiquetaForm, route('logout')) || str_contains($etiquetaForm, route('sede-activa.update'))) {
                    continue;
                }

                $this->assertTrue(
                    str_contains($etiquetaForm, 'data-form="1"') || str_contains($etiquetaForm, 'data-confirm="1"'),
                    "{$etiqueta}: formulario sin componentes → {$etiquetaForm}"
                );
            }
        }
    }

    public function test_el_login_usa_el_componente_en_modo_compacto(): void
    {
        $this->flushSession();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-form="1"', false)
            ->assertSee('data-modo="login"', false)
            ->assertSee('data-revelable', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }
}
