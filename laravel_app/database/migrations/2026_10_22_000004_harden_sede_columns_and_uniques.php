<?php

use App\Support\MigracionSedes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Unicidades globales que pasan a ser por sede.
        Schema::table('personas', fn (Blueprint $t) => $t->dropUnique(['numero_documento']));
        Schema::table('redes', fn (Blueprint $t) => $t->dropUnique(['clave']));
        Schema::table('zonas', fn (Blueprint $t) => $t->dropUnique(['clave']));

        // 2. sede_id obligatoria en las tablas de datos (users queda nullable: el admin no tiene sede).
        foreach (MigracionSedes::TABLAS as $tabla) {
            Schema::table($tabla, fn (Blueprint $t) => $t->unsignedBigInteger('sede_id')->nullable(false)->change());
        }

        // 3. Unicidades e índices por sede.
        Schema::table('personas', function (Blueprint $t) {
            $t->unique(['sede_id', 'numero_documento'], 'personas_sede_documento_unique');
            $t->index(['sede_id', 'apellidos', 'nombres'], 'personas_sede_nombre_index');
            $t->index(['sede_id', 'estado'], 'personas_sede_estado_index');
        });
        Schema::table('redes', fn (Blueprint $t) => $t->unique(['sede_id', 'clave'], 'redes_sede_clave_unique'));
        Schema::table('zonas', fn (Blueprint $t) => $t->unique(['sede_id', 'clave'], 'zonas_sede_clave_unique'));
        Schema::table('celulas', fn (Blueprint $t) => $t->index(['sede_id', 'nombre'], 'celulas_sede_nombre_index'));
        Schema::table('ministerios', fn (Blueprint $t) => $t->index(['sede_id', 'nombre'], 'ministerios_sede_nombre_index'));
        Schema::table('celula_reuniones', fn (Blueprint $t) => $t->index(['sede_id', 'fecha'], 'reuniones_sede_fecha_index'));
        Schema::table('eventos', fn (Blueprint $t) => $t->index(['sede_id', 'fecha'], 'eventos_sede_fecha_index'));
        Schema::table('encuentros', fn (Blueprint $t) => $t->index(['sede_id', 'fecha_realizacion'], 'encuentros_sede_fecha_index'));
        Schema::table('persona_cambios', fn (Blueprint $t) => $t->index(['sede_id', 'persona_id'], 'cambios_sede_persona_index'));
    }

    public function down(): void
    {
        Schema::table('persona_cambios', fn (Blueprint $t) => $t->dropIndex('cambios_sede_persona_index'));
        Schema::table('encuentros', fn (Blueprint $t) => $t->dropIndex('encuentros_sede_fecha_index'));
        Schema::table('eventos', fn (Blueprint $t) => $t->dropIndex('eventos_sede_fecha_index'));
        Schema::table('celula_reuniones', fn (Blueprint $t) => $t->dropIndex('reuniones_sede_fecha_index'));
        Schema::table('ministerios', fn (Blueprint $t) => $t->dropIndex('ministerios_sede_nombre_index'));
        Schema::table('celulas', fn (Blueprint $t) => $t->dropIndex('celulas_sede_nombre_index'));
        Schema::table('zonas', fn (Blueprint $t) => $t->dropUnique('zonas_sede_clave_unique'));
        Schema::table('redes', fn (Blueprint $t) => $t->dropUnique('redes_sede_clave_unique'));
        Schema::table('personas', function (Blueprint $t) {
            $t->dropIndex('personas_sede_estado_index');
            $t->dropIndex('personas_sede_nombre_index');
            $t->dropUnique('personas_sede_documento_unique');
        });

        foreach (MigracionSedes::TABLAS as $tabla) {
            Schema::table($tabla, fn (Blueprint $t) => $t->unsignedBigInteger('sede_id')->nullable()->change());
        }

        Schema::table('zonas', fn (Blueprint $t) => $t->unique('clave'));
        Schema::table('redes', fn (Blueprint $t) => $t->unique('clave'));
        Schema::table('personas', fn (Blueprint $t) => $t->unique('numero_documento'));
    }
};
