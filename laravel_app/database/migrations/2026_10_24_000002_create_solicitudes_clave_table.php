<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_clave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Sede de la cuenta; nula para administradores del sistema.
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->restrictOnDelete();
            // pendiente, atendida, descartada ("vencida" se calcula al consultar).
            $table->string('estado')->default('pendiente');
            $table->foreignId('atendida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atendida_en')->nullable();
            $table->timestamp('ultima_solicitud_en');
            // Huella no reversible del origen, solo para diagnóstico.
            $table->string('origen_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['sede_id', 'estado']);
            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_clave');
    }
};
