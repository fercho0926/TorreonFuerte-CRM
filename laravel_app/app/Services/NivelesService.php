<?php

namespace App\Services;

use App\Models\NivelFormacion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Plan de niveles de formación (spec 014): alta, edición, orden, desactivación y eliminación, siempre con
 * historial, y el resumen de cuántos miembros hay en cada nivel dentro del alcance del usuario.
 */
class NivelesService
{
    public function __construct(private AlcanceService $alcance)
    {
    }

    /** @param array{nombre: string, descripcion?: ?string} $datos */
    public function crear(array $datos, ?User $por = null): NivelFormacion
    {
        return DB::transaction(function () use ($datos, $por) {
            $nivel = NivelFormacion::create([
                'nombre' => trim($datos['nombre']),
                'descripcion' => $this->texto($datos['descripcion'] ?? null),
                'posicion' => (int) NivelFormacion::query()->max('posicion') + 1,
                'activo' => true,
            ]);

            $this->registrar($nivel, 'creado', null, $this->foto($nivel), $por);

            return $nivel;
        });
    }

    /** @param array{nombre: string, descripcion?: ?string} $datos */
    public function actualizar(NivelFormacion $nivel, array $datos, ?User $por = null): NivelFormacion
    {
        $antes = $this->foto($nivel);

        $nivel->fill(['nombre' => trim($datos['nombre']), 'descripcion' => $this->texto($datos['descripcion'] ?? null)])->save();

        $despues = $this->foto($nivel);
        if ($antes !== $despues) {
            $this->registrar($nivel, 'editado', $antes, $despues, $por);
        }

        return $nivel;
    }

    /** Sube un nivel una posición (intercambia con el anterior). En el primero no hace nada. */
    public function subir(NivelFormacion $nivel, ?User $por = null): void
    {
        $vecino = NivelFormacion::query()->where('posicion', '<', $nivel->posicion)->orderByDesc('posicion')->first();
        $this->intercambiar($nivel, $vecino, $por);
    }

    /** Baja un nivel una posición (intercambia con el siguiente). En el último no hace nada. */
    public function bajar(NivelFormacion $nivel, ?User $por = null): void
    {
        $vecino = NivelFormacion::query()->where('posicion', '>', $nivel->posicion)->orderBy('posicion')->first();
        $this->intercambiar($nivel, $vecino, $por);
    }

    public function desactivar(NivelFormacion $nivel, ?User $por = null): void
    {
        if (!$nivel->activo) {
            return;
        }
        $nivel->update(['activo' => false]);
        $this->registrar($nivel, 'desactivado', ['activo' => true], ['activo' => false], $por);
    }

    public function reactivar(NivelFormacion $nivel, ?User $por = null): void
    {
        if ($nivel->activo) {
            return;
        }
        $nivel->update(['activo' => true]);
        $this->registrar($nivel, 'reactivado', ['activo' => false], ['activo' => true], $por);
    }

    /** Elimina un nivel sin avances ni encuentros; en otro caso hay que desactivarlo. */
    public function eliminar(NivelFormacion $nivel, ?User $por = null): void
    {
        if (!$nivel->sePuedeEliminar()) {
            throw ValidationException::withMessages(['nivel' => 'Este nivel ya tiene miembros o encuentros asociados: no se puede eliminar. Puedes desactivarlo.']);
        }

        DB::transaction(function () use ($nivel, $por) {
            $this->registrar($nivel, 'eliminado', $this->foto($nivel), null, $por);
            $posicion = $nivel->posicion;
            $nivel->delete();

            // Posiciones consecutivas: los siguientes suben un lugar.
            foreach (NivelFormacion::query()->where('posicion', '>', $posicion)->orderBy('posicion')->get() as $siguiente) {
                $siguiente->update(['posicion' => $siguiente->posicion - 1]);
            }
        });
    }

    /**
     * Cuántos miembros activos (dentro del alcance del usuario) tienen cada nivel como actual, y cuántos no han iniciado.
     *
     * @return array{niveles: Collection<int, array{nivel: NivelFormacion, total: int}>, sin_iniciar: int, total: int}
     */
    public function resumen(?User $usuario): array
    {
        $niveles = NivelFormacion::query()->activos()->ordenados()->get();
        $visibles = $this->alcance->personasVisibles($usuario)->activas();
        $total = (clone $visibles)->count();

        $porPosicion = DB::query()
            ->fromSub(
                DB::table('avances_nivel as a')
                    ->join('niveles_formacion as n', 'n.id', '=', 'a.nivel_id')
                    ->where('n.activo', true)
                    ->whereIn('a.persona_id', (clone $visibles)->select('personas.id')->toBase())
                    ->groupBy('a.persona_id')
                    ->selectRaw('a.persona_id, max(n.posicion) as maxima'),
                't'
            )
            ->selectRaw('maxima, count(*) as total')
            ->groupBy('maxima')
            ->pluck('total', 'maxima');

        $lista = $niveles->map(fn (NivelFormacion $n) => ['nivel' => $n, 'total' => (int) ($porPosicion[$n->posicion] ?? 0)]);

        return ['niveles' => $lista, 'sin_iniciar' => max(0, $total - $lista->sum('total')), 'total' => $total];
    }

    private function intercambiar(NivelFormacion $nivel, ?NivelFormacion $vecino, ?User $por): void
    {
        if (!$vecino) {
            return;
        }

        DB::transaction(function () use ($nivel, $vecino, $por) {
            $a = $nivel->posicion;
            $b = $vecino->posicion;
            $provisional = (int) NivelFormacion::query()->max('posicion') + 1000; // la posición es única

            $nivel->update(['posicion' => $provisional]);
            $vecino->update(['posicion' => $a]);
            $nivel->update(['posicion' => $b]);

            $this->registrar($nivel, 'reordenado', ['posicion' => $a], ['posicion' => $b], $por);
        });
    }

    /** @return array{nombre: string, descripcion: ?string, posicion: int, activo: bool} */
    private function foto(NivelFormacion $nivel): array
    {
        return ['nombre' => $nivel->nombre, 'descripcion' => $nivel->descripcion, 'posicion' => $nivel->posicion, 'activo' => (bool) $nivel->activo];
    }

    private function texto(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }

    private function registrar(NivelFormacion $nivel, string $accion, ?array $antes, ?array $despues, ?User $por): void
    {
        app(Historial::class)->registrar($nivel, $accion, $antes, $despues, $por);
    }
}
