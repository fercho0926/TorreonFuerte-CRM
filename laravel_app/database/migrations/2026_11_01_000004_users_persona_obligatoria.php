<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 016: toda cuenta es de un miembro y el nombre y el celular solo existen en el miembro.
 * Se eliminan `users.name` y `users.celular`, y `users.persona_id` pasa a ser obligatorio y único.
 * Se detiene si queda alguna cuenta sin miembro (ejecuta antes `php artisan miembros:unificar-usuarios`).
 */
return new class extends Migration
{
    public function up(): void
    {
        $sinMiembro = DB::table('users')->whereNull('persona_id')->count();

        if ($sinMiembro > 0) {
            throw new RuntimeException("Hay {$sinMiembro} cuenta(s) sin miembro. Ejecuta `php artisan miembros:unificar-usuarios` y vuelve a migrar.");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'celular']);
            $table->unsignedBigInteger('persona_id')->nullable(false)->change();
            $table->unique('persona_id');
            $table->foreign('persona_id')->references('id')->on('personas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropUnique(['persona_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('celular', 20)->nullable()->after('email');
            $table->unsignedBigInteger('persona_id')->nullable()->change();
            $table->foreign('persona_id')->references('id')->on('personas')->nullOnDelete();
        });

        foreach (DB::table('users')->whereNotNull('persona_id')->get(['id', 'persona_id']) as $usuario) {
            $persona = DB::table('personas')->where('id', $usuario->persona_id)->first(['nombres', 'apellidos', 'celular']);
            if ($persona) {
                DB::table('users')->where('id', $usuario->id)->update([
                    'name' => trim($persona->nombres.' '.$persona->apellidos),
                    'celular' => $persona->celular,
                ]);
            }
        }
    }
};
