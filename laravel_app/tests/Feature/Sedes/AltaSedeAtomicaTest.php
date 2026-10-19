<?php

namespace Tests\Feature\Sedes;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\SedeCambio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AltaSedeAtomicaTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    private function datos(array $extra = []): array
    {
        return array_replace_recursive([
            'nombre' => 'Torreón Fuerte Norte',
            'ciudad' => 'Torreón',
            'direccion' => 'Calle 1',
            'pastor' => ['nombres' => 'Pablo', 'apellidos' => 'Pérez', 'email' => 'pablo@iglesia.co', 'password' => 'secreto123', 'celular' => '3001112222'],
            'pastora' => ['nombres' => 'Paula', 'apellidos' => 'Gómez', 'email' => 'paula@iglesia.co', 'password' => 'secreto456'],
        ], $extra);
    }

    public function test_el_alta_crea_sede_personas_y_usuarios_de_la_nueva_sede(): void
    {
        $admin = $this->comoAdminEnTodas();

        $this->post(route('sedes.store'), $this->datos())->assertSessionHasNoErrors();

        $sede = Sede::where('nombre', 'Torreón Fuerte Norte')->firstOrFail();
        $this->assertNotNull($sede->pastor_persona_id);
        $this->assertNotNull($sede->pastora_persona_id);

        foreach (['pablo@iglesia.co', 'paula@iglesia.co'] as $correo) {
            $usuario = User::withoutGlobalScopes()->where('email', $correo)->firstOrFail();
            $this->assertSame('pastor', $usuario->role);
            $this->assertSame($sede->id, $usuario->sede_id);
            $this->assertSame($sede->id, Persona::withoutGlobalScopes()->find($usuario->persona_id)->sede_id);
        }

        $this->assertDatabaseHas('sede_cambios', ['sede_id' => $sede->id, 'accion' => 'creada', 'user_id' => $admin->id]);
    }

    public function test_el_alta_solo_con_pastor_funciona(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('sedes.store'), $this->datos(['pastora' => ['nombres' => '', 'apellidos' => '', 'email' => '', 'password' => '']]))
            ->assertSessionHasNoErrors();

        $sede = Sede::where('nombre', 'Torreón Fuerte Norte')->firstOrFail();
        $this->assertNotNull($sede->pastor_persona_id);
        $this->assertNull($sede->pastora_persona_id);
        $this->assertSame(1, User::withoutGlobalScopes()->where('sede_id', $sede->id)->count());
    }

    public function test_un_correo_repetido_de_la_pastora_revierte_todo(): void
    {
        User::factory()->create(['email' => 'paula@iglesia.co', 'role' => 'admin']);
        $this->comoAdminEnTodas();
        $sedes = Sede::count();
        $personas = Persona::withoutGlobalScopes()->count();
        $usuarios = User::withoutGlobalScopes()->count();

        $this->post(route('sedes.store'), $this->datos())->assertSessionHasErrors('pastora.email');

        $this->assertSame($sedes, Sede::count());
        $this->assertSame($personas, Persona::withoutGlobalScopes()->count());
        $this->assertSame($usuarios, User::withoutGlobalScopes()->count());
        $this->assertSame(0, SedeCambio::count());
    }

    public function test_una_falla_a_mitad_del_alta_no_deja_nada_a_medias(): void
    {
        $this->comoAdminEnTodas();
        $sedes = Sede::count();

        // Se fuerza un error al crear a la pastora: el correo coincide con el del pastor (pasa la
        // validación de formato pero choca con el único de usuarios dentro de la transacción).
        $servicio = $this->app->make(\App\Services\SedeService::class);
        $datos = $this->datos(['pastora' => ['email' => 'pablo@iglesia.co']]);

        try {
            $servicio->crear($datos, null);
            $this->fail('Debió fallar por correo repetido');
        } catch (\Illuminate\Database\QueryException) {
            // esperado
        }

        $this->assertSame($sedes, Sede::count());
        $this->assertSame(0, User::withoutGlobalScopes()->where('email', 'pablo@iglesia.co')->count());
        $this->assertSame(0, Persona::withoutGlobalScopes()->where('nombres', 'Pablo')->count());
    }

    public function test_un_nombre_de_sede_repetido_da_error_por_campo(): void
    {
        $this->crearSede(['nombre' => 'Torreón Fuerte Norte']);
        $this->comoAdminEnTodas();

        $this->post(route('sedes.store'), $this->datos())->assertSessionHasErrors('nombre');
        $this->assertSame(1, Sede::count());
    }

    public function test_el_pastor_es_obligatorio(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('sedes.store'), ['nombre' => 'Sin pastor', 'ciudad' => 'Torreón'])->assertSessionHasErrors(['pastor']);
        $this->assertSame(0, Sede::count());
    }
}
