<?php

namespace Tests\Unit\Grillas;

use App\Grillas\Columna;
use App\Grillas\Grilla;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

/** Reglas de la base de las grillas sobre una fuente en memoria (spec 007, RF-002, RF-003, RF-004). */
class GrillaTest extends TestCase
{
    private function grilla(): Grilla
    {
        return new class extends Grilla
        {
            public function id(): string
            {
                return 'demo';
            }

            public function extras(): array
            {
                return ['activos'];
            }

            protected function definirColumnas(): array
            {
                return [
                    Columna::make('nombre', 'Nombre')->ordena()->filtraTexto()->valor(fn (array $f) => $f['nombre']),
                    Columna::make('edad', 'Edad')->ordena()->valor(fn (array $f) => $f['edad']),
                    Columna::make('estado', 'Estado')->filtraLista(['a' => 'A', 'b' => 'B'])->valor(fn (array $f) => $f['estado']),
                    Columna::make('correo', 'Correo')->valor(fn (array $f) => $f['correo'])->soloExportar(),
                    Columna::acciones(),
                ];
            }

            protected function base(?User $usuario, array $estado): Builder|Collection
            {
                return collect([
                    ['nombre' => 'Zoe', 'edad' => 30, 'estado' => 'a', 'correo' => 'z@x.co'],
                    ['nombre' => 'Ana', 'edad' => 25, 'estado' => 'b', 'correo' => '=ana@x.co'],
                    ['nombre' => 'ana maría', 'edad' => 41, 'estado' => 'a', 'correo' => 'm@x.co'],
                ]);
            }
        };
    }

    public function test_ordena_por_la_columna_pedida_y_ignora_las_que_no_estan_en_la_lista_blanca(): void
    {
        $g = $this->grilla();

        $this->assertSame(['Ana', 'ana maría', 'Zoe'], $g->consulta(null, ['orden' => 'nombre', 'dir' => 'asc'])->pluck('nombre')->all());
        $this->assertSame(['Zoe', 'ana maría', 'Ana'], $g->consulta(null, ['orden' => 'nombre', 'dir' => 'desc'])->pluck('nombre')->all());
        $this->assertSame([25, 30, 41], $g->consulta(null, ['orden' => 'edad', 'dir' => 'asc'])->pluck('edad')->all(), 'orden numérico natural');
        // «estado» no es ordenable y «inventada» no existe: cae a la primera columna ordenable, ascendente.
        $this->assertSame(['Ana', 'ana maría', 'Zoe'], $g->consulta(null, ['orden' => 'estado', 'dir' => 'desc'])->pluck('nombre')->all());
        $this->assertSame(['Ana', 'ana maría', 'Zoe'], $g->consulta(null, ['orden' => 'inventada'])->pluck('nombre')->all());
    }

    public function test_filtra_por_texto_sin_distinguir_mayusculas_y_por_lista_con_igualdad(): void
    {
        $g = $this->grilla();

        $this->assertCount(2, $g->consulta(null, ['nombre' => 'ANA']));
        $this->assertCount(2, $g->consulta(null, ['estado' => 'a']));
        $this->assertCount(1, $g->consulta(null, ['nombre' => 'ana', 'estado' => 'b']));
        $this->assertCount(3, $g->consulta(null, ['nombre' => '', 'estado' => '']), 'filtros vacíos no filtran');
    }

    public function test_solo_se_aceptan_las_filas_por_pagina_permitidas(): void
    {
        $g = $this->grilla();

        foreach ([10, 25, 50, 100] as $valido) {
            $this->assertSame($valido, $g->porPagina($valido));
        }
        foreach ([0, 7, 1000, -5, 'x', null] as $invalido) {
            $this->assertSame(25, $g->porPagina($invalido));
        }
    }

    public function test_pagina_una_coleccion_y_cuenta_el_total_filtrado(): void
    {
        $g = $this->grilla();

        $pagina = $g->paginar(null, ['orden' => 'nombre', 'dir' => 'asc'], 2, 2);

        $this->assertSame(3, $pagina->total());
        $this->assertCount(1, $pagina->items());
        $this->assertSame(3, $g->total(null, []));
    }

    public function test_el_estado_toma_solo_lo_declarado_de_la_peticion(): void
    {
        $g = $this->grilla();

        $estado = $g->estado(Request::create('/', 'GET', ['q' => ' hola ', 'nombre' => 'ana', 'dir' => 'DESC', 'activos' => '1', 'intruso' => 'x', 'orden' => 'edad']));

        $this->assertSame('hola', $estado['q']);
        $this->assertSame('ana', $estado['nombre']);
        $this->assertSame('desc', $estado['dir']);
        $this->assertSame('edad', $estado['orden']);
        $this->assertSame('1', $estado['activos']);
        $this->assertArrayNotHasKey('intruso', $estado);
        $this->assertArrayNotHasKey('edad', $estado, 'edad no tiene filtro');
    }

    public function test_las_columnas_de_la_tabla_excluyen_las_solo_exportacion_y_el_csv_las_incluye(): void
    {
        $g = $this->grilla();

        $this->assertSame(['nombre', 'edad', 'estado', 'acciones'], array_map(fn (Columna $c) => $c->clave, $g->columnas(null)));
        $this->assertSame(['nombre', 'edad', 'estado', 'correo'], array_map(fn (Columna $c) => $c->clave, $g->columnasExportables(null)), 'las acciones no se exportan');
    }

    public function test_neutraliza_las_formulas_del_csv(): void
    {
        foreach (['=1+1', '+54', '-2', '@SUMA'] as $peligroso) {
            $this->assertSame("'".$peligroso, Grilla::neutralizarFormula($peligroso));
        }
        $this->assertSame('Ana', Grilla::neutralizarFormula('Ana'));
        $this->assertSame('5', Grilla::neutralizarFormula(5));
        $this->assertNull(Grilla::neutralizarFormula(null));
    }
}
