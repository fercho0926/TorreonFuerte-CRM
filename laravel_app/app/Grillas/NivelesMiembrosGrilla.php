<?php

namespace App\Grillas;

use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;
use App\Services\CaminoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Miembros por nivel (spec 014): quienes lo tienen como nivel actual (`actual`), quienes ya lo completaron aunque vayan
 * más adelante (`completaron`) y a quienes les toca como siguiente (`siguiente`). `nivel` = id del nivel o `sin` (sin iniciar).
 */
class NivelesMiembrosGrilla extends Grilla
{
    public const VISTAS = ['actual' => 'Nivel actual', 'completaron' => 'Completaron este nivel', 'siguiente' => 'Les toca este nivel'];

    /** Nivel elegido en la petición actual (lo fija `base`); lo usa la columna «Completó el nivel el». */
    private ?int $nivelId = null;

    private ?Collection $niveles = null;

    public function __construct(private AlcanceService $alcance)
    {
    }

    public function id(): string
    {
        return 'niveles.miembros';
    }

    public function vistaCelda(): string
    {
        return 'niveles._celda';
    }

    public function archivo(): string
    {
        return 'miembros-por-nivel';
    }

    public function extras(): array
    {
        return ['nivel', 'vista', 'inactivos'];
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay miembros en esta lista.', 'icono' => 'fa-route'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario && $usuario->nivelEn('niveles') >= 1;
    }

    /** Niveles activos del plan, en orden (una consulta por petición). */
    public function niveles(): Collection
    {
        return $this->niveles ??= NivelFormacion::query()->activos()->ordenados()->get();
    }

    /** Vista pedida: `actual` si no es una de las conocidas. */
    public static function vistaDe(mixed $valor): string
    {
        return is_string($valor) && array_key_exists($valor, self::VISTAS) ? $valor : 'actual';
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy('personas.apellidos', $dir)->orderBy('personas.nombres', $dir))
                ->filtraTexto(fn (Builder $q, string $valor) => $q->buscar($valor))
                ->valor(fn (Persona $p) => $p->nombre_completo)
                ->prioridad(1),
            Columna::make('nivel', 'Nivel actual')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy(
                    DB::table('avances_nivel as a')->join('niveles_formacion as n', 'n.id', '=', 'a.nivel_id')
                        ->where('n.activo', true)->whereColumn('a.persona_id', 'personas.id')->selectRaw('coalesce(max(n.posicion), 0)'),
                    $dir
                ))
                ->valor(fn (Persona $p) => app(CaminoService::class)->camino($p, $this->niveles(), false)['actual']?->nombre ?? 'Sin iniciar')
                ->prioridad(2),
            Columna::make('celular', 'Celular')
                ->ordena('personas.celular')
                ->filtraTexto(fn (Builder $q, string $valor) => $q->where('personas.celular', 'like', '%'.preg_replace('/\D+/', '', $valor).'%'))
                ->valor(fn (Persona $p) => $p->celular)
                ->prioridad(3),
            Columna::make('completo', 'Completó el nivel el')
                ->valor(fn (Persona $p) => $this->fechaDelNivel($p)?->toDateString())
                ->prioridad(4),
            Columna::make('correo', 'Correo')->valor(fn (Persona $p) => $p->email)->soloExportar(),
            Columna::make('ultimo', 'Último nivel completado el')
                ->valor(fn (Persona $p) => $p->avances->max('fecha')?->toDateString())
                ->soloExportar(),
            Columna::sede('personas'),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        $nivel = $estado['nivel'] ?? null;
        $vista = self::vistaDe($estado['vista'] ?? null);
        $incluirInactivos = !empty($estado['inactivos']);

        $consulta = $this->alcance->personasVisibles($usuario)
            ->with(['avances', 'sede'])
            ->when(!$incluirInactivos, fn ($q) => $q->activas());

        if ($nivel === 'sin') {
            $this->nivelId = null;

            return $consulta->sinNivel();
        }

        if (!filled($nivel)) {
            $this->nivelId = null;

            return $consulta;
        }

        $this->nivelId = (int) $nivel;
        $objetivo = NivelFormacion::query()->find($this->nivelId);

        if (!$objetivo) {
            return $consulta->whereRaw('1 = 0');
        }

        return match ($vista) {
            'completaron' => $consulta->whereHas('avances', fn ($a) => $a->where('nivel_id', $objetivo->id)),
            'siguiente' => $consulta
                ->whereDoesntHave('avances', fn ($a) => $a->where('nivel_id', $objetivo->id))
                // Les toca este nivel: tienen hechos todos los niveles activos anteriores.
                ->whereNotExists(function ($sub) use ($objetivo) {
                    $sub->selectRaw('1')->from('niveles_formacion as n')
                        ->where('n.activo', true)->where('n.posicion', '<', $objetivo->posicion)
                        ->whereNotExists(fn ($a) => $a->selectRaw('1')->from('avances_nivel as av')->whereColumn('av.persona_id', 'personas.id')->whereColumn('av.nivel_id', 'n.id'));
                }),
            default => $consulta->enNivel($objetivo->id),
        };
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        return $consulta->buscar($q);
    }

    /** Fecha en que el miembro completó el nivel elegido (si lo completó). */
    public function fechaDelNivel(Persona $persona): ?\Illuminate\Support\Carbon
    {
        if ($this->nivelId === null) {
            return null;
        }

        return $persona->avances->firstWhere('nivel_id', $this->nivelId)?->fecha;
    }
}
