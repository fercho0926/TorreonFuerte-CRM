<?php

use App\Support\MigracionRedesZonas;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MigracionRedesZonas::ejecutar();
    }

    public function down(): void
    {
        // La migración de datos es aditiva: las tablas y columnas nuevas se eliminan en los
        // `down()` de las migraciones anteriores.
    }
};
