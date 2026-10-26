<?php

namespace Tests\Feature\Historial;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\RolPermiso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Quién abre «Control de cambios» y qué ve (spec 015, clarificación 2, RF-022, RF-023). */
class PermisosControlCambiosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    private function textoDeDatos(): string
    {
        return json_encode($this->getJson(route('control-cambios.datos', ['length' => 100]))->assertOk()->json('data'), JSON_UNESCAPED_UNICODE);
    }

    public function test_por_defecto_solo_el_administrador_lo_abre(): void
    {
        foreach (['pastor', 'liderUser'] as $quien) {
            $this->iniciarSesionComo($this->A[$quien]);
            $this->get(route('control-cambios.index'))->assertForbidden();
            $this->get(route('dashboard'))->assertDontSee(route('control-cambios.index'));
        }

        $this->comoAdminEnSede($this->A['sede']);
        $this->get(route('control-cambios.index'))->assertOk();
        $this->get(route('dashboard'))->assertSee(route('control-cambios.index'));
    }

    public function test_si_se_concede_al_pastor_solo_ve_su_sede(): void
    {
        RolPermiso::query()->where('rol_id', Rol::where('clave', 'pastor')->value('id'))->where('modulo', 'control_cambios')->update(['nivel' => 1]);
        app(\App\Services\AutorizacionRoles::class)->olvidar();
        $this->iniciarSesionComo($this->A['pastor']);

        $texto = $this->textoDeDatos();

        $this->assertStringContainsString('Evento Alfa', $texto);
        $this->assertStringNotContainsString('Beta', $texto);
    }

    public function test_un_rol_de_rama_con_el_modulo_solo_ve_miembros_y_celulas_de_su_alcance(): void
    {
        $rol = Rol::factory()->conAlcance('rama')->conPermisos(['tablero' => 1, 'control_cambios' => 1])->create();
        $usuario = $this->crearUsuario($rol->clave, $this->A['lider'], $this->A['sede']->id);
        $ajeno = Persona::factory()->deSede($this->A['sede']->id)->create(['nombres' => 'AjenoAlfa']);
        app(\App\Services\Historial::class)->registrar($ajeno, 'estado', ['estado' => 'visitante'], ['estado' => 'celula']);
        $this->iniciarSesionComo($usuario);

        $texto = $this->textoDeDatos();

        $this->assertStringContainsString('PersonaAlfa', $texto, 'su miembro');
        $this->assertStringNotContainsString('AjenoAlfa', $texto, 'fuera de su rama');
        $this->assertStringNotContainsString('Evento Alfa</', $texto);
    }

    public function test_el_administrador_en_una_sede_solo_ve_esa_sede(): void
    {
        $this->comoAdminEnSede($this->A['sede']);

        $this->assertStringNotContainsString('PersonaBeta', $this->textoDeDatos());
    }
}
