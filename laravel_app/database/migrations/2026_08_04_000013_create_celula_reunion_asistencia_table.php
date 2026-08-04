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
        Schema::create('celula_reunion_asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('celula_reunion_id')->constrained('celula_reuniones')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->boolean('asistio')->default(false);
            $table->timestamps();

            $table->unique(['celula_reunion_id', 'persona_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('celula_reunion_asistencia');
    }
};
