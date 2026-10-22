<?php

namespace Tests\Feature\Aislamiento;

use App\Models\CelulaReunion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RutaLaravel;
use Illuminate\Support\Facades\Route;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Recorre TODAS las rutas registradas como pastor de la sede A:
 *  - con IDs de recursos de la sede B, cada ruta debe responder 404 o 403 (nunca revelar ni modificar);
 *  - sin parámetros, ninguna pantalla GET debe mostrar datos de la sede B.
 * Si aparece una ruta con un parámetro desconocido, la prueba falla para obligar a cubrirla.
 */
class MatrizRutasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    /**
     * Rutas que no manejan datos de una sede. Las `registro.*` son públicas por diseño (el token del QR
     * es la llave) y su aislamiento se prueba en tests/Feature/RegistroQr.
     */
    private const IGNORADAS = ['login', 'login.post', 'logout', 'up', 'storage.local', 'registro.show', 'registro.buscar', 'registro.store', 'registro.listo'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    /**
     * @return array<string, int>
     */
    private function idsDeLaSedeB(): array
    {
        $reunion = CelulaReunion::create(['sede_id' => $this->B['sede']->id, 'celula_id' => $this->B['celula']->id, 'fecha' => now()->toDateString()]);

        $registro = \App\Models\RegistroClave::create([
            'user_id' => $this->B['liderUser']->id, 'sede_id' => $this->B['sede']->id, 'motivo' => 'alta',
            'preparada_por' => $this->B['pastor']->id, 'vence_en' => now()->addDay(),
        ]);
        $solicitud = \App\Models\SolicitudClave::create([
            'user_id' => $this->B['liderUser']->id, 'sede_id' => $this->B['sede']->id, 'ultima_solicitud_en' => now(),
        ]);

        return [
            'persona' => $this->B['persona']->id,
            'lider' => $this->B['lider']->id,
            'celula' => $this->B['celula']->id,
            'evento' => $this->B['evento']->id,
            'inscripcion' => $this->B['inscripcion']->id,
            'pago' => $this->B['pago']->id,
            'ministerio' => $this->B['ministerio']->id,
            'encuentro' => $this->B['encuentro']->id,
            'user' => $this->B['liderUser']->id,
            'reunion' => $reunion->id,
            'registro' => $registro->id,
            'solicitud' => $solicitud->id,
            'sede' => $this->B['sede']->id,
            // registro-qr.*: el tipo y el id de un evento de la sede B.
            'tipo' => 'evento',
            'id' => $this->B['evento']->id,
        ];
    }

    private function rutasDeLaApp(): iterable
    {
        foreach (Route::getRoutes() as $ruta) {
            /** @var RutaLaravel $ruta */
            $nombre = (string) $ruta->getName();

            if ($nombre === '' || in_array($nombre, self::IGNORADAS, true) || str_starts_with($ruta->uri(), 'storage/')) {
                continue;
            }

            yield $ruta;
        }
    }

    public function test_los_recursos_de_otra_sede_responden_404_o_403_en_todas_las_rutas(): void
    {
        $ids = $this->idsDeLaSedeB();
        $probadas = 0;

        foreach ($this->rutasDeLaApp() as $ruta) {
            $parametros = $ruta->parameterNames();

            if (!$parametros) {
                continue;
            }

            $desconocidos = array_diff($parametros, array_keys($ids));
            $this->assertSame([], array_values($desconocidos), "La ruta {$ruta->getName()} tiene parámetros sin cobertura en la matriz: ".implode(', ', $desconocidos));

            $url = $ruta->uri();
            foreach ($parametros as $parametro) {
                $url = preg_replace('/\{'.$parametro.'\??\}/', (string) $ids[$parametro], $url);
            }

            foreach (array_diff($ruta->methods(), ['HEAD', 'OPTIONS']) as $metodo) {
                $respuesta = $this->call($metodo, '/'.$url);

                $this->assertContains(
                    $respuesta->getStatusCode(),
                    [403, 404],
                    "{$metodo} /{$url} ({$ruta->getName()}) respondió {$respuesta->getStatusCode()} con IDs de otra sede"
                );
                $probadas++;
            }
        }

        $this->assertGreaterThan(40, $probadas, 'La matriz debería cubrir decenas de rutas con parámetros');

        // Nada de la sede B cambió.
        $this->assertSame('PersonaBeta', $this->B['persona']->fresh()->nombres);
        $this->assertNotNull($this->B['celula']->fresh());
        $this->assertNotNull($this->B['evento']->fresh());
        $this->assertSame(1, \App\Models\EventoPago::withoutGlobalScopes()->where('evento_persona_id', $this->B['inscripcion']->id)->count());
    }

    public function test_ninguna_pantalla_sin_parametros_muestra_datos_de_otra_sede(): void
    {
        $revisadas = 0;

        foreach ($this->rutasDeLaApp() as $ruta) {
            if ($ruta->parameterNames() || !in_array('GET', $ruta->methods(), true) || str_starts_with($ruta->getName(), 'sedes.')) {
                continue;
            }

            $respuesta = $this->get('/'.$ruta->uri());

            if ($respuesta->getStatusCode() === 200) {
                $respuesta->assertDontSee('Beta', false);
                $revisadas++;
            }
        }

        $this->assertGreaterThan(10, $revisadas, 'La matriz debería revisar las pantallas principales');
    }

    public function test_el_modulo_de_sedes_responde_403_a_todas_sus_rutas_para_un_pastor(): void
    {
        $ids = $this->idsDeLaSedeB();

        foreach ($this->rutasDeLaApp() as $ruta) {
            if (!str_starts_with($ruta->getName(), 'sedes.')) {
                continue;
            }

            $url = $ruta->uri();
            foreach ($ruta->parameterNames() as $parametro) {
                $url = preg_replace('/\{'.$parametro.'\??\}/', (string) $ids[$parametro], $url);
            }

            foreach (array_diff($ruta->methods(), ['HEAD', 'OPTIONS']) as $metodo) {
                $this->call($metodo, '/'.$url)->assertForbidden();
            }
        }
    }
}
