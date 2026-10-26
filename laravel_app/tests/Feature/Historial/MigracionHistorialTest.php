<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Integración de los historiales anteriores en el historial único (spec 015, D11, CE-007). */
class MigracionHistorialTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRACION = 'migrations/2026_10_30_000004_migrar_historiales_existentes.php';

    private const BORRADO = 'migrations/2026_10_30_000005_drop_historiales_antiguos.php';

    protected function setUp(): void
    {
        parent::setUp();

        // La base de prueba ya corrió el borrado: se recrean las tablas anteriores (vacías) para simular una base vieja.
        if (!Schema::hasTable('persona_cambios')) {
            (include database_path(self::BORRADO))->down();
        }
    }

    public function test_el_borrado_elimina_las_tablas_solo_si_todo_quedo_copiado(): void
    {
        $persona = Persona::factory()->create();
        DB::table('persona_cambios')->insert(['sede_id' => $persona->sede_id, 'persona_id' => $persona->id, 'user_id' => null, 'accion' => 'creada', 'antes' => null, 'despues' => null, 'created_at' => now()]);

        try {
            (include database_path(self::BORRADO))->up();
            $this->fail('Debió detenerse: la fila no estaba copiada.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('persona_cambios', $e->getMessage());
        }
        $this->assertTrue(Schema::hasTable('persona_cambios'), 'no borra nada si falta copiar');

        (include database_path(self::MIGRACION))->up();
        (include database_path(self::BORRADO))->up();

        foreach (['persona_cambios', 'sede_cambios', 'rol_cambios', 'nivel_cambios'] as $tabla) {
            $this->assertFalse(Schema::hasTable($tabla), $tabla);
        }
        $this->assertSame(1, CambioHistorial::query()->whereNotNull('detalle->legado->tabla')->count());
    }

    public function test_copia_las_cuatro_tablas_conservando_fecha_usuario_y_valores(): void
    {
        $usuario = User::factory()->create(['role' => 'admin', 'name' => 'Admin Viejo']);
        $sede = Sede::factory()->create(['nombre' => 'Sede Norte']);
        $persona = Persona::factory()->create(['sede_id' => $sede->id, 'nombres' => 'Ana', 'apellidos' => 'Pérez']);
        $rol = Rol::query()->firstOrFail();
        $nivel = NivelFormacion::query()->firstOrFail();
        $fecha = '2026-09-01 10:30:00';

        DB::table('persona_cambios')->insert(['sede_id' => $sede->id, 'persona_id' => $persona->id, 'user_id' => $usuario->id, 'accion' => 'editada', 'antes' => json_encode(['celular' => '1']), 'despues' => json_encode(['celular' => '2']), 'created_at' => $fecha]);
        DB::table('persona_cambios')->insert(['sede_id' => $sede->id, 'persona_id' => $persona->id, 'user_id' => null, 'accion' => 'inactivada', 'antes' => null, 'despues' => json_encode(['estado' => 'inactivo']), 'created_at' => $fecha]);
        DB::table('sede_cambios')->insert(['sede_id' => $sede->id, 'user_id' => $usuario->id, 'accion' => 'creada', 'antes' => null, 'despues' => json_encode(['nombre' => 'Sede Norte']), 'created_at' => $fecha]);
        DB::table('rol_cambios')->insert(['rol_id' => null, 'rol_clave' => 'viejo', 'rol_nombre' => 'Rol borrado', 'user_id' => $usuario->id, 'accion' => 'eliminado', 'antes' => null, 'despues' => null, 'created_at' => $fecha]);
        DB::table('nivel_cambios')->insert(['nivel_id' => $nivel->id, 'user_id' => $usuario->id, 'accion' => 'editado', 'nombre' => $nivel->nombre, 'antes' => json_encode(['nombre' => 'A']), 'despues' => json_encode(['nombre' => 'B']), 'created_at' => $fecha]);

        (include database_path(self::MIGRACION))->up();

        $this->assertSame(5, CambioHistorial::query()->whereNotNull('detalle->legado->tabla')->count());

        $edicion = CambioHistorial::where('sujeto_tipo', 'persona')->where('accion', 'editada')->sole();
        $this->assertSame($fecha, $edicion->created_at->format('Y-m-d H:i:s'));
        $this->assertSame($usuario->id, $edicion->user_id);
        $this->assertSame('Admin Viejo', $edicion->actor_nombre);
        $this->assertSame('Ana Pérez', $edicion->sujeto_nombre);
        $this->assertSame($sede->id, $edicion->sede_id);
        $this->assertSame('datos', $edicion->categoria);
        $this->assertSame(['celular' => '2'], $edicion->despues);
        $this->assertSame('persona_cambios', $edicion->detalle['legado']['tabla']);
        $this->assertTrue($edicion->es_legado);

        $sinUsuario = CambioHistorial::where('accion', 'inactivada')->sole();
        $this->assertSame(['sistema', 'Sistema'], [$sinUsuario->actor, $sinUsuario->actor_nombre]);

        $this->assertSame('Rol borrado', CambioHistorial::where('sujeto_tipo', 'rol')->sole()->sujeto_nombre);
        $this->assertSame('Sede Norte', CambioHistorial::where('sujeto_tipo', 'sede')->sole()->sujeto_nombre);
        $this->assertSame('niveles', CambioHistorial::where('sujeto_tipo', 'nivel')->sole()->modulo);
    }

    public function test_es_idempotente(): void
    {
        $persona = Persona::factory()->create();
        DB::table('persona_cambios')->insert(['sede_id' => $persona->sede_id, 'persona_id' => $persona->id, 'user_id' => null, 'accion' => 'creada', 'antes' => null, 'despues' => null, 'created_at' => now()]);

        (include database_path(self::MIGRACION))->up();
        (include database_path(self::MIGRACION))->up();

        $this->assertSame(1, CambioHistorial::query()->whereNotNull('detalle->legado->tabla')->count());
    }

    public function test_une_los_traslados_anteriores_por_el_json_del_historial(): void
    {
        $origen = Persona::factory()->create();
        $nueva = Persona::factory()->create();
        DB::table('persona_cambios')->insert(['sede_id' => $nueva->sede_id, 'persona_id' => $nueva->id, 'user_id' => null, 'accion' => 'creada', 'antes' => null, 'despues' => json_encode(['trasladada_desde' => 'Sede A', 'persona_origen_id' => $origen->id]), 'created_at' => now()]);

        (include database_path(self::MIGRACION))->up();

        $this->assertSame($origen->id, (int) $nueva->fresh()->trasladada_desde_persona_id);
    }
}
