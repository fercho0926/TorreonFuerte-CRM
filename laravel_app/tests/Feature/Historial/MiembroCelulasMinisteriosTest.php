<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Células, ministerios y liderazgo en el histórico del miembro (spec 015, historia 1). */
class MiembroCelulasMinisteriosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function de(Persona $persona, string $categoria): array
    {
        return CambioHistorial::query()->de('persona', $persona->id)->where('categoria', $categoria)->orderBy('id')->pluck('accion')->all();
    }

    public function test_entrar_y_salir_de_una_celula_y_asistir_a_su_reunion(): void
    {
        $celula = $this->A['celula'];
        $nueva = Persona::factory()->deSede($this->A['sede']->id)->create(['estado' => 'miembro_activo']);

        $this->post(route('celulas.miembros.store', $celula), ['personas' => [$nueva->id]])->assertSessionHasNoErrors();
        $this->post(route('celulas.reuniones.store', $celula), ['fecha' => now()->toDateString(), 'tema' => 'Fe', 'asistio' => [$nueva->id]])->assertSessionHasNoErrors();
        $this->delete(route('celulas.miembros.destroy', [$celula, $nueva]))->assertRedirect();

        $this->assertSame(['ingreso_celula', 'asistio_reunion', 'salida_celula'], $this->de($nueva, 'celulas_ministerios'));
        $this->assertSame(['miembros_actualizados', 'reunion_registrada', 'miembros_actualizados'], CambioHistorial::query()->de('celula', $celula->id)
            ->whereIn('accion', ['miembros_actualizados', 'reunion_registrada'])->orderBy('id')->pluck('accion')->all());
    }

    public function test_entrar_y_salir_de_un_ministerio(): void
    {
        $ministerio = $this->A['ministerio'];
        $persona = $this->A['persona'];
        $datos = ['nombre' => $ministerio->nombre, 'estado' => 'activo', 'encargado_id' => $ministerio->encargado_id];

        $this->put(route('ministerios.update', $ministerio), $datos + ['personas' => [$persona->id]])->assertSessionHasNoErrors();
        $this->put(route('ministerios.integrantes', $ministerio), ['rol' => [$persona->id => 'Músico']])->assertSessionHasNoErrors();
        $this->put(route('ministerios.update', $ministerio), $datos + ['personas' => []])->assertSessionHasNoErrors();

        $this->assertSame(['ingreso_ministerio', 'cambio_ministerio', 'salida_ministerio'], $this->de($persona, 'celulas_ministerios'));
        $cambio = CambioHistorial::query()->de('persona', $persona->id)->where('accion', 'cambio_ministerio')->sole();
        $this->assertSame(['Rol en el ministerio', '(vacío)', 'Músico'], [$cambio->cambios[0]['etiqueta'], $cambio->cambios[0]['antes'], $cambio->cambios[0]['despues']]);
    }

    public function test_cambio_de_lider_y_designacion_quedan_en_liderazgo(): void
    {
        $persona = $this->A['persona'];
        $otroLider = Persona::factory()->deSede($this->A['sede']->id)->lider()->create(['nombres' => 'Otro', 'apellidos' => 'Guía']);

        $this->put(route('personas.update', $persona), [
            'nombres' => $persona->nombres, 'apellidos' => $persona->apellidos, 'estado' => $persona->estado,
            'celular' => $persona->celular, 'lider_id' => $otroLider->id,
        ])->assertSessionHasNoErrors();

        $this->assertContains('lider', $this->de($persona, 'liderazgo'));
        $this->assertContains('salida_celula', $this->de($persona, 'celulas_ministerios'), 'al cambiar de líder sale de las células del anterior');

        $entrada = CambioHistorial::query()->de('persona', $persona->id)->where('accion', 'lider')->sole();
        $this->assertSame('Otro Guía', $entrada->cambios[0]['despues']);
    }
}
