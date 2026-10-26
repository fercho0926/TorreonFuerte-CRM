<?php

namespace App\Services;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\RegistroMiembroQr;
use App\Models\Sede;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Autorregistro de miembros por el QR de la sede. El formulario es público, así que todo corre dentro
 * de la sede del QR (`conSede`): un miembro que solo existe en otra sede nunca se lee ni se modifica.
 *
 * Reglas (spec 012): un miembro nuevo se crea con el estado que elige; uno existente se actualiza al
 * instante solo en los campos enviados, no vacíos y distintos; un inactivo o eliminado se reactiva;
 * nunca se vacía un dato, ni se cambia celular, estado (salvo reactivar), líder, células o sede.
 */
class RegistroMiembrosQr
{
    public const NUEVA = 'nueva';

    public const ACTUALIZADA = 'actualizada';

    public const REACTIVADA = 'reactivada';

    public const SIN_CAMBIOS = 'sin_cambios';

    /** Campos de texto o fecha que un miembro existente puede actualizar. */
    public const CAMPOS = ['nombres', 'apellidos', 'fecha_nacimiento', 'email', 'direccion', 'barrio', 'ciudad'];

    public const ETIQUETAS = [
        'nombres' => 'Nombres', 'apellidos' => 'Apellidos', 'fecha_nacimiento' => 'Fecha de nacimiento',
        'email' => 'Correo', 'direccion' => 'Dirección', 'barrio' => 'Barrio', 'ciudad' => 'Ciudad',
        'tipo_documento' => 'Tipo de documento', 'numero_documento' => 'Cédula',
    ];

    public const ESTADOS_INICIALES = ['visitante', 'miembro_activo'];

    /** Segundos durante los que un «sin cambios» idéntico no se vuelve a guardar (doble toque). */
    private const VENTANA_REPETIDO = 60;

    public function __construct(
        private SedeContext $contexto,
        private RegistroPorQr $busqueda,
        private LiderazgoService $liderazgo,
    ) {
    }

    /**
     * @param  array<string, mixed>  $datos  celular, nombres, apellidos, fecha_nacimiento, estado_inicial, tipo_documento,
     *                                       numero_documento, email, direccion, barrio, ciudad
     * @return array{resultado: string, persona: ?Persona, cambios: array<string, array{0: mixed, 1: mixed}>}
     *
     * @throws ValidationException
     */
    public function registrar(Sede $sede, array $datos): array
    {
        $datos = $this->normalizar($datos);

        // Lo hace el propio miembro desde el QR público: el historial lo muestra así (spec 015).
        $actor = 'Registro público por QR · QR de miembros de '.$sede->nombre;

        // Un mismo celular no se procesa dos veces a la vez (doble toque, dos pestañas).
        return Cache::lock('registro-miembros:'.$sede->id.':'.$datos['celular'], 15)->block(10, fn () => app(Historial::class)->comoActor(CambioHistorial::ACTOR_QR, $actor, function () use ($sede, $datos) {
            $busqueda = $this->busqueda->buscarEnSede($sede->id, $datos['celular'], $datos['numero_documento']);

            if ($busqueda['estado'] === RegistroPorQr::CONFLICTO) {
                throw ValidationException::withMessages(['celular' => $busqueda['mensaje']]);
            }

            return $this->contexto->conSede($sede->id, fn () => DB::transaction(function () use ($sede, $datos, $busqueda) {
                $persona = $busqueda['persona'];

                return $persona
                    ? $this->actualizar($sede, $persona, $datos)
                    : $this->crear($sede, $datos);
            }));
        }));
    }

