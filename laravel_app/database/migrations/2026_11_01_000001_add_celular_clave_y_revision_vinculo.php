<?php

use App\Support\Celular;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 016: clave de celular para buscar al miembro al crear una cuenta, marca de revisión del vínculo
 * cuenta ↔ miembro, y `users.name` deja de ser obligatorio (el nombre pasa a salir del miembro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('celular_clave', 15)->nullable()->index();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('revision_vinculo')->nullable();
            $table->string('name')->nullable()->change();
        });

        $this->rellenar();
    }

    /** Calcula la clave de los miembros que aún no la tienen. */
    public function rellenar(): void
    {
        DB::table('personas')->whereNotNull('celular')->whereNull('celular_clave')->orderBy('id')
            ->chunkById(500, function ($personas) {
                foreach ($personas as $persona) {
                    DB::table('personas')->where('id', $persona->id)->update(['celular_clave' => Celular::clave($persona->celular)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('revision_vinculo');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex(['celular_clave']);
            $table->dropColumn('celular_clave');
        });
    }
};
