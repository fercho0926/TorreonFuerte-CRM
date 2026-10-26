<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Support\AccionesHistorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Todo lo que se escribe tiene etiqueta en español: ni acciones fuera del catálogo ni campos con su nombre técnico. */
class CatalogoCompletoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_las_entradas_de_un_recorrido_tipico_usan_acciones_y_etiquetas_del_catalogo(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
        $a = $this->A;

        // Un poco de todo: escenario, ediciones, asistencia, abonos, células, ministerios, usuarios y QR.
        $this->put(route('personas.update', $a['persona']), ['nombres' => 'Otro', 'apellidos' => 'Nombre', 'estado' => 'miembro_activo', 'celular' => '3001112233', 'direccion' => 'Calle 1', 'lider_id' => $a['lider']->id]);
        $this->put(route('encuentros.update', $a['encuentro']), ['nombre' => 'E2', 'fecha_realizacion' => now()->toDateString(), 'valor' => 5, 'estado' => 'realizado', 'personas' => [$a['persona']->id]]);
        $this->put(route('encuentros.asistencia', $a['encuentro']), ['asistio' => [$a['persona']->id], 'valor_pagado' => [$a['persona']->id => 5]]);
        $this->put(route('eventos.update', $a['evento']), ['nombre' => 'E3', 'tipo' => 'Otro', 'fecha' => now()->toDateString(), 'costo' => 9, 'estado' => 'realizado']);
        $this->put(route('celulas.update', $a['celula']), ['nombre' => 'C2', 'lider_id' => $a['lider']->id, 'estado' => 'inactiva', 'dia_reunion' => 'Lunes', 'hora_reunion' => '19:00']);
        $this->put(route('ministerios.update', $a['ministerio']), ['nombre' => 'M2', 'estado' => 'inactivo', 'encargado_id' => $a['lider']->id]);
        $this->put(route('users.update', $a['liderUser']), ['email' => 'nuevo@iglesia.co', 'role' => 'user']);
        $this->post(route('registro-qr.abrir', ['encuentro', $a['encuentro']->id]));

        $entradas = CambioHistorial::all();
        $this->assertGreaterThan(10, $entradas->count());

        foreach ($entradas as $entrada) {
            $this->assertTrue(AccionesHistorial::existe($entrada->sujeto_tipo, $entrada->accion), "{$entrada->sujeto_tipo}.{$entrada->accion} no está en el catálogo");
            foreach ($entrada->cambios as $cambio) {
                $this->assertDoesNotMatchRegularExpression('/^[a-z]+(_[a-z]+)+$/', $cambio['etiqueta'], "campo sin etiqueta en {$entrada->sujeto_tipo}: {$cambio['campo']}");
                $this->assertNotSame($cambio['campo'], $cambio['etiqueta'], "campo sin etiqueta en {$entrada->sujeto_tipo}: {$cambio['campo']}");
            }
        }
    }
}
