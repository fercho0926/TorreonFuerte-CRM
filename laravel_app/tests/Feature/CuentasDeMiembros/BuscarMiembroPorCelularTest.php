<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use App\Models\User;
use App\Services\CuentasDeMiembros;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 1: buscar al miembro por su celular al crear una cuenta (spec 016, research D3 y D8). */
class BuscarMiembroPorCelularTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private \App\Models\Sede $sede;

    private User $pastor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sede = $this->crearSede(['nombre' => 'Sede Norte']);
        $this->pastor = $this->comoPastorDe($this->sede);
    }

    private function buscar(string $celular): array
    {
        return app(CuentasDeMiembros::class)->buscarPorCelular($celular, $this->pastor);
    }

    private function miembro(array $datos = []): Persona
    {
        return Persona::factory()->deSede($this->sede->id)->create($datos);
    }

    public function test_un_miembro_sin_cuenta_se_encuentra_con_cualquier_formato(): void
    {
        $milton = $this->miembro(['nombres' => 'Milton', 'celular' => '3001234567']);

        foreach (['3001234567', '300 123 4567', '300-123-4567', '+57 300 123 4567'] as $celular) {
            $busqueda = $this->buscar($celular);
            $this->assertSame('encontrado', $busqueda['estado'], $celular);
            $this->assertTrue($busqueda['personas']->first()->is($milton));
        }
    }

    public function test_dos_miembros_con_el_mismo_celular_dan_varios(): void
    {
        $this->miembro(['celular' => '3001234567']);
        $this->miembro(['celular' => '300 123 4567']);

        $busqueda = $this->buscar('3001234567');

        $this->assertSame('varios', $busqueda['estado']);
        $this->assertCount(2, $busqueda['personas']);
    }

    public function test_el_miembro_con_cuenta_da_con_cuenta(): void
    {
        $miembro = $this->miembro(['celular' => '3001234567']);
        User::factory()->create(['role' => 'user', 'sede_id' => $this->sede->id, 'persona_id' => $miembro->id]);

        $busqueda = $this->buscar('3001234567');

        $this->assertSame('con_cuenta', $busqueda['estado']);
        $this->assertTrue($busqueda['personas']->first()->is($miembro));
    }

    public function test_el_miembro_inactivo_da_inactivo(): void
    {
        $this->miembro(['celular' => '3001234567'])->update(['estado' => 'inactivo']);

        $this->assertSame('inactivo', $this->buscar('3001234567')['estado']);
    }

    public function test_un_celular_de_otra_sede_da_otra_sede_sin_devolver_al_miembro(): void
    {
        Persona::factory()->deSede($this->crearSede()->id)->create(['celular' => '3001234567']);

        $busqueda = $this->buscar('3001234567');

        $this->assertSame('otra_sede', $busqueda['estado']);
        $this->assertCount(0, $busqueda['personas']);
    }

    public function test_un_celular_nuevo_da_nuevo(): void
    {
        $this->assertSame('nuevo', $this->buscar('3009998877')['estado']);
    }

    public function test_un_celular_invalido_da_invalido(): void
    {
        $this->assertSame('invalido', $this->buscar('12345')['estado']);
        $this->assertSame('invalido', $this->buscar('abc')['estado']);
    }
}
