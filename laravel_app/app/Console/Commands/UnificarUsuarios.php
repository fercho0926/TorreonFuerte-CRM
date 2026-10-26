<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CuentasDeMiembros;
use Illuminate\Console\Command;

/** Unifica las cuentas de usuario con sus miembros (spec 016, research D7). Se puede repetir. */
class UnificarUsuarios extends Command
{
    protected $signature = 'miembros:unificar-usuarios';

    protected $description = 'Vincula cada cuenta de usuario con su miembro (o lo crea) y lista los casos por revisar';

    public function handle(CuentasDeMiembros $cuentas): int
    {
        $resumen = $cuentas->unificar();

        $this->info("Cuentas vinculadas: {$resumen['vinculadas']}");
        $this->info("Miembros creados: {$resumen['creadas']}");
        $this->info("Por revisar: {$resumen['por_revisar']}");

        $porRevisar = User::withoutGlobalScopes()->with('sede')->whereNotNull('revision_vinculo')->orderBy('email')->get();
        if ($porRevisar->isNotEmpty()) {
            $this->table(['Correo', 'Sede', 'Motivo', 'Candidatos'], $porRevisar->map(fn (User $u) => [
                $u->email,
                $u->sede?->nombre ?? 'Todas (administrador)',
                ($u->revision_vinculo['motivo'] ?? '') === 'varios' ? 'Varios miembros con ese celular' : 'El nombre no coincide',
                count($u->revision_vinculo['candidatos'] ?? []),
            ])->all());
            $this->line('Revísalas en Usuarios, filtro «Vínculo: Por revisar».');
        }

        if ($resumen['sedes_sin_pastor'] !== []) {
            $this->warn('Sedes sin pastor: '.implode(', ', $resumen['sedes_sin_pastor']).'. Asígnalo para poder designar líderes.');
        }

        return self::SUCCESS;
    }
}
