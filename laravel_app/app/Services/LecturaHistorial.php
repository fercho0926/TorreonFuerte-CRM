<?php

namespace App\Services;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\User;
use App\Support\AccionesHistorial;
use App\Support\Modulos;
use App\Support\Niveles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Lectura del historial (spec 015): la pestaña «Histórico» de cada ficha y lo que cada usuario puede ver dentro de
 * una entrada. La autorización de la pestaña es la de la ficha: quien llega aquí ya puede ver el registro (RF-021).
 *
 * Excepción declarada al aislamiento por sede (RF-023, clarificación 3): el histórico de un miembro trasladado
 * incluye las entradas de sus registros de origen en otras sedes. Solo se abre esa cadena de registros del mismo miembro.
 */
class LecturaHistorial
{
    public const POR_PAGINA = 20;

    /** @var array<string, bool> */
    private array $visibles = [];

    public function __construct(private AlcanceService $alcance)
    {
    }

    /**
     * Filtros válidos de la petición; lo que no sea válido se ignora (nunca un error).
     *
     * @return array{desde: ?string, hasta: ?string, usuario: ?string, accion: ?string}
     */
    public function filtros(Request $request): array
    {
        $fecha = function (?string $valor): ?string {
            if (!$valor || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                return null;
            }
            try {
                return Carbon::createFromFormat('Y-m-d', $valor)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        };
        $usuario = (string) $request->query('usuario', '');
        $accion = (string) $request->query('accion', '');

        return [
            'desde' => $fecha($request->query('desde')),
            'hasta' => $fecha($request->query('hasta')),
            'usuario' => preg_match('/^(\d+|qr|automatico|sistema)$/', $usuario) ? $usuario : null,
            'accion' => preg_match('/^[a-z_]{1,40}$/', $accion) ? $accion : null,
        ];
    }

    /** Lista única de un registro (todas las fichas salvo la del miembro). */
    public function deRegistro(Model $sujeto, array $filtros, int $pagina = 1): LengthAwarePaginator
    {
        $tipo = (string) AccionesHistorial::tipoDe($sujeto);

        return $this->filtrar(CambioHistorial::query()->de($tipo, (int) $sujeto->getKey()), $filtros, true)
            ->recientes()
            ->paginate(self::POR_PAGINA, ['*'], 'pagina', max(1, $pagina))
            ->withQueryString();
    }

    /**
     * Secciones del histórico del miembro (clarificación 4), cada una con su contador y su paginador.
     *
     * @param  array<string, int>  $paginas  categoría → página
     * @return array<string, array{titulo: string, total: int, entradas: LengthAwarePaginator}>
     */
    public function seccionesDePersona(Persona $persona, array $filtros, array $paginas = []): array
    {
        $ids = $this->cadenaDe($persona);
        $base = fn () => $this->filtrar(CambioHistorial::query()->de('persona', $ids), $filtros, false);
        $totales = $base()->selectRaw('categoria, count(*) as total')->groupBy('categoria')->pluck('total', 'categoria');
        $secciones = [];

        foreach (AccionesHistorial::CATEGORIAS as $categoria => $titulo) {
            $secciones[$categoria] = [
                'titulo' => $titulo,
                'total' => (int) ($totales[$categoria] ?? 0),
                'entradas' => $base()->where('categoria', $categoria)->recientes()
                    ->paginate(self::POR_PAGINA, ['*'], 'pagina_'.$categoria, max(1, (int) ($paginas[$categoria] ?? 1)))
                    ->withQueryString()
                    ->fragment('seccion-'.$categoria),
            ];
        }

        return $secciones;
    }

    /**
     * El miembro y los registros de origen de sus traslados (A → B → C), del más reciente al más antiguo.
     *
     * @return array<int, int>
     */
    public function cadenaDe(Persona $persona): array
    {
        $ids = [(int) $persona->id];
        $siguiente = $persona->trasladada_desde_persona_id;

        while ($siguiente && !in_array((int) $siguiente, $ids, true) && count($ids) < 20) {
            $ids[] = (int) $siguiente;
            $siguiente = Persona::query()->withoutGlobalScopes()->withTrashed()->whereKey($siguiente)->value('trasladada_desde_persona_id');
        }

        return $ids;
    }

    /** Usuarios que aparecen en un conjunto de entradas, para el filtro (id → nombre). */
    public function actoresDe(string $tipo, array $ids): array
    {
        return CambioHistorial::query()->de($tipo, $ids)->whereNotNull('user_id')
            ->select('user_id', 'actor_nombre')->distinct()->orderBy('actor_nombre')->get()
            ->mapWithKeys(fn ($c) => [(string) $c->user_id => preg_replace('/^Automático · por /', '', $c->actor_nombre)])->all();
    }

    /** ¿Puede el usuario abrir la ficha de un registro nombrado en una entrada? */
    public function puedeEnlazar(?User $user, ?string $tipo, ?int $id): bool
    {
        if (!$user || !$tipo || !$id || !isset(AccionesHistorial::CLASES[$tipo]) || !$this->ruta($tipo, $id)) {
            return false;
        }

        $sede = app(SedeContext::class)->sedeId() ?? 'todas';

        return $this->visibles["{$user->id}:{$sede}:{$tipo}:{$id}"] ??= $this->calcularVisible($user, $tipo, $id);
    }

    /** Dirección de la ficha de un registro (o de su pestaña «Histórico»). */
    public function ruta(string $tipo, int $id, bool $historico = false): ?string
    {
        $parametros = $historico ? ['pestana' => 'historico'] : [];

        return match ($tipo) {
            'persona' => route('personas.show', [$id] + $parametros),
            'celula' => route('celulas.show', [$id] + $parametros),
            'ministerio' => route('ministerios.show', [$id] + $parametros),
            'evento' => route('eventos.show', [$id] + $parametros),
            'encuentro' => route('encuentros.show', [$id] + $parametros),
            'nivel' => route('niveles.edit', [$id] + $parametros),
            'sede' => route('sedes.show', [$id] + $parametros),
            'usuario' => route('users.edit', [$id] + $parametros),
            'rol' => ($clave = \App\Models\Rol::query()->whereKey($id)->value('clave')) ? route('roles.show', [$clave] + $parametros) : null,
            default => null,
        };
    }

    /** @var array<int, ?string> */
    private array $sedes = [];

    /** Nombre de una sede (una consulta por sede y petición). */
    public function nombreSede(int $id): ?string
    {
        return $this->sedes[$id] ??= \App\Models\Sede::query()->whereKey($id)->value('nombre');
    }

    /** ¿Puede el usuario ver el valor de un campo marcado como sensible de otro módulo? */
    public function puedeVerSensible(?User $user, ?string $modulo): bool
    {
        return $modulo === null || ($user !== null && $user->nivelEn($modulo) >= Niveles::LECTURA);
    }

    /** @param array{desde: ?string, hasta: ?string, usuario: ?string, accion: ?string} $filtros */
    private function filtrar(Builder $query, array $filtros, bool $conAccion): Builder
    {
        if ($filtros['desde'] ?? null) {
            $query->where('created_at', '>=', $filtros['desde'].' 00:00:00');
        }
        if ($filtros['hasta'] ?? null) {
            $query->where('created_at', '<=', $filtros['hasta'].' 23:59:59');
        }
        if ($usuario = $filtros['usuario'] ?? null) {
            ctype_digit($usuario) ? $query->where('user_id', (int) $usuario) : $query->where('actor', $usuario);
        }
        if ($conAccion && ($accion = $filtros['accion'] ?? null)) {
            $query->where('accion', $accion);
        }

        return $query;
    }

    private function calcularVisible(User $user, string $tipo, int $id): bool
    {
        $modulo = AccionesHistorial::catalogo()[$tipo]['modulo'];

        if (in_array($modulo, ['sedes', 'roles'], true)) {
            return $user->esAdmin();
        }

        if (!Modulos::existe($modulo) || $user->nivelEn($modulo) < Niveles::LECTURA) {
            return false;
        }

        return match ($tipo) {
            'persona' => $this->alcance->personasVisibles($user)->whereKey($id)->exists(),
            'celula' => $this->alcance->celulasVisibles($user)->whereKey($id)->exists(),
            default => AccionesHistorial::CLASES[$tipo]::query()->whereKey($id)->exists(),
        };
    }
}
