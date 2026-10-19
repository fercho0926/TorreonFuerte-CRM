<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            // Nombre normalizado (sin tildes, mayúsculas ni espacios extra) para garantizar unicidad.
            $table->string('clave')->unique();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('clave')->unique();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('redes');
    }
};
