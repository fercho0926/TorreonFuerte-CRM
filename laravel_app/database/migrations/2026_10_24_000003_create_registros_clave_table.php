<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auditoría de claves temporales. NO guarda la clave ni su hash: la contraseña vigente vive
     * solo (con hash) en users.password.
     */
    public function up(): void
    {
        Schema::create('registros_clave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->restrictOnDelete();
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes_clave')->nullOnDelete();
            // alta, recuperacion, reenvio.
            $table->string('motivo');
            // Nulo = la propia persona.
            $table->foreignId('solicitada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('preparada_por')->nullable()->constrained('users')->nullOnDelete();
            // pendiente_envio, enviada, usada, reemplazada, vencida.
            $table->string('estado')->default('pendiente_envio');
            $table->timestamp('vence_en');
            $table->timestamp('enviada_en')->nullable();
            $table->timestamp('usada_en')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['sede_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_clave');
    }
};
