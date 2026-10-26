<?php

namespace Tests\Unit;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Services\SedeContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class SedeScopeTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private Sede $a;

    private Sede $b;

    protected function setUp(): void
    {
        parent::setUp();

        $this->a = $this->crearSede(['nombre' => 'A']);
        $this->b = $this->crearSede(['nombre' => 'B']);
        Persona::factory()->deSede($this->a->id)->create(['nombres' => 'EnA']);
        Persona::factory()->deSede($this->b->id)->create(['nombres' => 'EnB']);
    }

    private function nombres(): array
    {
        return Persona::query()->orderBy('nombres')->pluck('nombres')->all();
    }

    public function test_no_admin_con_sede_solo_ve_su_sede(): void
    {
        $this->comoPastorDe($this->a);

        $nombres = $this->nombres();

        $this->assertContains('EnA', $nombres);
        $this->assertNotContains('EnB', $nombres);
    }

    public function test_no_admin_sin_sede_no_ve_nada(): void
    {
        $user = User::factory()->create(['role' => 'pastor', 'sede_id' => null]);
        $this->iniciarSesionComo($user);

        $this->assertSame([], $this->nombres());
    }

    public function test_admin_con_sede_activa_solo_ve_esa_sede(): void
    {
        $this->comoAdminEnSede($this->b);

        $this->assertSame(['EnB'], $this->nombres());
    }

    public function test_admin_en_todas_ve_todo(): void
    {
        $this->comoAdminEnTodas();

        $this->assertContains('EnA', $this->nombres());
        $this->assertContains('EnB', $this->nombres());
    }

    public function test_sin_usuario_en_la_web_no_se_ve_nada_y_en_consola_se_ve_todo(): void
    {
        Session::flush();
        $contexto = app(SedeContext::class);

        // Consola (seeders, migraciones, tinker): sin filtro.
        $this->assertContains('EnA', $this->nombres());

        // Web sin usuario: falla cerrado.
        $contexto->iniciarPeticionWeb();
        $this->assertSame([], $this->nombres());
        $contexto->terminarPeticionWeb();
    }

    public function test_al_crear_se_asigna_la_sede_efectiva_y_se_rechaza_otra_sede(): void
    {
        $this->comoPastorDe($this->a);
        app(SedeContext::class)->iniciarPeticionWeb();

        $persona = Persona::create(['nombres' => 'Nueva', 'apellidos' => 'Persona', 'estado' => 'visitante']);
        $this->assertSame($this->a->id, $persona->sede_id);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        Persona::create(['nombres' => 'Ajena', 'apellidos' => 'Persona', 'estado' => 'visitante', 'sede_id' => $this->b->id]);
    }

    public function test_el_admin_en_todas_no_puede_crear_en_la_web(): void
    {
        $this->comoAdminEnTodas();
        app(SedeContext::class)->iniciarPeticionWeb();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        Persona::create(['nombres' => 'SinSede', 'apellidos' => 'Persona', 'estado' => 'visitante']);
    }

    public function test_con_sede_y_sin_filtro_se_pueden_usar_en_servicios(): void
    {
        $this->comoAdminEnTodas();
        $contexto = app(SedeContext::class);

        $this->assertSame(['EnB'], $contexto->conSede($this->b->id, fn () => $this->nombres()));
        // Sin filtro aparecen las dos sedes, más el miembro dueño de la cuenta del administrador (spec 016).
        $todos = $contexto->sinFiltro(fn () => $this->nombres());
        $this->assertContains('EnA', $todos);
        $this->assertContains('EnB', $todos);
        $this->assertCount(3, $todos);
    }
}
