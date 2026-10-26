<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Persona;
use App\Services\Historial;
use Database\Factories\SedeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Servicio de escritura del historial (spec 015, contracts/servicio-historial.md). */
class ServicioHistorialTest extends TestCase
{
    use IniciaSesion, RefreshDatabase;

    private function historial(): Historial
    {
        return app(Historial::class);
    }

    private function encuentro(string $nombre = 'Encuentro'): Encuentro
    {
        return Encuentro::create(['sede_id' => SedeFactory::idPorDefecto(), 'nombre' => $nombre, 'fecha_realizacion' => now()->toDateString(), 'estado' => 'planificado']);
    }

    public function test_registra_actor_sede_modulo_categoria_y_fotos_de_nombres(): void
    {
        $usuario = $this->crearUsuario('pastor');
        $persona = Persona::factory()->create(['nombres' => 'Ana', 'apellidos' => 'Pérez']);
        $encuentro = $this->encuentro('Encuentro de otoño');

        $entrada = $this->historial()->registrar($persona, 'asistio_encuentro', null, null, $usuario, ['fecha_evento' => '2026-10-18'], $encuentro);

        $this->assertSame('usuario', $entrada->actor);
        $this->assertSame($usuario->id, $entrada->user_id);
        $this->assertSame($usuario->name, $entrada->actor_nombre);
        $this->assertSame($persona->sede_id, $entrada->sede_id);
        $this->assertSame('encuentros', $entrada->modulo);
        $this->assertSame('encuentros_retiros', $entrada->categoria);
        $this->assertSame('persona', $entrada->sujeto_tipo);
        $this->assertSame('Ana Pérez', $entrada->sujeto_nombre);
        $this->assertSame('encuentro', $entrada->relacionado_tipo);
        $this->assertSame('Encuentro de otoño', $entrada->relacionado_nombre);
        $this->assertSame('2026-10-18', $entrada->detalle['fecha_evento']);
    }

    public function test_sin_usuario_ni_actor_queda_como_sistema(): void
    {
        $entrada = $this->historial()->registrar(Persona::factory()->create(), 'estado', ['estado' => 'visitante'], ['estado' => 'celula']);

        $this->assertSame('sistema', $entrada->actor);
        $this->assertSame('Sistema', $entrada->actor_nombre);
        $this->assertNull($entrada->user_id);
    }

    public function test_una_accion_fuera_del_catalogo_lanza_una_excepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->historial()->registrar(Persona::factory()->create(), 'inventada');
    }

    public function test_accion_con_nombre_de_negocio_solo_se_aplica_a_quien_la_tiene_en_su_catalogo(): void
    {
        $persona = Persona::factory()->create();

        $this->historial()->accion('inactivada', function () use ($persona) {
            $this->historial()->registrar($persona, 'estado', ['estado' => 'visitante'], ['estado' => 'inactivo']);
            $this->historial()->accion('reactivada', fn () => $this->historial()->registrar($persona, 'estado', ['estado' => 'inactivo'], ['estado' => 'visitante']));
            Celula::factory()->create();
        });

        $acciones = CambioHistorial::query()->orderBy('id')->get()->map(fn ($c) => $c->sujeto_tipo.'.'.$c->accion)->all();
        $this->assertSame(['persona.inactivada', 'persona.reactivada', 'celula.creado'], $acciones);
    }

    public function test_actor_especial_qr_y_automatico(): void
    {
        $persona = Persona::factory()->create();
        $usuario = $this->crearUsuario('pastor');

        $qr = $this->historial()->comoActor('qr', 'Registro público por QR · Evento X', fn () => $this->historial()->registrar($persona, 'creada_qr'));
        $auto = $this->historial()->comoActor('automatico', 'Automático', fn () => $this->historial()->registrar($persona, 'nivel_automatico'), $usuario);

        $this->assertSame(['qr', 'Registro público por QR · Evento X', null], [$qr->actor, $qr->actor_nombre, $qr->user_id]);
        $this->assertSame(['automatico', 'Automático · por '.$usuario->name, $usuario->id], [$auto->actor, $auto->actor_nombre, $auto->user_id]);
    }

    public function test_sin_registro_no_escribe(): void
    {
        $this->historial()->sinRegistro(fn () => $this->historial()->registrar(Persona::factory()->create(), 'creada'));

        $this->assertSame(0, CambioHistorial::count());
    }

    public function test_si_la_transaccion_se_revierte_no_queda_la_entrada(): void
    {
        $persona = Persona::factory()->create();

        try {
            DB::transaction(function () use ($persona) {
                $this->historial()->registrar($persona, 'estado', ['estado' => 'visitante'], ['estado' => 'celula']);
                throw new \RuntimeException('falla a mitad de camino');
            });
        } catch (\RuntimeException) {
        }

        $this->assertSame(0, CambioHistorial::count());
    }

    public function test_una_edicion_sin_diferencias_no_escribe(): void
    {
        $persona = Persona::factory()->create();

        $this->assertNull($this->historial()->registrar($persona, 'editada', ['celular' => '300'], ['celular' => '300']));
        $this->assertSame(0, CambioHistorial::count());
    }

    public function test_sincronizacion_sin_cambios_no_escribe_y_con_cambios_escribe_resumen_y_por_persona(): void
    {
        $encuentro = $this->historial()->sinRegistro(fn () => $this->encuentro());
        [$a, $b, $c] = Persona::factory()->count(3)->create();

        $this->historial()->sincronizacion($encuentro, ['attached' => [], 'detached' => []], 'inscritos_actualizados', 'inscrito_encuentro', 'quitado_encuentro');
        $this->assertSame(0, CambioHistorial::count());

        $this->historial()->sincronizacion($encuentro, ['attached' => [$a->id, $b->id], 'detached' => [$c->id]], 'inscritos_actualizados', 'inscrito_encuentro', 'quitado_encuentro');

        $resumen = CambioHistorial::where('sujeto_tipo', 'encuentro')->sole();
        $this->assertSame('inscritos_actualizados', $resumen->accion);
        $this->assertSame([$a->nombre_completo, $b->nombre_completo], collect($resumen->detalle['agregados'])->pluck('nombre')->all());
        $this->assertSame([$c->nombre_completo], collect($resumen->detalle['quitados'])->pluck('nombre')->all());

        $individuales = CambioHistorial::where('sujeto_tipo', 'persona')->orderBy('id')->get();
        $this->assertSame(['inscrito_encuentro', 'inscrito_encuentro', 'quitado_encuentro'], $individuales->pluck('accion')->all());
        $this->assertTrue($individuales->every(fn ($e) => $e->lote === $resumen->lote && $e->relacionado_id === $encuentro->id));
    }
}
