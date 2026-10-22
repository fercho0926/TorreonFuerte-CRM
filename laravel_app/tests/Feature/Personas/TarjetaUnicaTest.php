<?php

namespace Tests\Feature\Personas;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Historia 1: una sola tarjeta «Datos personales» (FR-001 a FR-003). */
class TarjetaUnicaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    private const CAMPOS = ['nombres', 'apellidos', 'fecha_nacimiento', 'celular', 'email', 'estado'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    /** HTML de la sección pedida, hasta la siguiente. */
    private function seccion(string $html, string $id): string
    {
        $inicio = strpos($html, 'id="'.$id.'"');
        $this->assertNotFalse($inicio, "No existe la sección {$id}");
        $fin = strpos($html, '</section>', $inicio);

        return substr($html, $inicio, $fin - $inicio);
    }

    public function test_crear_tiene_una_tarjeta_con_todos_los_datos_basicos(): void
    {
        $html = $this->get(route('personas.create'))->assertOk()->getContent();
        $tarjeta = $this->seccion($html, 'seccion-personal');

        foreach (self::CAMPOS as $campo) {
            $this->assertStringContainsString('name="'.$campo.'"', $tarjeta, "{$campo} debe estar en «Datos personales»");
        }

        $this->assertStringNotContainsString('id="seccion-contacto"', $html);
        $this->assertStringNotContainsString('id="seccion-documento"', $html);
        $this->assertStringNotContainsString('fm-seccion__titulo">Contacto<', $html);
        $this->assertStringNotContainsString('Estado y documento', $html);
        $this->assertStringContainsString('En célula', $tarjeta);
    }

    public function test_editar_muestra_los_valores_en_la_misma_tarjeta(): void
    {
        $persona = Persona::factory()->deSede($this->A['sede']->id)->create([
            'nombres' => 'Marta', 'apellidos' => 'Lucero', 'celular' => '3201112233', 'email' => 'marta@correo.co',
        ]);

        $tarjeta = $this->seccion($this->get(route('personas.edit', $persona))->assertOk()->getContent(), 'seccion-personal');

        foreach (['Marta', 'Lucero', '320 111 2233', 'marta@correo.co'] as $valor) {
            $this->assertStringContainsString($valor, str_replace('3201112233', '320 111 2233', $tarjeta), $valor);
        }
    }

    public function test_siguen_las_reglas_de_contacto_y_de_duplicados(): void
    {
        $this->post(route('personas.store'), ['nombres' => 'Sin', 'apellidos' => 'Contacto', 'estado' => 'visitante'])->assertSessionHasErrors(['celular', 'email']);

        $datos = ['nombres' => 'Rosa', 'apellidos' => 'Repetida', 'celular' => '3105559999', 'estado' => 'visitante'];
        $this->post(route('personas.store'), $datos)->assertSessionHasNoErrors();
        $this->post(route('personas.store'), $datos)->assertSessionHas('duplicado');
    }

    public function test_la_ficha_agrupa_contacto_estado_en_el_bloque_de_datos_personales(): void
    {
        $persona = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'Marta', 'apellidos' => 'Lucero', 'celular' => '3201112233', 'email' => 'marta@correo.co']);

        $this->get(route('personas.show', $persona))
            ->assertOk()
            ->assertSee('Celular')
            ->assertSee('Correo')
            ->assertSee('marta@correo.co')
            ->assertSee('Miembro activo');
    }
}
