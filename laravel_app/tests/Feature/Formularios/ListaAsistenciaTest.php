<?php

namespace Tests\Feature\Formularios;

use App\Models\CelulaReunion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class ListaAsistenciaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_genera_una_sola_estructura_con_un_interruptor_por_persona(): void
    {
        view()->share('errors', new ViewErrorBag());
        $filas = [['id' => 1, 'nombre' => 'Ana', 'apoyo' => '310'], ['id' => 2, 'nombre' => 'Beto', 'apoyo' => null], ['id' => 3, 'nombre' => 'Carla']];

        $html = Blade::render('<x-form.lista-asistencia name="asistio[]" :filas="$f" :marcados="[1, 3]" />', ['f' => $filas]);

        $this->assertSame(3, substr_count($html, 'name="asistio[]"'), 'Debe haber exactamente un control por persona (sin duplicar tabla y tarjetas)');
        $this->assertSame(1, substr_count($html, '<table'));
        $this->assertStringContainsString('fm-lista__tabla', $html);
        $this->assertSame(2, preg_match_all('/value="[13]"[^>]*checked/', $html));
        $this->assertDoesNotMatchRegularExpression('/value="2"[^>]*checked/', $html);
        $this->assertStringContainsString('data-etiqueta="Persona"', $html);
        $this->assertStringContainsString('<span data-lista-total>3</span>', $html);
    }

    public function test_cada_interruptor_tiene_etiqueta_accesible(): void
    {
        view()->share('errors', new ViewErrorBag());
        $html = Blade::render('<x-form.lista-asistencia name="asistio[]" :filas="$f" />', ['f' => [['id' => 7, 'nombre' => 'Ana Pérez']]]);

        $this->assertStringContainsString('<label class="form-check-label" for="fm-asistio-7">', $html);
        $this->assertStringContainsString('Asistió: Ana Pérez', $html);
    }

    public function test_muestra_columnas_extra_y_campos_editables_por_fila(): void
    {
        view()->share('errors', new ViewErrorBag());
        $filas = [['id' => 5, 'nombre' => 'Ana', 'extra' => ['Red' => 'Jóvenes'], 'campos' => [['etiqueta' => 'Valor pagado', 'name' => 'valor_pagado[5]', 'valor' => '1000.00', 'tipo' => 'monto']]]];

        $html = Blade::render('<x-form.lista-asistencia name="asistio[]" :filas="$f" :columnas="[\'Red\']" />', ['f' => $filas]);

        $this->assertStringContainsString('Jóvenes', $html);
        $this->assertStringContainsString('name="valor_pagado[5]"', $html);
        $this->assertStringContainsString('data-monto', $html);
        $this->assertStringContainsString('value="1000.00"', $html);
    }

    public function test_sin_interruptor_solo_muestra_campos(): void
    {
        view()->share('errors', new ViewErrorBag());
        $filas = [['id' => 9, 'nombre' => 'Ana', 'campos' => [['etiqueta' => 'Rol', 'name' => 'rol[9]', 'valor' => 'Coord', 'tipo' => 'texto']]]];

        $html = Blade::render('<x-form.lista-asistencia name="integrantes[]" :interruptor="false" :filas="$f" />', ['f' => $filas]);

        $this->assertStringNotContainsString('form-switch', $html);
        $this->assertStringContainsString('name="rol[9]"', $html);
    }

    public function test_la_ficha_de_celula_registra_asistencia_con_los_mismos_nombres_de_antes(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $this->get(route('celulas.show', $this->A['celula']))
            ->assertOk()
            ->assertSee('data-lista-asistencia', false)
            ->assertSee('name="asistio[]"', false);

        $this->post(route('celulas.reuniones.store', $this->A['celula']), [
            'fecha' => now()->toDateString(), 'tema' => 'Tema', 'asistio' => [$this->A['persona']->id],
        ])->assertSessionHasNoErrors();

        $reunion = CelulaReunion::withoutGlobalScopes()->where('celula_id', $this->A['celula']->id)->firstOrFail();
        $this->assertSame(1, $reunion->totalAsistio());
    }

    public function test_encuentro_y_ministerio_conservan_sus_campos_por_persona(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
        $this->A['encuentro']->personas()->attach($this->A['persona']->id);
        $this->A['ministerio']->integrantes()->attach($this->A['lider']->id, ['rol' => 'Coordinador']);

        $this->get(route('encuentros.show', $this->A['encuentro']))
            ->assertSee('name="valor_pagado['.$this->A['persona']->id.']"', false)
            ->assertSee('name="asistio[]"', false);

        $this->get(route('ministerios.show', $this->A['ministerio']))
            ->assertSee('name="rol['.$this->A['lider']->id.']"', false)
            ->assertSee('name="fecha_ingreso['.$this->A['lider']->id.']"', false)
            ->assertSee('value="Coordinador"', false);
    }
}
