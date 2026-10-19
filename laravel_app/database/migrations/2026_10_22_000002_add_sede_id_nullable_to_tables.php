<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas de datos de la iglesia que pasan a pertenecer a una sede.
     *
     * @var array<int, string>
     */
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

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                // Nullable en este paso: se rellena en la migración de datos y luego se vuelve obligatoria.
                $table->foreignId('sede_id')->nullable()->constrained('sedes')->restrictOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            // Solo el administrador del sistema queda sin sede.
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->restrictOnDelete();
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->foreignId('trasladada_a_sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->timestamp('trasladada_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trasladada_a_sede_id');
            $table->dropColumn('trasladada_en');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sede_id');
        });

        foreach (array_reverse(self::TABLAS) as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropConstrainedForeignId('sede_id');
            });
        }
    }
};
