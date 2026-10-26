<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roles configurables (spec 013). `users.role` sigue siendo una cadena que referencia `roles.clave`; los
 * 4 roles de siempre se siembran con exactamente el acceso que tenían en el código.
 */
return new class extends Migration
{
    /**
     * Niveles por módulo (0 sin acceso, 1 lectura, 2 escritura) en el orden admin, pastor, lider_celula, user.
     * Tablero y Organigrama solo se consultan: su nivel máximo es lectura.
     */
    private const MATRIZ = [
        'tablero' => [1, 1, 1, 1],
        'miembros' => [2, 2, 2, 1],
        'lideres' => [2, 2, 1, 1],
        'sin_lider' => [2, 2, 0, 0],
        'celulas' => [2, 2, 2, 1],
        'ministerios' => [2, 2, 2, 2],
        'eventos' => [2, 2, 2, 2],
        'encuentros' => [2, 2, 2, 2],
        'organigrama' => [1, 0, 0, 0],
        'registro_miembros' => [2, 2, 0, 0],
        'usuarios' => [2, 2, 0, 0],
        'solicitudes_clave' => [2, 2, 0, 0],
    ];

    private const ROLES = [
        ['clave' => 'admin', 'nombre' => 'Administrador', 'descripcion' => 'Acceso total a todas las sedes y módulos. Rol protegido.', 'alcance' => 'sede', 'asignable' => false],
        ['clave' => 'pastor', 'nombre' => 'Pastor', 'descripcion' => 'Ve y gestiona toda su sede.', 'alcance' => 'sede', 'asignable' => false],
        ['clave' => 'lider_celula', 'nombre' => 'Líder', 'descripcion' => 'Ve a sus miembros y gestiona sus células.', 'alcance' => 'rama', 'asignable' => true],
        ['clave' => 'user', 'nombre' => 'Usuario', 'descripcion' => 'Ve solo su propio registro.', 'alcance' => 'registro', 'asignable' => true],
    ];

    public function up(): void
    {
        $desconocidos = Schema::hasTable('users')
            ? DB::table('users')->whereNotIn('role', array_column(self::ROLES, 'clave'))->distinct()->pluck('role')->all()
            : [];

        if ($desconocidos) {
            throw new RuntimeException('Hay usuarios con roles desconocidos ('.implode(', ', $desconocidos).'); corrígelos antes de migrar.');
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 40)->unique();
            $table->string('nombre', 60)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->string('alcance', 10);
            $table->boolean('es_sistema')->default(false);
            $table->boolean('asignable')->default(false);
            $table->timestamps();
        });

        Schema::create('rol_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->string('modulo', 30);
            $table->unsignedTinyInteger('nivel');

            $table->unique(['rol_id', 'modulo']);
        });

        Schema::create('rol_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->string('rol_clave', 40);
            $table->string('rol_nombre', 60);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 20); // creado, editado, duplicado, eliminado
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['rol_id', 'created_at']);
        });

        $ahora = now();

        foreach (self::ROLES as $indice => $rol) {
            if (DB::table('roles')->where('clave', $rol['clave'])->exists()) {
                continue;
            }

            $id = DB::table('roles')->insertGetId($rol + ['es_sistema' => true, 'created_at' => $ahora, 'updated_at' => $ahora]);

            foreach (self::MATRIZ as $modulo => $niveles) {
                DB::table('rol_permisos')->insert(['rol_id' => $id, 'modulo' => $modulo, 'nivel' => $niveles[$indice]]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_cambios');
        Schema::dropIfExists('rol_permisos');
        Schema::dropIfExists('roles');
    }
};
