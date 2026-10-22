<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_qr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->morphs('registrable');
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('celular', 20);
            $table->date('fecha_nacimiento');
            $table->string('tipo_documento', 20)->nullable();
            $table->string('numero_documento', 30)->nullable();
            $table->string('resultado', 20); // nueva, existente, de_otra_sede, pendiente
            $table->text('nota_correccion')->nullable();
            $table->timestamps();

            $table->unique(['registrable_type', 'registrable_id', 'celular'], 'registros_qr_destino_celular_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_qr');
    }
};
