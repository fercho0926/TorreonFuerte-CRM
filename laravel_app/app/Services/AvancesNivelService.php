<?php

namespace App\Services;

use App\Models\AvanceNivel;
use App\Models\CambioHistorial;
use App\Models\Encuentro;
use App\Models\NivelFormacion;
use App\Models\Persona;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Avances de los miembros en el plan de niveles (spec 014): registro manual, avance automático a partir de la
 * asistencia a encuentros realizados vinculados a un nivel, y copia al trasladar a un miembro de sede.
 * Un miembro tiene a lo sumo un avance por nivel; el manual manda sobre el automático.
 */
class AvancesNivelService
{
    public function __construct(private LiderazgoService $liderazgo, private SedeContext $contexto)
    {
    }

    public function registrarManual(Persona $persona, NivelFormacion $nivel, Carbon|string $fecha, ?string $nota = null, ?User $por = null): AvanceNivel
    {
        if (!$nivel->activo) {
            throw ValidationException::withMessages(['nivel_id' => 'Ese nivel está desactivado.']);
        }
        if ($this->existente($persona, $nivel)) {
            throw ValidationException::withMessages(['nivel_id' => 'Ya tiene este nivel completado.']);
        }

        $fecha = Carbon::parse($fecha)->startOfDay();
        if ($fecha->isFuture()) {
            throw ValidationException::withMessages(['fecha' => 'La fecha no puede ser futura.']);
        }

        return $this->contexto->conSede($persona->sede_id, function () use ($persona, $nivel, $fecha, $nota, $por) {
            $avance = AvanceNivel::create([
                'sede_id' => $persona->sede_id,
                'persona_id' => $persona->id,
                'nivel_id' => $nivel->id,
                'fecha' => $fecha->toDateString(),
                'origen' => AvanceNivel::ORIGEN_MANUAL,
                'registrado_por' => $por?->id,
                'nota' => $nota ? mb_substr($nota, 0, 255) : null,
            ]);

            $this->liderazgo->registrarCambio($persona, 'nivel_registrado', null, [
                'nivel' => $nivel->nombre, 'fecha' => $fecha->toDateString(), 'origen' => 'manual',
            ], $por, ['fecha_evento' => $fecha->toDateString()], $nivel);

            return $avance;
        });
    }

    /** Quita un avance registrado a mano. Si la asistencia a un encuentro del nivel lo respalda, queda el automático. */
    public function quitarManual(AvanceNivel $avance, ?User $por = null): void
    {
        if (!$avance->esManual()) {
            throw ValidationException::withMessages(['avance' => 'Este nivel viene de un encuentro; corrige la asistencia.']);
        }

        $persona = Persona::withoutGlobalScopes()->findOrFail($avance->persona_id);
        $nivel = NivelFormacion::findOrFail($avance->nivel_id);

        $this->contexto->conSede($persona->sede_id, function () use ($avance, $persona, $nivel, $por) {
            $avance->delete();

            $this->liderazgo->registrarCambio($persona, 'nivel_quitado', [
                'nivel' => $nivel->nombre, 'fecha' => $avance->fecha?->toDateString(), 'origen' => 'manual',
            ], null, $por, [], $nivel);

            $this->recalcular($persona, $nivel, $por);
        });
    }

    /**
     * Deja el avance automático de un miembro en un nivel como corresponde a la asistencia: lo crea con el primer
     * encuentro realizado del nivel al que asistió, lo ajusta si cambió ese encuentro o lo retira si ya no hay asistencia.
     * Un avance manual no se toca.
     */
    public function recalcular(Persona $persona, NivelFormacion $nivel, ?User $por = null): void
    {
        $existente = $this->existente($persona, $nivel);
        if ($existente?->esManual()) {
            return;
        }

        $primero = Encuentro::query()->withoutGlobalScopes()
            ->where('encuentros.nivel_id', $nivel->id)
            ->where('encuentros.estado', 'realizado')
            ->whereIn('encuentros.id', DB::table('encuentro_persona')->select('encuentro_id')->where('persona_id', $persona->id)->where('asistio', true))
            ->orderBy('encuentros.fecha_realizacion')->orderBy('encuentros.id')
            ->first();

        $this->contexto->conSede($persona->sede_id, function () use ($persona, $nivel, $existente, $primero, $por) {
            if ($primero) {
                $fecha = $primero->fecha_realizacion->toDateString();
                if (!$existente) {
                    AvanceNivel::create([
                        'sede_id' => $persona->sede_id, 'persona_id' => $persona->id, 'nivel_id' => $nivel->id, 'fecha' => $fecha,
                        'origen' => AvanceNivel::ORIGEN_ENCUENTRO, 'encuentro_id' => $primero->id,
                    ]);
                    // Lo hace el sistema a raíz de la asistencia: queda como «Automático · por …» (spec 015).
                    app(Historial::class)->comoActor(CambioHistorial::ACTOR_AUTOMATICO, 'Automático', fn () => $this->liderazgo->registrarCambio($persona, 'nivel_automatico', null, [
                        'nivel' => $nivel->nombre, 'fecha' => $fecha, 'origen' => 'encuentro', 'encuentro' => $primero->nombre,
                    ], $por, ['fecha_evento' => $fecha], $nivel), $por);
                } elseif ($existente->encuentro_id !== $primero->id || $existente->fecha?->toDateString() !== $fecha) {
                    $existente->forceFill(['encuentro_id' => $primero->id, 'fecha' => $fecha])->save();
                }

                return;
            }

            if ($existente) {
                $existente->delete();
                app(Historial::class)->comoActor(CambioHistorial::ACTOR_AUTOMATICO, 'Automático', fn () => $this->liderazgo->registrarCambio($persona, 'nivel_quitado', [
                    'nivel' => $nivel->nombre, 'fecha' => $existente->fecha?->toDateString(), 'origen' => 'encuentro',
                ], ['motivo' => 'La asistencia al encuentro ya no cuenta'], $por, [], $nivel), $por);
            }
        });
    }

