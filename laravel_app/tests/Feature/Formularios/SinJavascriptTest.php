<?php

namespace Tests\Feature\Formularios;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * La interactividad es una mejora: sin JavaScript los formularios deben seguir funcionando con
 * controles nativos y con la validación del servidor.
 */
class SinJavascriptTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_los_controles_nativos_estan_presentes(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('personas.create'))->getContent();
        $this->assertMatchesRegularExpression('/<select id="fm-estado"/', $html);
        $this->assertMatchesRegularExpression('/<input type="date" id="fm-fecha_nacimiento"/', $html);
        $this->assertMatchesRegularExpression('/<input type="tel" id="fm-celular"/', $html);
        $this->assertMatchesRegularExpression('/<input type="checkbox"[^>]*id="fm-es_lider"/', $html);

        $html = $this->get(route('eventos.create'))->getContent();
        $this->assertMatchesRegularExpression('/<select id="fm-personas"[^>]*multiple/', $html, 'La selección múltiple es un <select multiple> nativo');
    }

    public function test_un_formulario_se_envia_y_se_valida_en_el_servidor_sin_atributos_de_javascript(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        // Datos tal como los enviaría el navegador sin JavaScript (celular sin máscara).
        $this->post(route('personas.store'), [
            'nombres' => 'Sin', 'apellidos' => 'Javascript', 'celular' => '3105559999', 'estado' => 'visitante',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, Persona::where('apellidos', 'Javascript')->count());

        // Y los errores vuelven con lo escrito (old) para corregir.
        $html = $this->from(route('personas.create'))->followingRedirects()->post(route('personas.store'), [
            'nombres' => '', 'apellidos' => 'Conservado', 'celular' => '3105550000', 'estado' => 'visitante',
        ])->getContent();
        $this->assertStringContainsString('value="Conservado"', $html);
        $this->assertStringContainsString('invalid-feedback', $html);
    }

    public function test_los_estilos_de_tarjetas_no_dependen_de_javascript(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('personas.create'))->getContent();

        // Las clases de estilo se imprimen desde el servidor.
        $this->assertStringContainsString('class="fm-seccion', $html);
        $this->assertStringContainsString('fm-seccion__ico', $html);
        $this->assertStringContainsString('input-group-text', $html);
    }

    public function test_en_todas_las_sedes_el_boton_principal_esta_deshabilitado_con_el_aviso_de_solo_consulta(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnTodas();

        $html = $this->get(route('personas.create'))->getContent();

        $this->assertMatchesRegularExpression('/<button type="submit"[^>]*disabled[^>]*data-boton-principal|<button type="submit"[^>]*data-boton-principal[^>]*disabled/', $html);
        $this->assertStringContainsString('Todas las sedes: solo consulta. Elige una sede activa para guardar.', $html);
    }

    public function test_los_filtros_de_listado_siguen_funcionando_como_formularios_get(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('personas.index'))->getContent();
        $this->assertMatchesRegularExpression('/<form method="GET" action="[^"]*personas"[^>]*data-form="1"/', $html);
        $this->assertStringNotContainsString('name="_token"', substr($html, strpos($html, 'method="GET"'), 400));

        $this->get(route('personas.index', ['q' => 'PersonaAlfa']))->assertViewHas('pagina', fn ($p) => $p->total() === 1);
    }
}
