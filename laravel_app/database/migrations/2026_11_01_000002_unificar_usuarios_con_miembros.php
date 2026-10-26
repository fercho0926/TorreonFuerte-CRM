<?php

use App\Services\CuentasDeMiembros;
use Illuminate\Database\Migrations\Migration;

/**
 * Spec 016 (research D7): toda cuenta queda vinculada a un miembro. Vincula por celular cuando hay una
 * coincidencia clara, crea el miembro cuando no la hay y marca para revisión los casos dudosos.
 * Idempotente; se puede repetir con `php artisan miembros:unificar-usuarios`.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(CuentasDeMiembros::class)->unificar();
    }

    public function down(): void
    {
        // Corrección de datos: no se revierte.
    }
};
