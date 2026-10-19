<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Red;
use App\Models\Sede;
use App\Models\User;
use App\Models\Zona;
use App\Support\MigracionSedes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class MigracionSedesTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    /**
     * Simula la base de la versión de una sola iglesia: sede_id vuelve a ser opcional y sin
     * filas con sede.
     */
    private function simularBaseAnterior(): void
    {
        foreach (MigracionSedes::TABLAS as $tabla) {
            Schema::table($tabla, fn (Blueprint $t) => $t->unsignedBigInteger('sede_id')->nullable()->change());
        }
    }

    private function insertarLegado(string $tabla, array $fila): int
    {
        $tiempos = array_filter(
            ['created_at' => now(), 'updated_at' => now()],
            fn ($valor, $columna) => Schema::hasColumn($tabla, $columna),
            ARRAY_FILTER_USE_BOTH
        );

        return DB::table($tabla)->insertGetId($fila + $tiempos);
    }

    /**
     * @return array<string, int>
     */
    private function contarTablas(): array
    {
        $conteo = [];
        foreach (array_merge(MigracionSedes::TABLAS, ['users']) as $tabla) {
            $conteo[$tabla] = DB::table($tabla)->count();
        }

        return $conteo;
    }

    private function datosLegados(): void
    {
        $this->simularBaseAnterior();

        $pastorPersona = $this->insertarLegado('personas', ['nombres' => 'Pablo', 'apellidos' => 'Pastor', 'tipo_documento' => 'CC', 'numero_documento' => '1', 'estado' => 'miembro_activo']);
        $otra = $this->insertarLegado('personas', ['nombres' => 'Ana', 'apellidos' => 'Miembro', 'tipo_documento' => 'CC', 'numero_documento' => '2', 'estado' => 'miembro_activo', 'lider_id' => $pastorPersona]);
        $red = $this->insertarLegado('redes', ['nombre' => 'Jóvenes', 'clave' => 'jovenes', 'activa' => 1]);
        $this->insertarLegado('zonas', ['nombre' => 'Norte', 'clave' => 'norte', 'activa' => 1]);
        $celula = $this->insertarLegado('celulas', ['nombre' => 'Célula 1', 'lider_id' => $pastorPersona, 'estado' => 'activa']);
        $reunion = $this->insertarLegado('celula_reuniones', ['celula_id' => $celula, 'fecha' => now()->toDateString()]);
        $evento = $this->insertarLegado('eventos', ['nombre' => 'Cena', 'tipo' => 'Cena de varones', 'fecha' => now()->addWeek()->toDateString(), 'costo' => 10000]);
        $inscripcion = $this->insertarLegado('evento_persona', ['evento_id' => $evento, 'persona_id' => $otra]);
        $this->insertarLegado('evento_pagos', ['evento_persona_id' => $inscripcion, 'monto' => 5000, 'fecha_pago' => now()->toDateString()]);
        $this->insertarLegado('ministerios', ['nombre' => 'Alabanza', 'estado' => 'activo']);
        $this->insertarLegado('encuentros', ['nombre' => 'Encuentro', 'fecha_realizacion' => now()->toDateString()]);
        $this->insertarLegado('persona_cambios', ['persona_id' => $otra, 'accion' => 'creada', 'created_at' => now()]);

        $this->insertarLegado('users', ['name' => 'Admin', 'email' => 'admin@legado.co', 'password' => 'x', 'role' => 'admin']);
        $this->insertarLegado('users', ['name' => 'Pastor', 'email' => 'pastor@legado.co', 'password' => 'x', 'role' => 'pastor', 'persona_id' => $pastorPersona]);
        $this->insertarLegado('users', ['name' => 'Lider', 'email' => 'lider@legado.co', 'password' => 'x', 'role' => 'lider_celula', 'persona_id' => $otra]);
        $this->assertSame([$red, $reunion], [$red, $reunion]);
    }

    public function test_todas_las_filas_quedan_en_la_primera_sede_sin_perder_registros(): void
    {
        $this->datosLegados();
        $antes = $this->contarTablas();

        $resultado = MigracionSedes::ejecutar('Torreón Fuerte Central');

        $this->assertTrue($resultado['sede_creada']);
        $this->assertSame($antes, $this->contarTablas());
        $this->assertSame(1, Sede::count());

        $sede = Sede::firstOrFail();
        $this->assertSame('Torreón Fuerte Central', $sede->nombre);

        foreach (MigracionSedes::TABLAS as $tabla) {
            $this->assertSame($antes[$tabla], DB::table($tabla)->where('sede_id', $sede->id)->count(), "La tabla {$tabla} no quedó completa en la primera sede");
        }

        $this->assertSame([], array_filter(MigracionSedes::contarSinSede()), 'Quedaron filas sin sede');
    }

    public function test_admins_sin_sede_pastores_y_usuarios_conservan_credenciales_en_la_primera_sede(): void
    {
        $this->datosLegados();

        MigracionSedes::ejecutar();

        $sede = Sede::firstOrFail();
        $this->assertNull(User::withoutGlobalScopes()->where('email', 'admin@legado.co')->value('sede_id'));
        $this->assertSame($sede->id, User::withoutGlobalScopes()->where('email', 'pastor@legado.co')->value('sede_id'));
        $this->assertSame($sede->id, User::withoutGlobalScopes()->where('email', 'lider@legado.co')->value('sede_id'));
        $this->assertSame('x', User::withoutGlobalScopes()->where('email', 'pastor@legado.co')->value('password'));

        $pastor = User::withoutGlobalScopes()->where('email', 'pastor@legado.co')->first();
        $this->assertSame($pastor->persona_id, $sede->pastor_persona_id);
    }

    public function test_el_pastor_actual_solo_ve_la_primera_sede_y_el_admin_ve_todo_tras_migrar(): void
    {
        $this->datosLegados();
        MigracionSedes::ejecutar();
        $primera = Sede::firstOrFail();
        $segunda = $this->crearSede(['nombre' => 'Segunda']);
        Persona::factory()->deSede($segunda->id)->create(['nombres' => 'DeLaSegunda']);

        $this->iniciarSesionComo(User::withoutGlobalScopes()->where('email', 'pastor@legado.co')->first());
        $this->get(route('personas.index'))->assertOk()->assertSee('Ana Miembro')->assertDontSee('DeLaSegunda');

        $this->comoAdminEnTodas();
        $this->get(route('personas.index'))->assertOk()->assertSee('Ana Miembro')->assertSee('DeLaSegunda');
        $this->assertSame($primera->id, Persona::withoutGlobalScopes()->where('nombres', 'Ana')->value('sede_id'));
    }

    public function test_es_idempotente_y_no_crea_sede_si_no_hay_datos_pendientes(): void
    {
        $resultado = MigracionSedes::ejecutar();
        $this->assertFalse($resultado['sede_creada']);
        $this->assertSame(0, Sede::count());

        $this->datosLegados();
        MigracionSedes::ejecutar();
        MigracionSedes::ejecutar();

        $this->assertSame(1, Sede::count());
    }

    public function test_tras_el_esquema_endurecido_documento_red_y_zona_se_repiten_entre_sedes(): void
    {
        $a = $this->crearSede(['nombre' => 'A']);
        $b = $this->crearSede(['nombre' => 'B']);

        Persona::factory()->deSede($a->id)->create(['numero_documento' => '555']);
        Persona::factory()->deSede($b->id)->create(['numero_documento' => '555']);
        Red::create(['sede_id' => $a->id, 'nombre' => 'Jóvenes']);
        Red::create(['sede_id' => $b->id, 'nombre' => 'Jóvenes']);
        Zona::create(['sede_id' => $a->id, 'nombre' => 'Norte']);
        Zona::create(['sede_id' => $b->id, 'nombre' => 'Norte']);

        $this->assertSame(2, Persona::withoutGlobalScopes()->where('numero_documento', '555')->count());
        $this->assertSame(2, Red::withoutGlobalScopes()->where('clave', 'jovenes')->count());
        $this->assertSame(2, Zona::withoutGlobalScopes()->where('clave', 'norte')->count());

        // Pero dentro de una misma sede siguen siendo únicos.
        $this->expectException(\Illuminate\Database\QueryException::class);
        Persona::factory()->deSede($a->id)->create(['numero_documento' => '555']);
    }

    public function test_no_quedan_filas_sin_sede_tras_crear_registros_normalmente(): void
    {
        $a = $this->crearSede(['nombre' => 'A']);
        $this->comoPastorDe($a);
        $this->post(route('personas.store'), ['nombres' => 'Nueva', 'apellidos' => 'Persona', 'celular' => '3001234567', 'estado' => 'visitante'])->assertSessionHasNoErrors();

        $this->assertSame([], array_filter(MigracionSedes::contarSinSede()));
    }
}
