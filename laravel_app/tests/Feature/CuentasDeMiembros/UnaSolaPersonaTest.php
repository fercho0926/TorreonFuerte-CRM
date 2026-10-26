<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\Persona;
use App\Models\SolicitudClave;
use App\Models\User;
use App\Services\ClavesTemporales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 3: el nombre y el celular de la cuenta son siempre los del miembro (spec 016). */
class UnaSolaPersonaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function datosMiembro(Persona $p, array $cambios = []): array
    {
        return $cambios + [
            'nombres' => $p->nombres, 'apellidos' => $p->apellidos, 'estado' => $p->estado,
            'celular' => $p->celular, 'email' => $p->email, 'lider_id' => $p->lider_id,
        ];
    }

    public function test_la_cuenta_muestra_el_nombre_y_celular_corregidos_del_miembro(): void
    {
        $cuenta = $this->A['liderUser'];
        $cuenta->persona->update(['nombres' => 'Milton Andrés', 'apellidos' => 'Pérez', 'celular' => '3001234567']);

        $cuenta = User::withoutGlobalScopes()->find($cuenta->id);
        $this->assertSame('Milton Andrés Pérez', $cuenta->name);
        $this->assertSame('3001234567', $cuenta->celular);

        $this->iniciarSesionComo($this->A['pastor']);
        $this->assertStringContainsString('Milton Andrés Pérez', json_encode($this->getJson(route('users.datos', ['length' => 25]))->json('data'), JSON_UNESCAPED_UNICODE));

        $this->iniciarSesionComo($cuenta);
        $this->get(route('dashboard'))->assertOk()->assertSee('Milton Andrés Pérez');

        $mensaje = app(ClavesTemporales::class)->mensaje($cuenta, 'ABCDE-FGHJK');
        $this->assertStringContainsString('Milton Andrés Pérez', $mensaje);
        $this->assertStringStartsWith('https://wa.me/573001234567', app(ClavesTemporales::class)->enlaceWhatsapp($cuenta, $mensaje));
    }

    public function test_la_edicion_de_la_cuenta_solo_toca_lo_de_la_cuenta(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $cuenta = $this->A['liderUser'];

        $html = $this->get(route('users.edit', $cuenta))->assertOk()->getContent();
        $this->assertStringNotContainsString('name="name"', $html);
        $this->assertStringNotContainsString('name="celular"', $html);
        $this->assertStringNotContainsString('name="persona_id"', $html);
        $this->assertStringContainsString(route('personas.show', $cuenta->persona_id), $html);
        $this->assertStringContainsString('Editar datos personales', $html);

        $this->put(route('users.update', $cuenta), [
            'email' => 'nuevo@iglesia.co', 'role' => 'lider_celula',
            'name' => 'Otro Nombre', 'celular' => '3119990000', 'persona_id' => $this->A['persona']->id,
        ])->assertRedirect(route('users.index'));

        $cuenta = $cuenta->fresh();
        $this->assertSame('nuevo@iglesia.co', $cuenta->email);
        $this->assertSame($this->A['lider']->id, $cuenta->persona_id);
        $this->assertSame('LiderAlfa Guia', $cuenta->name);
    }

    public function test_dos_miembros_con_cuenta_no_comparten_celular(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $lider = $this->A['lider'];
        $otraCuenta = $this->crearUsuario('user', null, $this->A['sede']->id);
        $otraCuenta->persona->update(['celular' => '3105550000']);

        $this->put(route('personas.update', $lider), $this->datosMiembro($lider, ['celular' => '+57 310 555 0000']))
            ->assertSessionHasErrors(['celular' => 'Ese celular ya es el de la cuenta de otro miembro.']);

        $this->put(route('personas.update', $lider), $this->datosMiembro($lider, ['celular' => '']))
            ->assertSessionHasErrors('celular');

        // Un miembro sin cuenta sí puede tener ese celular.
        $persona = $this->A['persona'];
        $this->put(route('personas.update', $persona), $this->datosMiembro($persona, ['celular' => '3105550000']))
            ->assertSessionHasNoErrors();
    }

    public function test_no_se_elimina_un_miembro_con_cuenta(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $lider = $this->A['lider'];

        $this->get(route('personas.show', $lider))
            ->assertOk()
            ->assertSee('Tiene cuenta de usuario; elimina primero la cuenta.')
            ->assertDontSee('¿Eliminar definitivamente?');

        $this->delete(route('personas.destroy', $lider))->assertForbidden();
        $this->assertNotNull($lider->fresh());
    }

    public function test_la_grilla_ordena_y_filtra_por_el_nombre_del_miembro(): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $this->crearUsuario('user', Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Aarón', 'apellidos' => 'Zapata']));

        $filas = $this->getJson(route('users.datos', ['length' => 25, 'orden' => 'nombre', 'dir' => 'asc']))->assertOk()->json('data');
        $this->assertStringContainsString('Aarón Zapata', json_encode($filas[0], JSON_UNESCAPED_UNICODE));

        $buscados = $this->getJson(route('users.datos', ['length' => 25, 'q' => 'Zapata']))->assertOk()->json('data');
        $this->assertCount(1, $buscados);
    }

    public function test_las_solicitudes_de_clave_se_buscan_por_el_nombre_del_miembro(): void
    {
        SolicitudClave::create(['user_id' => $this->A['liderUser']->id, 'sede_id' => $this->A['sede']->id, 'ultima_solicitud_en' => now()]);
        $this->iniciarSesionComo($this->A['pastor']);

        $filas = $this->getJson(route('solicitudes-clave.datos', ['length' => 25, 'q' => 'LiderAlfa']))->assertOk()->json('data');

        $this->assertCount(1, $filas);
    }
}
