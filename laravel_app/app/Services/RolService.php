<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\CambioHistorial;
use App\Models\RolPermiso;
use App\Models\User;
use App\Support\Modulos;
use App\Support\Niveles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Altas, cambios y bajas de roles (spec 013) con su historial. Todo ocurre en una transacción y, al terminar,
 * se descarta lo resuelto en la petición para que los permisos nuevos se apliquen de inmediato.
 */
class RolService
{
    public function __construct(private AutorizacionRoles $autorizacion)
    {
    }

    /**
     * @param  array{nombre: string, descripcion?: ?string, alcance: string, asignable?: bool}  $datos
     * @param  array<string, int>  $niveles  módulo → nivel
     */
    public function crear(array $datos, array $niveles, ?User $por, string $accion = 'creado'): Rol
    {
        return DB::transaction(function () use ($datos, $niveles, $por, $accion) {
            $rol = new Rol([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'alcance' => $datos['alcance'],
                'asignable' => (bool) ($datos['asignable'] ?? false),
            ]);
            $rol->clave = $this->claveUnica($datos['nombre']);
            $rol->es_sistema = false;
            $rol->save();

            $this->guardarNiveles($rol, $niveles);

            $this->registrar($rol, $accion, $por, null, $this->instantanea($rol, $niveles));
            $this->autorizacion->olvidar();

            return $rol->fresh(['permisos']);
        });
    }

    /**
     * Cambios de un rol respecto a lo enviado: solo lo que cambia, en el orden nombre, descripción, alcance,
     * asignable y permisos (estos últimos por módulo).
     *
     * @param  array{nombre: string, descripcion?: ?string, alcance: string, asignable?: bool}  $datos
     * @param  array<string, int>  $niveles
     * @return array{antes: array<string, mixed>, despues: array<string, mixed>}
     */
    public function diferencias(Rol $rol, array $datos, array $niveles): array
    {
        $actual = $this->instantanea($rol, $rol->niveles());
        $nuevo = $this->instantanea(
            (new Rol)->forceFill([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'alcance' => $datos['alcance'],
                'asignable' => (bool) ($datos['asignable'] ?? false),
            ]),
            $this->normalizarNiveles($niveles),
        );

        $antes = $despues = [];
        foreach (['nombre', 'descripcion', 'alcance', 'asignable'] as $campo) {
            if ($actual[$campo] !== $nuevo[$campo]) {
                $antes[$campo] = $actual[$campo];
                $despues[$campo] = $nuevo[$campo];
            }
        }

        foreach (Modulos::claves() as $modulo) {
            if (($actual['permisos'][$modulo] ?? 0) !== ($nuevo['permisos'][$modulo] ?? 0)) {
                $antes['permisos'][$modulo] = $actual['permisos'][$modulo] ?? 0;
                $despues['permisos'][$modulo] = $nuevo['permisos'][$modulo] ?? 0;
            }
        }

        return ['antes' => $antes, 'despues' => $despues];
    }

    /**
     * Guarda los cambios de un rol y los deja en su historial (solo si algo cambió). El rol Administrador no se edita.
     *
     * @param  array{nombre: string, descripcion?: ?string, alcance: string, asignable?: bool}  $datos
     * @param  array<string, int>  $niveles
     */
    public function actualizar(Rol $rol, array $datos, array $niveles, ?User $por): Rol
    {
        if ($rol->esAdministrador()) {
            throw new \DomainException('Rol protegido: el Administrador no se puede modificar.');
        }

        return DB::transaction(function () use ($rol, $datos, $niveles, $por) {
            $diferencias = $this->diferencias($rol, $datos, $niveles);

            if ($diferencias['antes'] === []) {
                return $rol;
            }

            $rol->fill([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'alcance' => $datos['alcance'],
                'asignable' => (bool) ($datos['asignable'] ?? false),
            ])->save();

            $this->guardarNiveles($rol, $niveles);
            $this->registrar($rol, 'editado', $por, $diferencias['antes'], $diferencias['despues']);
            $this->autorizacion->olvidar();

            return $rol->fresh(['permisos']);
        });
    }

    /** Elimina un rol sin usuarios que no sea de sistema; su historial se conserva. */
    public function eliminar(Rol $rol, ?User $por): void
    {
        if ($rol->esAdministrador()) {
            throw new \DomainException('Rol protegido: el Administrador no se puede eliminar.');
        }

        if ($rol->es_sistema) {
            throw new \DomainException('«'.$rol->nombre.'» es un rol del sistema y no se puede eliminar.');
        }

        $usuarios = $this->usuariosAfectados($rol);
        if ($usuarios > 0) {
            throw new \DomainException($usuarios.($usuarios === 1 ? ' usuario lo tiene' : ' usuarios lo tienen').'; reasígnalos a otro rol antes de eliminarlo.');
        }

        DB::transaction(function () use ($rol, $por) {
            $this->registrar($rol, 'eliminado', $por, $this->instantanea($rol, $rol->niveles()), null);
            $rol->delete();
            $this->autorizacion->olvidar();
        });
    }

