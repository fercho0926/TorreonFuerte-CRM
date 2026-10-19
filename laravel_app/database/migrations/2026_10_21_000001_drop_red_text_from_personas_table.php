<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina la columna de texto libre `red`, ya reemplazada por `red_id` (catálogo de redes).
     * Se ejecuta después de comprobar la migración de datos de `2026_10_20_000004`.
     */
    public function up(): void
    {
        if (Schema::hasColumn('personas', 'red')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn('red');
            });
        }
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('red')->nullable()->after('es_lider');
        });
    }
};
