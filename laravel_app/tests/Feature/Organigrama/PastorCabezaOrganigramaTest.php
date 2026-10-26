<?php

namespace Tests\Feature\Organigrama;

use App\Models\Persona;
use App\Services\OrganigramaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Con todos los líderes bajo el pastor, el organigrama tiene una sola cabeza (spec 016, research D12). */
class PastorCabezaOrganigramaTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_los_lideres_bajo_el_pastor_son_el_primer_nivel_y_el_pastor_va_en_la_cabecera(): void
    {
        $sede = $this->crearSede(['nombre' => 'Sede Norte']);
        $pastor = Persona::factory()->pastorDe($sede)->create(['nombres' => 'Juan', 'apellidos' => 'Pastor']);
        $ana = Persona::factory()->deSede($sede->id)->lider()->create(['nombres' => 'Ana', 'apellidos' => 'Beltrán', 'lider_id' => $pastor->id]);
        Persona::factory()->deSede($sede->id)->create(['nombres' => 'Eva', 'apellidos' => 'Ávila', 'lider_id' => $ana->id]);
        Persona::factory()->deSede($sede->id)->create(['nombres' => 'Teo', 'apellidos' => 'Torres', 'lider_id' => $pastor->id]);

        $usuario = $this->comoAdminDeOtraSedeEn($sede);
        $raiz = app(OrganigramaService::class)->construir($sede, $usuario)['raiz'];

        $this->assertSame(['Juan Pastor'], array_column($raiz['pastores'], 'nombre'));
        $titulos = array_column($raiz['hijos'], 'titulo');
        $this->assertSame(['Ana Beltrán', 'A cargo de los pastores'], $titulos, 'el pastor no aparece como un líder más');
        $this->assertSame(['Teo Torres'], array_column($raiz['hijos'][1]['hijos'], 'titulo'));
        $this->assertNotContains('Sin líder asignado', $titulos);
    }
}
