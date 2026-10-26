<?php

namespace App\Services;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Arma el organigrama de la sede efectiva: sede (con sus pastores) → líderes → miembros, más un
 * grupo «Sin líder asignado». Todo pasa por el filtro global de sede y por el alcance del rol.
 *
 * Estados: 'arbol' y 'vacio'.
 *
 * Nodo: tipo (sede|lider|miembro|sin_lider), id, titulo, rol, pastores [{id, nombre, puedeVerFicha}],
 * sinPastor, puedeVerFicha, hijos, totalHijos, etiquetaHijos.
 */
class OrganigramaService
{
    private const COLUMNAS = ['personas.id', 'personas.nombres', 'personas.apellidos', 'personas.lider_id'];

    public function __construct(private SedeContext $contexto)
    {
    }

    /**
     * Organigrama de una sede concreta (la de la pestaña del detalle de sede), sin depender de la
     * sede activa del administrador. Solo para roles de acceso total.
     *
     * @return array{estado: string, raiz: ?array<string, mixed>}
     */
    public function construir(Sede $sede, ?User $user): array
    {
        if (!$user || !$user->tieneAccesoTotal()) {
            return $this->resultado('vacio');
        }

        return $this->contexto->conSede($sede->id, fn () => $this->armar($sede));
    }

    /** @return array{estado: string, raiz: ?array<string, mixed>} */
    private function armar(Sede $sede): array
    {
        $verFicha = fn (int $id): bool => true;

        // El pastor y la pastora son la cabeza (spec 016): van en la cabecera, no como un líder más.
        $pastorales = array_values(array_filter([$sede->pastor_persona_id, $sede->pastora_persona_id]));

        $lideres = Persona::query()->lideres()->activas()->whereNotIn('personas.id', $pastorales)->get(self::COLUMNAS);
        $miembros = $this->miembrosPorLider($lideres->pluck('id')->all());

        $hijos = $lideres->sortBy(fn ($l) => $this->clave($l))->map(
            fn (Persona $l) => $this->nodoLider($l, $miembros[$l->id] ?? [], $verFicha)
        )->values()->all();

        // Los miembros que no son líderes y dependen directamente de los pastores.
        $conPastores = $pastorales
            ? Persona::query()->activas()->where('es_lider', false)->whereIn('personas.lider_id', $pastorales)->get(self::COLUMNAS)
            : collect();
        if ($conPastores->isNotEmpty()) {
            $hijos[] = $this->nodo('sin_lider', null, 'A cargo de los pastores', 'Grupo', false,
                $this->nodosMiembros($conPastores, $verFicha), 'miembro');
        }

        // Los pastores ya están en la cabecera: no cuentan como miembros sin líder.
        $sinLider = Persona::query()->sinLider()
            ->whereNotIn('personas.id', array_filter([$sede->pastor_persona_id, $sede->pastora_persona_id]))
            ->get(self::COLUMNAS);
        if ($sinLider->isNotEmpty()) {
            $hijos[] = $this->nodo('sin_lider', null, 'Sin líder asignado', 'Grupo', false,
                $this->nodosMiembros($sinLider, $verFicha), 'miembro');
        }

        $pastores = collect([$sede->pastor, $sede->pastora])->filter()->unique('id')->map(fn (Persona $p) => [
            'id' => $p->id, 'nombre' => $p->nombre_completo, 'puedeVerFicha' => $verFicha($p->id),
        ])->values()->all();

        $raiz = $this->nodo('sede', null, $sede->nombre, 'Sede', false, $hijos, 'lider');
        $lideresTotal = $lideres->count();
        $raiz['etiquetaHijos'] = $lideresTotal.($lideresTotal === 1 ? ' líder' : ' líderes');
        $raiz['pastores'] = $pastores;
        $raiz['sinPastor'] = $pastores === [];

        return $this->resultado($pastores === [] && $hijos === [] ? 'vacio' : 'arbol', $raiz);
    }

    /**
     * Miembros activos por líder: por `lider_id` y por las células que lidera o co-lidera, sin duplicados.
     *
     * @param  array<int, int>  $liderIds
     * @return array<int, array<int, Persona>>
     */
    private function miembrosPorLider(array $liderIds): array
    {
        if (!$liderIds) {
            return [];
        }

        $directos = Persona::query()->activas()->whereIn('personas.lider_id', $liderIds)->get(self::COLUMNAS);

        // Celula lleva el filtro global de sede.
        $vinculos = Celula::query()
            ->join('celula_persona', 'celula_persona.celula_id', '=', 'celulas.id')
            ->where(fn ($q) => $q->whereIn('celulas.lider_id', $liderIds)->orWhereIn('celulas.co_lider_id', $liderIds))
            ->get(['celula_persona.persona_id', 'celulas.lider_id', 'celulas.co_lider_id']);

        $porCelula = Persona::query()->activas()
            ->whereIn('personas.id', $vinculos->pluck('persona_id')->unique()->all())
            ->get(self::COLUMNAS)->keyBy('id');

        $resultado = [];
        foreach ($directos as $p) {
            $resultado[$p->lider_id][$p->id] = $p;
        }
        foreach ($vinculos as $v) {
            $persona = $porCelula[$v->persona_id] ?? null;
            if (!$persona) {
                continue;
            }
            foreach (array_filter([$v->lider_id, $v->co_lider_id]) as $liderId) {
                if (in_array($liderId, $liderIds, true)) {
                    $resultado[$liderId][$persona->id] = $persona;
                }
            }
        }

        foreach ($resultado as $liderId => $lista) {
            unset($lista[$liderId]);
            $resultado[$liderId] = collect($lista)->sortBy(fn ($p) => $this->clave($p))->values()->all();
        }

        return $resultado;
    }

    /** @param  array<int, Persona>  $miembros */
    private function nodoLider(Persona $lider, array $miembros, Closure $verFicha): array
    {
        return $this->nodo('lider', $lider->id, $lider->nombre_completo, 'Líder', $verFicha($lider->id),
            $this->nodosMiembros(collect($miembros), $verFicha), 'miembro');
    }

    /** @return array<int, array<string, mixed>> */
    private function nodosMiembros(Collection $personas, Closure $verFicha): array
    {
        return $personas->sortBy(fn ($p) => $this->clave($p))->map(
            fn (Persona $p) => $this->nodo('miembro', $p->id, $p->nombre_completo, 'Miembro', $verFicha($p->id), [], 'miembro')
        )->values()->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $hijos
     * @return array<string, mixed>
     */
    private function nodo(string $tipo, ?int $id, string $titulo, string $rol, bool $puedeVerFicha, array $hijos, string $tipoHijo): array
    {
        $total = count($hijos);
        $etiqueta = $tipoHijo === 'lider'
            ? $total.($total === 1 ? ' líder' : ' líderes')
            : $total.($total === 1 ? ' miembro' : ' miembros');

        return [
            'tipo' => $tipo, 'id' => $id, 'titulo' => $titulo, 'rol' => $rol, 'pastores' => [], 'sinPastor' => false,
            'puedeVerFicha' => $id !== null && $puedeVerFicha, 'hijos' => $hijos, 'totalHijos' => $total, 'etiquetaHijos' => $etiqueta,
        ];
    }

    private function clave(Persona $p): string
    {
        return mb_strtolower(Str::ascii($p->apellidos.' '.$p->nombres));
    }

    /** @return array{estado: string, raiz: ?array<string, mixed>} */
    private function resultado(string $estado, ?array $raiz = null): array
    {
        return ['estado' => $estado, 'raiz' => $raiz];
    }
}