    /** Cuántos usuarios tienen el rol (en todas las sedes). */
    public function usuariosAfectados(Rol $rol): int
    {
        return $rol->usuarios()->count();
    }

    /**
     * Clave estable en minúsculas, sin acentos ni espacios; si ya existe, se le agrega un número.
     */
    public function claveUnica(string $nombre): string
    {
        $base = Str::slug(Str::ascii($nombre), '_') ?: 'rol';
        $base = Str::limit($base, 36, '');
        $clave = $base;
        $n = 2;

        while (Rol::where('clave', $clave)->exists()) {
            $clave = $base.'_'.$n++;
        }

        return $clave;
    }

    /**
     * Lleva cada módulo del catálogo a un nivel válido (0 si falta; máximo del módulo si se pasa).
     *
     * @param  array<string, int>  $niveles
     * @return array<string, int>
     */
    protected function normalizarNiveles(array $niveles): array
    {
        $salida = [];
        foreach (Modulos::claves() as $modulo) {
            $salida[$modulo] = min((int) ($niveles[$modulo] ?? Niveles::SIN_ACCESO), Modulos::nivelMaximo($modulo));
        }

        return $salida;
    }

    /** @param array<string, int> $niveles */
    protected function guardarNiveles(Rol $rol, array $niveles): void
    {
        foreach (Modulos::claves() as $modulo) {
            $nivel = min((int) ($niveles[$modulo] ?? Niveles::SIN_ACCESO), Modulos::nivelMaximo($modulo));

            RolPermiso::updateOrCreate(['rol_id' => $rol->id, 'modulo' => $modulo], ['nivel' => $nivel]);
        }
    }

    /**
     * @param  array<string, mixed>|null  $antes
     * @param  array<string, mixed>|null  $despues
     */
    protected function registrar(Rol $rol, string $accion, ?User $por, ?array $antes, ?array $despues): ?CambioHistorial
    {
        return app(Historial::class)->registrar($rol, $accion, $antes, $despues, $por, [
            'clave' => $rol->clave,
            'lineas' => self::lineas($accion, $antes ?? [], $despues ?? []),
        ]);
    }

    /**
     * Qué cambió en un rol, en frases cortas para el historial («Eventos: Lectura → Escritura»).
     *
     * @param  array<string, mixed>  $antes
     * @param  array<string, mixed>  $despues
     * @return array<int, string>
     */
    public static function lineas(string $accion, array $antes, array $despues): array
    {
        $nivel = fn ($valor) => Niveles::ETIQUETAS[(int) $valor] ?? (string) $valor;
        $alcance = fn ($valor) => Niveles::ALCANCES[$valor] ?? (string) $valor;
        $si = fn ($valor) => $valor ? 'Sí' : 'No';

        if ($accion === 'eliminado') {
            return ['Rol eliminado.'];
        }

        if (in_array($accion, ['creado', 'duplicado'], true)) {
            $lineas = ['Alcance: '.$alcance($despues['alcance'] ?? '')];
            foreach (($despues['permisos'] ?? []) as $modulo => $valor) {
                if ((int) $valor > 0) {
                    $lineas[] = Modulos::etiqueta($modulo).': '.$nivel($valor);
                }
            }

            return $lineas;
        }

        $lineas = [];
        foreach (['nombre' => 'Nombre', 'descripcion' => 'Descripción'] as $campo => $etiqueta) {
            if (array_key_exists($campo, $despues)) {
                $lineas[] = $etiqueta.': '.($antes[$campo] ?? '—').' → '.($despues[$campo] ?? '—');
            }
        }
        if (array_key_exists('alcance', $despues)) {
            $lineas[] = 'Alcance: '.$alcance($antes['alcance'] ?? '').' → '.$alcance($despues['alcance']);
        }
        if (array_key_exists('asignable', $despues)) {
            $lineas[] = 'Asignable: '.$si($antes['asignable'] ?? false).' → '.$si($despues['asignable']);
        }
        foreach (($despues['permisos'] ?? []) as $modulo => $valor) {
            $lineas[] = Modulos::etiqueta($modulo).': '.$nivel($antes['permisos'][$modulo] ?? 0).' → '.$nivel($valor);
        }

        return $lineas;
    }

    /**
     * @param  array<string, int>  $niveles
     * @return array<string, mixed>
     */
    protected function instantanea(Rol $rol, array $niveles): array
    {
        return [
            'nombre' => $rol->nombre,
            'descripcion' => $rol->descripcion,
            'alcance' => $rol->alcance,
            'asignable' => $rol->asignable,
            'permisos' => array_map(fn ($nivel) => (int) $nivel, array_intersect_key($niveles, array_flip(Modulos::claves()))),
        ];
    }
}
