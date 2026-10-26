<?php

namespace Tests\Feature\Roles;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\SolicitudClave;
use App\Models\User;
use App\Support\Modulos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Matriz módulo × nivel (spec 013): para cada módulo, un rol con ese nivel (y solo el Tablero en lectura para
 * poder ver el menú) debe poder o no poder ver, entrar por dirección directa y cambiar datos.
 */
class MatrizModulosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    /** @var array<string, int|string> */
    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();

        // Cuenta de destino con un rol sin permisos: queda dentro del techo de cualquier actor.
        $sinPermisos = Rol::factory()->asignable()->conAlcance('registro')->conPermisos([])->create(['nombre' => 'Sin permisos']);
        $destino = User::factory()->create(['role' => $sinPermisos->clave, 'sede_id' => $this->A['sede']->id]);
        $solicitud = SolicitudClave::create(['user_id' => $destino->id, 'sede_id' => $this->A['sede']->id, 'ultima_solicitud_en' => now()]);

        $this->ids = [
            'persona' => $this->A['persona']->id, 'lider' => $this->A['lider']->id, 'celula' => $this->A['celula']->id,
            'evento' => $this->A['evento']->id, 'ministerio' => $this->A['ministerio']->id, 'encuentro' => $this->A['encuentro']->id,
            'sede' => $this->A['sede']->id, 'solicitud' => $solicitud->id, 'destino' => $destino->id,
            'nivel' => \App\Models\NivelFormacion::query()->orderBy('posicion')->value('id'),
        ];
    }

    /**
     * Por módulo: rutas de lectura (GET que deben responder 200 con lectura), rutas de cambio (método, nombre de
     * ruta, parámetros, datos) que con lectura responden 403 y con escritura no, la opción del menú y un botón que
     * solo existe con escritura (página, texto o ruta que debe aparecer).
     *
     * @return array<string, array{0: string, 1: array<string, mixed>}>
     */
    public static function modulos(): array
    {
        return [
            'tablero' => ['tablero', ['lectura' => [['dashboard']], 'cambios' => [], 'menu' => 'dashboard']],
            'miembros' => ['miembros', [
                'lectura' => [['personas.index'], ['personas.datos'], ['personas.show', ['persona' => 'persona']]],
                'cambios' => [['POST', 'personas.inactivar', ['persona' => 'persona']], ['POST', 'personas.store'], ['PUT', 'personas.update', ['persona' => 'persona']]],
                'menu' => 'personas.index',
                'boton' => ['personas.show', ['persona' => 'persona'], 'personas.edit', ['persona' => 'persona']],
            ]],
            'lideres' => ['lideres', [
                'lectura' => [['lideres.index'], ['lideres.datos'], ['lideres.show', ['lider' => 'lider']]],
                'cambios' => [['POST', 'lideres.designar.store'], ['DELETE', 'lideres.destroy', ['lider' => 'lider']]],
                'menu' => 'lideres.index',
                'boton' => ['lideres.index', [], 'lideres.designar', []],
            ]],
            'sin_lider' => ['sin_lider', [
                'lectura' => [['sin-lider.index'], ['sin-lider.datos']],
                'cambios' => [['POST', 'sin-lider.asignar', ['persona' => 'persona']]],
                'menu' => 'sin-lider.index',
            ]],
            'celulas' => ['celulas', [
                'lectura' => [['celulas.index'], ['celulas.datos'], ['celulas.show', ['celula' => 'celula']]],
                'cambios' => [['POST', 'celulas.store'], ['POST', 'celulas.miembros.store', ['celula' => 'celula']], ['POST', 'celulas.reuniones.store', ['celula' => 'celula']]],
                'menu' => 'celulas.index',
                'boton' => ['celulas.show', ['celula' => 'celula'], 'celulas.miembros.store', ['celula' => 'celula']],
            ]],
            'ministerios' => ['ministerios', [
                'lectura' => [['ministerios.index'], ['ministerios.datos'], ['ministerios.show', ['ministerio' => 'ministerio']]],
                'cambios' => [['POST', 'ministerios.store'], ['PUT', 'ministerios.integrantes', ['ministerio' => 'ministerio']], ['DELETE', 'ministerios.destroy', ['ministerio' => 'ministerio']]],
                'menu' => 'ministerios.index',
                'boton' => ['ministerios.show', ['ministerio' => 'ministerio'], 'ministerios.edit', ['ministerio' => 'ministerio']],
            ]],
            'eventos' => ['eventos', [
                'lectura' => [['eventos.index'], ['eventos.datos'], ['eventos.show', ['evento' => 'evento']]],
                'cambios' => [['POST', 'eventos.store'], ['POST', 'eventos.personas.store', ['evento' => 'evento']], ['DELETE', 'eventos.destroy', ['evento' => 'evento']]],
                'menu' => 'eventos.index',
                'boton' => ['eventos.show', ['evento' => 'evento'], 'eventos.edit', ['evento' => 'evento']],
            ]],
            'encuentros' => ['encuentros', [
                'lectura' => [['encuentros.index'], ['encuentros.datos'], ['encuentros.show', ['encuentro' => 'encuentro']]],
                'cambios' => [['POST', 'encuentros.store'], ['PUT', 'encuentros.asistencia', ['encuentro' => 'encuentro']], ['DELETE', 'encuentros.destroy', ['encuentro' => 'encuentro']]],
                'menu' => 'encuentros.index',
                'boton' => ['encuentros.show', ['encuentro' => 'encuentro'], 'encuentros.edit', ['encuentro' => 'encuentro']],
            ]],
            'niveles' => ['niveles', [
                'lectura' => [['niveles.index']],
                'cambios' => [['POST', 'niveles.store'], ['POST', 'niveles.subir', ['nivel' => 'nivel']], ['DELETE', 'niveles.destroy', ['nivel' => 'nivel']]],
                'menu' => 'niveles.index',
                'boton' => ['niveles.index', [], 'niveles.create', []],
            ]],
            'registro_miembros' => ['registro_miembros', [
                'lectura' => [['registro-miembros.index']],
                'cambios' => [['POST', 'registro-miembros.abrir', ['sede' => 'sede']], ['POST', 'registro-miembros.regenerar', ['sede' => 'sede']]],
                'menu' => 'registro-miembros.index',
            ]],
            'usuarios' => ['usuarios', [
                'lectura' => [['users.index'], ['users.datos']],
                'cambios' => [['POST', 'users.store'], ['PUT', 'users.update', ['user' => 'destino']], ['POST', 'users.clave.store', ['user' => 'destino']]],
                'menu' => 'users.index',
                'boton' => ['users.index', [], 'users.create', []],
            ]],
            'solicitudes_clave' => ['solicitudes_clave', [
                'lectura' => [['solicitudes-clave.index'], ['solicitudes-clave.datos']],
                'cambios' => [['POST', 'solicitudes-clave.descartar', ['solicitud' => 'solicitud']]],
                'menu' => 'solicitudes-clave.index',
            ]],
            // Solo lectura (spec 015): el historial no se modifica desde ninguna pantalla.
            'control_cambios' => ['control_cambios', [
                'lectura' => [['control-cambios.index'], ['control-cambios.datos']],
                'cambios' => [],
                'menu' => 'control-cambios.index',
            ]],
        ];
    }

    private function url(string $ruta, array $parametros = []): string
    {
        return route($ruta, array_map(fn ($clave) => $this->ids[$clave], $parametros));
    }

    private function entrarCon(string $modulo, int $nivel): User
    {
        $niveles = ['tablero' => 1] + [$modulo => $nivel];
        $rol = Rol::factory()->conAlcance('sede')->conPermisos($niveles)->create();
        $usuario = User::factory()->create([
            'role' => $rol->clave, 'sede_id' => $this->A['sede']->id,
            'persona_id' => Persona::factory()->deSede($this->A['sede']->id)->create()->id,
        ]);

        return $this->iniciarSesionComo($usuario);
    }

    /** @param array<string, mixed> $config */
    #[\PHPUnit\Framework\Attributes\DataProvider('modulos')]
    public function test_sin_acceso_nada_del_modulo_se_ve_ni_se_abre(string $modulo, array $config): void
    {
        $this->entrarCon($modulo, 0);

        if ($modulo !== 'tablero') {
            $this->get(route('dashboard'))->assertOk()->assertDontSee(route($config['menu']), false);
        }

        foreach ($config['lectura'] as $ruta) {
            if ($modulo === 'tablero') {
                continue;
            }
            $this->get($this->url($ruta[0], $ruta[1] ?? []))->assertForbidden();
        }
        foreach ($config['cambios'] as $cambio) {
            [$metodo, $ruta] = $cambio;
            $parametros = $cambio[2] ?? [];
            $this->call($metodo, $this->url($ruta, $parametros))->assertForbidden();
        }
    }

    /** @param array<string, mixed> $config */
    #[\PHPUnit\Framework\Attributes\DataProvider('modulos')]
    public function test_con_lectura_se_ve_pero_no_se_cambia_nada(string $modulo, array $config): void
    {
        $this->entrarCon($modulo, 1);

        $this->get(route('dashboard'))->assertOk()->assertSee(route($config['menu']), false);

        foreach ($config['lectura'] as $ruta) {
            $this->get($this->url($ruta[0], $ruta[1] ?? []))->assertOk();
        }
        foreach ($config['cambios'] as $cambio) {
            [$metodo, $ruta] = $cambio;
            $parametros = $cambio[2] ?? [];
            $this->call($metodo, $this->url($ruta, $parametros))->assertForbidden();
            $this->assertNotNull(Persona::find($this->ids['persona']));
        }

        if (isset($config['boton'])) {
            [$pagina, $paramPagina, $botonRuta, $paramBoton] = $config['boton'];
            $this->get($this->url($pagina, $paramPagina))->assertOk()->assertDontSee($this->url($botonRuta, $paramBoton), false);
        }
    }

    /** @param array<string, mixed> $config */
    #[\PHPUnit\Framework\Attributes\DataProvider('modulos')]
    public function test_con_escritura_se_puede_cambiar_y_aparecen_los_botones(string $modulo, array $config): void
    {
        if ($modulo === 'tablero') {
            $this->markTestSkipped('El Tablero solo se consulta.');
        }

        $this->entrarCon($modulo, 2);

        foreach ($config['lectura'] as $ruta) {
            $this->get($this->url($ruta[0], $ruta[1] ?? []))->assertOk();
        }

        if (isset($config['boton'])) {
            [$pagina, $paramPagina, $botonRuta, $paramBoton] = $config['boton'];
            $this->get($this->url($pagina, $paramPagina))->assertOk()->assertSee($this->url($botonRuta, $paramBoton), false);
        }
        foreach ($config['cambios'] as $cambio) {
            [$metodo, $ruta] = $cambio;
            $parametros = $cambio[2] ?? [];
            $respuesta = $this->call($metodo, $this->url($ruta, $parametros));
            $this->assertNotSame(403, $respuesta->getStatusCode(), "{$metodo} {$ruta} respondió 403 con escritura");
        }
    }

    public function test_el_catalogo_y_la_matriz_cubren_los_mismos_modulos(): void
    {
        $this->assertEqualsCanonicalizing(
            array_values(array_diff(Modulos::claves(), [Modulos::ORGANIGRAMA])),
            array_keys(self::modulos()),
        );
    }
}
