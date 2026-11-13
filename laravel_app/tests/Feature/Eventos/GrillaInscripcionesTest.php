<?php

namespace Tests\Feature\Eventos;

use App\Models\EventoPago;
use App\Models\EventoPersona;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Grilla «Miembros inscritos» de un evento (spec 019): protocolo de DataTables, búsqueda por nombre,
 * orden, paginación, exportación y que un evento nunca vea los inscritos de otro ni de otra sede.
 */
class GrillaInscripcionesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    private function inscribir(array $persona = [], ?float $pago = null): EventoPersona
    {
        $p = Persona::factory()->deSede($this->A['sede']->id)->create($persona);
        $inscripcion = EventoPersona::create(['sede_id' => $this->A['sede']->id, 'evento_id' => $this->A['evento']->id, 'persona_id' => $p->id]);
        if ($pago !== null) {
            EventoPago::create(['sede_id' => $this->A['sede']->id, 'evento_persona_id' => $inscripcion->id, 'monto' => $pago, 'fecha_pago' => now()->toDateString()]);
        }

        return $inscripcion;
    }

    public function test_devuelve_el_protocolo_de_datatables_con_una_celda_por_columna(): void
    {
        $this->inscribir(['nombres' => 'Carlos', 'apellidos' => 'Zapata']);

        $json = $this->getJson(route('eventos.inscripciones.datos', [$this->A['evento'], 'draw' => 5, 'start' => 0, 'length' => 10]))
            ->assertOk()->json();

        $this->assertSame(5, $json['draw']);
        // El evento A ya trae su inscripción sembrada (PersonaAlfa) + la que acabamos de crear.
        $this->assertSame(2, $json['recordsTotal']);
        $this->assertSame($json['recordsTotal'], $json['recordsFiltered']);
        $this->assertStringContainsString('Carlos Zapata', implode('', array_merge(...$json['data'])));
        $this->assertCount(6, $json['data'][0], 'miembro, costo, pagado, saldo, estado, acciones');
    }

    public function test_busca_por_nombre(): void
    {
        $this->inscribir(['nombres' => 'Buscada', 'apellidos' => 'Unica']);
        $this->inscribir(['nombres' => 'Otra', 'apellidos' => 'Persona']);

        $json = $this->getJson(route('eventos.inscripciones.datos', [$this->A['evento'], 'q' => 'Buscada', 'length' => 10]))->json();

        $this->assertSame(1, $json['recordsFiltered']);
        $this->assertGreaterThan(1, $json['recordsTotal']);
    }

    public function test_ordena_por_apellido_y_pagina(): void
    {
        // Se ordena por apellidos: «Aaaa» va antes y «Zzzz» después de «Miembro» (PersonaAlfa, ya sembrada).
        $this->inscribir(['nombres' => 'Primero', 'apellidos' => 'Aaaa']);
        $this->inscribir(['nombres' => 'Ultimo', 'apellidos' => 'Zzzz']);

        $asc = $this->getJson(route('eventos.inscripciones.datos', [$this->A['evento'], 'length' => 10, 'order' => [['column' => 0, 'dir' => 'asc']]]))->json('data');
        $this->assertStringContainsString('Aaaa', $asc[0][0]);

        $desc = $this->getJson(route('eventos.inscripciones.datos', [$this->A['evento'], 'length' => 10, 'order' => [['column' => 0, 'dir' => 'desc']]]))->json('data');
        $this->assertStringContainsString('Zzzz', $desc[0][0]);

        $pag = $this->getJson(route('eventos.inscripciones.datos', [$this->A['evento'], 'length' => 10, 'start' => 0]))->json();
        $resto = $this->getJson(route('eventos.inscripciones.datos', [$this->A['evento'], 'length' => 10, 'start' => 10]))->json();
        $this->assertGreaterThan(0, count($pag['data']));
        $this->assertSame([], $resto['data']);
    }

    public function test_no_muestra_inscritos_de_otro_evento_ni_de_otra_sede(): void
    {
        $this->inscribir(['nombres' => 'DeEsteEvento', 'apellidos' => 'Si']);

        $json = $this->getJson(route('eventos.inscripciones.datos', [$this->A['evento'], 'length' => 100]))->json();
        $texto = json_encode($json['data']);

        $this->assertStringContainsString('DeEsteEvento', $texto);
        $this->assertStringNotContainsString('PersonaBeta', $texto, 'no se filtra por evento_id en vano: no debe verse la inscripción del evento B');
    }

    public function test_un_evento_de_otra_sede_responde_404(): void
    {
        $this->getJson(route('eventos.inscripciones.datos', [$this->B['evento'], 'length' => 10]))->assertNotFound();
    }

    public function test_sin_sesion_responde_401_json(): void
    {
        session()->flush();
        $this->getJson(route('eventos.inscripciones.datos', $this->A['evento']))->assertStatus(401);
    }

    public function test_sin_permiso_de_lectura_en_eventos_responde_403(): void
    {
        $rol = \App\Models\Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1])->create();
        $usuario = \App\Models\User::factory()->create([
            'role' => $rol->clave, 'sede_id' => $this->A['sede']->id,
            'persona_id' => Persona::factory()->deSede($this->A['sede']->id)->create()->id,
        ]);
        $this->iniciarSesionComo($usuario);

        $this->getJson(route('eventos.inscripciones.datos', $this->A['evento']))->assertStatus(403);
    }

    public function test_exportar_genera_un_csv_con_los_inscritos_de_ese_evento(): void
    {
        $this->inscribir(['nombres' => 'Exportada', 'apellidos' => 'DelEvento']);

        $respuesta = $this->get(route('eventos.inscripciones.exportar', $this->A['evento']))->assertOk();
        $csv = $respuesta->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Exportada DelEvento', $csv);
        $this->assertStringNotContainsString('PersonaBeta', $csv);
        $this->assertStringContainsString('text/csv', $respuesta->headers->get('Content-Type'));
    }

    public function test_el_boton_ver_logs_muestra_la_trazabilidad_de_ese_miembro_y_no_la_de_otro(): void
    {
        $evento = $this->A['evento'];
        $nueva = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Trazada', 'apellidos' => 'Unica']);

        $this->post(route('eventos.personas.store', $evento), ['persona_id' => $nueva->id])->assertSessionHasNoErrors();
        $inscripcion = $evento->inscripciones()->where('persona_id', $nueva->id)->firstOrFail();
        $this->post(route('eventos.pagos.store', [$evento, $inscripcion]), ['monto' => 20000, 'fecha_pago' => now()->toDateString(), 'notas' => 'Primer abono de prueba'])
            ->assertSessionHasNoErrors();
        $pago = $inscripcion->pagos()->latest('id')->firstOrFail();
        $this->delete(route('eventos.pagos.destroy', [$evento, $inscripcion, $pago]));

        $html = $this->get(route('eventos.show', [$evento, 'pestana' => 'asistencia']))->assertOk()->getContent();

        $this->assertStringContainsString('Ver logs', $html);
        $this->assertStringContainsString('Trazabilidad de Trazada Unica', $html);
        $this->assertStringContainsString('Inscripción', $html);
        $this->assertStringContainsString('Abono registrado', $html);
        $this->assertStringContainsString('Abono eliminado', $html);
        // La nota del abono solo pertenece a la trazabilidad de «Trazada Unica», no a la de PersonaAlfa: aparece
        // dos veces (al registrarlo y al eliminarlo), nunca en el panel de otro miembro.
        $this->assertSame(2, substr_count($html, 'Primer abono de prueba'));
    }

    public function test_el_indice_redirige_a_la_pestana_asistencia_conservando_los_filtros(): void
    {
        $this->get(route('eventos.inscripciones.index', [$this->A['evento'], 'q' => 'algo']))
            ->assertRedirect(route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia', 'q' => 'algo']));
    }
}
