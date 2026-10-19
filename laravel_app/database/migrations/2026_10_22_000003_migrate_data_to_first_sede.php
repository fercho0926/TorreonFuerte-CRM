<?php

use App\Support\MigracionSedes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        MigracionSedes::ejecutar();
    }

    public function down(): void
    {
        // Solo se puede deshacer mientras el esquema no se haya endurecido (sede_id nullable);
        // si ya es obligatoria, se revierte primero la migración de endurecimiento.
        foreach (MigracionSedes::TABLAS as $tabla) {
            try {
                DB::table($tabla)->update(['sede_id' => null]);
            } catch (\Throwable) {
                // Columna ya obligatoria.
            }
        }

        DB::table('users')->update(['sede_id' => null]);
    }
};
