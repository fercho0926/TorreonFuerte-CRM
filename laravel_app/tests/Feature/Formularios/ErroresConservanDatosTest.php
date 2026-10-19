<?php

namespace Tests\Feature\Formularios;

use App\Models\Celula;
use App\Models\Evento;
use App\Models\Persona;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class ErroresConservanDatosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    public function test_persona_con_error_conserva_lo_escrito_marca_campos_y_resume_errores(): void
    {
        $respuesta = $this->from(route('personas.create'))->followingRedirects()->post(route('personas.store'), [
            'nombres' => '',
            'apellidos' => 'Apellido Escrito',
            'celular' => '3105559999',
            'estado' => 'visitante',
            'barrio' => 'Barrio Escrito',
        ]);

        $html = $respuesta->getContent();
        $this->assertStringContainsString('value="Apellido Escrito"', $html);
        $this->assertStringContainsString('value="3105559999"', $html);
        $this->assertStringContainsString('value="Barrio Escrito"', $html);
        $this->assertMatchesRegularExpression('/id="fm-nombres"[^>]*aria-invalid="true"/s', $html);
        $this->assertStringContainsString('is-invalid', $html);
        $this->assertStringContainsString('data-resumen-servidor', $html);
        $this->assertStringContainsString('href="#fm-nombres"', $html);
        $this->assertStringContainsString('id="fm-nombres-error"', $html);
        $this->assertSame(0, Persona::where('apellidos', 'Apellido Escrito')->count());
    }

    public function test_documento_repetido_marca_el_campo_y_conserva_el_resto(): void
    {
        $documento = $this->A['persona']->numero_documento;

        $html = $this->from(route('personas.create'))->followingRedirects()->post(route('personas.store'), [
            'nombres' => 'Nuevo', 'apellidos' => 'Repetido', 'celular' => '3001112222', 'estado' => 'miembro_activo',
            'tipo_documento' => 'CC', 'numero_documento' => $documento,
        ])->getContent();

        $this->assertMatchesRegularExpression('/id="fm-numero_documento"[^>]*aria-invalid="true"/s', $html);
        $this->assertStringContainsString('value="Nuevo"', $html);
        $this->assertStringContainsString('value="'.$documento.'"', $html);
        $this->assertStringContainsString('href="#fm-numero_documento"', $html);
    }

    public function test_celula_y_evento_con_error_conservan_los_datos(): void
    {
        $html = $this->from(route('celulas.create'))->followingRedirects()->post(route('celulas.store'), [
            'nombre' => '', 'sede' => 'Sector Escrito', 'estado' => 'activa', 'lider_id' => $this->A['lider']->id,
        ])->getContent();
        $this->assertStringContainsString('value="Sector Escrito"', $html);
        $this->assertMatchesRegularExpression('/id="fm-nombre"[^>]*aria-invalid="true"/s', $html);
        $this->assertSame(0, Celula::withoutGlobalScopes()->where('sede', 'Sector Escrito')->count());

        $html = $this->from(route('eventos.create'))->followingRedirects()->post(route('eventos.store'), [
            'nombre' => 'Evento Escrito', 'tipo' => '', 'fecha' => now()->addDay()->toDateString(), 'costo' => '50000', 'estado' => 'planificado',
        ])->getContent();
        $this->assertStringContainsString('value="Evento Escrito"', $html);
        $this->assertMatchesRegularExpression('/id="fm-tipo"[^>]*aria-invalid="true"/s', $html);
        $this->assertSame(0, Evento::withoutGlobalScopes()->where('nombre', 'Evento Escrito')->count());
    }

    public function test_sede_con_correo_repetido_conserva_los_datos_y_marca_el_campo_anidado(): void
    {
        $sedes = Sede::count();

        $html = $this->from(route('sedes.create'))->followingRedirects()->post(route('sedes.store'), [
            'nombre' => 'Sede Escrita', 'ciudad' => 'Ciudad Escrita',
            'pastor' => ['nombres' => 'Pablo', 'apellidos' => 'Pérez', 'email' => $this->A['pastor']->email, 'password' => 'secreto123'],
        ])->getContent();

        $this->assertStringContainsString('value="Sede Escrita"', $html);
        $this->assertStringContainsString('value="Ciudad Escrita"', $html);
        $this->assertStringContainsString('value="Pablo"', $html);
        $this->assertMatchesRegularExpression('/id="fm-pastor-email"[^>]*aria-invalid="true"/s', $html);
        $this->assertStringContainsString('href="#fm-pastor-email"', $html);
        $this->assertSame($sedes, Sede::count());
    }

    public function test_los_campos_que_pasan_la_validacion_no_se_marcan_como_error(): void
    {
        $html = $this->from(route('personas.create'))->followingRedirects()->post(route('personas.store'), [
            'nombres' => '', 'apellidos' => 'Bien', 'celular' => '3105559999', 'estado' => 'visitante',
        ])->getContent();

        $this->assertDoesNotMatchRegularExpression('/id="fm-apellidos"[^>]*aria-invalid/s', $html);
    }
}