    /** Recalcula los avances que dependen de un encuentro: sus asistentes (y quienes tenían avance por él) en su nivel y en el anterior. */
    public function recalcularEncuentro(Encuentro $encuentro, ?int $nivelAnterior = null, ?User $por = null): void
    {
        $niveles = NivelFormacion::query()->whereIn('id', array_filter(array_unique([$encuentro->nivel_id, $nivelAnterior])))->get();
        if ($niveles->isEmpty()) {
            return;
        }

        $personaIds = DB::table('encuentro_persona')->where('encuentro_id', $encuentro->id)->pluck('persona_id')
            ->merge(AvanceNivel::query()->withoutGlobalScopes()->where('encuentro_id', $encuentro->id)->pluck('persona_id'))
            ->unique();

        foreach (Persona::query()->withoutGlobalScopes()->whereIn('id', $personaIds)->get() as $persona) {
            foreach ($niveles as $nivel) {
                $this->recalcular($persona, $nivel, $por);
            }
        }
    }

    /** Antes de eliminar un encuentro: la asistencia deja de contar y se recalculan los avances que dependían de él. */
    public function retirarDeEncuentro(Encuentro $encuentro, ?User $por = null): void
    {
        $nivel = $encuentro->nivel_id ? NivelFormacion::find($encuentro->nivel_id) : null;
        $personaIds = AvanceNivel::query()->withoutGlobalScopes()->where('encuentro_id', $encuentro->id)->pluck('persona_id')
            ->merge(DB::table('encuentro_persona')->where('encuentro_id', $encuentro->id)->pluck('persona_id'))->unique();

        DB::table('encuentro_persona')->where('encuentro_id', $encuentro->id)->delete();
        // El encuentro se va a eliminar: no debe contar como realizado al recalcular.
        DB::table('encuentros')->where('id', $encuentro->id)->update(['estado' => 'cancelado']);

        if ($nivel) {
            foreach (Persona::query()->withoutGlobalScopes()->whereIn('id', $personaIds)->get() as $persona) {
                $this->recalcular($persona, $nivel, $por);
            }
        }
    }

    /** Copia el camino de un miembro al nuevo registro que crea el traslado de sede, conservando fechas y origen en la nota. */
    public function copiarATrasladado(Persona $origen, Persona $destino, ?User $por = null): int
    {
        $sedeOrigen = DB::table('sedes')->where('id', $origen->sede_id)->value('nombre');
        $copiados = 0;

        $this->contexto->conSede($destino->sede_id, function () use ($origen, $destino, $por, $sedeOrigen, &$copiados) {
            $avances = AvanceNivel::query()->withoutGlobalScopes()->where('persona_id', $origen->id)->get();

            foreach ($avances as $avance) {
                $nota = trim('Trasladado desde '.($sedeOrigen ?? 'otra sede').' ('.$avance->origen_etiqueta.')'.($avance->nota ? '. '.$avance->nota : ''));
                AvanceNivel::create([
                    'sede_id' => $destino->sede_id, 'persona_id' => $destino->id, 'nivel_id' => $avance->nivel_id,
                    'fecha' => $avance->fecha->toDateString(), 'origen' => AvanceNivel::ORIGEN_MANUAL,
                    'registrado_por' => $por?->id, 'nota' => mb_substr($nota, 0, 255),
                ]);
                $copiados++;
            }

            if ($copiados > 0) {
                $this->liderazgo->registrarCambio($destino, 'nivel_registrado', null, ['trasladados' => $copiados, 'desde' => $sedeOrigen], $por);
            }
        });

        return $copiados;
    }

    private function existente(Persona $persona, NivelFormacion $nivel): ?AvanceNivel
    {
        return AvanceNivel::query()->withoutGlobalScopes()->where('persona_id', $persona->id)->where('nivel_id', $nivel->id)->first();
    }
}
