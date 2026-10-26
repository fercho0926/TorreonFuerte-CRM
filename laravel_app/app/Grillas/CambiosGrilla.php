<?php

namespace App\Grillas;

use App\Models\CambioHistorial;
use App\Models\User;
use App\Services\AlcanceService;
use App\Services\SedeContext;
use App\Support\AccionesHistorial;
use App\Support\Modulos;
use App\Support\Niveles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * «Control de cambios» (spec 015, historia 4): todo lo que cambió en la plataforma dentro del alcance del usuario.
 *
 * Excepción declarada al principio III: esta grilla **no se exporta** (clarificación 1, RF-020); no tiene ruta de
 * exportación y `puedeExportar()` siempre es falso. El resto del contrato de la grilla estándar se cumple.
 */
class CambiosGrilla extends Grilla
{
    public function id(): string
    {
        return 'control-cambios';
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function extras(): array
    {
        return ['desde', 'hasta'];
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay cambios con estos filtros.', 'icono' => 'fa-history'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario !== null && $usuario->nivelEn(Modulos::CONTROL_CAMBIOS) >= Niveles::LECTURA;
    }

    public function puedeExportar(?User $usuario): bool
    {
        return false;
    }

    public function ordenInicial(): array
    {
        return ['fecha', 'desc'];
    }

    protected function definirColumnas(): array
    {
        $modulos = AccionesHistorial::modulos();
        $acciones = collect(AccionesHistorial::catalogo())
            ->flatMap(fn ($tipo) => array_map(fn ($a) => $a[0], $tipo['acciones']))
            ->unique()->sort()->all();

        return [
            Columna::make('fecha', 'Fecha y hora')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy('historial_cambios.created_at', $dir)->orderBy('historial_cambios.id', $dir))
                ->valor(fn (CambioHistorial $c) => $c->created_at->format('Y-m-d H:i'))
                ->prioridad(1),
            Columna::make('usuario', 'Usuario')
                ->ordena('historial_cambios.actor_nombre')
                ->filtraTexto('historial_cambios.actor_nombre')
                ->valor(fn (CambioHistorial $c) => $c->actor_nombre)
                ->prioridad(2),
            Columna::make('sede', 'Sede')
                ->valor(fn (CambioHistorial $c) => $c->sede_id ? (string) app(\App\Services\LecturaHistorial::class)->nombreSede((int) $c->sede_id) : '—')
                ->visibleSi(fn () => app(SedeContext::class)->estaEnTodas())
                ->prioridad(5),
            Columna::make('modulo', 'Módulo')
                ->ordena('historial_cambios.modulo')
                ->filtraLista($modulos, 'historial_cambios.modulo')
                ->valor(fn (CambioHistorial $c) => AccionesHistorial::etiquetaModulo($c->modulo))
                ->prioridad(4),
            Columna::make('accion', 'Acción')
                ->filtraLista(array_combine($acciones, $acciones), function (Builder $q, string $etiqueta) {
                    $q->where(function (Builder $q) use ($etiqueta) {
                        foreach (AccionesHistorial::catalogo() as $tipo => $definicion) {
                            foreach ($definicion['acciones'] as $clave => $datos) {
                                if ($datos[0] === $etiqueta) {
                                    $q->orWhere(fn ($w) => $w->where('historial_cambios.sujeto_tipo', $tipo)->where('historial_cambios.accion', $clave));
                                }
                            }
                        }
                    });
                })
                ->valor(fn (CambioHistorial $c) => $c->etiqueta)
                ->prioridad(2),
            Columna::make('registro', 'Registro')
                ->ordena('historial_cambios.sujeto_nombre')
                ->filtraTexto(['historial_cambios.sujeto_nombre', 'historial_cambios.relacionado_nombre'])
                ->valor(fn (CambioHistorial $c) => $c->sujeto_nombre)
                ->prioridad(3),
            Columna::make('detalle', 'Detalle')->prioridad(6),
        ];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        $consulta = CambioHistorial::query();

        // La tabla no lleva el filtro global de sede: aquí se aplica el de la petición (RF-023).
        $contexto = app(SedeContext::class);
        if (!$contexto->estaEnTodas()) {
            $sede = $contexto->sedeId();
            $sede === null
                ? $consulta->whereRaw('1 = 0')
                : $consulta->where(fn (Builder $q) => $q->where('historial_cambios.sede_id', $sede)
                    ->orWhere(fn (Builder $g) => $g->whereNull('historial_cambios.sede_id')->when(!$usuario?->esAdmin(), fn ($n) => $n->whereRaw('1 = 0'))));
        }

        // Sin acceso total, solo lo que nombra a miembros y células de su alcance.
        if ($usuario && !$usuario->tieneAccesoTotal()) {
            $alcance = app(AlcanceService::class);
            $personas = $alcance->personasVisibles($usuario)->select('personas.id');
            $celulas = $alcance->celulasVisibles($usuario)->select('celulas.id');
            $consulta->where(fn (Builder $q) => $q
                ->where(fn ($p) => $p->where('historial_cambios.sujeto_tipo', 'persona')->whereIn('historial_cambios.sujeto_id', $personas))
                ->orWhere(fn ($c) => $c->where('historial_cambios.sujeto_tipo', 'celula')->whereIn('historial_cambios.sujeto_id', $celulas)));
        }

        foreach (['desde' => '>=', 'hasta' => '<='] as $clave => $operador) {
            $valor = (string) ($estado[$clave] ?? '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2})?$/', $valor)) {
                $momento = str_replace('T', ' ', $valor);
                $momento .= strlen($momento) === 10 ? ($operador === '>=' ? ' 00:00:00' : ' 23:59:59') : ($operador === '>=' ? ':00' : ':59');
                $consulta->where('historial_cambios.created_at', $operador, $momento);
            }
        }

        return $consulta;
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        $like = '%'.str_replace(['%', '_'], '', $q).'%';

        return $consulta->where(fn (Builder $w) => $w
            ->where('historial_cambios.sujeto_nombre', 'like', $like)
            ->orWhere('historial_cambios.relacionado_nombre', 'like', $like)
            ->orWhere('historial_cambios.actor_nombre', 'like', $like)
            ->orWhere('historial_cambios.detalle', 'like', $like));
    }
}
