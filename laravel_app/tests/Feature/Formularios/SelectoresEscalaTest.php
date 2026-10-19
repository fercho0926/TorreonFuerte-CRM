<?php

namespace Tests\Feature\Formularios;

use App\Models\EventoPersona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class SelectoresEscalaTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_el_formulario_de_evento_con_1000_personas_responde_y_pesa_poco(): void
    {
        $sede = $this->crearSede(['nombre' => 'Grande']);
        $filas = [];
        for ($i = 1; $i <= 1000; $i++) {
            $filas[] = [
                'sede_id' => $sede->id, 'nombres' => 'Persona'.$i, 'apellidos' => 'Grande', 'tipo_documento' => 'CC',
                'numero_documento' => (string) (1000000 + $i), 'celular' => '3'.str_pad((string) $i, 9, '0', STR_PAD_LEFT),
                'estado' => 'miembro_activo', 'es_lider' => false, 'created_at' => now(), 'updated_at' => now(),
            ];
        }
        foreach (array_chunk($filas, 250) as $lote) {
            DB::table('personas')->insert($lote);
        }

        $this->comoPastorDe($sede);

        $inicio = microtime(true);
        $respuesta = $this->get(route('eventos.create'))->assertOk();
        $segundos = microtime(true) - $inicio;

        // 1.000 personas sembradas + la persona del pastor que inicia sesión.
        $this->assertSame(1001, substr_count($respuesta->getContent(), 'data-apoyo="Doc. '), 'Debe ofrecer todas las personas de la sede');
        $this->assertLessThan(300 * 1024, strlen($respuesta->getContent()), 'El HTML con 1.000 personas debe pesar menos de 300 KB');
        $this->assertLessThan(2.0, $segundos);
    }

    public function test_enviar_diez_personas_de_una_lista_grande_inscribe_exactamente_esas(): void
    {
        $sede = $this->crearSede(['nombre' => 'Grande']);
        $ids = [];
        for ($i = 1; $i <= 500; $i++) {
            $ids[] = DB::table('personas')->insertGetId([
                'sede_id' => $sede->id, 'nombres' => 'Persona'.$i, 'apellidos' => 'Lista', 'estado' => 'miembro_activo', 'es_lider' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->comoPastorDe($sede);

        $elegidas = array_slice($ids, 100, 10);
        $this->post(route('eventos.store'), [
            'nombre' => 'Evento Grande', 'tipo' => \App\Models\Evento::TIPOS[0], 'fecha' => now()->addDay()->toDateString(), 'costo' => '1000', 'estado' => 'planificado',
            'personas' => $elegidas,
        ])->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing($elegidas, EventoPersona::pluck('persona_id')->all());
    }
}
