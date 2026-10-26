<?php

namespace Tests\Feature\RegistroMiembrosQr;

use App\Models\Persona;
use App\Models\RegistroMiembroQr;
use App\Models\Sede;
use App\Services\RegistroMiembrosQr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class ListaTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private Sede $sede;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sede = $this->crearSede();
    }

    public function test_la_lista_muestra_resultado_fecha_y_cambios(): void
    {
        $persona = Persona::factory()->deSede($this->sede->id)->create(['celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04', 'barrio' => 'Centro']);
        app(RegistroMiembrosQr::class)->registrar($this->sede, [
            'celular' => '3101112233', 'nombres' => 'Ana', 'apellidos' => 'Pérez', 'fecha_nacimiento' => '1990-05-04', 'barrio' => 'Norte',
        ]);
        app(RegistroMiembrosQr::class)->registrar($this->sede, [
            'celular' => '3209990000', 'nombres' => 'Luis', 'apellidos' => 'Gómez', 'fecha_nacimiento' => '1985-01-01', 'estado_inicial' => 'visitante',
        ]);

        $this->comoAdminEnSede($this->sede);
        $this->get(route('sedes.show', ['sede' => $this->sede, 'pestana' => 'registro']))
            ->assertOk()
            ->assertSee('Ana Pérez')
            ->assertSee('Luis Gómez')
            ->assertSee('Actualizada')
            ->assertSee('Nueva')
            ->assertSee('Barrio: Centro → Norte')
            ->assertSee(route('personas.show', $persona), false)
            ->assertSee(now()->format('Y-m-d'));
    }

    public function test_los_mas_recientes_van_primero(): void
    {
        RegistroMiembroQr::factory()->deSede($this->sede->id)->create(['nombres' => 'Antiguo', 'created_at' => now()->subDay()]);
        RegistroMiembroQr::factory()->deSede($this->sede->id)->create(['nombres' => 'Reciente', 'created_at' => now()]);

        $this->comoAdminEnSede($this->sede);
        $html = $this->get(route('sedes.show', ['sede' => $this->sede, 'pestana' => 'registro']))->getContent();

        $this->assertLessThan(strpos($html, 'Antiguo'), strpos($html, 'Reciente'));
    }

    public function test_el_historial_de_la_ficha_muestra_el_origen_qr(): void
    {
        $persona = app(RegistroMiembrosQr::class)->registrar($this->sede, [
            'celular' => '3209990000', 'nombres' => 'Luis', 'apellidos' => 'Gómez', 'fecha_nacimiento' => '1985-01-01', 'estado_inicial' => 'visitante',
        ])['persona'];

        $this->comoAdminEnSede($this->sede);
        $this->get(route('personas.show', [$persona, 'pestana' => 'historico']))->assertOk()->assertSee('Creado por QR de miembros');
    }
}
