<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina los historiales anteriores (spec 015, D11) una vez integrados en `historial_cambios`. Antes de borrar
 * comprueba, tabla por tabla, que todas sus filas fueron copiadas; si falta alguna, se detiene sin borrar nada.
 */
return new class extends Migration
{
    public const TABLAS = ['persona_cambios', 'sede_cambios', 'rol_cambios', 'nivel_cambios'];

    public function up(): void
    {
        $existentes = array_values(array_filter(self::TABLAS, fn ($tabla) => Schema::hasTable($tabla)));

        foreach ($existentes as $tabla) {
            $origen = DB::table($tabla)->count();
            $copiadas = DB::table('historial_cambios')->where('detalle->legado->tabla', $tabla)->count();

            if ($copiadas < $origen) {
                throw new RuntimeException("No se borra {$tabla}: tiene {$origen} filas y el historial único solo {$copiadas}. Revisa la migración 2026_10_30_000004.");
            }
        }

        foreach ($existentes as $tabla) {
            Schema::drop($tabla);
        }
    }

    /** Vuelve a crear las tablas vacías (los datos siguen en `historial_cambios`). */
    public function down(): void
    {
        Schema::create('persona_cambios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->foreignId('persona_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion');
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('sede_cambios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sede_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion');
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('rol_cambios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->string('rol_clave', 40);
            $table->string('rol_nombre', 60);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion', 20);
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('nivel_cambios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion', 20);
            $table->string('nombre', 80);
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
};
