<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sedes', function (Blueprint $table) {
            $table->string('registro_token', 32)->nullable();
            $table->boolean('registro_abierto')->default(true);
        });

        // Las sedes existentes reciben su enlace propio antes de exigir unicidad.
        DB::table('sedes')->orderBy('id')->each(function ($fila) {
            DB::table('sedes')->where('id', $fila->id)->update(['registro_token' => Str::random(32)]);
        });

        Schema::table('sedes', function (Blueprint $table) {
            $table->unique('registro_token', 'sedes_registro_token_unique');
        });

        Schema::create('registros_miembros_qr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('celular', 20);
            $table->string('resultado', 20); // nueva, actualizada, reactivada, sin_cambios
            $table->json('cambios')->nullable();
            $table->timestamps();

            $table->index(['sede_id', 'created_at'], 'registros_miembros_qr_sede_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_miembros_qr');

        Schema::table('sedes', function (Blueprint $table) {
            $table->dropUnique('sedes_registro_token_unique');
            $table->dropColumn(['registro_token', 'registro_abierto']);
        });
    }
};
