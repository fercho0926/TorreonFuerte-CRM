<?php

namespace Tests\Feature\Roles;

use App\Models\CelulaReunion;
use App\Models\Persona;
use App\Models\RegistroClave;
use App\Models\SolicitudClave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RutaLaravel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Caracterización del acceso (spec 013): registra qué responde cada ruta con sesión a cada uno de los 4
 * roles de sistema y lo compara con `tests/Support/matriz-acceso-base.json`, capturado ANTES de reemplazar
 * los roles fijos por roles configurables. Cualquier diferencia debe ser una decisión explícita.
 *
 * Para regenerar la base (solo con el comportamiento que se quiere conservar):
 *   GENERAR_MATRIZ_BASE=1 php artisan test --filter=Caracterizacion
 *
 * Cada petición corre dentro de un punto de guardado de la base que se revierte, para que una acción que
 * escribe (inactivar, eliminar...) no cambie lo que ven los roles siguientes.
 */
class CaracterizacionTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    /** Rutas públicas o de sesión que no dependen del rol. */
    private const IGNORADAS = ['login', 'login.post', 'logout', 'up', 'storage.local', 'recuperacion.create', 'recuperacion.store',
        'registro.show', 'registro.buscar', 'registro.store', 'registro.listo',
        'registro-sede.show', 'registro-sede.buscar', 'registro-sede.store', 'registro-sede.listo'];

    /**
     * Diferencias aceptadas a propósito (spec 013): el rol «Usuario» solo tiene lectura en Miembros y Líderes, así
     * que el módulo niega con 403 las acciones de cambio que antes se negaban más adelante (404 por alcance o una
     * validación que redirigía). En todos los casos la acción seguía sin poder hacerse.
     */
    private const JUSTIFICADAS = [
        'DELETE /lideres/{lider}|user', 'DELETE /personas/{persona}|user', 'GET /lideres/{lider}/quitar|user', 'GET /personas/{persona}/edit|user',
        'POST /personas|user', 'POST /personas/{persona}/inactivar|user', 'POST /personas/{persona}/reactivar|user', 'PUT /personas/{persona}|user',
    ];

    private function archivoBase(): string
    {
        return dirname(__DIR__, 2).'/Support/matriz-acceso-base.json';
    }

    /** @return array<string, int|string> */
    private function ids(): array
    {
        $reunion = CelulaReunion::create(['sede_id' => $this->A['sede']->id, 'celula_id' => $this->A['celula']->id, 'fecha' => now()->toDateString()]);
        $registro = RegistroClave::create([
            'user_id' => $this->A['liderUser']->id, 'sede_id' => $this->A['sede']->id, 'motivo' => 'alta',
            'preparada_por' => $this->A['pastor']->id, 'vence_en' => now()->addDay(),
        ]);
        $solicitud = SolicitudClave::create(['user_id' => $this->A['liderUser']->id, 'sede_id' => $this->A['sede']->id, 'ultima_solicitud_en' => now()]);

        return [
            'persona' => $this->A['persona']->id, 'lider' => $this->A['lider']->id, 'celula' => $this->A['celula']->id,
            'evento' => $this->A['evento']->id, 'inscripcion' => $this->A['inscripcion']->id, 'pago' => $this->A['pago']->id,
            'ministerio' => $this->A['ministerio']->id, 'encuentro' => $this->A['encuentro']->id, 'user' => $this->A['liderUser']->id,
            'reunion' => $reunion->id, 'registro' => $registro->id, 'solicitud' => $solicitud->id, 'sede' => $this->A['sede']->id,
            'tipo' => 'evento', 'id' => $this->A['evento']->id,
        ];
    }

    /** @return array<string, \App\Models\User> */
    private function usuarios(): array
    {
        $simple = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Simple', 'apellidos' => 'Usuario']);

        return [
            'admin' => $this->crearUsuario('admin'),
            'pastor' => $this->A['pastor'],
            'lider_celula' => $this->A['liderUser'],
            'user' => $this->crearUsuario('user', $simple),
        ];
    }

    /** @return iterable<RutaLaravel> */
    private function rutas(): iterable
    {
        foreach (Route::getRoutes() as $ruta) {
            $nombre = (string) $ruta->getName();

            if ($nombre === '' || in_array($nombre, self::IGNORADAS, true) || str_starts_with($ruta->uri(), 'storage/')) {
                continue;
            }

            yield $ruta;
        }
    }

    /** @return array<string, array<string, string>> */
    private function medir(): array
    {
        $ids = $this->ids();
        $usuarios = $this->usuarios();
        $resultado = [];

        foreach ($this->rutas() as $ruta) {
            $url = $ruta->uri();
            foreach ($ruta->parameterNames() as $parametro) {
                $url = preg_replace('/\{'.$parametro.'\??\}/', (string) ($ids[$parametro] ?? '1'), $url);
            }

            foreach (array_diff($ruta->methods(), ['HEAD', 'OPTIONS']) as $metodo) {
                foreach ($usuarios as $rol => $usuario) {
                    $this->flushSession();
                    $this->iniciarSesionComo($usuario, $usuario->esAdmin() ? $this->A['sede']->id : null);

                    DB::beginTransaction();
                    try {
                        $respuesta = $this->call($metodo, '/'.$url);
                        $estado = (string) $respuesta->getStatusCode();
                        if ($respuesta->isRedirection()) {
                            $estado .= '→'.parse_url((string) $respuesta->headers->get('Location'), PHP_URL_PATH);
                        }
                    } catch (\Throwable $e) {
                        $estado = 'ERR:'.class_basename($e);
                    } finally {
                        DB::rollBack();
                    }

                    $resultado[$metodo.' /'.$ruta->uri()][$rol] = $estado;
                }
            }
        }

        ksort($resultado);

        return $resultado;
    }

    /**
     * Negar el acceso puede hacerse con 403 o redirigiendo al tablero (según el controlador); para la
     * comparación ambos cuentan como «denegado». Lo demás (200, 404, 422, otras redirecciones) se compara tal cual.
     */
    private function normalizar(string $estado): string
    {
        return in_array($estado, ['403', '302→/dashboard'], true) ? 'DENEGADO' : $estado;
    }

    public function test_el_acceso_de_los_cuatro_roles_coincide_con_la_base_capturada(): void
    {
        $this->armarDosSedes();
        $actual = $this->medir();

        if (getenv('GENERAR_MATRIZ_BASE')) {
            file_put_contents($this->archivoBase(), json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
            $this->markTestSkipped('Base generada: '.count($actual).' rutas.');
        }

        $this->assertFileExists($this->archivoBase(), 'Falta la base: ejecuta con GENERAR_MATRIZ_BASE=1 antes de refactorizar.');
        $base = json_decode((string) file_get_contents($this->archivoBase()), true);

        $diferencias = [];
        foreach ($base as $clave => $roles) {
            foreach ($roles as $rol => $esperado) {
                $obtenido = $actual[$clave][$rol] ?? 'AUSENTE';
                if ($this->normalizar($obtenido) !== $this->normalizar($esperado) && !($obtenido === '403' && in_array($clave.'|'.$rol, self::JUSTIFICADAS, true))) {
                    $diferencias[] = "{$clave} [{$rol}]: antes {$esperado}, ahora {$obtenido}";
                }
            }
        }

        $this->assertSame([], $diferencias, "El acceso cambió respecto a la base:\n".implode("\n", $diferencias));
        $this->assertGreaterThan(100, count($base), 'La base debería cubrir más de 100 rutas con método.');
    }
}
