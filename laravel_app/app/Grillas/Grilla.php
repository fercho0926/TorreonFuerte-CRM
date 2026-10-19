<?php

namespace App\Grillas;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as PaginadorContrato;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Definición de un listado estándar (ordenar, filtrar por columna, paginar y exportar).
 * Cada módulo hereda y declara sus columnas y su consulta base ya limitada por alcance.
 * Ver README → «Grillas» y specs/007-grillas-datatables.
 */
abstract class Grilla
{
    public const POR_PAGINA = [10, 25, 50, 100];

    public const POR_PAGINA_INICIAL = 25;

    /** Prefijo de rutas: se esperan `{id}.index`, `{id}.datos` y `{id}.exportar`. */
    abstract public function id(): string;

    /** @return array<int, Columna> */
    abstract protected function definirColumnas(): array;

    /**
     * Consulta base ya limitada por alcance de rol y sede, con los filtros propios del módulo (`extras`)
     * pero sin búsqueda general ni filtros por columna. Puede ser un `Builder` o una `Collection`.
     */
    abstract protected function base(?User $usuario, array $estado): Builder|Collection;

    /** Vista Blade de las celdas (recibe `$fila`, `$columna` y `$grilla`). */
    public function vistaCelda(): string
    {
        return $this->id().'._celda';
    }

    /** Parámetros propios del módulo que viajan con la grilla (por ejemplo `inactivos`, `sede_id`). */
    public function extras(): array
    {
        return [];
    }

    /** Mensaje e icono del estado vacío. */
    public function vacio(): array
    {
        return ['mensaje' => 'No se encontraron resultados.', 'icono' => 'fa-inbox'];
    }

    /** ¿Ofrece búsqueda general `q`? Los módulos que implementan `buscar()` la activan. */
    public function conBusqueda(): bool
    {
        return false;
    }

