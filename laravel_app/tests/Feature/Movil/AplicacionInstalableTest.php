<?php

namespace Tests\Feature\Movil;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Aplicación instalable (spec 008, contrato «aplicacion-instalable»): manifiesto, iconos, etiquetas y service worker. */
class AplicacionInstalableTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_el_manifiesto_es_valido_y_sus_iconos_existen(): void
    {
        $manifiesto = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, 512, JSON_THROW_ON_ERROR);

        foreach (['name', 'short_name', 'start_url', 'scope', 'display', 'theme_color', 'background_color', 'lang', 'icons'] as $campo) {
            $this->assertArrayHasKey($campo, $manifiesto, "falta $campo");
        }
        $this->assertSame('standalone', $manifiesto['display']);
        $this->assertSame('es', $manifiesto['lang']);

        $tamanos = [];
        foreach ($manifiesto['icons'] as $icono) {
            $this->assertFileExists(public_path($icono['src']), $icono['src']);
            $tamanos[] = $icono['sizes'].' '.$icono['purpose'];
        }
        $this->assertContains('192x192 any', $tamanos);
        $this->assertContains('512x512 any', $tamanos);
        $this->assertContains('512x512 maskable', $tamanos);
    }

    public function test_existen_los_iconos_de_la_pantalla_de_inicio_y_el_favicon(): void
    {
        foreach (['apple-touch-180.png', 'favicon-32.png', 'icono-192.png', 'icono-512.png', 'icono-maskable-512.png'] as $icono) {
            $this->assertFileExists(public_path('iconos/'.$icono));
        }
        $this->assertFileExists(public_path('favicon.ico'));
    }

    public function test_los_dos_disenos_declaran_las_etiquetas_de_la_aplicacion(): void
    {
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);

        $paginas = [
            $this->get(route('dashboard'))->assertOk()->getContent(),
            $this->flushSession()->get('/')->assertOk()->getContent(),
        ];

        foreach ($paginas as $html) {
            $this->assertStringContainsString('viewport-fit=cover', $html);
            $this->assertStringContainsString('<meta name="theme-color"', $html);
            $this->assertStringContainsString('rel="manifest"', $html);
            $this->assertStringContainsString('rel="apple-touch-icon"', $html);
            $this->assertStringContainsString('name="apple-mobile-web-app-capable"', $html);
            $this->assertStringContainsString('name="crm-sw"', $html);
        }
    }

    public function test_la_pantalla_sin_conexion_es_autocontenida(): void
    {
        $html = file_get_contents(public_path('offline.html'));

        $this->assertStringContainsString('Sin conexión', $html);
        $this->assertStringContainsString('Reintentar', $html);
        $this->assertStringNotContainsString('/build/', $html, 'no depende de archivos compilados que podrían no estar guardados');
        $this->assertStringNotContainsString('<script src', $html);
    }

    public function test_el_service_worker_solo_guarda_archivos_de_la_interfaz(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        // Lo único que puede escribirse en la caché.
        $this->assertStringContainsString('build/assets/', $sw);
        $this->assertStringContainsString('iconos/', $sw);
        $this->assertStringContainsString('offline.html', $sw);

        // Nunca datos: no se guardan respuestas con cookies, JSON, CSV ni HTML, ni envíos.
        $this->assertStringContainsString("peticion.method !== 'GET'", $sw);
        $this->assertStringContainsString('set-cookie', $sw);
        $this->assertMatchesRegularExpression('/json\|csv\|html/', $sw);

        // Una sola escritura (`put`) y solo dentro de la rama de la interfaz.
        $this->assertSame(1, substr_count($sw, '.put('), 'una única escritura en la caché');
        $this->assertStringNotContainsString('addAll(PRECACHE.concat', $sw);
        foreach (['/datos', '/exportar', '/buscar', 'dashboard', 'personas'] as $ruta) {
            $this->assertStringNotContainsString($ruta, $sw, "el service worker no debe nombrar rutas de datos ($ruta)");
        }
    }

    public function test_el_archivo_de_acceso_comprime_y_cachea_los_archivos_compilados(): void
    {
        $htaccess = file_get_contents(public_path('.htaccess'));

        $this->assertStringContainsString('mod_deflate', $htaccess);
        $this->assertStringContainsString('immutable', $htaccess);
        $this->assertStringContainsString('application/manifest+json', $htaccess);
        $this->assertStringContainsString('sw\.js', $htaccess);
    }
}
