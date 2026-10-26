<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial único e inalterable de toda la plataforma (spec 015). No tiene claves foráneas a propósito: borrar un
 * registro o un usuario no debe borrar ni anular sus entradas (RF-008); los nombres se guardan como fotos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_cambios', function (Blueprint $table) {
            $table->id();
            $table->dateTime('created_at');
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->string('actor', 12);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor_nombre', 120);
            $table->string('modulo', 30);
            $table->string('accion', 40);
            $table->string('categoria', 24)->nullable();
            $table->string('sujeto_tipo', 30);
            $table->unsignedBigInteger('sujeto_id');
            $table->string('sujeto_nombre', 160);
            $table->string('relacionado_tipo', 30)->nullable();
            $table->unsignedBigInteger('relacionado_id')->nullable();
            $table->string('relacionado_nombre', 160)->nullable();
            $table->uuid('lote')->nullable();
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->json('detalle')->nullable();

            $table->index(['sujeto_tipo', 'sujeto_id', 'categoria', 'created_at'], 'historial_sujeto_categoria');
            $table->index(['sujeto_tipo', 'sujeto_id', 'created_at'], 'historial_sujeto');
            $table->index(['sede_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['modulo', 'created_at']);
            $table->index(['relacionado_tipo', 'relacionado_id']);
            $table->index('lote');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_cambios');
    }
};
