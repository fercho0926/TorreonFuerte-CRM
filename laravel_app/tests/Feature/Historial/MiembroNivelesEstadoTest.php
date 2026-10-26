<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\NivelFormacion;
use App\Services\RegistroMiembrosQr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Niveles, estado y registros por QR en el histórico del miembro (spec 015, historia 1 e historia 2). */
class MiembroNivelesEstadoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);
    }

    public function test_nivel_manual_y_automatico_con_su_actor(): void
    {
        $persona = $this->A['persona'];
        $nivel1 = NivelFormacion::where('posicion', 1)->firstOrFail();
        $nivel2 = NivelFormacion::where('posicion', 2)->firstOrFail();

        $this->post(route('niveles.avances.store'), ['persona_id' => $persona->id, 'nivel_id' => $nivel1->id, 'fecha' => '2026-03-12'])->assertSessionHasNoErrors();

        $encuentro = $this->A['encuentro'];
        $this->put(route('encuentros.update', $encuentro), [
            'nombre' => $encuentro->nombre, 'fecha_realizacion' => '2026-04-01', 'valor' => 0, 'estado' => 'realizado', 'nivel_id' => $nivel2->id, 'personas' => [$persona->id],
        ])->assertSessionHasNoErrors();
        $this->put(route('encuentros.asistencia', $encuentro), ['asistio' => [$persona->id]])->assertSessionHasNoErrors();

        $manual = CambioHistorial::query()->de('persona', $persona->id)->where('accion', 'nivel_registrado')->sole();
        $this->assertSame(['usuario', 'niveles', 'nivel', $nivel1->id], [$manual->actor, $manual->categoria, $manual->relacionado_tipo, $manual->relacionado_id]);

        $automatico = CambioHistorial::query()->de('persona', $persona->id)->where('accion', 'nivel_automatico')->sole();
        $this->assertSame('automatico', $automatico->actor);
        $this->assertSame('Automático · por '.$this->A['pastor']->name, $automatico->actor_nombre);
        $this->assertSame($this->A['pastor']->id, $automatico->user_id);

        $this->get(route('personas.show', [$persona, 'pestana' => 'historico']))->assertSee('Nivel completado por encuentro')->assertSee('Automático · por');
    }

    public function test_inactivar_y_reactivar_quedan_en_cambios_de_datos(): void
    {
        $persona = $this->A['persona'];

        $this->post(route('personas.inactivar', $persona));
        $this->post(route('personas.reactivar', $persona));

        $this->assertSame(['inactivada', 'reactivada'], CambioHistorial::query()->de('persona', $persona->id)->where('categoria', 'datos')
            ->whereIn('accion', ['inactivada', 'reactivada'])->orderBy('id')->pluck('accion')->all());
    }

    public function test_el_registro_publico_por_qr_queda_como_tal(): void
    {
        $resultado = app(RegistroMiembrosQr::class)->registrar($this->A['sede'], [
            'celular' => '3209990000', 'nombres' => 'Luis', 'apellidos' => 'Gómez', 'fecha_nacimiento' => '1985-01-01', 'estado_inicial' => 'visitante',
        ]);

        $alta = CambioHistorial::query()->de('persona', $resultado['persona']->id)->where('accion', 'creada_qr')->sole();
        $this->assertSame('qr', $alta->actor);
        $this->assertStringStartsWith('Registro público por QR', $alta->actor_nombre);
        $this->assertNull($alta->user_id);
    }
}
