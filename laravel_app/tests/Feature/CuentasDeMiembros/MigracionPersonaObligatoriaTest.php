<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Migración 4 de la spec 016: sin columnas propias de nombre y celular; un miembro por cuenta. */
class MigracionPersonaObligatoriaTest extends TestCase
{
    use RefreshDatabase;

    private function migracion(): object
    {
        return require database_path('migrations/2026_11_01_000004_users_persona_obligatoria.php');
    }

    public function test_la_cuenta_ya_no_tiene_nombre_ni_celular_y_el_miembro_es_obligatorio_y_unico(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'name'));
        $this->assertFalse(Schema::hasColumn('users', 'celular'));

        $usuario = User::factory()->create(['role' => 'user']);

        $this->expectException(QueryException::class);
        DB::table('users')->insert([
            'email' => 'otra@iglesia.co', 'password' => 'x', 'role' => 'user', 'sede_id' => $usuario->sede_id,
            'persona_id' => $usuario->persona_id, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_sin_miembro_la_cuenta_no_se_puede_guardar(): void
    {
        $this->expectException(QueryException::class);
        DB::table('users')->insert([
            'email' => 'suelta@iglesia.co', 'password' => 'x', 'role' => 'user', 'persona_id' => null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_se_detiene_si_queda_una_cuenta_sin_miembro_y_down_recrea_las_columnas(): void
    {
        $usuario = User::factory()->conMiembro(['nombres' => 'Luis', 'apellidos' => 'Acosta', 'celular' => '3001234567'])->create(['role' => 'user']);

        $this->migracion()->down();
        $this->assertSame('Luis Acosta', DB::table('users')->where('id', $usuario->id)->value('name'));
        $this->assertSame('3001234567', DB::table('users')->where('id', $usuario->id)->value('celular'));

        DB::table('users')->insert([
            'name' => 'Suelta', 'email' => 'suelta@iglesia.co', 'password' => 'x', 'role' => 'user',
            'sede_id' => Sede::query()->value('id'), 'persona_id' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);

        try {
            $this->migracion()->up();
            $this->fail('Debió detenerse');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('1 cuenta(s) sin miembro', $e->getMessage());
        }

        DB::table('users')->where('email', 'suelta@iglesia.co')->update(['persona_id' => Persona::factory()->create()->id]);
        $this->migracion()->up();
        $this->assertFalse(Schema::hasColumn('users', 'name'));
    }
}
