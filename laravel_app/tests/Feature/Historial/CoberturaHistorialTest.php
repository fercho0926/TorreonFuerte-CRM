<?php

namespace Tests\Feature\Historial;

use App\Models\CambioHistorial;
use App\Models\Celula;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Models\RegistroClave;
use App\Models\Sede;
use App\Models\SolicitudClave;
use App\Models\User;
use App\Services\ClavesTemporales;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/**
 * Toda ruta que cambia información deja su entrada en el historial (spec 015, RF-001, CE-001).
 * Si se agrega una ruta POST/PUT/DELETE nueva sin caso aquí, la prueba falla: agrega el caso (y su registro).
 */
class CoberturaHistorialTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    /** Rutas de escritura que no cambian información de la plataforma (o no son de la aplicación). */
    private const EXENTAS = [
        'login.post', 'logout', 'sede-activa.update', 'registro.buscar', 'registro-sede.buscar', 'storage.local.upload',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
    }

    /**
     * Cada caso prepara lo necesario y devuelve [método, url, datos, tipo esperado, acción esperada].
     *
     * @return array<string, Closure(): array{0: string, 1: string, 2: array, 3: string, 4: string}>
     */
    private function casos(): array
    {
        $A = fn () => $this->A;
        $nueva = fn (array $extra = []) => Persona::factory()->deSede($this->A['sede']->id)->create($extra + ['estado' => 'miembro_activo']);
        $persona = fn ($p, array $extra = []) => $extra + ['nombres' => $p->nombres, 'apellidos' => $p->apellidos, 'estado' => $p->estado, 'celular' => $p->celular, 'lider_id' => $p->lider_id];
        $nivel = fn (int $posicion) => NivelFormacion::where('posicion', $posicion)->firstOrFail();
        $encuentro = fn (array $extra = []) => $extra + ['nombre' => 'Encuentro Alfa', 'fecha_realizacion' => now()->toDateString(), 'valor' => 0, 'estado' => 'planificado'];

        return [
            'personas.store' => fn () => ['POST', route('personas.store'), ['nombres' => 'Nuevo', 'apellidos' => 'Miembro', 'celular' => '3001234567', 'estado' => 'visitante'], 'persona', 'creada'],
            'personas.rapida' => fn () => ['POST', route('personas.rapida'), ['nombres' => 'Rápido', 'apellidos' => 'Alta', 'celular' => '3007654321'], 'persona', 'creada'],
            'personas.update' => fn () => ['PUT', route('personas.update', $A()['persona']), $persona($A()['persona'], ['direccion' => 'Calle nueva 1']), 'persona', 'editada'],
            'personas.destroy' => fn () => ['DELETE', route('personas.destroy', $p = $nueva()), [], 'persona', 'eliminada'],
            'personas.inactivar' => fn () => ['POST', route('personas.inactivar', $A()['persona']), [], 'persona', 'inactivada'],
            'personas.reactivar' => fn () => [
                'POST', route('personas.reactivar', tap($nueva(), fn ($p) => $p->update(['estado' => 'inactivo']))), [], 'persona', 'reactivada',
            ],
            'traslados.store' => fn () => ['POST', route('traslados.store', $A()['persona']), ['sede_destino_id' => $this->B['sede']->id], 'persona', 'trasladada'],
            'lideres.designar.store' => fn () => ['POST', route('lideres.designar.store'), ['persona_id' => $nueva()->id], 'persona', 'rol_lider'],
            'lideres.destroy' => fn () => ['DELETE', route('lideres.destroy', $p = $nueva(['es_lider' => true])), [], 'persona', 'rol_lider'],
            'sin-lider.asignar' => fn () => ['POST', route('sin-lider.asignar', $nueva()), ['lider_id' => $A()['lider']->id], 'persona', 'lider'],

            'celulas.store' => fn () => ['POST', route('celulas.store'), ['nombre' => 'Célula nueva', 'lider_id' => $A()['lider']->id, 'estado' => 'activa', 'integrantes_presente' => 1], 'celula', 'creado'],
            'celulas.update' => fn () => ['PUT', route('celulas.update', $A()['celula']), ['nombre' => 'Renombrada', 'lider_id' => $A()['lider']->id, 'estado' => 'activa'], 'celula', 'editado'],
            'celulas.destroy' => fn () => ['DELETE', route('celulas.destroy', Celula::factory()->create(['sede_id' => $A()['sede']->id, 'lider_id' => $A()['lider']->id])), [], 'celula', 'eliminado'],
            'celulas.miembros.store' => fn () => ['POST', route('celulas.miembros.store', $A()['celula']), ['personas' => [$nueva()->id]], 'celula', 'miembros_actualizados'],
            'celulas.miembros.destroy' => fn () => ['DELETE', route('celulas.miembros.destroy', [$A()['celula'], $A()['persona']]), [], 'persona', 'salida_celula'],
            'celulas.reuniones.store' => fn () => ['POST', route('celulas.reuniones.store', $A()['celula']), ['fecha' => now()->toDateString(), 'asistio' => [$A()['persona']->id]], 'celula', 'reunion_registrada'],
            'celulas.reuniones.destroy' => fn () => [
                'DELETE', route('celulas.reuniones.destroy', [$A()['celula'], \App\Models\CelulaReunion::create(['sede_id' => $A()['sede']->id, 'celula_id' => $A()['celula']->id, 'fecha' => now()->toDateString()])]), [], 'celula', 'reunion_eliminada',
            ],

            'ministerios.store' => fn () => ['POST', route('ministerios.store'), ['nombre' => 'Nuevo ministerio', 'estado' => 'activo', 'personas' => [$A()['persona']->id]], 'ministerio', 'creado'],
            'ministerios.update' => fn () => ['PUT', route('ministerios.update', $A()['ministerio']), ['nombre' => 'Renombrado', 'estado' => 'activo'], 'ministerio', 'editado'],
            'ministerios.destroy' => fn () => ['DELETE', route('ministerios.destroy', $A()['ministerio']), [], 'ministerio', 'eliminado'],
            'ministerios.integrantes' => fn () => [
                'PUT', route('ministerios.integrantes', tap($A()['ministerio'], fn ($m) => $m->integrantes()->attach($A()['persona']->id))), ['rol' => [$A()['persona']->id => 'Músico']], 'persona', 'cambio_ministerio',
            ],

            'eventos.store' => fn () => ['POST', route('eventos.store'), ['nombre' => 'Retiro', 'tipo' => 'Otro', 'fecha' => now()->addWeek()->toDateString(), 'costo' => 1000, 'estado' => 'planificado'], 'evento', 'creado'],
            'eventos.update' => fn () => ['PUT', route('eventos.update', $A()['evento']), ['nombre' => 'Evento renombrado', 'tipo' => 'Otro', 'fecha' => now()->addWeek()->toDateString(), 'costo' => 1000, 'estado' => 'planificado'], 'evento', 'editado'],
            'eventos.destroy' => fn () => ['DELETE', route('eventos.destroy', $A()['evento']), [], 'evento', 'eliminado'],
            'eventos.personas.store' => fn () => ['POST', route('eventos.personas.store', $A()['evento']), ['persona_id' => $nueva()->id], 'persona', 'inscrito_evento'],
            'eventos.personas.destroy' => fn () => [
                'DELETE', route('eventos.personas.destroy', [$A()['evento'], \App\Models\EventoPersona::create(['sede_id' => $A()['sede']->id, 'evento_id' => $A()['evento']->id, 'persona_id' => $nueva()->id])]), [], 'persona', 'quitado_evento',
            ],
            'eventos.pagos.store' => fn () => ['POST', route('eventos.pagos.store', [$A()['evento'], $A()['inscripcion']]), ['monto' => 5000, 'fecha_pago' => now()->toDateString()], 'persona', 'abono_registrado'],
            'eventos.pagos.destroy' => fn () => ['DELETE', route('eventos.pagos.destroy', [$A()['evento'], $A()['inscripcion'], $A()['pago']]), [], 'persona', 'abono_eliminado'],

            'encuentros.store' => fn () => ['POST', route('encuentros.store'), $encuentro(['personas' => [$A()['persona']->id]]), 'encuentro', 'creado'],
            'encuentros.update' => fn () => ['PUT', route('encuentros.update', $A()['encuentro']), $encuentro(['personas' => [$A()['persona']->id]]), 'persona', 'inscrito_encuentro'],
            'encuentros.destroy' => fn () => ['DELETE', route('encuentros.destroy', $A()['encuentro']), [], 'encuentro', 'eliminado'],
            'encuentros.asistencia' => fn () => [
                'PUT', route('encuentros.asistencia', tap($A()['encuentro'], fn ($e) => $e->personas()->attach($A()['persona']->id))), ['asistio' => [$A()['persona']->id]], 'persona', 'asistio_encuentro',
            ],

            'niveles.store' => fn () => ['POST', route('niveles.store'), ['nombre' => 'Nivel nuevo'], 'nivel', 'creado'],
            'niveles.update' => fn () => ['PUT', route('niveles.update', $nivel(1)), ['nombre' => 'Libres para servir (nuevo)'], 'nivel', 'editado'],
            'niveles.destroy' => fn () => ['DELETE', route('niveles.destroy', NivelFormacion::create(['nombre' => 'Temporal', 'posicion' => 9, 'activo' => true])), [], 'nivel', 'eliminado'],
            'niveles.subir' => fn () => ['POST', route('niveles.subir', $nivel(2)), [], 'nivel', 'reordenado'],
            'niveles.bajar' => fn () => ['POST', route('niveles.bajar', $nivel(1)), [], 'nivel', 'reordenado'],
            'niveles.desactivar' => fn () => ['POST', route('niveles.desactivar', $nivel(4)), [], 'nivel', 'desactivado'],
            'niveles.reactivar' => fn () => ['POST', route('niveles.reactivar', tap($nivel(4), fn ($n) => $n->update(['activo' => false]))), [], 'nivel', 'reactivado'],
            'niveles.avances.store' => fn () => ['POST', route('niveles.avances.store'), ['persona_id' => $A()['persona']->id, 'nivel_id' => $nivel(1)->id, 'fecha' => now()->toDateString()], 'persona', 'nivel_registrado'],
            'niveles.avances.destroy' => fn () => [
                'DELETE', route('niveles.avances.destroy', app(\App\Services\AvancesNivelService::class)->registrarManual($A()['persona'], $nivel(1), now(), null, null)), [], 'persona', 'nivel_quitado',
            ],

            'sedes.store' => fn () => ['POST', route('sedes.store'), [
                'nombre' => 'Sede Nueva', 'ciudad' => 'Cali',
                'pastor' => ['nombres' => 'Pablo', 'apellidos' => 'Nuevo', 'email' => 'pablo.nuevo@iglesia.co', 'password' => 'secreto123'],
            ], 'sede', 'creada'],
            'sedes.update' => fn () => ['PUT', route('sedes.update', $A()['sede']), ['nombre' => 'Iglesia Alfa 2', 'ciudad' => 'Otra'], 'sede', 'editada'],
            'sedes.destroy' => fn () => ['DELETE', route('sedes.destroy', Sede::factory()->create(['nombre' => 'Vacía'])), [], 'sede', 'eliminada'],
            'sedes.desactivar' => fn () => ['POST', route('sedes.desactivar', $this->B['sede']), [], 'sede', 'desactivada'],
            'sedes.reactivar' => fn () => ['POST', route('sedes.reactivar', tap($this->B['sede'], fn ($s) => $s->update(['activa' => false]))), [], 'sede', 'reactivada'],
            'sedes.pastores.update' => fn () => ['PUT', route('sedes.pastores.update', $A()['sede']), [
                'rol' => 'pastora', 'nombres' => 'Paula', 'apellidos' => 'Pastora', 'email' => 'paula.p@iglesia.co', 'password' => 'secreto123',
            ], 'sede', 'pastora_cambiada'],
            'registro-miembros.abrir' => fn () => ['POST', route('registro-miembros.abrir', $A()['sede']), [], 'sede', 'registro_cerrado'],
            'registro-miembros.regenerar' => fn () => ['POST', route('registro-miembros.regenerar', $A()['sede']), [], 'sede', 'registro_regenerado'],
            'registro-qr.abrir' => fn () => ['POST', route('registro-qr.abrir', ['evento', $A()['evento']->id]), [], 'evento', 'qr_cambiado'],
            'registro-qr.regenerar' => fn () => ['POST', route('registro-qr.regenerar', ['celula', $A()['celula']->id]), [], 'celula', 'qr_cambiado'],

            'roles.store' => fn () => ['POST', route('roles.store'), ['nombre' => 'Apoyo', 'alcance' => 'sede', 'permisos' => ['eventos' => 1]], 'rol', 'creado'],
            'roles.update' => fn () => ['PUT', route('roles.update', 'lider_celula'), ['nombre' => 'Líder', 'alcance' => 'rama', 'asignable' => 1, 'permisos' => ['eventos' => 1], 'confirmo_afectados' => 1], 'rol', 'editado'],
            'roles.destroy' => fn () => ['DELETE', route('roles.destroy', \App\Models\Rol::factory()->create(['nombre' => 'Borrable', 'clave' => 'borrable'])), [], 'rol', 'eliminado'],

            'users.store' => fn () => ['POST', route('users.store'), ['miembro' => ['nombres' => 'Nueva', 'apellidos' => 'Cuenta'], 'email' => 'nueva@iglesia.co', 'celular' => '3001112222', 'role' => 'user', 'sede_id' => $A()['sede']->id], 'usuario', 'creado'],
            'users.update' => fn () => ['PUT', route('users.update', $A()['liderUser']), ['email' => 'renombrado@iglesia.co', 'role' => 'lider_celula'], 'usuario', 'editado'],
            'users.destroy' => fn () => ['DELETE', route('users.destroy', User::factory()->create(['role' => 'user', 'sede_id' => $A()['sede']->id])), [], 'usuario', 'eliminado'],
            'users.clave.store' => fn () => ['POST', route('users.clave.store', tap($A()['liderUser'], fn ($u) => $u->persona->update(['celular' => '3005556666']))), [], 'usuario', 'clave_generada'],
            'claves.enviada' => fn () => [
                'POST', route('claves.enviada', $this->registroClave($A()['liderUser'])), [], 'usuario', 'clave_enviada',
            ],
            'solicitudes-clave.atender' => fn () => ['POST', route('solicitudes-clave.atender', $this->solicitud($A()['liderUser'])), [], 'solicitud_clave', 'atendida'],
            'solicitudes-clave.descartar' => fn () => ['POST', route('solicitudes-clave.descartar', $this->solicitud($A()['liderUser'])), [], 'solicitud_clave', 'descartada'],

            // Cuenta propia y rutas públicas.
            'cuenta.clave.update' => fn () => $this->comoDueno(['PUT', route('cuenta.clave.update'), ['password_actual' => 'password', 'password' => 'nueva-segura-1', 'password_confirmation' => 'nueva-segura-1'], 'usuario', 'clave_cambiada']),
            'cambiar-clave.update' => fn () => $this->comoDueno(['PUT', route('cambiar-clave.update'), ['password' => 'nueva-segura-1', 'password_confirmation' => 'nueva-segura-1'], 'usuario', 'clave_cambiada'], true),
            'recuperacion.store' => fn () => $this->sinSesion(['POST', route('recuperacion.store'), ['email' => $A()['liderUser']->email], 'solicitud_clave', 'creado']),
            'registro.store' => fn () => $this->sinSesion(['POST', route('registro.store', $A()['evento']->registro_token), [
                'celular' => '3201112233', 'nombres' => 'Visita', 'apellidos' => 'QR', 'fecha_nacimiento' => '1990-01-01',
            ], 'persona', 'creada_qr_actividad']),
            'registro-sede.store' => fn () => $this->sinSesion(['POST', route('registro-sede.store', tap($A()['sede'], fn ($s) => $s->update(['registro_abierto' => true]))->registro_token), [
                'celular' => '3204445566', 'nombres' => 'Miembro', 'apellidos' => 'QR', 'fecha_nacimiento' => '1990-01-01', 'estado_inicial' => 'visitante',
            ], 'persona', 'creada_qr']),
        ];
    }

    public function test_toda_ruta_de_escritura_tiene_su_caso(): void
    {
        $escritura = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => array_intersect($r->methods(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $r->getName())
            ->map(fn ($r) => $r->getName())
            ->reject(fn ($n) => in_array($n, self::EXENTAS, true))
            ->values()->all();

        $sinCaso = array_values(array_diff($escritura, array_keys($this->casos())));

        $this->assertSame([], $sinCaso, 'Rutas de escritura sin caso de historial: '.implode(', ', $sinCaso));
    }

    /** @return array<string, array{0: string}> */
    public static function rutas(): array
    {
        // Los casos usan el escenario de cada prueba; aquí solo se listan sus nombres (cada uno corre en una base limpia).
        $nombres = (new \ReflectionClass(self::class))->getMethod('nombresDeCasos')->invoke(null);

        return array_combine($nombres, array_map(fn ($n) => [$n], $nombres));
    }

    /** @return array<int, string> */
    private static function nombresDeCasos(): array
    {
        $archivo = (string) file_get_contents(__FILE__);
        preg_match_all("/^            '([a-z\\-.]+)' => fn \\(\\)/m", $archivo, $coincidencias);

        return $coincidencias[1];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rutas')]
    public function test_cada_ruta_de_escritura_deja_su_entrada(string $ruta): void
    {
        $this->actor = $this->comoAdminEnSede($this->A['sede']);
        $antes = (int) CambioHistorial::max('id');

        [$metodo, $url, $datos, $tipo, $accion] = $this->casos()[$ruta]();
        $respuesta = $this->call($metodo, $url, $datos);

        $nuevas = CambioHistorial::query()->where('id', '>', $antes)->get();
        $this->assertNotNull(
            $nuevas->first(fn ($e) => $e->sujeto_tipo === $tipo && $e->accion === $accion),
            "{$ruta} ({$respuesta->getStatusCode()}): se esperaba {$tipo}.{$accion}; hubo: ".$nuevas->map(fn ($e) => $e->sujeto_tipo.'.'.$e->accion)->join(', ')
                .' — errores: '.json_encode(($e = session('errors')) instanceof \Illuminate\Support\ViewErrorBag ? $e->getBag('default')->toArray() : $e, JSON_UNESCAPED_UNICODE),
        );
    }

    private ?User $actor = null;

    private function registroClave(User $usuario): RegistroClave
    {
        $usuario->persona->update(['celular' => '3005556666']);
        $registro = app(ClavesTemporales::class)->generar($usuario, RegistroClave::MOTIVO_REENVIO, $this->actor)['registro'];

        return $registro;
    }

    private function solicitud(User $usuario): SolicitudClave
    {
        $usuario->persona->update(['celular' => '3005556666']);

        return SolicitudClave::create(['user_id' => $usuario->id, 'sede_id' => $usuario->sede_id, 'estado' => SolicitudClave::PENDIENTE, 'ultima_solicitud_en' => now()]);
    }

    /** @param array{0: string, 1: string, 2: array, 3: string, 4: string} $caso */
    private function comoDueno(array $caso, bool $temporal = false): array
    {
        $usuario = User::factory()->create(['role' => 'user', 'sede_id' => $this->A['sede']->id, 'password' => 'password', 'debe_cambiar_clave' => $temporal]);
        $this->iniciarSesionComo($usuario);

        return $caso;
    }

    /** @param array{0: string, 1: string, 2: array, 3: string, 4: string} $caso */
    private function sinSesion(array $caso): array
    {
        $this->flushSession();

        return $caso;
    }
}
