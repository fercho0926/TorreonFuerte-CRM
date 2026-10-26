<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Comando `miembros:unificar-usuarios` (spec 016, research D7). */
class ComandoUnificarTest extends TestCase
{
    use RefreshDatabase;

    public function test_imprime_el_resumen_y_las_cuentas_por_revisar(): void
    {
        // Base como antes de la spec 016: la cuenta todavía tiene nombre y celular propios.
        (require database_path('migrations/2026_11_01_000004_users_persona_obligatoria.php'))->down();
        $sede = Sede::factory()->create(['nombre' => 'Sede Centro']);
        Persona::factory()->deSede($sede->id)->create(['nombres' => 'Carlos', 'apellidos' => 'Ruiz', 'celular' => '3001112233']);
        DB::table('users')->insert([
            'name' => 'Milton Pérez', 'email' => 'milton@iglesia.co', 'celular' => '3001112233', 'password' => 'x',
            'role' => 'user', 'sede_id' => $sede->id, 'persona_id' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('miembros:unificar-usuarios')
            ->expectsOutputToContain('Cuentas vinculadas: 0')
            ->expectsOutputToContain('Miembros creados: 1')
            ->expectsOutputToContain('Por revisar: 1')
            ->expectsOutputToContain('milton@iglesia.co')
            ->expectsOutputToContain('Sedes sin pastor: Sede Centro')
            ->assertExitCode(0);
    }
}
