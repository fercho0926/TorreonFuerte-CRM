<?php

namespace Tests\Feature\Historial;

use App\Support\AccionesHistorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** La pestaña carga rápido aunque el miembro tenga miles de entradas (spec 015, CE-004). */
class RendimientoHistorialTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_cinco_mil_entradas_cargan_en_menos_de_dos_segundos_con_consultas_acotadas(): void
    {
        $this->armarDosSedes();
        $persona = $this->A['persona'];
        $acciones = array_keys(AccionesHistorial::catalogo()['persona']['acciones']);
        $filas = [];

        foreach (range(1, 5000) as $i) {
            $accion = $acciones[$i % count($acciones)];
            $filas[] = [
                'created_at' => now()->subMinutes($i), 'sede_id' => $persona->sede_id, 'actor' => 'usuario', 'user_id' => $this->A['pastor']->id,
                'actor_nombre' => $this->A['pastor']->name, 'modulo' => AccionesHistorial::modulo('persona', $accion), 'accion' => $accion,
                'categoria' => AccionesHistorial::categoria('persona', $accion), 'sujeto_tipo' => 'persona', 'sujeto_id' => $persona->id,
                'sujeto_nombre' => $persona->nombre_completo, 'relacionado_tipo' => 'encuentro', 'relacionado_id' => $this->A['encuentro']->id,
                'relacionado_nombre' => 'Encuentro Alfa', 'detalle' => json_encode(['cambios' => [['campo' => 'celular', 'etiqueta' => 'Celular', 'antes' => '1', 'despues' => '2', 'largo' => false, 'secreto' => false, 'modulo_sensible' => null]]]),
            ];
        }
        foreach (array_chunk($filas, 500) as $lote) {
            DB::table('historial_cambios')->insert($lote);
        }

        $this->iniciarSesionComo($this->A['pastor']);
        $this->get(route('personas.show', [$persona, 'pestana' => 'historico']))->assertOk(); // calienta vistas compiladas

        DB::enableQueryLog();
        $inicio = microtime(true);
        $this->get(route('personas.show', [$persona, 'pestana' => 'historico']))->assertOk()->assertSee('Cambios de datos');
        $duracion = microtime(true) - $inicio;
        $consultas = count(DB::getQueryLog());

        $this->assertLessThan(2.0, $duracion, "tardó {$duracion} s");
        $this->assertLessThan(60, $consultas, "hizo {$consultas} consultas (¿N+1?)");
    }
}
