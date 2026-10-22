<?php

namespace Tests\Feature\RegistroQr;

use App\Models\Persona;
use App\Models\RegistroQr;
use PHPUnit\Framework\Attributes\DataProvider;

class AislamientoTest extends RegistroQrTestCase
{
    #[DataProvider('destinos')]
    public function test_el_pastor_de_una_sede_no_gestiona_el_qr_de_otra(string $destino): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $id = $this->B[$destino]->id;
        $token = $this->token($destino, 'B');

        $this->get(route('registro-qr.svg', [$destino, $id]))->assertNotFound();
        $this->post(route('registro-qr.abrir', [$destino, $id]))->assertNotFound();
        $this->post(route('registro-qr.regenerar', [$destino, $id]))->assertNotFound();

        $this->assertSame($token, $this->token($destino, 'B'));
        $this->assertTrue((bool) $this->B[$destino]->fresh()->registro_abierto);
    }

    public function test_un_usuario_sin_sesion_no_gestiona_ningun_qr(): void
    {
        $this->get(route('registro-qr.svg', ['evento', $this->A['evento']->id]))->assertForbidden();
    }

    public function test_una_persona_de_otra_sede_no_se_precarga_no_se_modifica_ni_se_duplica(): void
    {
        $ajena = $this->B['persona'];
        $ajena->update(['celular' => '3201112222', 'numero_documento' => '5550001111', 'tipo_documento' => 'CC']);
        $token = $this->token('evento');
        $antes = Persona::withoutGlobalScopes()->count();

        // La búsqueda no revela que existe en otra sede.
        $this->postJson(route('registro.buscar', $token), ['celular' => '3201112222'])->assertOk()->assertExactJson(['estado' => 'nueva']);
        $this->postJson(route('registro.buscar', $token), ['numero_documento' => '5550001111'])->assertOk()->assertExactJson(['estado' => 'nueva']);

        $this->post(route('registro.store', $token), $this->datosNuevos([
            'celular' => '320 111 2222', 'nombres' => 'Escrito', 'apellidos' => 'PorElla',
        ]))->assertRedirect(route('registro.listo', $token));

        $this->assertSame($antes, Persona::withoutGlobalScopes()->count(), 'No se crea persona en la sede A');
        $this->assertSame('PersonaBeta', $ajena->fresh()->nombres);
        $this->assertSame($this->B['sede']->id, $ajena->fresh()->sede_id);
        $this->assertSame(0, $this->A['evento']->inscripciones()->withoutGlobalScopes()->where('persona_id', $ajena->id)->count());

        $registro = RegistroQr::withoutGlobalScopes()->firstOrFail();
        $this->assertSame('de_otra_sede', $registro->resultado);
        $this->assertNull($registro->persona_id);
        $this->assertSame('Escrito', $registro->nombres);
        $this->assertSame($this->A['sede']->id, $registro->sede_id);
    }

    public function test_el_admin_de_la_sede_ve_de_otra_sede_sin_enlace_a_la_ficha(): void
    {
        $this->B['persona']->update(['celular' => '3201112222']);
        $this->post(route('registro.store', $this->token('evento')), $this->datosNuevos(['celular' => '3201112222', 'nombres' => 'Visitante', 'apellidos' => 'Foraneo']));

        $this->iniciarSesionComo($this->A['pastor']);
        $respuesta = $this->get(route('eventos.show', $this->A['evento']))->assertOk()->assertSee('Visitante Foraneo')->assertSee('De otra sede');
        $respuesta->assertDontSee('PersonaBeta')->assertDontSee(route('personas.show', $this->B['persona']), false);

        $this->iniciarSesionComo($this->B['pastor']);
        $this->get(route('eventos.show', $this->B['evento']))->assertOk()->assertDontSee('Visitante Foraneo');
    }
}
