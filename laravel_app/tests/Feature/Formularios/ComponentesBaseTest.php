<?php

namespace Tests\Feature\Formularios;

use App\Support\Formulario;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ComponentesBaseTest extends TestCase
{
    private function renderizar(string $plantilla, array $errores = [], array $viejo = [], array $datos = []): string
    {
        $bolsa = new ViewErrorBag();
        $bolsa->put('default', new MessageBag($errores));

        if ($viejo) {
            $this->app['session.store']->flashInput($viejo + ['_token' => 'x']);
            $this->app['request']->setLaravelSession($this->app['session.store']);
        }

        // En la aplicación real $errors se comparte con todas las vistas (middleware de sesión).
        view()->share('errors', $bolsa);

        return Blade::render($plantilla, $datos);
    }

    public function test_identificadores_y_claves_de_campos(): void
    {
        $this->assertSame('fm-nombres', Formulario::id('nombres'));
        $this->assertSame('fm-pastor-nombres', Formulario::id('pastor[nombres]'));
        $this->assertSame('fm-personas', Formulario::id('personas[]'));
        $this->assertSame('pastor.nombres', Formulario::clave('pastor[nombres]'));
        $this->assertSame('personas', Formulario::clave('personas[]'));
    }

    public function test_el_campo_de_texto_asocia_etiqueta_ayuda_y_error(): void
    {
        $html = $this->renderizar(
            '<x-form.input name="email" label="Correo" type="email" icono="fa-envelope" :required="true" ayuda="Lo usarás para entrar" />',
            ['email' => ['Correo inválido']]
        );

        $this->assertStringContainsString('<label for="fm-email"', $html);
        $this->assertStringContainsString('id="fm-email"', $html);
        $this->assertStringContainsString('aria-required="true"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('aria-describedby="fm-email-ayuda fm-email-error"', $html);
        $this->assertStringContainsString('id="fm-email-error"', $html);
        $this->assertStringContainsString('Correo inválido', $html);
        $this->assertStringContainsString('fm-req', $html);
        $this->assertStringContainsString('fa-envelope', $html);
        $this->assertStringContainsString('inputmode="email"', $html);
        $this->assertStringContainsString('is-invalid', $html);
    }

    public function test_sin_error_no_marca_invalido_ni_aria_invalid(): void
    {
        $html = $this->renderizar('<x-form.input name="nombres" label="Nombres" />');

        $this->assertStringNotContainsString('aria-invalid', $html);
        $this->assertStringNotContainsString('is-invalid', $html);
        $this->assertStringNotContainsString('aria-required', $html);
    }

    public function test_el_valor_previo_se_conserva_con_old(): void
    {
        $html = $this->renderizar('<x-form.input name="nombres" label="Nombres" value="Original" />', [], ['nombres' => 'Lo escrito']);

        $this->assertStringContainsString('value="Lo escrito"', $html);
        $this->assertStringNotContainsString('value="Original"', $html);
    }

    public function test_campos_anidados_usan_clave_con_puntos_para_errores_y_old(): void
    {
        $html = $this->renderizar(
            '<x-form.input name="pastor[email]" label="Correo" />',
            ['pastor.email' => ['Ya existe']],
            ['pastor' => ['email' => 'p@x.co']]
        );

        $this->assertStringContainsString('name="pastor[email]"', $html);
        $this->assertStringContainsString('id="fm-pastor-email"', $html);
        $this->assertStringContainsString('value="p@x.co"', $html);
        $this->assertStringContainsString('Ya existe', $html);
    }

    public function test_el_select_marca_la_opcion_actual_e_imprime_el_dato_de_apoyo(): void
    {
        $opciones = [['id' => 1, 'texto' => 'Ana', 'apoyo' => 'CC 123'], ['id' => 2, 'texto' => 'Beto', 'apoyo' => null]];
        $html = $this->renderizar('<x-form.select name="lider_id" label="Líder" :opciones="$opciones" :value="2" />', [], [], ['opciones' => $opciones]);

        $this->assertStringContainsString('<option value="">Seleccione...</option>', $html);
        $this->assertStringContainsString('data-apoyo="CC 123"', $html);
        $this->assertMatchesRegularExpression('/<option value="2"[^>]*selected/', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="1"[^>]*selected/', $html);
    }

    public function test_el_layout_incluye_marcador_csrf_metodo_y_acciones(): void
    {
        $html = $this->renderizar(
            '<x-form.layout action="/personas/1" method="PUT" guardar="Actualizar" cancelar="/personas"><x-form.section titulo="Datos" icono="fa-user"><x-form.input name="nombres" label="Nombres" /></x-form.section></x-form.layout>'
        );

        $this->assertStringContainsString('data-form="1"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('name="_method" value="PUT"', $html);
        $this->assertStringContainsString('fm-seccion', $html);
        $this->assertStringContainsString('Datos', $html);
        $this->assertStringContainsString('fm-acciones', $html);
        $this->assertStringContainsString('Actualizar', $html);
        $this->assertStringContainsString('href="/personas"', $html);
        $this->assertStringContainsString('Campo obligatorio', $html);
    }

    public function test_el_layout_con_errores_muestra_resumen_con_enlaces(): void
    {
        $html = $this->renderizar(
            '<x-form.layout action="/x"><x-form.input name="nombres" label="Nombres" /></x-form.layout>',
            ['nombres' => ['El campo nombres es obligatorio.']]
        );

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('href="#fm-nombres"', $html);
        $this->assertStringContainsString('data-resumen-servidor', $html);
    }

    public function test_un_formulario_get_no_lleva_csrf(): void
    {
        $html = $this->renderizar('<x-form.layout action="/buscar" method="GET" modo="filtro" guardar="Buscar"></x-form.layout>');

        $this->assertStringContainsString('method="GET"', $html);
        $this->assertStringNotContainsString('name="_token"', $html);
    }

    public function test_fecha_y_monto_declaran_sus_atributos(): void
    {
        $html = $this->renderizar('<x-form.date name="fecha_nacimiento" label="Nacimiento" max="hoy" /><x-form.money name="costo" label="Costo" />');

        $this->assertStringContainsString('type="date"', $html);
        $this->assertStringContainsString('data-fecha', $html);
        $this->assertStringContainsString('data-max="hoy"', $html);
        $this->assertStringContainsString('max="'.now()->toDateString().'"', $html);
        $this->assertStringContainsString('inputmode="decimal"', $html);
        $this->assertStringContainsString('data-monto', $html);
    }

    public function test_el_interruptor_conserva_el_estado_marcado(): void
    {
        $this->assertStringContainsString('checked', $this->renderizar('<x-form.switch name="es_lider" label="Es líder" :marcado="true" />'));
        $this->assertStringNotContainsString('checked', $this->renderizar('<x-form.switch name="es_lider" label="Es líder" :marcado="false" />'));
    }
}
