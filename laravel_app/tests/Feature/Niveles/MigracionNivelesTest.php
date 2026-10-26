<?php

namespace Tests\Feature\Niveles;

use App\Models\NivelFormacion;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Tablas, datos iniciales y permisos del módulo Niveles (spec 014, data-model). */
class MigracionNivelesTest extends TestCase
{
    use RefreshDatabase;

    public function test_existen_las_tablas_y_la_columna_del_encuentro(): void
    {
        // `nivel_cambios` se integró al historial único y se eliminó (spec 015).
        foreach (['niveles_formacion', 'avances_nivel'] as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla), $tabla);
        }
        $this->assertTrue(Schema::hasColumn('encuentros', 'nivel_id'));
        $this->assertTrue(Schema::hasColumns('avances_nivel', ['sede_id', 'persona_id', 'nivel_id', 'fecha', 'origen', 'encuentro_id', 'registrado_por', 'nota']));
    }

    public function test_se_cargan_los_cuatro_niveles_iniciales_en_orden_y_activos(): void
    {
        $niveles = NivelFormacion::query()->ordenados()->get();

        $this->assertSame(
            ['Libres para servir', 'Aliviando el fuego', 'Caminando en santidad', 'Viviendo en el espíritu'],
            $niveles->pluck('nombre')->all(),
        );
        $this->assertSame([1, 2, 3, 4], $niveles->pluck('posicion')->all());
        $this->assertTrue($niveles->every(fn ($n) => $n->activo));
    }

    public function test_correr_la_migracion_otra_vez_no_duplica_los_niveles_ni_los_permisos(): void
    {
        $permisos = DB::table('rol_permisos')->where('modulo', 'niveles')->count();
        $niveles = NivelFormacion::count();

        (include database_path('migrations/2026_10_29_000002_permisos_modulo_niveles.php'))->up();

        $this->assertSame($permisos, DB::table('rol_permisos')->where('modulo', 'niveles')->count());
        $this->assertSame($niveles, NivelFormacion::count());
    }

    public function test_los_roles_del_sistema_reciben_el_permiso_del_modulo(): void
    {
        $nivel = fn (string $clave) => Rol::where('clave', $clave)->firstOrFail()->nivelEn('niveles');

        $this->assertSame(2, $nivel('admin'));
        $this->assertSame(2, $nivel('pastor'));
        $this->assertSame(1, $nivel('lider_celula'));
        $this->assertSame(1, $nivel('user'));
    }

    public function test_un_rol_personalizado_nace_sin_acceso_al_modulo(): void
    {
        $rol = Rol::factory()->conPermisos(['miembros' => 1])->create();

        $this->assertSame(0, $rol->nivelEn('niveles'));
    }

    public function test_el_nombre_de_un_nivel_es_unico_y_la_posicion_tambien(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        NivelFormacion::create(['nombre' => 'Libres para servir', 'posicion' => 9, 'activo' => true]);
    }
}
