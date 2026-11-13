<?php

namespace Tests\Feature\Eventos;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Pestaña «Asistencia» en la ficha de un evento, separada de «Datos» (spec 019). */
class PestanaAsistenciaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function usuarioSoloLecturaEnEventos(): User
    {
        $rol = Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1, 'eventos' => 1])->create();

        return User::factory()->create([
            'role' => $rol->clave,
            'sede_id' => $this->A['sede']->id,
            'persona_id' => Persona::factory()->deSede($this->A['sede']->id)->create()->id,
        ]);
    }

    public function test_la_pestana_datos_no_muestra_inscripcion_ni_tarjetas_de_pago_pero_si_el_panel_qr(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('eventos.show', $this->A['evento']))->assertOk()->getContent();

        $this->assertStringContainsString('Evento Alfa', $html);
        $this->assertStringContainsString('Total recaudado', $html);
        $this->assertStringContainsString('Registro por QR', $html);
        $this->assertStringNotContainsString('Inscribir miembro', $html);
        $this->assertStringNotContainsString('PersonaAlfa', $html);
        $this->assertStringNotContainsString('Miembros inscritos', $html);
    }

    public function test_la_pestana_datos_de_un_evento_sin_inscritos_no_muestra_mensaje_de_inscripciones(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->A['inscripcion']->pagos()->delete();
        $this->A['inscripcion']->delete();

        $html = $this->get(route('eventos.show', $this->A['evento']))->assertOk()->getContent();

        $this->assertStringNotContainsString('Este evento aún no tiene miembros inscritos.', $html);
    }

    public function test_la_pestana_asistencia_muestra_formulario_y_tarjetas_con_permiso_de_escritura_pero_no_el_panel_qr(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia']))->assertOk()->getContent();

        $this->assertStringContainsString('Inscribir miembro', $html);
        $this->assertStringContainsString('Miembros inscritos', $html);
        $this->assertStringContainsString('1 resultado', $html);
        $this->assertStringContainsString('PersonaAlfa Miembro', $html);
        $this->assertStringContainsString('Saldo pendiente', $html);
        $this->assertStringContainsString('Registrar abono', $html);
        $this->assertStringNotContainsString('Registro por QR', $html);
    }

    public function test_la_pestana_asistencia_en_solo_lectura_oculta_formulario_y_acciones(): void
    {
        $this->iniciarSesionComo($this->usuarioSoloLecturaEnEventos());

        $html = $this->get(route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia']))->assertOk()->getContent();

        $this->assertStringContainsString('PersonaAlfa Miembro', $html);
        $this->assertStringContainsString('Saldo pendiente', $html);
        $this->assertStringNotContainsString('Inscribir miembro', $html);
        $this->assertStringNotContainsString('Registro por QR', $html);
        $this->assertStringNotContainsString('>Quitar<', $html);
    }

    public function test_inscribir_abonar_y_eliminar_abono_dejan_al_usuario_en_asistencia(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $evento = $this->A['evento'];
        $urlAsistencia = route('eventos.show', [$evento, 'pestana' => 'asistencia']);
        $nueva = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Nueva', 'apellidos' => 'Inscrita']);

        $this->post(route('eventos.personas.store', $evento), ['persona_id' => $nueva->id])
            ->assertSessionHasNoErrors()->assertRedirect($urlAsistencia);

        $inscripcion = $evento->inscripciones()->where('persona_id', $nueva->id)->firstOrFail();
        $this->post(route('eventos.pagos.store', [$evento, $inscripcion]), ['monto' => 15000, 'fecha_pago' => now()->toDateString()])
            ->assertSessionHasNoErrors()->assertRedirect($urlAsistencia);

        $pago = $inscripcion->pagos()->latest('id')->firstOrFail();
        $this->delete(route('eventos.pagos.destroy', [$evento, $inscripcion, $pago]))->assertRedirect($urlAsistencia);

        $this->delete(route('eventos.personas.destroy', [$evento, $inscripcion]))->assertRedirect($urlAsistencia);
    }

    public function test_asistencia_sin_inscritos_muestra_formulario_y_mensaje_vacio(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $this->A['inscripcion']->pagos()->delete();
        $this->A['inscripcion']->delete();

        $html = $this->get(route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia']))->assertOk()->getContent();

        $this->assertStringContainsString('Inscribir miembro', $html);
        $this->assertStringContainsString('Este evento aún no tiene miembros inscritos.', $html);
        $this->assertStringNotContainsString('Registro por QR', $html);
    }

    public function test_las_tres_pestanas_aparecen_en_orden_datos_asistencia_historico(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('eventos.show', $this->A['evento']))->assertOk()->getContent();

        $posicionDatos = strpos($html, 'href="'.route('eventos.show', $this->A['evento']).'"');
        $posicionAsistencia = strpos($html, 'pestana=asistencia');
        $posicionHistorico = strpos($html, 'pestana=historico');

        $this->assertNotFalse($posicionDatos, 'falta el enlace a Datos');
        $this->assertNotFalse($posicionAsistencia, 'falta el enlace a Asistencia');
        $this->assertNotFalse($posicionHistorico, 'falta el enlace a Histórico');
        $this->assertLessThan($posicionAsistencia, $posicionDatos);
        $this->assertLessThan($posicionHistorico, $posicionAsistencia);
    }

    public function test_pestana_desconocida_cae_a_datos(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('eventos.show', [$this->A['evento'], 'pestana' => 'lo-que-sea']))->assertOk()->getContent();

        $this->assertStringContainsString('Evento Alfa', $html);
        $this->assertStringContainsString('Total recaudado', $html);
        $this->assertStringNotContainsString('Inscribir miembro', $html);
    }
}
