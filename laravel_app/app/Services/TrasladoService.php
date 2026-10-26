<?php

namespace App\Services;

use App\Models\Celula;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Traslado de un miembro entre sedes (solo administrador).
 *
 * No se mueve la fila: se crea un registro nuevo en la sede destino (sin líder, célula ni
 * ministerios) y el de origen queda inactivo conservando todo su historial en la sede de
 * origen. Así ningún dato de una sede apunta a registros de otra.
 */
class TrasladoService
{
    public function __construct(private SedeContext $contexto, private LiderazgoService $liderazgo)
    {
    }

    /**
     * Lo que impide trasladar al miembro hasta reasignarlo.
     *
     * @return array{personas:int, celulas:int, ministerios:int}
     */
    public function pendientes(Persona $persona): array
    {
        return [
            'personas' => $persona->miembros()->count(),
            'celulas' => Celula::where('lider_id', $persona->id)->orWhere('co_lider_id', $persona->id)->count(),
            'ministerios' => $persona->ministeriosLiderados()->count(),
        ];
    }

    public function hayPendientes(Persona $persona): bool
    {
        return array_sum($this->pendientes($persona)) > 0;
    }

    public function mensajePendientes(Persona $persona): string
    {
        $p = $this->pendientes($persona);

        return "No se puede trasladar a {$persona->nombre_completo}: dirige {$p['personas']} miembro(s), {$p['celulas']} célula(s) y {$p['ministerios']} ministerio(s). Reasígnalos primero y vuelve a intentarlo.";
    }

    /**
     * @throws ValidationException si la sede destino no es válida o el documento ya existe en ella
     */
    public function trasladar(Persona $persona, Sede $destino, ?User $por = null): Persona
    {
        if ($destino->id === $persona->sede_id) {
            throw ValidationException::withMessages(['sede_destino_id' => 'La sede destino debe ser distinta de la sede actual del miembro.']);
        }

        if (!$destino->activa) {
            throw ValidationException::withMessages(['sede_destino_id' => 'La sede destino está inactiva.']);
        }

        if ($persona->trasladada_a_sede_id) {
            throw ValidationException::withMessages(['sede_destino_id' => 'Este miembro ya fue trasladada; su registro de origen está inactivo.']);
        }

        if ($persona->numero_documento && $this->documentoExisteEn($destino, $persona->numero_documento)) {
            throw ValidationException::withMessages(['sede_destino_id' => 'Ya existe un miembro con ese documento en la sede destino.']);
        }

        $origen = Sede::find($persona->sede_id);

        return DB::transaction(function () use ($persona, $destino, $origen, $por) {
            $nueva = $this->contexto->conSede($destino->id, function () use ($persona, $destino, $origen, $por) {
                $nueva = Persona::create([
                    'sede_id' => $destino->id,
                    'nombres' => $persona->nombres,
                    'apellidos' => $persona->apellidos,
                    'tipo_documento' => $persona->tipo_documento,
                    'numero_documento' => $persona->numero_documento,
                    'fecha_nacimiento' => $persona->fecha_nacimiento,
                    'celular' => $persona->celular,
                    'email' => $persona->email,
                    'direccion' => $persona->direccion,
                    'barrio' => $persona->barrio,
                    'ciudad' => $persona->ciudad,
                    'estado' => $persona->estado === 'inactivo' ? 'visitante' : $persona->estado,
                    'es_lider' => false,
                    'lider_id' => null,
                    // Une el registro nuevo con el de origen: el histórico del miembro lo acompaña (spec 015, RF-023).
                    'trasladada_desde_persona_id' => $persona->id,
                ]);

                $this->liderazgo->registrarCambio($nueva, 'llego_por_traslado', null, [
                    'trasladada_desde' => $origen?->nombre,
                    'persona_origen_id' => $persona->id,
                ], $por, [], $origen);

                return $nueva;
            });

            // El camino de niveles del miembro viaja con él (spec 014).
            app(AvancesNivelService::class)->copiarATrasladado($persona, $nueva, $por);

            // Y también su cuenta de acceso: la cuenta es del miembro, no de la sede (spec 016).
            if ($cuenta = $persona->cuenta) {
                app(CuentasDeMiembros::class)->vincular($cuenta, $nueva, $por);
            }

            $this->contexto->conSede($persona->sede_id, function () use ($persona, $destino, $por) {
                $antes = ['estado' => $persona->estado];

                $persona->forceFill([
                    'estado' => 'inactivo',
                    'inactivado_en' => now(),
                    'trasladada_a_sede_id' => $destino->id,
                    'trasladada_en' => now(),
                ])->save();

                $this->liderazgo->registrarCambio($persona, 'trasladada', $antes, [
                    'estado' => 'inactivo',
                    'trasladada_a' => $destino->nombre,
                ], $por, [], $destino);
            });

            return $nueva;
        });
    }

    private function documentoExisteEn(Sede $sede, string $documento): bool
    {
        return $this->contexto->sinFiltro(
            fn () => Persona::query()->withTrashed()->where('sede_id', $sede->id)->where('numero_documento', $documento)->exists()
        );
    }
}
