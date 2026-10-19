<?php

namespace Tests\Feature;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\User;
use App\Support\MigracionRedesZonas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigracionDatosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // La columna `red` ya fue eliminada del esquema; se recrea solo para simular datos legados.
        if (!Schema::hasColumn('personas', 'red')) {
            Schema::table('personas', fn ($table) => $table->string('red')->nullable());
        }
    }

    private function crearLegado(string $nombres, ?string $red, array $extra = []): Persona
    {
        // Simula datos de la versión anterior: red como texto y sin estado migrado.
        $id = DB::table('personas')->insertGetId(array_merge([
            'sede_id' => \Database\Factories\SedeFactory::idPorDefecto(),
            'nombres' => $nombres,
            'apellidos' => 'Prueba',
            'tipo_documento' => 'CC',
            'numero_documento' => (string) random_int(1000000, 9999999),
            'es_lider' => $red !== null,
            'red' => $red,
            'estado' => 'visitante',
            'created_at' => now(),
            'updated_at' => now(),
        ], $extra));

        return Persona::find($id);
    }

    public function test_los_usuarios_con_el_rol_retirado_de_lider_de_zona_pasan_a_lider(): void
    {
        $persona = $this->crearLegado('Zona', 'Mujeres');
        $usuario = User::factory()->create(['role' => 'lider_zona', 'persona_id' => $persona->id]);

        $resultado = MigracionRedesZonas::ejecutar();

        $this->assertSame(1, $resultado['usuarios_convertidos']);
        $this->assertSame('lider_celula', $usuario->fresh()->role);
    }

    public function test_no_se_pierde_ninguna_persona_y_quedan_como_miembros_activos(): void
    {
        $this->crearLegado('Uno', 'Red A');
        $this->crearLegado('Dos', null, ['es_lider' => false]);
        $total = Persona::count();

        MigracionRedesZonas::ejecutar();

        $this->assertSame($total, Persona::count());
        $this->assertSame(0, Persona::where('estado', 'visitante')->count());
        $this->assertSame($total, Persona::where('estado', 'miembro_activo')->count());
    }

    public function test_el_lider_de_la_celula_completa_al_miembro_sin_lider_y_los_conflictos_se_informan(): void
    {
        $lider = Persona::factory()->lider()->create();
        $otro = Persona::factory()->lider()->create();
        $celula = Celula::factory()->create(['lider_id' => $lider->id]);

        $sinLider = Persona::factory()->create(['lider_id' => null]);
        $conflicto = Persona::factory()->create(['lider_id' => $otro->id]);
        $celula->miembros()->attach([$sinLider->id, $conflicto->id]);

        $resultado = MigracionRedesZonas::ejecutar();

        $this->assertSame($lider->id, $sinLider->fresh()->lider_id);
        $this->assertSame($otro->id, $conflicto->fresh()->lider_id);
        $this->assertArrayHasKey($conflicto->id, $resultado['conflictos']);
    }
}
