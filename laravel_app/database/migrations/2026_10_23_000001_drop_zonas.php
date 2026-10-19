<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El concepto "zona" ya no se usa: se elimina el catálogo y la columna de las personas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('personas', 'zona_id')) {
            Schema::table('personas', fn (Blueprint $table) => $table->dropConstrainedForeignId('zona_id'));
        }

        Schema::dropIfExists('zonas');
    }

    public function down(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->restrictOnDelete();
            $table->string('nombre');
            $table->string('clave');
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['sede_id', 'clave'], 'zonas_sede_clave_unique');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->foreignId('zona_id')->nullable()->after('red_id')->constrained('zonas')->nullOnDelete();
        });
    }
};
