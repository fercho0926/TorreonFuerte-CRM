<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('ciudad');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->boolean('activa')->default(true);
            $table->foreignId('pastor_persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->foreignId('pastora_persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sede_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // creada, editada, desactivada, reactivada, pastor_cambiado, pastora_cambiada
            $table->string('accion');
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sede_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sede_cambios');
        Schema::dropIfExists('sedes');
    }
};
