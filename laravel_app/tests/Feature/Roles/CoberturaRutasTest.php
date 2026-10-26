<?php

namespace Tests\Feature\Roles;

use App\Support\Modulos;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Ninguna ruta con sesión puede quedar sin control de acceso (spec 013): o pertenece a un módulo y lleva el
 * middleware `modulo:{clave}`, o es del administrador y lleva `administrador`, o depende de un gate por destino
 * (QR de eventos, encuentros y células), o es pública o de la cuenta propia.
 */
class CoberturaRutasTest extends TestCase
{
    private function coincide(string $nombre, array $patrones): bool
    {
        foreach ($patrones as $patron) {
            if (fnmatch($patron, $nombre)) {
                return true;
            }
        }

        return false;
    }

    public function test_toda_ruta_tiene_su_control_de_acceso(): void
    {
        $sinControl = [];
        $revisadas = 0;

        foreach (Route::getRoutes() as $ruta) {
            $nombre = (string) $ruta->getName();
            if ($nombre === '' || str_starts_with($ruta->uri(), 'storage/')) {
                continue;
            }

            $middleware = $ruta->gatherMiddleware();
            $modulo = Modulos::deRuta($nombre);

            if ($this->coincide($nombre, Modulos::rutasReservadas())) {
                if (!in_array('administrador', $middleware, true)) {
                    $sinControl[] = "{$nombre}: reservada al administrador pero sin middleware «administrador»";
                }
            } elseif ($modulo !== null) {
                if (!in_array('modulo:'.$modulo, $middleware, true)) {
                    $sinControl[] = "{$nombre}: del módulo {$modulo} pero sin middleware «modulo:{$modulo}»";
                }
            } elseif (!$this->coincide($nombre, Modulos::rutasPorPolitica()) && !$this->coincide($nombre, Modulos::rutasPublicas())) {
                $sinControl[] = "{$nombre}: no pertenece a ningún módulo, ni es reservada, ni pública, ni por política";
            }

            $revisadas++;
        }

        $this->assertSame([], $sinControl, "Rutas sin control de acceso:\n".implode("\n", $sinControl));
        $this->assertGreaterThan(100, $revisadas);
    }

    public function test_el_catalogo_de_modulos_es_el_acordado(): void
    {
        $this->assertSame(
            ['tablero', 'miembros', 'lideres', 'sin_lider', 'celulas', 'ministerios', 'eventos', 'encuentros', 'niveles', 'organigrama', 'registro_miembros', 'usuarios', 'solicitudes_clave', 'control_cambios'],
            Modulos::claves(),
        );
        $this->assertFalse(Modulos::existe('sedes'));
        $this->assertFalse(Modulos::existe('roles'));
    }
}
