<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('encuentro_persona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuentro_id')->constrained('encuentros')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->boolean('asistio')->default(false);
            $table->decimal('valor_pagado', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['encuentro_id', 'persona_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuentro_persona');
    }
};
