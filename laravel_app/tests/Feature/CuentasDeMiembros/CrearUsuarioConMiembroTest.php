<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historias 1 y 2: «Nuevo usuario» en dos pasos, vinculando o creando el miembro (spec 016). */
class CrearUsuarioConMiembroTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function milton(): Persona
    {
        return Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Milton', 'apellidos' => 'Pérez', 'celular' => '3001234567']);
    }

    private function cuenta(array $extra = []): array
    {
        return $extra + ['celular' => '3001234567', 'email' => 'milton@iglesia.co', 'role' => 'lider_celula'];
    }

    // Historia 1: miembro existente

    public function test_el_paso_uno_solo_pide_el_celular(): void
    {
        $html = $this->get(route('users.create'))->assertOk()->getContent();

        $this->assertStringContainsString('name="celular"', $html);
        $this->assertStringContainsString('inputmode="tel"', $html);
        $this->assertStringNotContainsString('name="email"', $html);
        $this->assertStringNotContainsString('name="name"', $html);
        $this->assertStringNotContainsString('name="sede_id"', $html, 'el pastor no elige sede');
    }

    public function test_al_buscar_el_celular_muestra_al_miembro_encontrado(): void
    {
        $milton = $this->milton();

        $this->get(route('users.create', ['celular' => '+57 300 123 4567']))
            ->assertOk()
            ->assertSee('Milton Pérez')
            ->assertSee('name="persona_id" value="'.$milton->id.'"', false)
            ->assertSee('name="email"', false)
            ->assertDontSee('name="miembro[nombres]"', false);
    }

    public function test_guardar_vincula_la_cuenta_al_miembro_sin_crear_otro(): void
    {
        $milton = $this->milton();
        $miembrosAntes = Persona::withoutGlobalScopes()->count();

        $this->post(route('users.store'), $this->cuenta(['persona_id' => $milton->id]))
            ->assertOk()
            ->assertSee('Clave generada');

        $usuario = User::withoutGlobalScopes()->where('email', 'milton@iglesia.co')->firstOrFail();
        $this->assertSame($milton->id, $usuario->persona_id);
        $this->assertSame('Milton Pérez', $usuario->name);
        $this->assertSame($this->A['sede']->id, $usuario->sede_id);
        $this->assertSame($miembrosAntes, Persona::withoutGlobalScopes()->count());
        $this->assertSame('milton@iglesia.co', $milton->fresh()->email, 'el miembro sin correo toma el de la cuenta');
    }

    public function test_un_miembro_con_correo_conserva_el_suyo(): void
    {
        $milton = $this->milton();
        $milton->update(['email' => 'personal@correo.co']);

        $this->post(route('users.store'), $this->cuenta(['persona_id' => $milton->id]))->assertOk();

        $this->assertSame('personal@correo.co', $milton->fresh()->email);
    }

    public function test_un_miembro_que_no_corresponde_no_crea_nada(): void
    {
        $conCuenta = $this->A['lider'];
        $inactivo = Persona::factory()->deSede($this->A['sede']->id)->inactiva()->create(['celular' => '3005550001']);
        $otroCelular = Persona::factory()->deSede($this->A['sede']->id)->create(['celular' => '3005550002']);

        foreach ([
            [$conCuenta, $conCuenta->celular],
            [$inactivo, '3005550001'],
            [$otroCelular, '3001234567'],
            [$this->B['persona'], $this->B['persona']->celular],
        ] as [$persona, $celular]) {
            $this->post(route('users.store'), $this->cuenta(['persona_id' => $persona->id, 'celular' => $celular]))
                ->assertSessionHasErrors('persona_id');
        }

        $this->assertSame(0, User::withoutGlobalScopes()->where('email', 'milton@iglesia.co')->count());
    }

    public function test_un_miembro_con_cuenta_muestra_el_aviso_sin_formulario(): void
    {
        $lider = $this->A['lider'];

        $this->get(route('users.create', ['celular' => $lider->celular]))
            ->assertOk()
            ->assertSee('ya tiene una cuenta')
            ->assertSee(route('users.edit', $this->A['liderUser']), false)
            ->assertDontSee('name="email"', false);
    }

    public function test_varios_miembros_con_el_mismo_celular_se_eligen(): void
    {
        $milton = $this->milton();
        $otro = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Marta', 'apellidos' => 'Pérez', 'celular' => '300 123 4567']);

        $this->get(route('users.create', ['celular' => '3001234567']))
            ->assertOk()
            ->assertSee('Milton Pérez')
            ->assertSee('Marta Pérez')
            ->assertSee('type="radio"', false);

        $this->post(route('users.store'), $this->cuenta(['persona_id' => $otro->id]))->assertOk();
        $this->assertSame($otro->id, User::withoutGlobalScopes()->where('email', 'milton@iglesia.co')->value('persona_id'));
    }

    public function test_un_miembro_inactivo_muestra_el_aviso_para_reactivarlo(): void
    {
        $inactivo = Persona::factory()->deSede($this->A['sede']->id)->inactiva()->create(['celular' => '3005550001']);

        $this->get(route('users.create', ['celular' => '3005550001']))
            ->assertOk()
            ->assertSee('inactivo')
            ->assertSee(route('personas.show', $inactivo), false)
            ->assertDontSee('name="email"', false);
    }

    // Historia 2: el miembro no existe

    public function test_un_celular_nuevo_pide_los_datos_del_miembro(): void
    {
        $this->get(route('users.create', ['celular' => '3009998877']))
            ->assertOk()
            ->assertSee('name="miembro[nombres]"', false)
            ->assertSee('name="miembro[apellidos]"', false)
            ->assertSee('name="email"', false)
            ->assertSee('300 999 8877', false)
            ->assertDontSee('name="persona_id"', false);
    }

    public function test_guardar_crea_el_miembro_y_la_cuenta_juntos(): void
    {
        $this->post(route('users.store'), $this->cuenta(['celular' => '300 999 8877', 'miembro' => ['nombres' => 'Rosa', 'apellidos' => 'Gómez']]))
            ->assertOk()
            ->assertSee('Clave generada');

        $usuario = User::withoutGlobalScopes()->where('email', 'milton@iglesia.co')->firstOrFail();
        $miembro = $usuario->persona;
        $this->assertSame('Rosa Gómez', $miembro->nombre_completo);
        $this->assertSame('300 999 8877', $miembro->celular);
        $this->assertSame('milton@iglesia.co', $miembro->email);
        $this->assertSame('miembro_activo', $miembro->estado);
        $this->assertSame($this->A['sede']->id, $miembro->sede_id);
        $this->assertSame('Rosa Gómez', $usuario->name);
    }

    public function test_si_falta_un_dato_del_miembro_no_se_crea_nada(): void
    {
        $miembrosAntes = Persona::withoutGlobalScopes()->count();

        $this->post(route('users.store'), $this->cuenta(['celular' => '3009998877', 'miembro' => ['nombres' => 'Rosa', 'apellidos' => '']]))
            ->assertSessionHasErrors('miembro.apellidos');

        $this->assertSame($miembrosAntes, Persona::withoutGlobalScopes()->count());
        $this->assertSame(0, User::withoutGlobalScopes()->where('email', 'milton@iglesia.co')->count());
    }

    public function test_si_la_cuenta_falla_tampoco_queda_el_miembro(): void
    {
        $miembrosAntes = Persona::withoutGlobalScopes()->count();

        $this->post(route('users.store'), $this->cuenta(['celular' => '3009998877', 'email' => $this->A['pastor']->email, 'miembro' => ['nombres' => 'Rosa', 'apellidos' => 'Gómez']]))
            ->assertSessionHasErrors('email');

        $this->assertSame($miembrosAntes, Persona::withoutGlobalScopes()->count());
    }

    public function test_no_se_crea_un_miembro_nuevo_si_el_celular_ya_es_de_otro(): void
    {
        $this->milton();

        $this->post(route('users.store'), $this->cuenta(['miembro' => ['nombres' => 'Milton', 'apellidos' => 'Duplicado']]))
            ->assertSessionHasErrors('celular');

        $this->assertSame(0, Persona::where('apellidos', 'Duplicado')->count());
    }

    public function test_el_administrador_elige_la_sede_del_miembro_y_los_admins_quedan_sin_sede(): void
    {
        $this->comoAdminEnTodas();

        $this->get(route('users.create'))->assertOk()->assertSee('name="sede_id"', false);

        $this->post(route('users.store'), $this->cuenta([
            'celular' => '3009998877', 'email' => 'enb@iglesia.co', 'sede_id' => $this->B['sede']->id,
            'miembro' => ['nombres' => 'Beto', 'apellidos' => 'Beta'],
        ]))->assertOk();
        $enB = User::withoutGlobalScopes()->where('email', 'enb@iglesia.co')->firstOrFail();
        $this->assertSame($this->B['sede']->id, $enB->sede_id);
        $this->assertSame($this->B['sede']->id, $enB->persona->sede_id);

        $this->post(route('users.store'), $this->cuenta([
            'celular' => '3009998866', 'email' => 'adm@iglesia.co', 'role' => 'admin', 'sede_id' => $this->A['sede']->id,
            'miembro' => ['nombres' => 'Ada', 'apellidos' => 'Admin'],
        ]))->assertOk()->assertSee('Enviar por WhatsApp');
        $admin = User::withoutGlobalScopes()->where('email', 'adm@iglesia.co')->firstOrFail();
        $this->assertNull($admin->sede_id);
        $this->assertSame($this->A['sede']->id, $admin->persona->sede_id);
    }

    public function test_el_administrador_debe_elegir_la_sede(): void
    {
        $this->comoAdminEnTodas();

        $this->post(route('users.store'), $this->cuenta(['celular' => '3009998877', 'miembro' => ['nombres' => 'Sin', 'apellidos' => 'Sede']]))
            ->assertSessionHasErrors('sede_id');
    }
}
