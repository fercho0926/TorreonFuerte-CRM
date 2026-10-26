<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\NivelFormacion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Los historiales que existían (miembros, sedes, roles y niveles) escriben ahora en el historial único, una entrada
 * por acción (spec 015, D2).
 */
class EscritoresReemplazadosTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_inactivar_y_reactivar_un_miembro_deja_una_entrada_cada_uno(): void
    {
        $pastor = $this->comoPastor();
        $persona = Persona::factory()->create(['estado' => 'miembro_activo']);

        $this->post(route('personas.inactivar', $persona))->assertRedirect();
        $this->post(route('personas.reactivar', $persona))->assertRedirect();

        $entradas = CambioHistorial::query()->de('persona', $persona->id)->orderBy('id')->get();
        $this->assertSame(['inactivada', 'reactivada'], $entradas->pluck('accion')->all());
        $this->assertTrue($entradas->every(fn ($e) => $e->user_id === $pastor->id && $e->categoria === 'datos'));
        $this->assertSame('Miembro activo', $entradas[0]->cambios[0]['antes']);
    }

    public function test_editar_un_rol_deja_lineas_por_modulo(): void
    {
        $this->comoAdmin();
        $this->post(route('roles.store'), ['nombre' => 'Apoyo', 'alcance' => 'sede', 'permisos' => ['eventos' => 1]]);
        $this->put(route('roles.update', 'apoyo'), ['nombre' => 'Apoyo', 'alcance' => 'sede', 'permisos' => ['eventos' => 2]]);

        $edicion = CambioHistorial::where('sujeto_tipo', 'rol')->where('accion', 'editado')->sole();
        $this->assertContains('Eventos: Lectura → Escritura', $edicion->detalle['lineas']);
        $this->assertSame('apoyo', $edicion->detalle['clave']);
    }

    public function test_reordenar_un_nivel_deja_su_entrada_global(): void
    {
        $this->comoAdmin();
        $nivel = NivelFormacion::query()->ordenados()->skip(1)->firstOrFail();

        $this->post(route('niveles.subir', $nivel));

        $entrada = CambioHistorial::where('sujeto_tipo', 'nivel')->sole();
        $this->assertSame('reordenado', $entrada->accion);
        $this->assertNull($entrada->sede_id);
    }
}
