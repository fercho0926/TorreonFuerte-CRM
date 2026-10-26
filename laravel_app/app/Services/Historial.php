<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\CambioHistorial;
use App\Models\User;
use App\Support\AccionesHistorial;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Única puerta de escritura del historial de la plataforma (spec 015, contracts/servicio-historial.md).
 *
 * - Escribe en la conexión y la transacción en curso: si la acción se revierte, la entrada también (RF-005).
 * - Traduce cada campo cambiado a texto legible con la definición del modelo (`camposHistorial()`) y guarda esa foto,
 *   para que la entrada siga diciendo lo que era aunque después cambie o se borre lo que nombra (RF-003, RF-009).
 * - Nunca guarda el valor de un campo secreto (claves, tokens, códigos): solo que cambió (RF-007).
 */
class Historial
{
    /** Campos que nunca se guardan, en cualquier modelo. */
    private const PATRON_SECRETO = '/^(password|remember_token)$|^(clave|token|codigo)|_token$|_hash$/';

    /** Campos técnicos que no se registran. */
    private const IGNORADOS = ['id', 'created_at', 'updated_at', 'deleted_at', 'sede_id'];

    private const LARGO = 120;

    /** @var array<int, string> acciones con nombre de negocio activas (la más interna manda) */
    private array $acciones = [];

    /** @var array<int, array{actor: string, nombre: string, usuario: ?User}> */
    private array $actores = [];

    private int $silencio = 0;

    private ?bool $tablaLista = null;

    /**
     * Escribe una entrada. Devuelve null si el registro está en silencio o si no hay nada que contar
     * (una edición sin campos cambiados).
     *
     * @param  array<string, mixed>|null  $antes  valores crudos anteriores
     * @param  array<string, mixed>|null  $despues  valores crudos nuevos
     * @param  array<string, mixed>  $detalle  datos adicionales (`fecha_evento`, `agregados`, `quitados`, …)
     */
    public function registrar(
        Model $sujeto,
        string $accion,
        ?array $antes = null,
        ?array $despues = null,
        ?User $por = null,
        array $detalle = [],
        ?Model $relacionado = null,
        ?string $lote = null,
    ): ?CambioHistorial {
        if ($this->silencio > 0 || !$this->tablaLista()) {
            return null;
        }

        $tipo = AccionesHistorial::tipoDe($sujeto)
            ?? throw new InvalidArgumentException('El modelo '.$sujeto::class.' no lleva historial.');
        $accion = $this->accionEfectiva($tipo, $accion);

        if (!AccionesHistorial::existe($tipo, $accion)) {
            throw new InvalidArgumentException("La acción «{$accion}» no está en el catálogo del historial para «{$tipo}».");
        }

        $antes = $this->sinSecretos($sujeto, $antes);
        $despues = $this->sinSecretos($sujeto, $despues);

        if (!array_key_exists('cambios', $detalle) && ($antes !== null || $despues !== null)) {
            $cambios = $this->cambios($sujeto, $antes, $despues, $this->camposSecretosCambiados($sujeto));
            if ($cambios) {
                $detalle['cambios'] = $cambios;
            } elseif ($antes !== null && $despues !== null && !$detalle) {
                // Una edición en la que ningún valor cambió de verdad no deja entrada vacía.
                return null;
            }
        }

        if (in_array($accion, AccionesHistorial::CON_FOTO, true) && !isset($detalle['datos'])) {
            $detalle['datos'] = $this->foto($sujeto);
        }

        [$actor, $actorNombre, $usuario] = $this->actor($por);

        return CambioHistorial::create([
            'created_at' => now(),
            'sede_id' => $this->sedeDe($sujeto) ?? ($relacionado ? $this->sedeDe($relacionado) : null),
            'actor' => $actor,
            'user_id' => $usuario?->id,
            'actor_nombre' => Str::limit($actorNombre, 117),
            'modulo' => AccionesHistorial::modulo($tipo, $accion),
            'accion' => $accion,
            'categoria' => AccionesHistorial::categoria($tipo, $accion),
            'sujeto_tipo' => $tipo,
            'sujeto_id' => $sujeto->getKey(),
            'sujeto_nombre' => Str::limit($this->nombreDe($sujeto), 157),
            'relacionado_tipo' => $relacionado ? AccionesHistorial::tipoDe($relacionado) : null,
            'relacionado_id' => $relacionado?->getKey(),
            'relacionado_nombre' => $relacionado ? Str::limit($this->nombreDe($relacionado), 157) : null,
            'lote' => $lote,
            'antes' => $antes,
            'despues' => $despues,
            'detalle' => $detalle ?: null,
        ]);
    }

