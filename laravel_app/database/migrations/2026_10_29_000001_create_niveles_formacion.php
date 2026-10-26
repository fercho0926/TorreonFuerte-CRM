<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Niveles de formación de los miembros (spec 014): plan común a todas las sedes, avances de cada miembro
 * (aislados por sede), historial del plan y vínculo opcional de un encuentro con un nivel.
 */
return new class extends Migration
{
    /** Plan inicial de retiros, en orden. */
    private const NIVELES_INICIALES = ['Libres para servir', 'Aliviando el fuego', 'Caminando en santidad', 'Viviendo en el espíritu'];

    public function up(): void
    {
        Schema::create('niveles_formacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('descripcion', 500)->nullable();
            $table->unsignedInteger('posicion')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('avances_nivel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('nivel_id')->constrained('niveles_formacion');
            $table->date('fecha');
            $table->string('origen', 10); // encuentro, manual
            $table->foreignId('encuentro_id')->nullable()->constrained('encuentros')->nullOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nota', 255)->nullable();
            $table->timestamps();

            $table->unique(['persona_id', 'nivel_id']);
            $table->index(['sede_id', 'nivel_id']);
        });

        Schema::create('nivel_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_id')->nullable()->constrained('niveles_formacion')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 20); // creado, editado, reordenado, desactivado, reactivado, eliminado
            $table->string('nombre', 80);
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('encuentros', function (Blueprint $table) {
            $table->foreignId('nivel_id')->nullable()->constrained('niveles_formacion');
        });

        if (DB::table('niveles_formacion')->count() === 0) {
            $ahora = now();
            foreach (self::NIVELES_INICIALES as $indice => $nombre) {
                DB::table('niveles_formacion')->insert([
                    'nombre' => $nombre, 'posicion' => $indice + 1, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('encuentros', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nivel_id');
        });
        Schema::dropIfExists('nivel_cambios');
        Schema::dropIfExists('avances_nivel');
        Schema::dropIfExists('niveles_formacion');
    }
};
