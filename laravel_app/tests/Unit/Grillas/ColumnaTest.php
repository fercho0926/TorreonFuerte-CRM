<?php

namespace Tests\Unit\Grillas;

use App\Grillas\Columna;
use PHPUnit\Framework\TestCase;

class ColumnaTest extends TestCase
{
    public function test_por_defecto_no_ordena_ni_filtra_y_se_exporta(): void
    {
        $c = Columna::make('nombre', 'Nombre');

        $this->assertFalse($c->ordenable);
        $this->assertNull($c->filtro);
        $this->assertTrue($c->exportable);
        $this->assertFalse($c->soloExportar);
        $this->assertTrue($c->esVisible(null));
    }

    public function test_la_columna_de_acciones_nunca_ordena_filtra_ni_se_exporta_y_no_se_pliega(): void
    {
        $c = Columna::acciones();

        $this->assertTrue($c->esAcciones);
        $this->assertFalse($c->ordenable);
        $this->assertNull($c->filtro);
        $this->assertFalse($c->exportable);
        $this->assertSame(1, $c->prioridad);
    }

    public function test_los_filtros_declaran_su_tipo_y_opciones(): void
    {
        $texto = Columna::make('a', 'A')->filtraTexto();
        $lista = Columna::make('b', 'B')->filtraLista(['x' => 'X']);

        $this->assertSame(Columna::FILTRO_TEXTO, $texto->filtro);
        $this->assertSame(Columna::FILTRO_LISTA, $lista->filtro);
        $this->assertSame(['x' => 'X'], $lista->opciones);
    }

    public function test_la_visibilidad_depende_de_la_funcion_indicada(): void
    {
        $oculta = Columna::make('a', 'A')->visibleSi(fn () => false);

        $this->assertFalse($oculta->esVisible(null));
    }

    public function test_solo_exportar_y_sin_exportar(): void
    {
        $this->assertTrue(Columna::make('a', 'A')->soloExportar()->soloExportar);
        $this->assertFalse(Columna::make('a', 'A')->sinExportar()->exportable);
    }
}
