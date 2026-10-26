<?php

namespace Tests\Feature\Interfaz;

use App\Support\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Menú y migas a partir de config/menu.php. */
class MenuTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_cada_ruta_del_menu_existe(): void
    {
        foreach (config('menu.secciones') as $seccion) {
            foreach ($seccion['opciones'] as $opcion) {
                $this->assertNotEmpty(route($opcion['ruta']), $opcion['ruta']);
            }
        }
    }

    public function test_el_menu_se_filtra_por_permisos_y_oculta_secciones_vacias(): void
    {
        $sede = $this->crearSede();
        $admin = $this->crearUsuario('admin');
        $lider = $this->crearUsuario('lider_celula', null, $sede->id);

        $porAdmin = collect(Menu::secciones($admin, [], 'dashboard'))->pluck('clave')->all();
        $porLider = collect(Menu::secciones($lider, [], 'dashboard'))->pluck('clave')->all();

        $this->assertContains('administracion', $porAdmin);
        $this->assertNotContains('administracion', $porLider);
        $this->assertSame([], Menu::secciones(null, [], 'dashboard'));
    }

    public function test_marca_la_opcion_activa_por_patron_y_abre_su_seccion(): void
    {
        $admin = $this->crearUsuario('admin');

        $secciones = Menu::secciones($admin, [], 'celulas.show');
        $congregacion = collect($secciones)->firstWhere('clave', 'congregacion');

        $this->assertTrue($congregacion['abierta']);
        $this->assertSame(['Células'], collect($congregacion['opciones'])->where('activa', true)->pluck('etiqueta')->values()->all());
    }

    public function test_migas_por_defecto(): void
    {
        $admin = $this->crearUsuario('admin');
        $secciones = Menu::secciones($admin, [], 'personas.edit');

        $migas = Menu::migas($secciones, 'Editar miembro');

        $this->assertSame(['Inicio', 'Congregación', 'Miembros', 'Editar miembro'], array_column($migas, 'texto'));
        $this->assertNotNull($migas[0]['url']);
        $this->assertNull($migas[1]['url']);
        $this->assertNotNull($migas[2]['url']);
        $this->assertNull(end($migas)['url'], 'el último tramo no es enlace');

        $inicio = Menu::migas(Menu::secciones($admin, [], 'dashboard'), 'Panel de administración');
        $this->assertSame(['Inicio', 'Panel de administración'], array_column($inicio, 'texto'));
    }

    public function test_la_insignia_del_menu_usa_el_contador_indicado(): void
    {
        $admin = $this->crearUsuario('admin');

        $opciones = collect(Menu::secciones($admin, ['sinLider' => 7], 'dashboard'))->pluck('opciones')->flatten(1);

        $this->assertSame(7, $opciones->firstWhere('ruta', 'sin-lider.index')['insignia']);
    }
}
