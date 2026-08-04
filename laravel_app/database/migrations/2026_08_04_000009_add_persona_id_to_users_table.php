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
        Schema::table('users', function (Blueprint $table) {
            // Vincula la cuenta de acceso con su registro de Persona, necesario para
            // resolver qué célula/red lidera y así aplicar el alcance jerárquico.
            $table->foreignId('persona_id')->nullable()->after('role')->constrained('personas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('persona_id');
        });
    }
};
