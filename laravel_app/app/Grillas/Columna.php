<?php

namespace App\Grillas;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Services\SedeContext;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Una columna de una grilla estándar: cómo se titula, ordena, filtra, pinta y exporta.
 * Las columnas de orden y filtro son una lista blanca: el cliente solo elige entre las declaradas aquí.
 */
class Columna
{
    public const FILTRO_TEXTO = 'texto';

    public const FILTRO_LISTA = 'lista';

    public bool $ordenable = false;

    public ?Closure $ordenFn = null;

    public ?string $filtro = null;

    /** @var array<string, string> valor => etiqueta (filtro de lista) */
    public array $opciones = [];

    public ?Closure $filtroFn = null;

    public ?Closure $valor = null;

    public bool $exportable = true;

    public int $prioridad = 100;

    public bool $esAcciones = false;

    /** Solo aparece en el CSV, no en la tabla. */
    public bool $soloExportar = false;

    public ?Closure $visible = null;

    private function __construct(public readonly string $clave, public readonly string $titulo)
    {
    }

    public static function make(string $clave, string $titulo): static
    {
        return new static($clave, $titulo);
    }

    /** Columna «Sede» (relación `sede`): solo la ve el administrador cuando consulta todas las sedes. */
    public static function sede(string $tabla): static
    {
        return static::make('sede', 'Sede')
            ->ordena(fn (Builder $q, string $dir) => $q->orderBy(
                Sede::query()->withoutGlobalScopes()->select('sedes.nombre')->whereColumn('sedes.id', $tabla.'.sede_id'),
                $dir
            ))
            ->valor(fn ($fila) => $fila->getRelationValue('sede')?->nombre ?? '')
            ->visibleSi(fn (?User $u) => $u && $u->esAdmin() && app(SedeContext::class)->estaEnTodas())
            ->prioridad(50);
    }

    /** Columna de acciones por fila: nunca ordena, filtra ni se exporta, y no se pliega en teléfono. */
    public static function acciones(): static
    {
        $columna = static::make('acciones', 'Acciones');
        $columna->esAcciones = true;
        $columna->exportable = false;
        $columna->prioridad = 1;

        return $columna;
    }

    /** Ordena por una columna de la tabla o con una función `fn (Builder $q, string $dir)`. */
    public function ordena(string|Closure|null $campo = null): static
    {
        $this->ordenable = true;
        $campo ??= $this->clave;
        $this->ordenFn = $campo instanceof Closure
            ? $campo
            : fn (Builder $q, string $dir) => $q->orderBy($campo, $dir);

        return $this;
    }

    /** Ordena por el apellido de un miembro relacionada por `$tabla.$llave` (por ejemplo `celulas.lider_id`). */
    public function ordenaPersona(string $tabla, string $llave): static
    {
        return $this->ordena(fn (Builder $q, string $dir) => $q->orderBy(
            Persona::query()->withoutGlobalScopes()->from('personas as p_orden')->select('p_orden.apellidos')->whereColumn('p_orden.id', $tabla.'.'.$llave),
            $dir
        ));
    }

    /** Filtro de texto por nombre o documento de un miembro relacionada (`whereHas` sobre la relación). */
    public function filtraPersona(string $relacion): static
    {
        return $this->filtraTexto(fn (Builder $q, string $valor) => $q->whereHas($relacion, fn ($p) => $p->buscar($valor)));
    }

    /** Filtro de texto: `LIKE` sobre una o varias columnas, o una función `fn (Builder $q, string $valor)`. */
    public function filtraTexto(string|array|Closure|null $campos = null): static
    {
        $this->filtro = self::FILTRO_TEXTO;
        $campos ??= $this->clave;
        $this->filtroFn = $campos instanceof Closure ? $campos : function (Builder $q, string $valor) use ($campos) {
            $like = '%'.$valor.'%';
            $q->where(function (Builder $q) use ($campos, $like) {
                foreach ((array) $campos as $campo) {
                    $q->orWhere($campo, 'like', $like);
                }
            });
        };

        return $this;
    }

    /** Filtro de lista: igualdad con una de las opciones (valor => etiqueta), o una función personalizada. */
    public function filtraLista(array $opciones, string|Closure|null $campo = null): static
    {
        $this->filtro = self::FILTRO_LISTA;
        $this->opciones = $opciones;
        $campo ??= $this->clave;
        $this->filtroFn = $campo instanceof Closure ? $campo : fn (Builder $q, string $valor) => $q->where($campo, $valor);

        return $this;
    }

    /** Valor escalar de la fila: se usa para ordenar y filtrar colecciones y como valor del CSV. */
    public function valor(Closure $valor): static
    {
        $this->valor = $valor;

        return $this;
    }

    /** Columna que solo se incluye en la exportación (por ejemplo, el correo). */
    public function soloExportar(): static
    {
        $this->soloExportar = true;

        return $this;
    }

    public function sinExportar(): static
    {
        $this->exportable = false;

        return $this;
    }

    /** Prioridad en teléfono (1 = nunca se pliega; números mayores se pliegan antes). */
    public function prioridad(int $prioridad): static
    {
        $this->prioridad = $prioridad;

        return $this;
    }

    public function visibleSi(Closure $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function esVisible(?User $usuario): bool
    {
        return $this->visible === null || (bool) ($this->visible)($usuario);
    }
}
