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
        Schema::create('celulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('lider_id')->constrained('personas')->restrictOnDelete();
            $table->foreignId('co_lider_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->string('sede')->nullable();
            $table->string('dia_reunion')->nullable();
            $table->time('hora_reunion')->nullable();
            $table->string('lugar_reunion')->nullable();
            $table->string('estado')->default('activa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('celulas');
    }
};
