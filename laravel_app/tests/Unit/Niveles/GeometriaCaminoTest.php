<?php

namespace Tests\Unit\Niveles;

use App\Services\CaminoService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Geometría del dibujo del camino (spec 014, contrato «camino-y-componentes»). */
class GeometriaCaminoTest extends TestCase
{
    private function geometria(int $n): array
    {
        return (new CaminoService())->geometria($n);
    }

    public function test_sin_estaciones_no_hay_nada_que_dibujar(): void
    {
        $g = $this->geometria(0);

        $this->assertSame(0, $g['alto']);
        $this->assertSame([], $g['puntos']);
        $this->assertSame('', $g['sendero']);
        $this->assertSame(0, $this->geometria(-3)['alto']);
    }

    public function test_una_estacion_solo_tiene_punto_de_inicio(): void
    {
        $g = $this->geometria(1);

        $this->assertCount(1, $g['puntos']);
        $this->assertSame(0, substr_count($g['sendero'], 'C'));
        $this->assertStringStartsWith('M ', $g['sendero']);
    }

    #[DataProvider('cantidades')]
    public function test_los_puntos_quedan_en_el_lienzo_en_zigzag_y_bajan(int $n): void
    {
        $g = $this->geometria($n);

        $this->assertCount($n, $g['puntos']);
        $this->assertSame($n - 1, substr_count($g['sendero'], 'C'), 'una curva por tramo');
        $this->assertStringStartsWith('M ', $g['sendero']);

        $anteriorY = -1;
        foreach ($g['puntos'] as $i => $punto) {
            $this->assertGreaterThanOrEqual(0, $punto['x']);
            $this->assertLessThanOrEqual(100, $punto['x']);
            $this->assertGreaterThan($anteriorY, $punto['y'], 'cada estación está más abajo');
            $this->assertLessThanOrEqual($g['alto'], $punto['y']);
            $anteriorY = $punto['y'];
            $this->assertSame([20, 50, 80, 50][$i % 4], $punto['x'], 'izquierda, centro, derecha, centro');
        }
    }

    public static function cantidades(): array
    {
        return ['dos' => [2], 'cuatro' => [4], 'doce' => [12]];
    }

    public function test_el_alto_crece_con_las_estaciones(): void
    {
        $this->assertGreaterThan($this->geometria(4)['alto'], $this->geometria(12)['alto']);
    }
}
