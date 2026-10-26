<?php

namespace Tests\Unit\Historial;

use App\Support\AccionesHistorial;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/** Catálogo único de acciones del historial (spec 015). */
class AccionesHistorialTest extends TestCase
{
    public function test_cada_accion_tiene_etiqueta_icono_y_modulo(): void
    {
        foreach (AccionesHistorial::catalogo() as $tipo => $definicion) {
            $this->assertNotEmpty($definicion['etiqueta'], $tipo);
            $this->assertNotEmpty($definicion['modulo'], $tipo);
            foreach ($definicion['acciones'] as $accion => $datos) {
                $this->assertNotSame('', AccionesHistorial::etiqueta($tipo, $accion), "{$tipo}.{$accion}");
                $this->assertStringStartsWith('fa-', AccionesHistorial::icono($tipo, $accion), "{$tipo}.{$accion}");
                $this->assertNotSame('', AccionesHistorial::modulo($tipo, $accion), "{$tipo}.{$accion}");
                $this->assertLessThanOrEqual(40, strlen($accion), "{$tipo}.{$accion} cabe en la columna");
            }
        }
    }

    public function test_toda_accion_de_un_miembro_tiene_una_seccion_valida(): void
    {
        foreach (array_keys(AccionesHistorial::catalogo()['persona']['acciones']) as $accion) {
            $this->assertArrayHasKey(AccionesHistorial::categoria('persona', $accion), AccionesHistorial::CATEGORIAS, $accion);
        }
    }

    public function test_las_acciones_de_otros_registros_no_tienen_seccion(): void
    {
        foreach (AccionesHistorial::catalogo() as $tipo => $definicion) {
            if ($tipo === 'persona') {
                continue;
            }
            foreach (array_keys($definicion['acciones']) as $accion) {
                $this->assertNull(AccionesHistorial::categoria($tipo, $accion), "{$tipo}.{$accion}");
            }
        }
    }

    public function test_las_secciones_del_miembro_estan_en_el_orden_acordado(): void
    {
        $this->assertSame(
            ['Cambios de datos', 'Encuentros y retiros', 'Células y ministerios', 'Liderazgo', 'Niveles', 'Sede'],
            array_values(AccionesHistorial::CATEGORIAS),
        );
    }

    public function test_todo_tipo_tiene_su_modelo(): void
    {
        $this->assertEqualsCanonicalizing(array_keys(AccionesHistorial::catalogo()), array_keys(AccionesHistorial::CLASES));
        foreach (AccionesHistorial::CLASES as $tipo => $clase) {
            $this->assertSame($tipo, AccionesHistorial::tipoDe($clase));
        }
    }

    public function test_una_accion_desconocida_lanza_una_excepcion(): void
    {
        $this->assertFalse(AccionesHistorial::existe('persona', 'inventada'));
        $this->expectException(InvalidArgumentException::class);
        AccionesHistorial::etiqueta('persona', 'inventada');
    }

    public function test_conserva_las_claves_de_los_historiales_anteriores(): void
    {
        foreach (['creada', 'editada', 'estado', 'lider', 'rol_lider', 'inactivada', 'reactivada', 'eliminada', 'creada_qr', 'actualizada_qr', 'reactivada_qr', 'nivel_registrado', 'nivel_quitado', 'nivel_automatico'] as $accion) {
            $this->assertTrue(AccionesHistorial::existe('persona', $accion), "persona.{$accion}");
        }
        foreach (['creada', 'editada', 'desactivada', 'reactivada', 'pastor_cambiado', 'pastora_cambiada'] as $accion) {
            $this->assertTrue(AccionesHistorial::existe('sede', $accion), "sede.{$accion}");
        }
        foreach (['creado', 'editado', 'duplicado', 'eliminado'] as $accion) {
            $this->assertTrue(AccionesHistorial::existe('rol', $accion), "rol.{$accion}");
        }
        foreach (['creado', 'editado', 'reordenado', 'desactivado', 'reactivado', 'eliminado'] as $accion) {
            $this->assertTrue(AccionesHistorial::existe('nivel', $accion), "nivel.{$accion}");
        }
    }
}
