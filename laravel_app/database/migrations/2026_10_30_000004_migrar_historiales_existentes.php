<?php

use App\Support\AccionesHistorial;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Integra los historiales anteriores (miembros, sedes, roles y niveles) en el historial único (spec 015, D11).
 * Conserva fecha, usuario y los valores crudos; el JSON original queda en `detalle.legado`. Las tablas viejas se
 * borran en una migración posterior, solo si los conteos coinciden. Es idempotente: no copia dos veces.
 */
return new class extends Migration
{
    private const ORIGENES = [
        'persona_cambios' => 'persona',
        'sede_cambios' => 'sede',
        'rol_cambios' => 'rol',
        'nivel_cambios' => 'nivel',
    ];

    public function up(): void
    {
        foreach (self::ORIGENES as $tabla => $tipo) {
            if (!Schema::hasTable($tabla) || DB::table('historial_cambios')->where('sujeto_tipo', $tipo)->whereNotNull('detalle->legado->tabla')->exists()) {
                continue;
            }

            DB::table($tabla)->orderBy('id')->chunk(500, function ($filas) use ($tabla, $tipo) {
                DB::table('historial_cambios')->insert($filas->map(fn ($fila) => $this->entrada($tabla, $tipo, $fila))->all());
            });
        }

        $this->unirTraslados();
    }

    public function down(): void
    {
        DB::table('historial_cambios')->whereNotNull('detalle->legado->tabla')->delete();
    }

    /** @return array<string, mixed> */
    private function entrada(string $tabla, string $tipo, object $fila): array
    {
        [$sujetoId, $sujetoNombre, $sedeId] = match ($tipo) {
            'persona' => [$fila->persona_id, $this->nombrePersona($fila->persona_id), $fila->sede_id ?? DB::table('personas')->where('id', $fila->persona_id)->value('sede_id')],
            'sede' => [$fila->sede_id, DB::table('sedes')->where('id', $fila->sede_id)->value('nombre') ?? 'Sede #'.$fila->sede_id, $fila->sede_id],
            'rol' => [$fila->rol_id ?? 0, $fila->rol_nombre, null],
            'nivel' => [$fila->nivel_id ?? 0, $fila->nombre, null],
        };

        $usuario = $fila->user_id ? $this->nombreUsuario($fila->user_id) : null;
        $conocida = AccionesHistorial::existe($tipo, $fila->accion);

        return [
            'created_at' => $fila->created_at ?? now(),
            'sede_id' => $sedeId,
            'actor' => $usuario ? 'usuario' : 'sistema',
            'user_id' => $fila->user_id,
            'actor_nombre' => mb_substr($usuario ?? 'Sistema', 0, 120),
            'modulo' => $conocida ? AccionesHistorial::modulo($tipo, $fila->accion) : AccionesHistorial::catalogo()[$tipo]['modulo'],
            'accion' => mb_substr($fila->accion, 0, 40),
            'categoria' => $conocida ? AccionesHistorial::categoria($tipo, $fila->accion) : ($tipo === 'persona' ? AccionesHistorial::DATOS : null),
            'sujeto_tipo' => $tipo,
            'sujeto_id' => $sujetoId,
            'sujeto_nombre' => mb_substr((string) $sujetoNombre, 0, 160),
            'relacionado_tipo' => null,
            'relacionado_id' => null,
            'relacionado_nombre' => null,
            'lote' => null,
            'antes' => $fila->antes,
            'despues' => $fila->despues,
            'detalle' => json_encode(['legado' => ['tabla' => $tabla, 'id' => $fila->id, 'accion' => $fila->accion]], JSON_UNESCAPED_UNICODE),
        ];
    }

    private function nombrePersona(int $id): string
    {
        $persona = DB::table('personas')->where('id', $id)->first(['nombres', 'apellidos']);

        return $persona ? trim($persona->nombres.' '.$persona->apellidos) : 'Miembro #'.$id;
    }

    /** Nombre de la cuenta: el de su miembro y, en bases anteriores a la spec 016, la columna propia. */
    private function nombreUsuario(int $id): ?string
    {
        $usuario = DB::table('users')->where('id', $id)->first();

        if (!$usuario) {
            return null;
        }

        if (!empty($usuario->persona_id) && DB::table('personas')->where('id', $usuario->persona_id)->exists()) {
            return $this->nombrePersona($usuario->persona_id);
        }

        return $usuario->name ?? null;
    }

    /** Los traslados anteriores solo dejaron el vínculo dentro del JSON del historial del registro nuevo. */
    private function unirTraslados(): void
    {
        if (!Schema::hasTable('persona_cambios')) {
            return;
        }

        foreach (DB::table('persona_cambios')->where('accion', 'creada')->where('despues', 'like', '%persona_origen_id%')->get(['persona_id', 'despues']) as $fila) {
            $origen = json_decode($fila->despues, true)['persona_origen_id'] ?? null;
            if ($origen) {
                DB::table('personas')->where('id', $fila->persona_id)->whereNull('trasladada_desde_persona_id')
                    ->update(['trasladada_desde_persona_id' => (int) $origen]);
            }
        }
    }
};
