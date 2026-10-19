<?php

namespace Tests\Feature\Sedes;

use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PastoresIniciaSesionTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_el_pastor_y_la_pastora_recien_creados_inician_sesion_y_trabajan_en_su_sede(): void
    {
        $this->comoAdminEnTodas();
        $this->post(route('sedes.store'), [
            'nombre' => 'Iglesia Nueva',
            'ciudad' => 'Torreón',
            'pastor' => ['nombres' => 'Pablo', 'apellidos' => 'Pérez', 'email' => 'pablo@iglesia.co', 'password' => 'secreto123'],
            'pastora' => ['nombres' => 'Paula', 'apellidos' => 'Gómez', 'email' => 'paula@iglesia.co', 'password' => 'secreto456'],
        ])->assertSessionHasNoErrors();

        $sede = Sede::where('nombre', 'Iglesia Nueva')->firstOrFail();

        foreach ([['pablo@iglesia.co', 'secreto123'], ['paula@iglesia.co', 'secreto456']] as $indice => [$correo, $clave]) {
            $this->flushSession();
            $this->post(route('login.post'), ['email' => $correo, 'password' => $clave])->assertRedirect(route('dashboard'));

            $this->get(route('dashboard'))->assertOk()->assertSee('Iglesia Nueva');

            $this->get(route('personas.index'))
                ->assertOk()
                ->assertViewHas('personas', fn ($p) => $p->total() === 2 + $indice);

            $this->post(route('personas.store'), ['nombres' => 'Visita', 'apellidos' => 'De '.$correo, 'celular' => '3004445555', 'estado' => 'visitante'])
                ->assertSessionHasNoErrors();

            $this->assertSame($sede->id, Persona::where('apellidos', 'De '.$correo)->value('sede_id'));
            $this->post(route('logout'));
        }
    }

    public function test_un_usuario_de_una_sede_inactiva_no_inicia_sesion(): void
    {
        $sede = $this->crearSede();
        $pastor = $this->comoPastorDe($sede);
        $this->flushSession();
        $sede->update(['activa' => false]);

        $this->post(route('login.post'), ['email' => $pastor->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
    }
}
