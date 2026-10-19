<?php

namespace Tests\Feature\Personas;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Alta rápida de personas desde la célula (FR-009). */
class AltaRapidaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    private function datos(array $extra = []): array
    {
        return $extra + ['nombres' => 'Rosa', 'apellidos' => 'Invitada', 'celular' => '3105559999'];
    }

    public function test_crea_en_estado_celula_y_responde_201_con_el_formato_del_contrato(): void
    {
        $this->postJson(route('personas.rapida'), $this->datos())
            ->assertCreated()
            ->assertJsonStructure(['id', 'texto', 'apoyo', 'estado', 'sinDocumento'])
            ->assertJsonPath('texto', 'Rosa Invitada')
            ->assertJsonPath('estado', 'celula');

        $persona = Persona::withoutGlobalScopes()->where('nombres', 'Rosa')->firstOrFail();
        $this->assertSame('celula', $persona->estado);
        $this->assertSame($this->A['sede']->id, $persona->sede_id);
        $this->assertNull($persona->numero_documento);
        $this->assertSame(1, $persona->cambios()->where('accion', 'creada')->count());
    }

    public function test_acepta_solo_correo_y_rechaza_sin_ningun_medio_de_contacto(): void
    {
        $this->postJson(route('personas.rapida'), ['nombres' => 'Solo', 'apellidos' => 'Correo', 'email' => 'solo@correo.co'])->assertCreated();
        $this->postJson(route('personas.rapida'), ['nombres' => 'Sin', 'apellidos' => 'Contacto'])
            ->assertUnprocessable()->assertJsonStructure(['errors' => ['celular', 'email']]);
        $this->postJson(route('personas.rapida'), $this->datos(['nombres' => '']))->assertUnprocessable()->assertJsonStructure(['errors' => ['nombres']]);
    }

    public function test_un_posible_duplicado_responde_409_y_con_confirmacion_crea(): void
    {
        $this->postJson(route('personas.rapida'), $this->datos())->assertCreated();

        $this->postJson(route('personas.rapida'), $this->datos())
            ->assertStatus(409)
            ->assertJsonPath('duplicado.nombre', 'Rosa Invitada');
        $this->assertSame(1, Persona::withoutGlobalScopes()->where('nombres', 'Rosa')->count());

        $this->postJson(route('personas.rapida'), $this->datos(['confirmar_duplicado' => true]))->assertCreated();
        $this->assertSame(2, Persona::withoutGlobalScopes()->where('nombres', 'Rosa')->count());
    }

    public function test_un_lider_la_deja_a_su_cargo_y_la_ve_en_su_ambito(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);

        $id = $this->postJson(route('personas.rapida'), $this->datos(['nombres' => 'Delicia']))->assertCreated()->json('id');

        $this->assertSame($this->A['lider']->id, Persona::withoutGlobalScopes()->find($id)->lider_id);
        $this->getJson(route('personas.buscar', ['q' => 'delicia']))->assertJsonPath('resultados.0.id', $id);
    }

    public function test_un_usuario_sin_permiso_de_crear_recibe_403_y_sin_sesion_401(): void
    {
        $sinPersona = $this->crearUsuario('user', null, $this->A['sede']->id);
        $this->iniciarSesionComo($sinPersona);
        $this->postJson(route('personas.rapida'), $this->datos())->assertForbidden();

        $this->flushSession();
        $this->postJson(route('personas.rapida'), $this->datos())->assertUnauthorized();
        $this->assertSame(0, Persona::withoutGlobalScopes()->where('nombres', 'Rosa')->count());
    }
}
