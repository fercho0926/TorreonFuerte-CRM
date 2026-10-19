<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Services\SedeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class EscalaSedesTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_se_pueden_dar_de_alta_20_sedes_consecutivas_igual_que_la_primera(): void
    {
        $servicio = app(SedeService::class);

        for ($i = 1; $i <= 20; $i++) {
            $sede = $servicio->crear([
                'nombre' => "Iglesia {$i}",
                'ciudad' => "Ciudad {$i}",
                'pastor' => ['nombres' => 'Pastor', 'apellidos' => "N{$i}", 'email' => "pastor{$i}@iglesia.co", 'password' => 'secreto123'],
                'pastora' => ['nombres' => 'Pastora', 'apellidos' => "N{$i}", 'email' => "pastora{$i}@iglesia.co", 'password' => 'secreto123'],
            ]);

            $this->assertNotNull($sede->pastor_persona_id);
            $this->assertNotNull($sede->pastora_persona_id);
        }

        $this->assertSame(20, Sede::count());
        $this->assertSame(40, User::withoutGlobalScopes()->where('role', 'pastor')->count());
        $this->assertSame(0, array_sum(\App\Support\MigracionSedes::contarSinSede()));

        // Cada pastor ve exactamente sus 2 personas pastorales, sin importar cuántas sedes haya.
        $this->iniciarSesionComo(User::withoutGlobalScopes()->where('email', 'pastor7@iglesia.co')->first());
        $this->get(route('personas.index'))->assertOk()->assertViewHas('pagina', fn ($p) => $p->total() === 2);
    }

    public function test_el_listado_de_una_sede_con_1000_personas_responde_paginado_y_rapido(): void
    {
        $sede = $this->crearSede(['nombre' => 'Grande']);
        $otra = $this->crearSede(['nombre' => 'Otra']);

        foreach ([$sede, $otra] as $s) {
            $filas = [];
            for ($i = 1; $i <= 1000; $i++) {
                $filas[] = [
                    'sede_id' => $s->id, 'nombres' => 'Persona'.$i, 'apellidos' => 'Sede'.$s->id, 'tipo_documento' => 'CC',
                    'numero_documento' => (string) ($s->id * 100000 + $i), 'celular' => '3'.str_pad((string) $i, 9, '0', STR_PAD_LEFT),
                    'estado' => 'miembro_activo', 'es_lider' => false, 'created_at' => now(), 'updated_at' => now(),
                ];
            }
            foreach (array_chunk($filas, 200) as $lote) {
                DB::table('personas')->insert($lote);
            }
        }

        $this->comoPastorDe($sede);

        $inicio = microtime(true);
        $this->get(route('personas.index'))
            ->assertOk()
            ->assertViewHas('pagina', fn ($p) => $p->total() === 1001 && $p->count() === 25 && $p->lastPage() === 41);
        $this->get(route('personas.index', ['q' => 'Persona500']))->assertViewHas('pagina', fn ($p) => $p->total() >= 1);
        $segundos = microtime(true) - $inicio;

        $this->assertLessThan(2.0, $segundos, 'El listado y la búsqueda con 1.000 personas deben responder en menos de 2 s');
        $this->assertSame(1000, Persona::withoutGlobalScopes()->where('sede_id', $otra->id)->count());
    }
}
