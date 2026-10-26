<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Da el permiso del módulo «Control de cambios» (spec 015, clarificación 2): solo el administrador lo consulta por
 * defecto; los demás roles quedan sin acceso hasta que el administrador se lo conceda desde Roles. Es idempotente.
 */
return new class extends Migration
{
    private const NIVELES_POR_ROL = ['admin' => 1];

    public function up(): void
    {
        foreach (DB::table('roles')->get(['id', 'clave']) as $rol) {
            DB::table('rol_permisos')->updateOrInsert(
                ['rol_id' => $rol->id, 'modulo' => 'control_cambios'],
                ['nivel' => self::NIVELES_POR_ROL[$rol->clave] ?? 0],
            );
        }
    }

    public function down(): void
    {
        DB::table('rol_permisos')->where('modulo', 'control_cambios')->delete();
    }
};
