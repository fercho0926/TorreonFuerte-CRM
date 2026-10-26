<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Migra los datos de la versión anterior (red como texto, sin zonas ni estado) al modelo
 * nuevo (catálogos de redes y zonas, estado del miembro). Es idempotente y no borra datos.
 */
class MigracionRedesZonas
{
    /**
     * @return array{redes:int, usuarios_convertidos:int, personas:int, conflictos:array<int, string>}
     */
    public static function ejecutar(): array
    {
        $resultado = ['redes' => 0, 'usuarios_convertidos' => 0, 'personas' => 0, 'conflictos' => []];

        // El catálogo de redes ya no existe (spec 006): solo se migra el texto legado si la tabla sigue ahí.
        if (Schema::hasColumn('personas', 'red') && Schema::hasTable('redes')) {
            $resultado['redes'] = self::migrarRedes();
        }

        $resultado['usuarios_convertidos'] = self::convertirLideresDeZona();

        $resultado['personas'] = DB::table('personas')
            ->where('estado', 'visitante')
            ->update(['estado' => 'miembro_activo']);

        $resultado['conflictos'] = self::completarLiderDesdeCelulas();

        Log::info('Migración de miembros, redes y zonas', $resultado);

        return $resultado;
    }

    public static function clave(string $texto): string
    {
        return (string) Str::of($texto)->ascii()->lower()->squish();
    }

    private static function migrarRedes(): int
    {
        // Con el modelo multi-sede las redes se crean en la sede del miembro (si la columna existe).
        $conSede = Schema::hasColumn('redes', 'sede_id') && Schema::hasColumn('personas', 'sede_id');
        $creadas = 0;
        $ids = [];

        $columnas = $conSede ? ['id', 'red', 'sede_id'] : ['id', 'red'];

        foreach (DB::table('personas')->whereNotNull('red')->orderBy('id')->get($columnas) as $persona) {
            $nombre = (string) Str::of($persona->red)->squish();
            if ($nombre === '') {
                continue;
            }

            $clave = self::clave($nombre);
            $sedeId = $conSede ? $persona->sede_id : null;
            $llave = ($sedeId ?? 0).'|'.$clave;

            if (!isset($ids[$llave])) {
                $existente = DB::table('redes')->where('clave', $clave)
                    ->when($conSede, fn ($q) => $q->where('sede_id', $sedeId))
                    ->value('id');

                if (!$existente) {
                    $existente = DB::table('redes')->insertGetId([
                        'nombre' => $nombre,
                        'clave' => $clave,
                        'activa' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ] + ($conSede ? ['sede_id' => $sedeId] : []));
                    $creadas++;
                }
                $ids[$llave] = $existente;
            }

            DB::table('personas')->where('id', $persona->id)->update(['red_id' => $ids[$llave]]);
        }

        return $creadas;
    }

    /**
     * El concepto de "líder de zona" ya no existe: esos usuarios pasan a ser líderes normales.
     */
    private static function convertirLideresDeZona(): int
    {
        return DB::table('users')->where('role', 'lider_zona')->update(['role' => 'lider_celula']);
    }

    /**
     * Los miembros en una célula sin líder directo toman el líder de la célula. Si ya tienen
     * otro líder distinto no se modifica nada y se informa el conflicto para revisión manual.
     *
     * @return array<int, string>
     */
    private static function completarLiderDesdeCelulas(): array
    {
        $conflictos = [];

        $filas = DB::table('celula_persona')
            ->join('celulas', 'celulas.id', '=', 'celula_persona.celula_id')
            ->join('personas', 'personas.id', '=', 'celula_persona.persona_id')
            ->get(['personas.id as persona_id', 'personas.lider_id', 'celulas.id as celula_id', 'celulas.lider_id as celula_lider_id', 'celulas.co_lider_id']);

        foreach ($filas as $fila) {
            if ($fila->lider_id === null) {
                DB::table('personas')->where('id', $fila->persona_id)->update(['lider_id' => $fila->celula_lider_id]);
            } elseif ($fila->lider_id != $fila->celula_lider_id && $fila->lider_id != $fila->co_lider_id) {
                $conflictos[$fila->persona_id] = "Miembro {$fila->persona_id} está en la célula {$fila->celula_id} pero su líder directo es {$fila->lider_id}";
            }
        }

        return $conflictos;
    }
}
