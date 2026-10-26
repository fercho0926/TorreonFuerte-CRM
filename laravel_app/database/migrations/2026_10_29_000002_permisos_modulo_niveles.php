<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Da el permiso del módulo «Niveles» (spec 014) a los roles existentes: administrador y pastor escritura,
 * líder y usuario lectura; cualquier otro rol (personalizado) queda sin acceso. Es idempotente.
 */
return new class extends Migration
{
    private const NIVELES_POR_ROL = ['admin' => 2, 'pastor' => 2, 'lider_celula' => 1, 'user' => 1];

    public function up(): void
    {
        foreach (DB::table('roles')->get(['id', 'clave']) as $rol) {
            DB::table('rol_permisos')->updateOrInsert(
                ['rol_id' => $rol->id, 'modulo' => 'niveles'],
                ['nivel' => self::NIVELES_POR_ROL[$rol->clave] ?? 0],
            );
        }
    }

    public function down(): void
    {
        DB::table('rol_permisos')->where('modulo', 'niveles')->delete();
    }
};