    /**
     * Cambios de una relación muchos a muchos (resultado de `sync()`, `attach()` o `detach()`): una entrada resumen en
     * el registro principal con quiénes entraron y salieron, y una entrada por cada miembro afectado, todas con el
     * mismo lote (RF-006). Si no hubo agregados ni quitados no escribe nada.
     *
     * @param  array{attached?: array<int, int|string>, detached?: array<int, int|string>}  $resultado
     * @param  array<string, mixed>  $extra  detalle común (por ejemplo `fecha_evento`)
     */
    public function sincronizacion(
        Model $principal,
        array $resultado,
        ?string $accionResumen,
        string $accionAgregado,
        string $accionQuitado,
        array $extra = [],
        ?User $por = null,
    ): void {
        $agregados = array_map('intval', array_values($resultado['attached'] ?? []));
        $quitados = array_map('intval', array_values($resultado['detached'] ?? []));

        if ((!$agregados && !$quitados) || $this->silencio > 0) {
            return;
        }

        $personas = \App\Models\Persona::query()->withoutGlobalScopes()->withTrashed()
            ->whereIn('id', [...$agregados, ...$quitados])->get()->keyBy('id');
        $nombre = fn (int $id) => ['id' => $id, 'nombre' => $personas[$id]?->nombre_completo ?? "Miembro #{$id}"];
        $lote = (string) Str::uuid();

        if ($accionResumen) {
            $this->registrar($principal, $accionResumen, null, null, $por, $extra + [
                'agregados' => array_map($nombre, $agregados),
                'quitados' => array_map($nombre, $quitados),
            ], null, $lote);
        }

        foreach ([$accionAgregado => $agregados, $accionQuitado => $quitados] as $accion => $ids) {
            foreach ($ids as $id) {
                if ($persona = $personas[$id] ?? null) {
                    $this->registrar($persona, $accion, null, null, $por, $extra, $principal, $lote);
                }
            }
        }
    }

    /**
     * Ejecuta una operación dando nombre de negocio a las entradas que generen los modelos dentro de ella. Solo se
     * aplica a los registros cuyo catálogo tiene esa acción; las demás entradas conservan la suya.
     */
    public function accion(string $accion, Closure $operacion): mixed
    {
        $this->acciones[] = $accion;

        try {
            return $operacion();
        } finally {
            array_pop($this->acciones);
        }
    }

    /**
     * Ejecuta una operación con un actor especial: `qr` (registro público), `automatico` (lo hace el sistema a raíz de
     * otra acción; se conserva quién la provocó) o `sistema` (migraciones y comandos).
     */
    public function comoActor(string $actor, string $nombre, Closure $operacion, ?User $provocadoPor = null): mixed
    {
        $this->actores[] = ['actor' => $actor, 'nombre' => $nombre, 'usuario' => $provocadoPor];

        try {
            return $operacion();
        } finally {
            array_pop($this->actores);
        }
    }

    /** Solo para migraciones de datos, siembras y pruebas: no escribe entradas. */
    public function sinRegistro(Closure $operacion): mixed
    {
        $this->silencio++;

        try {
            return $operacion();
        } finally {
            $this->silencio--;
        }
    }

    public function enSilencio(): bool
    {
        return $this->silencio > 0;
    }

    /**
     * Lista legible de los campos que cambiaron entre dos conjuntos de valores crudos.
     *
     * @param  array<string, mixed>|null  $antes
     * @param  array<string, mixed>|null  $despues
     * @param  array<int, string>  $secretosCambiados
     * @return array<int, array<string, mixed>>
     */
    public function cambios(Model|string $modelo, ?array $antes, ?array $despues, array $secretosCambiados = []): array
    {
        $antes ??= [];
        $despues ??= [];
        $definiciones = $this->definiciones($modelo);
        $ignorados = array_diff([...self::IGNORADOS, ...$this->llamar($modelo, 'camposIgnorados', [])], array_keys($definiciones));
        $cambios = [];

        foreach (array_unique([...array_keys($antes), ...array_keys($despues)]) as $campo) {
            if (in_array($campo, $ignorados, true) || is_iterable($antes[$campo] ?? null) || is_iterable($despues[$campo] ?? null)
                || is_object($antes[$campo] ?? null) && !$antes[$campo] instanceof DateTimeInterface
                || is_object($despues[$campo] ?? null) && !$despues[$campo] instanceof DateTimeInterface) {
                continue;
            }

            $valorAntes = $antes[$campo] ?? null;
            $valorDespues = $despues[$campo] ?? null;
            if ($this->normalizar($valorAntes) === $this->normalizar($valorDespues) && array_key_exists($campo, $antes) && array_key_exists($campo, $despues)) {
                continue;
            }

            $definicion = $definiciones[$campo] ?? [$this->etiquetaTecnica($campo)];
            $textoAntes = $this->legible($definicion, $valorAntes);
            $textoDespues = $this->legible($definicion, $valorDespues);

            $cambios[] = [
                'campo' => $campo,
                'etiqueta' => $definicion[0],
                'antes' => $textoAntes,
                'despues' => $textoDespues,
                'largo' => mb_strlen($textoAntes) > self::LARGO || mb_strlen($textoDespues) > self::LARGO,
                'secreto' => false,
                'modulo_sensible' => $definicion['sensible'] ?? null,
            ];
        }

        foreach (array_unique($secretosCambiados) as $campo) {
            $cambios[] = [
                'campo' => $campo,
                'etiqueta' => ($definiciones[$campo] ?? [$this->etiquetaTecnica($campo)])[0],
                'antes' => '', 'despues' => '', 'largo' => false, 'secreto' => true, 'modulo_sensible' => null,
            ];
        }

        return $cambios;
    }

