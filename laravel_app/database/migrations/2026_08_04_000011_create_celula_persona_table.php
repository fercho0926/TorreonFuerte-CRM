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
        Schema::create('celula_persona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('celula_id')->constrained('celulas')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->date('fecha_ingreso')->nullable();
            $table->timestamps();

            $table->unique(['celula_id', 'persona_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('celula_persona');
    }
};