    /** Búsqueda general `q`. Por defecto no hace nada; los módulos con buscador la sobrescriben. */
    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        return $consulta;
    }

    /** ¿Puede el usuario abrir el listado (y por tanto pedir sus datos)? */
    public function puedeVer(?User $usuario): bool
    {
        return (bool) $usuario;
    }

    public function puedeExportar(?User $usuario): bool
    {
        return $this->puedeVer($usuario);
    }

    /** Clave de la columna por la que se ordena si el usuario no elige otra. */
    public function ordenInicial(): array
    {
        $primera = collect($this->definirColumnas())->first(fn (Columna $c) => $c->ordenable);

        return [$primera?->clave, 'asc'];
    }

    /** @return array<int, Columna> columnas de la tabla que ve este usuario, en orden */
    public function columnas(?User $usuario): array
    {
        return array_values(array_filter($this->definirColumnas(), fn (Columna $c) => $c->esVisible($usuario) && !$c->soloExportar));
    }

    /** @return array<int, Columna> columnas del CSV: las de la tabla que se exportan más las solo-exportación */
    public function columnasExportables(?User $usuario): array
    {
        return array_values(array_filter($this->definirColumnas(), fn (Columna $c) => $c->esVisible($usuario) && $c->exportable));
    }

    /** Estado de la grilla a partir de la petición: búsqueda, filtros por columna, orden y extras. */
    public function estado(Request $request, ?User $usuario = null): array
    {
        [$ordenClave, $ordenDir] = $this->ordenInicial();
        $estado = [
            'q' => trim((string) $request->input('q', '')),
            'orden' => (string) $request->input('orden', $ordenClave),
            'dir' => strtolower((string) $request->input('dir', $ordenDir)) === 'desc' ? 'desc' : 'asc',
        ];

        foreach ($this->definirColumnas() as $columna) {
            if ($columna->filtro) {
                $estado[$columna->clave] = trim((string) $request->input($columna->clave, ''));
            }
        }

        foreach ($this->extras() as $extra) {
            $estado[$extra] = $request->input($extra);
        }

        return $estado;
    }

    /** Filas por página permitidas; otro valor vuelve al inicial. */
    public function porPagina(mixed $valor): int
    {
        $valor = (int) $valor;

        return in_array($valor, self::POR_PAGINA, true) ? $valor : self::POR_PAGINA_INICIAL;
    }

    /** Consulta completa: base, búsqueda general, filtros por columna y orden. */
    public function consulta(?User $usuario, array $estado): Builder|Collection
    {
        $consulta = $this->base($usuario, $estado);
        $columnas = collect($this->columnas($usuario))->keyBy('clave');

        if (($estado['q'] ?? '') !== '') {
            $consulta = $this->buscar($consulta, $estado['q']);
        }

        foreach ($columnas as $clave => $columna) {
            $valor = $estado[$clave] ?? '';
            if (!$columna->filtro || $valor === '') {
                continue;
            }
            $consulta = $consulta instanceof Builder
                ? tap($consulta, fn ($q) => ($columna->filtroFn)($q, $valor))
                : $this->filtrarColeccion($consulta, $columna, $valor);
        }

        return $this->ordenar($consulta, $columnas, $estado);
    }

    public function total(?User $usuario, array $estado): int
    {
        $base = $this->base($usuario, $estado);

        return $base instanceof Builder ? (clone $base)->toBase()->getCountForPagination() : $base->count();
    }

    public function paginar(?User $usuario, array $estado, int $porPagina, int $pagina = 1): PaginadorContrato
    {
        $consulta = $this->consulta($usuario, $estado);

        if ($consulta instanceof Builder) {
            return $consulta->paginate($porPagina, ['*'], 'page', $pagina);
        }

        return new LengthAwarePaginator($consulta->forPage($pagina, $porPagina)->values(), $consulta->count(), $porPagina, $pagina, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
    }

    /**
     * Celdas HTML de cada fila, en el orden de las columnas visibles.
     *
     * @param  iterable<int, mixed>  $filas
     * @return array<int, array<int, string>>
     */
    public function filas(iterable $filas, ?User $usuario): array
    {
        $columnas = $this->columnas($usuario);
        $salida = [];

        foreach ($filas as $fila) {
            $salida[] = array_map(
                fn (Columna $c) => trim(view($this->vistaCelda(), ['fila' => $fila, 'columna' => $c->clave, 'grilla' => $this])->render()),
                $columnas
            );
        }

        return $salida;
    }

    /** Descarga CSV (Excel) con todas las filas que coinciden con el estado actual. */
    public function exportar(?User $usuario, array $estado, string $nombre): StreamedResponse
    {
        $columnas = $this->columnasExportables($usuario);
        $consulta = $this->consulta($usuario, $estado);

        return response()->streamDownload(function () use ($columnas, $consulta) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM para que Excel lea las tildes
            fputcsv($salida, array_map(fn (Columna $c) => $c->titulo, $columnas), ';');

            $escribir = function ($fila) use ($salida, $columnas) {
                fputcsv($salida, array_map(
                    fn (Columna $c) => self::neutralizarFormula($c->valor ? ($c->valor)($fila) : data_get($fila, $c->clave)),
                    $columnas
                ), ';');
            };

            if ($consulta instanceof Builder) {
                $consulta->chunk(500, fn ($lote) => $lote->each($escribir));
            } else {
                $consulta->each($escribir);
            }
            fclose($salida);
        }, $nombre.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita que Excel interprete como fórmula un texto que empieza por = + - @. */
    public static function neutralizarFormula(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }
        $texto = (string) $valor;

        return preg_match('/^[=+\-@]/', $texto) ? "'".$texto : $texto;
    }

    private function ordenar(Builder|Collection $consulta, Collection $columnas, array $estado): Builder|Collection
    {
        $dir = ($estado['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $columna = $columnas->get($estado['orden'] ?? '');
        if (!$columna || !$columna->ordenable) {
            $columna = $columnas->first(fn (Columna $c) => $c->ordenable);
            $dir = 'asc';
        }
        if (!$columna) {
            return $consulta;
        }

        if ($consulta instanceof Builder) {
            if ($columna->ordenFn) {
                ($columna->ordenFn)($consulta, $dir);
            }

            // Desempate estable para que la paginación no repita ni pierda filas.
            return $consulta->orderBy($consulta->getModel()->getQualifiedKeyName());
        }

        $valor = $columna->valor ?? fn ($fila) => data_get($fila, $columna->clave);

        return $consulta->sortBy(fn ($fila) => mb_strtolower((string) $valor($fila)), SORT_NATURAL, $dir === 'desc')->values();
    }

    private function filtrarColeccion(Collection $consulta, Columna $columna, string $valor): Collection
    {
        $leer = $columna->valor ?? fn ($fila) => data_get($fila, $columna->clave);

        return $consulta->filter(function ($fila) use ($leer, $columna, $valor) {
            $actual = mb_strtolower((string) $leer($fila));

            return $columna->filtro === Columna::FILTRO_LISTA
                ? $actual === mb_strtolower($valor)
                : str_contains($actual, mb_strtolower($valor));
        })->values();
    }
}
