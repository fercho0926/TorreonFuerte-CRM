<?php

namespace Tests\Unit;

use App\Models\Persona;
use App\Support\Celular;
use PHPUnit\Framework\TestCase;

class CelularTest extends TestCase
{
    public function test_limpia_espacios_guiones_y_parentesis(): void
    {
        $this->assertSame('3105559999', Celular::limpio('(310) 555-9999'));
        $this->assertSame('573105559999', Celular::limpio(' +57 310 555 9999 '));
        $this->assertNull(Celular::limpio(''));
        $this->assertNull(Celular::limpio(null));
        $this->assertNull(Celular::limpio('sin numero'));
    }

    public function test_diez_digitos_llevan_el_indicativo_de_colombia_y_no_se_duplica(): void
    {
        $this->assertSame('573105559999', Celular::whatsapp('310 555 9999'));
        $this->assertSame('573105559999', Celular::whatsapp('573105559999'));
        $this->assertSame('123456', Celular::whatsapp('123456'));
        $this->assertNull(Celular::whatsapp(null));
    }

    public function test_formato_legible(): void
    {
        $this->assertSame('310 555 9999', Celular::formatoLegible('3105559999'));
        $this->assertSame('573105559999', Celular::formatoLegible('573105559999'));
        $this->assertSame('', Celular::formatoLegible(null));
    }

    public function test_persona_sigue_entregando_los_mismos_valores(): void
    {
        $persona = new Persona(['celular' => '(310) 555-9999']);

        $this->assertSame('3105559999', $persona->celular_limpio);
        $this->assertSame('573105559999', $persona->celular_whatsapp);
    }
}
