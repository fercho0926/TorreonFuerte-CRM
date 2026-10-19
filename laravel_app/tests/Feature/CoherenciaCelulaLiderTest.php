<?php

namespace Tests\Feature;

use App\Models\Celula;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class CoherenciaCelulaLiderTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    public function test_se_puede_agregar_a_la_celula_a_alguien_de_otro_lider_sin_cambiar_su_lider_directo(): void
    {
        $this->comoAdmin();
        $lider = Persona::factory()->lider()->create();
        $celula = Celula::factory()->create(['lider_id' => $lider->id]);
        $propia = Persona::factory()->create(['lider_id' => $lider->id]);
        $otroLider = Persona::factory()->lider()->create();
        $ajena = Persona::factory()->create(['lider_id' => $otroLider->id]);

        $this->post(route('celulas.miembros.store', $celula), ['persona_id' => $propia->id])->assertSessionHas('success');
        $this->post(route('celulas.miembros.store', $celula), ['persona_id' => $ajena->id])->assertSessionHas('success');

        $this->assertTrue($celula->miembros()->whereKey($propia->id)->exists());
        $this->assertTrue($celula->miembros()->whereKey($ajena->id)->exists());
        $this->assertSame($otroLider->id, $ajena->fresh()->lider_id, 'la célula no cambia el líder directo');
    }

    public function test_al_cambiar_el_lider_la_persona_sale_de_las_celulas_del_lider_anterior(): void
    {
        $this->comoAdmin();
        $anterior = Persona::factory()->lider()->create();
        $nuevo = Persona::factory()->lider()->create();
        $celula = Celula::factory()->create(['lider_id' => $anterior->id]);
        $persona = Persona::factory()->create(['lider_id' => $anterior->id]);
        $celula->miembros()->attach($persona->id);

        $this->put(route('personas.update', $persona), [
            'nombres' => $persona->nombres,
            'apellidos' => $persona->apellidos,
            'estado' => 'miembro_activo',
            'tipo_documento' => 'CC',
            'numero_documento' => $persona->numero_documento,
            'celular' => $persona->celular,
            'lider_id' => $nuevo->id,
        ])->assertRedirect(route('personas.show', $persona));

        $this->assertSame($nuevo->id, $persona->fresh()->lider_id);
        $this->assertFalse($celula->miembros()->whereKey($persona->id)->exists());
    }

    public function test_no_se_puede_formar_un_ciclo_de_liderazgo(): void
    {
        $this->comoAdmin();
        $a = Persona::factory()->lider()->create();
        $b = Persona::factory()->lider()->create(['lider_id' => $a->id]);

        $this->put(route('personas.update', $a), [
            'nombres' => $a->nombres,
            'apellidos' => $a->apellidos,
            'estado' => 'miembro_activo',
            'tipo_documento' => 'CC',
            'numero_documento' => $a->numero_documento,
            'celular' => $a->celular,
            'es_lider' => 1,
            'lider_id' => $b->id,
        ])->assertSessionHasErrors('lider_id');

        $this->assertNull($a->fresh()->lider_id);
    }
}
