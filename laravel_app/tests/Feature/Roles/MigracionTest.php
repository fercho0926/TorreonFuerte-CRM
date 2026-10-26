<?php

namespace Tests\Feature\Roles;

use App\Models\Rol;
use App\Models\User;
use App\Support\Modulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigracionTest extends TestCase
{
    use RefreshDatabase;

    /** Niveles esperados (spec 013, research D3) en el orden admin, pastor, lider_celula, user. */
    private const MATRIZ = [
        'tablero' => [1, 1, 1, 1],
        'miembros' => [2, 2, 2, 1],
        'lideres' => [2, 2, 1, 1],
        'sin_lider' => [2, 2, 0, 0],
        'celulas' => [2, 2, 2, 1],
        'ministerios' => [2, 2, 2, 2],
        'eventos' => [2, 2, 2, 2],
        'encuentros' => [2, 2, 2, 2],
        'niveles' => [2, 2, 1, 1],
        'organigrama' => [1, 0, 0, 0],
        'registro_miembros' => [2, 2, 0, 0],
        'usuarios' => [2, 2, 0, 0],
        'solicitudes_clave' => [2, 2, 0, 0],
        'control_cambios' => [1, 0, 0, 0],
    ];

    private const CLAVES = ['admin', 'pastor', 'lider_celula', 'user'];

    public function test_se_siembran_los_cuatro_roles_de_sistema(): void
    {
        $this->assertSame(4, Rol::count());

        foreach (self::CLAVES as $clave) {
            $rol = Rol::where('clave', $clave)->firstOrFail();
            $this->assertTrue($rol->es_sistema, $clave);
        }

        $this->assertSame(['sede', 'sede', 'rama', 'registro'], array_map(fn ($c) => Rol::where('clave', $c)->value('alcance'), self::CLAVES));
        $this->assertSame(['Administrador', 'Pastor', 'Líder', 'Usuario'], array_map(fn ($c) => Rol::where('clave', $c)->value('nombre'), self::CLAVES));
    }

    public function test_cada_rol_tiene_los_niveles_del_acceso_que_ya_tenia(): void
    {
        foreach (self::CLAVES as $indice => $clave) {
            $niveles = Rol::where('clave', $clave)->firstOrFail()->niveles();

            foreach (self::MATRIZ as $modulo => $esperados) {
                $this->assertSame($esperados[$indice], $niveles[$modulo], "{$clave} en {$modulo}");
            }
        }

        $this->assertSame(array_keys(self::MATRIZ), Modulos::claves());
    }

    public function test_el_administrador_siempre_tiene_el_maximo_y_alcance_total(): void
    {
        $admin = Rol::where('clave', 'admin')->firstOrFail();

        foreach ($admin->niveles() as $modulo => $nivel) {
            $this->assertSame(Modulos::nivelMaximo($modulo), $nivel, $modulo);
        }
        $this->assertSame('sede', $admin->alcance);
        $this->assertFalse($admin->asignable);
    }

    public function test_los_roles_asignables_de_origen_son_lider_y_usuario(): void
    {
        $this->assertSame(
            ['lider_celula', 'user'],
            Rol::where('asignable', true)->orderBy('clave')->pluck('clave')->all(),
        );
    }

    public function test_la_migracion_es_idempotente_y_conserva_los_usuarios(): void
    {
        $usuario = User::factory()->create(['role' => 'pastor']);
        $migracion = require database_path('migrations/2026_10_28_000001_create_roles_y_permisos.php');

        $migracion->down();
        $this->assertFalse(Schema::hasTable('roles'));

        $migracion->up();
        $migracion->down();
        $migracion->up();

        $this->assertSame(4, Rol::count());
        $this->assertSame(4 * 12, DB::table('rol_permisos')->count());
        $this->assertSame('pastor', $usuario->fresh()->role);
        $this->assertNotNull($usuario->fresh()->rol);
    }

    public function test_un_rol_desconocido_en_los_usuarios_detiene_la_migracion(): void
    {
        $migracion = require database_path('migrations/2026_10_28_000001_create_roles_y_permisos.php');
        $migracion->down();
        User::factory()->create(['role' => 'lider_zona']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('lider_zona');

        $migracion->up();
    }
}
