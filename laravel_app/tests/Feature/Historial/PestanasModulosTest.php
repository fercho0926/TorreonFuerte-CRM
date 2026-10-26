<?php

namespace Tests\Feature\Historial;

use App\Models\NivelFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Pestaña «Histórico» en las fichas de cada módulo (spec 015, historia 3, RF-011 a RF-013). */
class PestanasModulosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    /** @return array<string, array{0: string}> */
    public static function fichas(): array
    {
        return array_combine($f = ['celula', 'ministerio', 'evento', 'encuentro', 'nivel', 'sede', 'usuario', 'rol'], array_map(fn ($x) => [$x], $f));
    }

    private function url(string $ficha, array $extra = []): string
    {
        return match ($ficha) {
            'celula' => route('celulas.show', [$this->A['celula']] + $extra),
            'ministerio' => route('ministerios.show', [$this->A['ministerio']] + $extra),
            'evento' => route('eventos.show', [$this->A['evento']] + $extra),
            'encuentro' => route('encuentros.show', [$this->A['encuentro']] + $extra),
            'nivel' => route('niveles.edit', [NivelFormacion::query()->ordenados()->firstOrFail()] + $extra),
            'sede' => route('sedes.show', [$this->A['sede']] + $extra),
            'usuario' => route('users.edit', [$this->A['liderUser']] + $extra),
            'rol' => route('roles.show', ['pastor'] + $extra),
        };
    }

    #[DataProvider('fichas')]
    public function test_la_pestana_de_datos_es_la_inicial_y_la_de_historico_se_abre_con_un_enlace(string $ficha): void
    {
        $datos = $this->get($this->url($ficha))->assertOk()->getContent();
        $this->assertStringContainsString('pestana=historico', $datos);
        $this->assertStringNotContainsString('Filtrar el histórico', $datos);

        $this->get($this->url($ficha, ['pestana' => 'historico']))->assertOk()->assertSee('Filtrar el histórico')->assertSee('Histórico');
    }

    #[DataProvider('fichas')]
    public function test_la_pestana_respeta_la_autorizacion_de_la_ficha(string $ficha): void
    {
        if ($ficha === 'nivel') {
            $this->markTestSkipped('El plan de niveles es común a todas las sedes (spec 014): cualquier pastor lo ve.');
        }

        $this->iniciarSesionComo($this->B['pastor']);

        $estado = $this->get($this->url($ficha, ['pestana' => 'historico']))->getStatusCode();

        $this->assertContains($estado, [302, 403, 404], "{$ficha}: un pastor de otra sede no debe verla");
    }

    public function test_el_filtro_por_accion_y_la_paginacion_funcionan(): void
    {
        $celula = $this->A['celula'];
        foreach (range(1, 22) as $i) {
            $celula->update(['nombre' => "Célula {$i}"]);
        }

        $html = $this->get($this->url('celula', ['pestana' => 'historico', 'accion' => 'editado']))->getContent();
        $this->assertSame(20, substr_count($html, 'class="historial-entrada"'));
        $this->assertStringContainsString('pagina=2', $html);

        $html = $this->get($this->url('celula', ['pestana' => 'historico', 'accion' => 'creado']))->getContent();
        $this->assertSame(1, substr_count($html, 'class="historial-entrada"'));
    }
}
