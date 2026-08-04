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
        Schema::create('celula_reuniones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('celula_id')->constrained('celulas')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('tema')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('celula_reuniones');
    }
};
