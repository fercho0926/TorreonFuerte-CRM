<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('tipo_documento')->nullable()->change();
            $table->string('numero_documento')->nullable()->change();
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->string('estado')->default('visitante')->after('ciudad');
            $table->foreignId('red_id')->nullable()->after('es_lider')->constrained('redes')->nullOnDelete();
            $table->foreignId('zona_id')->nullable()->after('red_id')->constrained('zonas')->nullOnDelete();
            $table->timestamp('inactivado_en')->nullable()->after('zona_id');
            $table->softDeletes();

            $table->index('estado');
            $table->index(['apellidos', 'nombres']);
            $table->index('celular');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['apellidos', 'nombres']);
            $table->dropIndex(['celular']);
            $table->dropConstrainedForeignId('red_id');
            $table->dropConstrainedForeignId('zona_id');
            $table->dropColumn(['estado', 'inactivado_en']);
            $table->dropSoftDeletes();
        });
    }
};
