<?php

use App\Services\LiderazgoService;
use Illuminate\Database\Migrations\Migration;

/**
 * Spec 016 (research D11): en cada sede con pastor, el pastor y la pastora quedan como líderes sin líder,
 * y todo líder sin líder pasa a depender del pastor. Idempotente; cada cambio queda en el historial
 * como acción del sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(LiderazgoService::class)->corregirCabezas();
    }

    public function down(): void
    {
        // Corrección de datos: no se revierte.
    }
};