    /** @return array{resultado: string, persona: Persona, cambios: array} */
    private function crear(Sede $sede, array $datos): array
    {
        if (!in_array($datos['estado_inicial'] ?? null, self::ESTADOS_INICIALES, true)) {
            throw ValidationException::withMessages(['estado_inicial' => 'Elige si te registras como visitante o como miembro activo.']);
        }

        $persona = Persona::create([
            'sede_id' => $sede->id,
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'celular' => $datos['celular'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'tipo_documento' => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'email' => $datos['email'],
            'direccion' => $datos['direccion'],
            'barrio' => $datos['barrio'],
            'ciudad' => $datos['ciudad'],
            'estado' => $datos['estado_inicial'],
        ]);

        $this->liderazgo->registrarCambio($persona, 'creada_qr', null, ['estado' => $persona->estado, 'origen' => 'QR de miembros']);
        $this->guardarRegistro($sede, $persona, $datos, self::NUEVA, null);

        return ['resultado' => self::NUEVA, 'persona' => $persona, 'cambios' => []];
    }

    /** @return array{resultado: string, persona: Persona, cambios: array} */
    private function actualizar(Sede $sede, Persona $persona, array $datos): array
    {
        $eliminada = $persona->trashed();
        $inactiva = $persona->estado === 'inactivo';
        $reactivar = $eliminada || $inactiva;

        $cambios = $this->cambios($persona, $datos);

        if ($reactivar) {
            $estadoAntes = $inactiva ? 'inactivo' : $persona->estado;
            $estadoNuevo = $inactiva ? $this->liderazgo->estadoAlReactivar($persona) : $persona->estado;

            if ($eliminada) {
                $persona->restore();
            }
        }

        if ($cambios) {
            $persona->update(array_map(fn ($par) => $par[1], $cambios));
        }

        if ($reactivar) {
            if ($inactiva) {
                $persona->update(['estado' => $estadoNuevo, 'inactivado_en' => null]);
            }

            $this->liderazgo->registrarCambio(
                $persona,
                'reactivada_qr',
                ['estado' => $estadoAntes, 'eliminada' => $eliminada] + array_map(fn ($par) => $par[0], $cambios),
                ['estado' => $estadoNuevo, 'origen' => 'QR de miembros'] + array_map(fn ($par) => $par[1], $cambios),
            );
            $resultado = self::REACTIVADA;
        } elseif ($cambios) {
            $this->liderazgo->registrarCambio(
                $persona,
                'actualizada_qr',
                array_map(fn ($par) => $par[0], $cambios),
                array_map(fn ($par) => $par[1], $cambios) + ['origen' => 'QR de miembros'],
            );
            $resultado = self::ACTUALIZADA;
        } else {
            $resultado = self::SIN_CAMBIOS;
        }

        $this->guardarRegistro($sede, $persona, $datos, $resultado, $cambios ?: null);

        return ['resultado' => $resultado, 'persona' => $persona, 'cambios' => $cambios];
    }

    /**
     * Campos que cambian: enviados, no vacíos y distintos de lo guardado. Nunca vacía un dato; la cédula solo
     * se escribe si la ficha no tiene una.
     *
     * @return array<string, array{0: mixed, 1: mixed}>
     */
    private function cambios(Persona $persona, array $datos): array
    {
        $cambios = [];

        foreach (self::CAMPOS as $campo) {
            $nuevo = $datos[$campo] ?? null;
            if ($nuevo === null) {
                continue;
            }

            $actual = $campo === 'fecha_nacimiento' ? $persona->fecha_nacimiento?->toDateString() : $persona->{$campo};

            if ($this->distinto($actual, $nuevo)) {
                $cambios[$campo] = [$actual, $nuevo];
            }
        }

        if ($datos['numero_documento'] && !$persona->numero_documento) {
            $cambios['tipo_documento'] = [$persona->tipo_documento, $datos['tipo_documento']];
            $cambios['numero_documento'] = [null, $datos['numero_documento']];
        }

        return $cambios;
    }

    private function distinto(mixed $actual, string $nuevo): bool
    {
        return mb_strtolower(trim((string) $actual)) !== mb_strtolower(trim($nuevo));
    }

    /** @param  array<string, array{0: mixed, 1: mixed}>|null  $cambios */
    private function guardarRegistro(Sede $sede, ?Persona $persona, array $datos, string $resultado, ?array $cambios): void
    {
        if ($resultado === self::SIN_CAMBIOS && RegistroMiembroQr::where('sede_id', $sede->id)
            ->where('celular', $datos['celular'])->where('resultado', self::SIN_CAMBIOS)
            ->where('created_at', '>=', now()->subSeconds(self::VENTANA_REPETIDO))->exists()) {
            return;
        }

        RegistroMiembroQr::create([
            'sede_id' => $sede->id,
            'persona_id' => $persona?->id,
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'celular' => $datos['celular'],
            'resultado' => $resultado,
            'cambios' => $cambios,
        ]);
    }

    /** Celular solo con dígitos, textos recortados y vacíos como nulos. */
    private function normalizar(array $datos): array
    {
        $limpio = [];
        foreach (['nombres', 'apellidos', 'fecha_nacimiento', 'tipo_documento', 'numero_documento', 'email', 'direccion', 'barrio', 'ciudad', 'estado_inicial'] as $campo) {
            $valor = isset($datos[$campo]) ? trim((string) $datos[$campo]) : '';
            $limpio[$campo] = $valor === '' ? null : preg_replace('/\s+/', ' ', $valor);
        }

        $limpio['celular'] = $this->busqueda->normalizarCelular($datos['celular'] ?? null);
        $limpio['tipo_documento'] = $limpio['numero_documento'] ? $limpio['tipo_documento'] : null;

        return $limpio;
    }

    /**
     * Datos de un miembro encontrado para precargar el formulario (decisión del responsable: todos los campos).
     *
     * @return array<string, string>
     */
    public function datosParaFormulario(Persona $persona): array
    {
        return array_filter([
            'nombres' => $persona->nombres,
            'apellidos' => $persona->apellidos,
            'fecha_nacimiento' => $persona->fecha_nacimiento?->toDateString(),
            'celular' => \App\Support\Celular::limpio($persona->celular),
            'tipo_documento' => $persona->tipo_documento,
            'numero_documento' => $persona->numero_documento,
            'email' => $persona->email,
            'direccion' => $persona->direccion,
            'barrio' => $persona->barrio,
            'ciudad' => $persona->ciudad,
        ], fn ($valor) => $valor !== null && $valor !== '');
    }
}
