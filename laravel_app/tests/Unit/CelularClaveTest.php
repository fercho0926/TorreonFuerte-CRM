<?php

namespace Tests\Unit;

use App\Support\Celular;
use PHPUnit\Framework\TestCase;

/** Forma canónica del celular para buscar al miembro (spec 016, research D3). */
class CelularClaveTest extends TestCase
{
    public function test_espacios_guiones_e_indicativo_dan_la_misma_clave(): void
    {
        $this->assertSame('3001234567', Celular::clave('300 123 4567'));
        $this->assertSame('3001234567', Celular::clave('300-123-4567'));
        $this->assertSame('3001234567', Celular::clave('+57 300 123 4567'));
        $this->assertSame('3001234567', Celular::clave('573001234567'));
    }

    public function test_otros_largos_quedan_tal_cual(): void
    {
        $this->assertSame('30012345678', Celular::clave('30012345678'));
        $this->assertSame('583001234567', Celular::clave('583001234567'));
    }

    public function test_vacio_o_sin_digitos_es_nulo(): void
    {
        $this->assertNull(Celular::clave(null));
        $this->assertNull(Celular::clave(''));
        $this->assertNull(Celular::clave('sin numero'));
    }
}
