<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use App\Models\User;
use App\Services\TrasladoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** La cuenta acompaña al miembro cuando se traslada de sede (spec 016, research D13). */
class TrasladoConCuentaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_la_cuenta_pasa_al_miembro_nuevo_y_a_la_sede_destino(): void
    {
        $this->armarDosSedes();
        $admin = $this->comoAdminEnSede($this->A['sede']);
        $cuenta = $this->A['liderUser'];

        $nueva = app(TrasladoService::class)->trasladar($this->A['lider'], $this->B['sede'], $admin);

        $cuenta = User::withoutGlobalScopes()->find($cuenta->id);
        $this->assertSame($nueva->id, $cuenta->persona_id);
        $this->assertSame($this->B['sede']->id, $cuenta->sede_id);
        $this->assertSame($this->A['lider']->nombre_completo, $cuenta->name);
    }

    public function test_si_el_traslado_falla_la_cuenta_no_cambia(): void
    {
        $this->armarDosSedes();
        $admin = $this->comoAdminEnSede($this->A['sede']);
        $cuenta = $this->A['liderUser'];
        Persona::factory()->deSede($this->B['sede']->id)->create(['numero_documento' => '99887766']);
        $this->A['lider']->update(['numero_documento' => '99887766']);

        try {
            app(TrasladoService::class)->trasladar($this->A['lider'], $this->B['sede'], $admin);
            $this->fail('Debió rechazarse por documento repetido');
        } catch (ValidationException) {
        }

        $cuenta = User::withoutGlobalScopes()->find($cuenta->id);
        $this->assertSame($this->A['lider']->id, $cuenta->persona_id);
        $this->assertSame($this->A['sede']->id, $cuenta->sede_id);
    }
}
