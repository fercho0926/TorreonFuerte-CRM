<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Migra los datos de la versión de una sola iglesia al modelo multi-sede: crea la primera sede,
 * le asigna todas las filas existentes y a todos los usuarios que no son admin. Es idempotente y
 * no borra nada. Si no hay datos pendientes (instalación nueva) no crea ninguna sede.
 */
class MigracionSedes
{
    /** Tablas de datos de la iglesia que llevan sede_id (users se trata aparte). */
    public const TABLAS = [
        'personas',
        'redes',
        'zonas',
        'celulas',
        'celula_reuniones',
        'encuentros',
        'eventos',
        'evento_persona',
        'evento_pagos',
        'ministerios',
        'persona_cambios',
    ];

    /**
     * @return array{sede_id:?int, sede_creada:bool, asignados:array<string,int>, usuarios:int, filas_sin_sede:int}
     */
    public static function ejecutar(?string $nombreSede = null): array
    {
        $resultado = ['sede_id' => null, 'sede_creada' => false, 'asignados' => [], 'usuarios' => 0, 'filas_sin_sede' => 0];

        if (array_sum(self::contarSinSede()) === 0) {
            return $resultado;
        }

        $nombre = $nombreSede ?? (string) config('app.sede_inicial', 'Sede principal');

        $sede = DB::table('sedes')->where('nombre', $nombre)->first()
            ?? DB::table('sedes')->orderBy('id')->first();

        if (!$sede) {
            $id = DB::table('sedes')->insertGetId([
                'nombre' => $nombre,
                'ciudad' => 'Sin definir',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sede = DB::table('sedes')->find($id);
            $resultado['sede_creada'] = true;
        }

        foreach (self::TABLAS as $tabla) {
            $resultado['asignados'][$tabla] = DB::table($tabla)->whereNull('sede_id')->update(['sede_id' => $sede->id]);
        }

        $resultado['usuarios'] = DB::table('users')
            ->whereNull('sede_id')
            ->where('role', '!=', 'admin')
            ->update(['sede_id' => $sede->id]);

        // El primer pastor con persona vinculada queda como pastor de la sede.
        if (!$sede->pastor_persona_id) {
            $personaId = DB::table('users')
                ->where('role', 'pastor')
                ->where('sede_id', $sede->id)
                ->whereNotNull('persona_id')
                ->orderBy('id')
                ->value('persona_id');

            if ($personaId) {
                DB::table('sedes')->where('id', $sede->id)->update(['pastor_persona_id' => $personaId]);
            }
        }

        $resultado['sede_id'] = $sede->id;
        $resultado['filas_sin_sede'] = array_sum(self::contarSinSede());

        Log::info('Migración de datos a la primera sede', $resultado);

        return $resultado;
    }

    /**
     * Filas sin sede por tabla (los administradores no cuentan: no pertenecen a ninguna sede).
     *
     * @return array<string, int>
     */
    public static function contarSinSede(): array
    {
        $conteo = [];

        foreach (self::TABLAS as $tabla) {
            $conteo[$tabla] = DB::table($tabla)->whereNull('sede_id')->count();
        }

        $conteo['users'] = DB::table('users')->whereNull('sede_id')->where('role', '!=', 'admin')->count();

        return $conteo;
    }
}
