<?php

namespace Tests\Feature\RegistroQr;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Base de las pruebas del registro por QR: dos sedes (A y B) con evento, encuentro y célula cada una.
 */
abstract class RegistroQrTestCase extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    /** @return array<string, array{0: string}> */
    public static function destinos(): array
    {
        return ['evento' => ['evento'], 'encuentro' => ['encuentro'], 'celula' => ['celula']];
    }

    protected function token(string $destino, string $sede = 'A'): string
    {
        $datos = $sede === 'A' ? $this->A : $this->B;

        return $datos[$destino]->fresh()->registro_token;
    }

    /** @return array<string, string> */
    protected function datosNuevos(array $cambios = []): array
    {
        return array_merge([
            'celular' => '3105559999',
            'nombres' => 'Ana María',
            'apellidos' => 'Gómez Ruiz',
            'fecha_nacimiento' => '1990-05-04',
        ], $cambios);
    }
}
