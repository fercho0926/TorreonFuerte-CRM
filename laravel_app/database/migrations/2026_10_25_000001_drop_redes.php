<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El concepto «red» ya no se usa: se elimina el catálogo y la columna de las personas.
 * No se pierde ninguna persona ni líder; solo desaparece el dato de red.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('personas', 'red_id')) {
            Schema::table('personas', fn (Blueprint $table) => $table->dropConstrainedForeignId('red_id'));
        }

        Schema::dropIfExists('redes');
    }

    public function down(): void
    {
        Schema::create('redes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->restrictOnDelete();
            $table->string('nombre');
            $table->string('clave');
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['sede_id', 'clave'], 'redes_sede_clave_unique');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->foreignId('red_id')->nullable()->after('es_lider')->constrained('redes')->nullOnDelete();
        });
    }
};
