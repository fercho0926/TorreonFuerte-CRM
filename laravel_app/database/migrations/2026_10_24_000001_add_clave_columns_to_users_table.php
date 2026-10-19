<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Celular de WhatsApp al que se envía la clave temporal (nulo en cuentas anteriores).
            $table->string('celular', 20)->nullable()->after('email');
            // Verdadero mientras la contraseña vigente sea una clave temporal.
            $table->boolean('debe_cambiar_clave')->default(false)->after('password');
            $table->timestamp('clave_vence_en')->nullable()->after('debe_cambiar_clave');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['celular', 'debe_cambiar_clave', 'clave_vence_en']);
        });
    }
};
