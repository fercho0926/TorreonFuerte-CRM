<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const TABLAS = ['eventos', 'encuentros', 'celulas'];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->string('registro_token', 32)->nullable();
                $table->boolean('registro_abierto')->default(true);
            });

            // Las filas existentes reciben su enlace propio antes de exigir unicidad.
            DB::table($tabla)->orderBy('id')->each(function ($fila) use ($tabla) {
                DB::table($tabla)->where('id', $fila->id)->update(['registro_token' => Str::random(32)]);
            });

            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->unique('registro_token', "{$tabla}_registro_token_unique");
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->dropUnique("{$tabla}_registro_token_unique");
                $table->dropColumn(['registro_token', 'registro_abierto']);
            });
        }
    }
};
