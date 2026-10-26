<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Ninguna vía de la aplicación edita o borra el historial (spec 015, historia 5, RF-008). */
class InalterableRutasTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_no_hay_rutas_que_modifiquen_el_historial(): void
    {
        foreach (Route::getRoutes()->getRoutes() as $ruta) {
            $nombre = (string) $ruta->getName();
            if (str_starts_with($nombre, 'control-cambios') || str_contains($ruta->uri(), 'historico')) {
                $this->assertSame(['GET', 'HEAD'], $ruta->methods(), $nombre);
            }
        }
    }

    public function test_las_pantallas_del_historial_no_tienen_formularios_de_edicion_ni_borrado(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        foreach ([route('control-cambios.index'), route('personas.show', [$this->A['persona'], 'pestana' => 'historico']), route('eventos.show', [$this->A['evento'], 'pestana' => 'historico'])] as $url) {
            // Solo el contenido de la pestaña (los botones de la ficha, como «Eliminar miembro», quedan fuera).
            $html = $this->get($url)->assertOk()->getContent();
            $html = substr($html, (int) strpos($html, 'historial-pestanas'));
            $this->assertStringNotContainsString('name="_method" value="DELETE"', $html, $url);
            $this->assertStringNotContainsString('name="_method" value="PUT"', $html, $url);
        }
    }

    public function test_eliminar_un_evento_una_celula_y_un_usuario_conserva_sus_entradas(): void
    {
        $this->armarDosSedes();
        $admin = $this->comoAdminEnSede($this->A['sede']);
        $usuario = User::factory()->create(['role' => 'user', 'sede_id' => $this->A['sede']->id, 'name' => 'Cuenta Temporal']);

        $this->delete(route('eventos.destroy', $this->A['evento']))->assertRedirect();
        $this->delete(route('celulas.destroy', $this->A['celula']))->assertRedirect();
        $this->delete(route('users.destroy', $usuario))->assertRedirect();

        foreach ([['evento', $this->A['evento']->id, 'Evento Alfa'], ['celula', $this->A['celula']->id, 'Celula Alfa'], ['usuario', $usuario->id, 'Cuenta Temporal']] as [$tipo, $id, $nombre]) {
            $baja = CambioHistorial::query()->de($tipo, $id)->where('accion', 'eliminado')->sole();
            $this->assertSame($nombre, $baja->sujeto_nombre);
            $this->assertSame($admin->name, $baja->actor_nombre);
            $this->assertNotEmpty($baja->detalle['datos']);
        }

        $this->getJson(route('control-cambios.datos', ['registro' => 'Evento Alfa', 'length' => 100]))->assertOk()->assertJsonFragment(['recordsFiltered' => CambioHistorial::query()->where('sujeto_nombre', 'Evento Alfa')->orWhere('relacionado_nombre', 'Evento Alfa')->count()]);
    }
}
