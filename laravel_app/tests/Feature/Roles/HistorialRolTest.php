<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\CambioHistorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class HistorialRolTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_el_detalle_lista_los_usuarios_del_rol_con_su_sede(): void
    {
        $this->comoAdmin();
        $sede = $this->crearSede(['nombre' => 'Sede Norte']);
        $rol = Rol::factory()->create(['nombre' => 'Apoyo', 'clave' => 'apoyo']);
        User::factory()->create(['role' => 'apoyo', 'sede_id' => $sede->id, 'persona_id' => Persona::factory()->deSede($sede->id)->create(['nombres' => 'Ana', 'apellidos' => 'Apoyo'])->id]);

        $this->get(route('roles.show', $rol))->assertOk()->assertSee('Usuarios con este rol')->assertSee('Ana Apoyo')->assertSee('Sede Norte');
        $this->get(route('roles.show', 'pastor'))->assertOk()->assertSee('Usuarios con este rol');
    }

    public function test_el_historial_muestra_autor_fecha_y_niveles_anterior_y_nuevo(): void
    {
        $admin = $this->comoAdmin();

        $this->post(route('roles.store'), ['nombre' => 'Apoyo', 'alcance' => 'sede', 'permisos' => ['eventos' => 1]])->assertRedirect();
        $this->put(route('roles.update', 'apoyo'), ['nombre' => 'Apoyo', 'alcance' => 'rama', 'permisos' => ['eventos' => 2, 'miembros' => 1]])->assertRedirect();

        $html = $this->get(route('roles.show', ['apoyo', 'pestana' => 'historico']))->assertOk()->assertSee('Histórico')->getContent();
        // Solo la lista de entradas (el filtro de acciones también nombra «Creado» y «Editado»).
        $html = substr($html, strpos($html, 'historial-lista'));

        $this->assertStringContainsString('Eventos: Lectura → Escritura', $html);
        $this->assertStringContainsString('Miembros: Sin acceso → Lectura', $html);
        $this->assertStringContainsString('Alcance: Toda la sede → Solo su rama', $html);
        $this->assertStringContainsString($admin->name, $html);
        $this->assertStringContainsString(now()->format('Y-m-d'), $html);
        $this->assertStringContainsString('Creado', $html);
        $this->assertStringContainsString('Editado', $html);

        // Más reciente primero.
        $this->assertLessThan(strpos($html, 'Creado'), strpos($html, 'Editado'));
    }

    public function test_un_rol_eliminado_conserva_su_historial_con_su_nombre(): void
    {
        $this->comoAdmin();
        $this->post(route('roles.store'), ['nombre' => 'Temporal', 'alcance' => 'sede', 'permisos' => []])->assertRedirect();
        $this->delete(route('roles.destroy', 'temporal'))->assertRedirect();

        $historial = CambioHistorial::where('sujeto_tipo', 'rol')->where('detalle->clave', 'temporal')->orderBy('id')->get();

        $this->assertSame(['creado', 'eliminado'], $historial->pluck('accion')->all());
        $this->assertSame(['Temporal', 'Temporal'], $historial->pluck('sujeto_nombre')->all());
        $this->assertNull(\App\Models\Rol::find($historial->last()->sujeto_id), 'el historial sobrevive al rol');
    }

    public function test_solo_el_administrador_ve_usuarios_e_historial(): void
    {
        $this->comoPastor();

        $this->get(route('roles.show', 'pastor'))->assertForbidden();
    }
}