    /**
     * Foto legible de los campos principales de un registro (altas y eliminaciones).
     *
     * @return array<string, string>
     */
    public function foto(Model $modelo): array
    {
        $foto = [];
        // Solo valores crudos de columnas: `getAttribute()` devolvería una relación con el mismo nombre (por ejemplo
        // `celulas.sede`, texto, frente a la relación `sede()`), y con ella datos que no son del registro.
        $valores = $modelo->getAttributes();
        foreach ($this->definiciones($modelo) as $campo => $definicion) {
            $valor = $valores[$campo] ?? null;
            if ($this->esSecreto($modelo, $campo) || !is_scalar($valor) || $valor === '') {
                continue;
            }
            $foto[$definicion[0]] = $this->legible($definicion, $valor);
        }

        return $foto;
    }

    /** Valor legible de un campo de un modelo (para textos de detalle que no son ediciones). */
    public function valorLegible(Model|string $modelo, string $campo, mixed $valor): string
    {
        return $this->legible($this->definiciones($modelo)[$campo] ?? [$campo], $valor);
    }

    public function nombreDe(Model $modelo): string
    {
        $nombre = method_exists($modelo, 'nombreHistorial') ? $modelo->nombreHistorial() : null;
        $nombre ??= $modelo->getAttribute('nombre_completo') ?? $modelo->getAttribute('nombre') ?? $modelo->getAttribute('name');

        return trim((string) $nombre) !== '' ? (string) $nombre : AccionesHistorial::etiquetaTipo((string) AccionesHistorial::tipoDe($modelo)).' #'.$modelo->getKey();
    }

    public function esSecreto(Model|string $modelo, string $campo): bool
    {
        return (bool) preg_match(self::PATRON_SECRETO, $campo)
            || in_array($campo, $this->llamar($modelo, 'camposSecretos', []), true);
    }

    // ---------------------------------------------------------------------------------------------------------

    /**
     * Las migraciones anteriores a la spec 015 (y las pruebas que las recorren) crean registros antes de que exista
     * la tabla: en ese caso no hay dónde escribir y no se escribe.
     */
    private function tablaLista(): bool
    {
        if ($this->tablaLista === true) {
            return true;
        }

        return $this->tablaLista = \Illuminate\Support\Facades\Schema::hasTable('historial_cambios');
    }

    private function accionEfectiva(string $tipo, string $accion): string
    {
        for ($i = count($this->acciones) - 1; $i >= 0; $i--) {
            if (AccionesHistorial::existe($tipo, $this->acciones[$i])) {
                return $this->acciones[$i];
            }
        }

        return $accion;
    }

    /** @return array{0: string, 1: string, 2: ?User} */
    private function actor(?User $por): array
    {
        if ($this->actores) {
            $actor = end($this->actores);
            // Lo automático lo provoca alguien; el registro público y el sistema no tienen usuario propio.
            $usuario = $actor['actor'] === CambioHistorial::ACTOR_AUTOMATICO
                ? ($actor['usuario'] ?? $por ?? $this->usuarioDeLaPeticion())
                : $actor['usuario'];
            $nombre = $actor['actor'] === CambioHistorial::ACTOR_AUTOMATICO && $usuario
                ? $actor['nombre'].' · por '.$usuario->name
                : $actor['nombre'];

            return [$actor['actor'], $nombre, $usuario];
        }

        $usuario = $por ?? $this->usuarioDeLaPeticion();

        return $usuario
            ? [CambioHistorial::ACTOR_USUARIO, (string) $usuario->name, $usuario]
            : [CambioHistorial::ACTOR_SISTEMA, 'Sistema', null];
    }

