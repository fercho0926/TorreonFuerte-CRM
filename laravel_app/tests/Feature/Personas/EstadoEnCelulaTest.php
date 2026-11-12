<?php

namespace Tests\Feature\Personas;

use App\Models\Persona;
use App\Support\Estados;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Estado «En célula» (FR-006): miembros que asisten a una célula y aún no son parte de la iglesia. */
class EstadoEnCelulaTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_se_crea_con_nombres_apellidos_celular_y_documento(): void
    {
        $this->comoPastor();

        $this->post(route('personas.store'), [
            'nombres' => 'Rosa', 'apellidos' => 'Gómez', 'celular' => '3105559999', 'estado' => 'celula',
            'tipo_documento' => 'CC', 'numero_documento' => '1122334455',
        ])->assertSessionHasNoErrors();

        $persona = Persona::where('nombres', 'Rosa')->firstOrFail();
        $this->assertSame('celula', $persona->estado);
        $this->assertSame('En célula', $persona->estado_etiqueta);
        $this->assertSame('1122334455', $persona->numero_documento);
    }

    public function test_cuenta_como_activa_y_no_aparece_en_sin_lider(): void
    {
        $this->comoPastor();
        $enCelula = Persona::factory()->enCelula()->create();
        $visitante = Persona::factory()->visitante()->create();
        $inactiva = Persona::factory()->inactiva()->create();

        $this->assertTrue(Persona::activas()->whereKey($enCelula->id)->exists());
        $this->assertFalse(Persona::activas()->whereKey($inactiva->id)->exists());

        $sinLider = Persona::sinLider()->pluck('id');
        $this->assertTrue($sinLider->contains($visitante->id));
        $this->assertFalse($sinLider->contains($enCelula->id), 'un miembro «En célula» no se asigna a un líder por defecto');
        $this->get(route('sin-lider.index'))->assertOk()->assertDontSee($enCelula->nombre_completo);
    }

    public function test_tiene_color_y_etiqueta_propios(): void
    {
        $this->assertSame('dark', Estados::color('celula'));
        $this->assertSame('En célula', Estados::etiqueta('celula'));
    }

    public function test_el_formulario_ofrece_el_estado(): void
    {
        $this->comoPastor();

        $this->get(route('personas.create'))->assertOk()->assertSee('En célula');
    }
}
