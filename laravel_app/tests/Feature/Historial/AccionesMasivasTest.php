<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Una acción sobre muchos registros: resumen en el principal y una entrada por miembro, en un mismo lote (RF-006). */
class AccionesMasivasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_la_asistencia_de_treinta_miembros_deja_un_resumen_y_treinta_entradas(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
        $encuentro = $this->A['encuentro'];
        $miembros = Persona::factory()->count(30)->deSede($this->A['sede']->id)->create();
        $encuentro->personas()->attach($miembros->pluck('id'));
        $ids = $miembros->pluck('id')->all();
        $antes = (int) CambioHistorial::max('id');

        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => $ids])->assertSessionHasNoErrors();

        $nuevas = CambioHistorial::query()->where('id', '>', $antes)->get();
        $resumen = $nuevas->where('sujeto_tipo', 'encuentro')->sole();
        $individuales = $nuevas->where('sujeto_tipo', 'persona')->where('accion', 'asistio_encuentro');

        $this->assertSame('asistencia_actualizada', $resumen->accion);
        $this->assertCount(30, $resumen->detalle['agregados']);
        $this->assertCount(30, $individuales);
        $this->assertTrue($individuales->every(fn ($e) => $e->lote === $resumen->lote));

        $antes = (int) CambioHistorial::max('id');
        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => $ids])->assertSessionHasNoErrors();
        $this->assertSame(0, CambioHistorial::query()->where('id', '>', $antes)->count(), 'guardar lo mismo otra vez no escribe');
    }
}
