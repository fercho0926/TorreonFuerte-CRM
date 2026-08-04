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
        Schema::create('ministerio_persona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministerio_id')->constrained('ministerios')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->string('rol')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->timestamps();

            $table->unique(['ministerio_id', 'persona_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ministerio_persona');
    }
};
