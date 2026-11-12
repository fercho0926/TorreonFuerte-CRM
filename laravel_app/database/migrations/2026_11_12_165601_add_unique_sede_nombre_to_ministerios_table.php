<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ministerios', function (Blueprint $table) {
            $table->dropIndex('ministerios_sede_nombre_index');
            $table->unique(['sede_id', 'nombre'], 'ministerios_sede_nombre_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ministerios', function (Blueprint $table) {
            $table->dropUnique('ministerios_sede_nombre_unique');
            $table->index(['sede_id', 'nombre'], 'ministerios_sede_nombre_index');
        });
    }
};
