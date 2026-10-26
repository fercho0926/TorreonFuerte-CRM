<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Sin cambio real no hay entrada: errores de validación, falta de permiso y ediciones iguales (spec 015, RF-005). */
class SinCambioSinEntradaTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function sinEntradasNuevas(callable $accion): void
    {
        $antes = CambioHistorial::count();
        $accion();
        $this->assertSame($antes, CambioHistorial::count());
    }

    public function test_un_error_de_validacion_no_escribe(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->sinEntradasNuevas(function () {
            $this->put(route('personas.update', $this->A['persona']), ['nombres' => ''])->assertSessionHasErrors('nombres');
            $this->post(route('celulas.store'), ['nombre' => ''])->assertSessionHasErrors();
            $this->post(route('eventos.store'), ['nombre' => 'X'])->assertSessionHasErrors();
            $this->post(route('users.store'), ['name' => 'Sin correo'])->assertSessionHasErrors();
            $this->post(route('roles.store'), ['nombre' => ''])->assertSessionHasErrors();
        });
    }

    public function test_sin_permiso_no_escribe(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);

        $this->sinEntradasNuevas(function () {
            $this->delete(route('personas.destroy', $this->A['persona']))->assertForbidden();
            $this->post(route('roles.store'), ['nombre' => 'X', 'alcance' => 'sede'])->assertForbidden();
            $this->put(route('personas.update', $this->B['persona']), ['nombres' => 'Hack'])->assertNotFound();
        });
    }

    public function test_guardar_sin_cambiar_nada_no_escribe(): void
    {
        $this->comoAdminEnSede($this->A['sede']);
        $p = $this->A['persona'];
        $celula = $this->A['celula'];

        $this->sinEntradasNuevas(function () use ($p, $celula) {
            $this->put(route('personas.update', $p), [
                'nombres' => $p->nombres, 'apellidos' => $p->apellidos, 'estado' => $p->estado, 'celular' => $p->celular,
                'email' => $p->email, 'direccion' => $p->direccion, 'barrio' => $p->barrio, 'ciudad' => $p->ciudad,
                'fecha_nacimiento' => $p->fecha_nacimiento?->toDateString(), 'lider_id' => $p->lider_id,
            ])->assertSessionHasNoErrors();
            // `sede` de la célula es texto (lugar o sector), no la relación con la sede.
            $columnas = $celula->fresh()->getAttributes();
            $this->put(route('celulas.update', $celula), array_filter([
                'nombre' => $columnas['nombre'], 'lider_id' => $columnas['lider_id'], 'co_lider_id' => $columnas['co_lider_id'], 'estado' => $columnas['estado'],
                'sede' => $columnas['sede'], 'dia_reunion' => $columnas['dia_reunion'], 'hora_reunion' => $columnas['hora_reunion'] ? substr($columnas['hora_reunion'], 0, 5) : null,
                'lugar_reunion' => $columnas['lugar_reunion'],
            ], fn ($v) => $v !== null))->assertSessionHasNoErrors();
        });
    }
}
