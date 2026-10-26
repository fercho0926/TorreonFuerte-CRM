<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Services\CuentasDeMiembros;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Historia 4: unificar las cuentas existentes con sus miembros (spec 016, research D7). */
class UnificarUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private Sede $sede;

    protected function setUp(): void
    {
        parent::setUp();
        // Base como antes de la spec 016: la cuenta todavía tiene nombre y celular propios.
        (require database_path('migrations/2026_11_01_000004_users_persona_obligatoria.php'))->down();
        $this->sede = Sede::factory()->create(['nombre' => 'Sede Centro']);
    }

    /** Cuenta antigua, sin miembro, tal como quedaba antes de la spec 016. */
    private function cuentaSuelta(string $nombre, ?string $celular, array $extra = []): int
    {
        return DB::table('users')->insertGetId($extra + [
            'name' => $nombre, 'email' => strtolower(str_replace(' ', '.', $nombre)).'@iglesia.co', 'celular' => $celular,
            'password' => 'x', 'role' => 'lider_celula', 'sede_id' => $this->sede->id, 'persona_id' => null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function miembro(array $datos): Persona
    {
        return Persona::factory()->deSede($this->sede->id)->create($datos);
    }

    private function unificar(): array
    {
        return app(CuentasDeMiembros::class)->unificar();
    }

    private function cuenta(int $id): User
    {
        return User::withoutGlobalScopes()->findOrFail($id);
    }

    public function test_una_coincidencia_con_el_mismo_nombre_se_vincula(): void
    {
        $milton = $this->miembro(['nombres' => 'Milton', 'apellidos' => 'Pérez', 'celular' => '300 123 4567']);
        $id = $this->cuentaSuelta('Milton P', '+57 300 123 4567');

        $resumen = $this->unificar();

        $this->assertSame($milton->id, $this->cuenta($id)->persona_id);
        $this->assertNull($this->cuenta($id)->revision_vinculo);
        $this->assertSame(1, $resumen['vinculadas']);
        $this->assertSame(1, Persona::where('nombres', 'Milton')->count(), 'no se duplica');
    }

    public function test_sin_coincidencia_se_crea_el_miembro_con_los_datos_de_la_cuenta(): void
    {
        $id = $this->cuentaSuelta('Rosa María Gómez', '3109998877');

        $resumen = $this->unificar();

        $miembro = $this->cuenta($id)->persona;
        $this->assertSame('Rosa', $miembro->nombres);
        $this->assertSame('María Gómez', $miembro->apellidos);
        $this->assertSame('3109998877', $miembro->celular);
        $this->assertSame('rosa.maría.gómez@iglesia.co', $miembro->email);
        $this->assertSame($this->sede->id, $miembro->sede_id);
        $this->assertSame(1, $resumen['creadas']);
    }

    public function test_varias_coincidencias_quedan_por_revisar(): void
    {
        $a = $this->miembro(['nombres' => 'Ana', 'apellidos' => 'Uno', 'celular' => '3001112233']);
        $b = $this->miembro(['nombres' => 'Ana', 'apellidos' => 'Dos', 'celular' => '3001112233']);
        $id = $this->cuentaSuelta('Ana Tres', '3001112233');

        $resumen = $this->unificar();

        $revision = $this->cuenta($id)->revision_vinculo;
        $this->assertSame('varios', $revision['motivo']);
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $revision['candidatos']);
        $this->assertSame($this->cuenta($id)->persona_id, $revision['creado_automatico']);
        $this->assertSame(1, $resumen['por_revisar']);
    }

    public function test_un_nombre_distinto_queda_por_revisar(): void
    {
        $otro = $this->miembro(['nombres' => 'Carlos', 'apellidos' => 'Ruiz', 'celular' => '3001112233']);
        $id = $this->cuentaSuelta('Milton Pérez', '3001112233');

        $this->unificar();

        $revision = $this->cuenta($id)->revision_vinculo;
        $this->assertSame('nombre_distinto', $revision['motivo']);
        $this->assertSame([$otro->id], $revision['candidatos']);
        $this->assertNotSame($otro->id, $this->cuenta($id)->persona_id);
    }

    public function test_el_administrador_busca_en_todas_las_sedes_y_si_no_va_a_la_primera(): void
    {
        $otraSede = Sede::factory()->create();
        $enOtra = Persona::factory()->deSede($otraSede->id)->create(['nombres' => 'Ada', 'apellidos' => 'Admin', 'celular' => '3005556677']);
        $conCoincidencia = $this->cuentaSuelta('Ada Admin', '3005556677', ['role' => 'admin', 'sede_id' => null]);
        $sinCoincidencia = $this->cuentaSuelta('Otro Admin', '3005550000', ['role' => 'admin', 'sede_id' => null]);

        $this->unificar();

        $this->assertSame($enOtra->id, $this->cuenta($conCoincidencia)->persona_id);
        $this->assertSame($this->sede->id, $this->cuenta($sinCoincidencia)->persona->sede_id);
        $this->assertNull($this->cuenta($sinCoincidencia)->sede_id);
    }

    public function test_en_una_cuenta_ya_vinculada_prevalecen_los_datos_del_miembro(): void
    {
        $miembro = $this->miembro(['nombres' => 'Luis', 'apellidos' => 'Acosta', 'celular' => '3001234567']);
        $id = $this->cuentaSuelta('Lucho', '3000000000', ['persona_id' => $miembro->id]);

        $this->unificar();

        $this->assertSame('Luis Acosta', $this->cuenta($id)->name);
        $this->assertTrue(CambioHistorial::query()->de('usuario', $id)->exists(), 'la diferencia queda en el historial');
    }

    public function test_repetir_la_unificacion_no_cambia_nada(): void
    {
        $this->cuentaSuelta('Rosa Gómez', '3109998877');
        $this->unificar();
        $miembros = Persona::withoutGlobalScopes()->count();

        $resumen = $this->unificar();

        $this->assertSame(['vinculadas' => 0, 'creadas' => 0, 'por_revisar' => 0], array_intersect_key($resumen, array_flip(['vinculadas', 'creadas', 'por_revisar'])));
        $this->assertSame($miembros, Persona::withoutGlobalScopes()->count());
        $this->assertArrayHasKey('sedes_sin_pastor', $resumen);
    }
}
