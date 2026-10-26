<?php

namespace Tests\Feature\Niveles;

use App\Models\AvanceNivel;
use App\Models\NivelFormacion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Miembros por nivel: actual, completaron y siguiente, con la grilla estándar (spec 014, HU4, RF-015 a RF-017 y RF-023). */
class MiembrosPorNivelTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    /** @var array<string, Persona> */
    private array $m = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);

        $this->m['ana'] = $this->miembro('Ana', 'Uno', ['2026-01-10' => 1]);
        $this->m['beto'] = $this->miembro('Beto', 'Dos', ['2026-01-10' => 1, '2026-02-20' => 2]);
        $this->m['carla'] = $this->miembro('Carla', 'Dos', ['2026-03-05' => 2]);          // saltó el 1
        $this->m['dario'] = $this->miembro('Dario', 'Cuatro', ['2025-05-01' => 1, '2025-08-01' => 2, '2025-11-01' => 3, '2026-02-02' => 4]);
        $this->m['eva'] = $this->miembro('Eva', 'Nadie', []);
    }

    private function miembro(string $nombres, string $apellidos, array $niveles): Persona
    {
        $p = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => $nombres, 'apellidos' => $apellidos]);
        foreach ($niveles as $fecha => $posicion) {
            AvanceNivel::factory()->de($p, NivelFormacion::where('posicion', $posicion)->first())->create(['fecha' => $fecha]);
        }

        return $p;
    }

    private function nivelId(int $posicion): int
    {
        return NivelFormacion::where('posicion', $posicion)->value('id');
    }

    private function nombres(array $parametros): array
    {
        $datos = $this->getJson(route('niveles.miembros.datos', $parametros + ['length' => 100]))->assertOk()->json('data');
        $texto = strip_tags(json_encode($datos, JSON_UNESCAPED_UNICODE));

        return array_values(array_filter(['Ana', 'Beto', 'Carla', 'Dario', 'Eva'], fn ($n) => str_contains($texto, "{$n} ")));
    }

    public function test_actual_lista_a_quienes_tienen_ese_nivel_como_el_mas_alto(): void
    {
        $this->assertSame(['Ana'], $this->nombres(['nivel' => $this->nivelId(1)]));
        $this->assertEqualsCanonicalizing(['Beto', 'Carla'], $this->nombres(['nivel' => $this->nivelId(2)]));
        $this->assertSame([], $this->nombres(['nivel' => $this->nivelId(3)]));
        $this->assertSame(['Dario'], $this->nombres(['nivel' => $this->nivelId(4)]));
    }

    public function test_sin_iniciar_lista_a_quienes_no_completaron_ninguno(): void
    {
        $nombres = $this->nombres(['nivel' => 'sin']);

        $this->assertContains('Eva', $nombres);
        $this->assertNotContains('Ana', $nombres);
    }

    public function test_completaron_incluye_a_quienes_ya_van_mas_adelante(): void
    {
        $this->assertEqualsCanonicalizing(['Ana', 'Beto', 'Dario'], $this->nombres(['nivel' => $this->nivelId(1), 'vista' => 'completaron']));
        $this->assertEqualsCanonicalizing(['Beto', 'Carla', 'Dario'], $this->nombres(['nivel' => $this->nivelId(2), 'vista' => 'completaron']));
    }

    public function test_siguiente_lista_a_quienes_les_toca_ese_nivel(): void
    {
        $this->assertContains('Eva', $this->nombres(['nivel' => $this->nivelId(1), 'vista' => 'siguiente']));
        $this->assertContains('Carla', $this->nombres(['nivel' => $this->nivelId(1), 'vista' => 'siguiente']), 'saltó el 1: le toca el 1');
        $this->assertSame(['Ana'], $this->nombres(['nivel' => $this->nivelId(2), 'vista' => 'siguiente']));
        $this->assertSame([], $this->nombres(['nivel' => $this->nivelId(4), 'vista' => 'siguiente']), 'nadie tiene hecho hasta el 3 y no el 4');
    }

    public function test_los_inactivos_solo_con_el_interruptor(): void
    {
        $this->m['ana']->update(['estado' => 'inactivo', 'inactivado_en' => now()]);

        $this->assertSame([], $this->nombres(['nivel' => $this->nivelId(1)]));
        $this->assertSame(['Ana'], $this->nombres(['nivel' => $this->nivelId(1), 'inactivos' => 1]));
    }

    public function test_la_pagina_usa_la_grilla_estandar_con_nombre_del_nivel_y_acciones_de_contacto(): void
    {
        $this->get(route('niveles.miembros.index', ['nivel' => $this->nivelId(2)]))->assertOk()
            ->assertSee('Aliviando el fuego')
            ->assertSee('data-grilla="niveles.miembros"', false)
            ->assertSee('Beto')
            ->assertSee('Llamar')
            ->assertSee('WhatsApp')
            ->assertSee(route('niveles.miembros.exportar'), false)
            ->assertSee('Completaron este nivel')
            ->assertSee('Les toca este nivel');
    }

    public function test_los_filtros_por_columna_y_el_orden_funcionan(): void
    {
        $this->assertSame(['Beto'], $this->nombres(['nivel' => $this->nivelId(2), 'nombre' => 'Beto']));

        $datos = $this->getJson(route('niveles.miembros.datos', ['nivel' => $this->nivelId(2), 'length' => 10, 'order' => [['column' => 0, 'dir' => 'desc']]]))->json('data');
        $this->assertStringContainsString('Carla', $datos[0][0]);
    }

    public function test_exporta_csv_con_nivel_actual_y_fecha_del_ultimo_nivel_completado(): void
    {
        $respuesta = $this->get(route('niveles.miembros.exportar', ['nivel' => $this->nivelId(2)]))->assertOk();
        $csv = $respuesta->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Nivel actual', $csv);
        $this->assertStringContainsString('Último nivel completado el', $csv);
        $this->assertMatchesRegularExpression('/Beto Dos.*Aliviando el fuego.*2026-02-20/', $csv);
        $this->assertStringNotContainsString('Dario', $csv);
    }

    public function test_respeta_el_aislamiento_por_sede(): void
    {
        AvanceNivel::factory()->de($this->B['persona'], NivelFormacion::where('posicion', 1)->first())->create();

        $this->assertNotContains('PersonaBeta', $this->nombres(['nivel' => $this->nivelId(1)]));
        $texto = json_encode($this->getJson(route('niveles.miembros.datos', ['nivel' => $this->nivelId(1), 'length' => 100]))->json('data'));
        $this->assertStringNotContainsString('PersonaBeta', $texto);

        $this->iniciarSesionComo($this->B['pastor']);
        $textoB = json_encode($this->getJson(route('niveles.miembros.datos', ['nivel' => $this->nivelId(1), 'length' => 100]))->json('data'));
        $this->assertStringContainsString('PersonaBeta', $textoB);
        $this->assertStringNotContainsString('Ana', strip_tags($textoB));
    }

    public function test_un_lider_solo_ve_su_rama_y_sin_acceso_es_403(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);
        $texto = strip_tags(json_encode($this->getJson(route('niveles.miembros.datos', ['nivel' => $this->nivelId(1), 'length' => 100]))->assertOk()->json('data'), JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('Ana ', $texto);

        $rol = \App\Models\Rol::factory()->conAlcance('sede')->conPermisos(['tablero' => 1])->create();
        $this->iniciarSesionComo(\App\Models\User::factory()->create(['role' => $rol->clave, 'sede_id' => $this->A['sede']->id]));
        $this->getJson(route('niveles.miembros.datos'))->assertForbidden();
        $this->get(route('niveles.miembros.exportar'))->assertForbidden();
    }

    public function test_un_nivel_inexistente_responde_404(): void
    {
        $this->get(route('niveles.miembros.index', ['nivel' => 999999]))->assertNotFound();
    }
}
