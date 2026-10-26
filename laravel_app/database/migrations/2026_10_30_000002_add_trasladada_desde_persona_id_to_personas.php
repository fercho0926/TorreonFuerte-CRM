<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une el registro nuevo que crea un traslado con el de la sede de origen, para que el histórico del miembro lo
 * acompañe (spec 015, RF-023). Sin clave foránea: el registro de origen pertenece a otra sede.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('trasladada_desde_persona_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex(['trasladada_desde_persona_id']);
            $table->dropColumn('trasladada_desde_persona_id');
        });
    }
};