    private function usuarioDeLaPeticion(): ?User
    {
        return app()->runningInConsole() && !app()->runningUnitTests() ? null : Controller::usuarioDeLaPeticion();
    }

    private function sedeDe(Model $modelo): ?int
    {
        if ($modelo instanceof \App\Models\Sede) {
            return $modelo->getKey();
        }

        $sede = $modelo->getAttribute('sede_id');

        return $sede === null ? null : (int) $sede;
    }

    /** @param array<string, mixed>|null $valores */
    private function sinSecretos(Model $modelo, ?array $valores): ?array
    {
        if ($valores === null) {
            return null;
        }

        foreach (array_keys($valores) as $campo) {
            if ($this->esSecreto($modelo, $campo)) {
                unset($valores[$campo]);
            }
        }

        return $valores;
    }

    /**
     * Campos secretos que el modelo cambió en su último guardado, para anotar «Clave cambiada» sin el valor.
     *
     * @return array<int, string>
     */
    private function camposSecretosCambiados(Model $modelo): array
    {
        return array_values(array_filter(
            array_keys($modelo->getChanges()),
            fn ($campo) => $this->esSecreto($modelo, $campo) && !in_array($campo, $this->llamar($modelo, 'camposIgnorados', []), true),
        ));
    }

    /**
     * Definición de los campos de un modelo: campo → [etiqueta, tipo?, 'sensible' => módulo?].
     *
     * @return array<string, array<int|string, mixed>>
     */
    private function definiciones(Model|string $modelo): array
    {
        $definiciones = [];
        foreach ($this->llamar($modelo, 'camposHistorial', []) as $campo => $definicion) {
            $definiciones[$campo] = is_array($definicion) ? $definicion : [$definicion];
        }

        return $definiciones;
    }

    private function llamar(Model|string $modelo, string $metodo, mixed $porDefecto): mixed
    {
        if (!method_exists($modelo, $metodo)) {
            return $porDefecto;
        }

        return is_string($modelo) ? (new $modelo())->{$metodo}() : $modelo->{$metodo}();
    }

    /** @param array<int|string, mixed> $definicion */
    private function legible(array $definicion, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '(vacío)';
        }

        $tipo = $definicion[1] ?? null;

        if ($tipo instanceof Closure) {
            return (string) ($tipo($valor) ?? '(vacío)');
        }

        if (is_array($tipo)) {
            return (string) ($tipo[$valor] ?? $valor);
        }

        return match ($tipo) {
            'booleano' => $valor ? 'Sí' : 'No',
            'fecha' => $this->fecha($valor, 'j M Y'),
            'fecha_hora' => $this->fecha($valor, 'j M Y, g:i a'),
            'dinero' => '$ '.number_format((float) $valor, 0, ',', '.'),
            'persona' => $this->nombreEn(\App\Models\Persona::class, $valor, fn ($p) => $p->nombre_completo),
            'usuario' => $this->nombreEn(User::class, $valor, fn ($u) => $u->name),
            'sede' => $this->nombreEn(\App\Models\Sede::class, $valor, fn ($s) => $s->nombre),
            'nivel' => $this->nombreEn(\App\Models\NivelFormacion::class, $valor, fn ($n) => $n->nombre),
            'rol' => $this->nombreDeRol((string) $valor),
            default => $valor instanceof DateTimeInterface ? $this->fecha($valor, 'j M Y, g:i a') : (is_bool($valor) ? ($valor ? 'Sí' : 'No') : (string) $valor),
        };
    }

    private function nombreDeRol(string $clave): string
    {
        try {
            return app(AutorizacionRoles::class)->nombres()[$clave] ?? $clave;
        } catch (\Throwable) {
            return $clave;
        }
    }

    private function fecha(mixed $valor, string $formato): string
    {
        try {
            return Carbon::parse($valor)->locale('es')->translatedFormat($formato);
        } catch (\Throwable) {
            return (string) $valor;
        }
    }

    /** @param class-string<Model> $clase */
    private function nombreEn(string $clase, mixed $id, Closure $nombre): string
    {
        $query = $clase::query()->withoutGlobalScopes();
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($clase), true)) {
            $query->withTrashed();
        }
        $modelo = $query->find($id);

        return $modelo ? (string) $nombre($modelo) : '#'.$id;
    }

    private function normalizar(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if ($valor instanceof DateTimeInterface) {
            return $valor->format('Y-m-d H:i:s');
        }
        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        return (string) $valor;
    }

    private function etiquetaTecnica(string $campo): string
    {
        return Str::ucfirst(str_replace('_', ' ', $campo));
    }
}
