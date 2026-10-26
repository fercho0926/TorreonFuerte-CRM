<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MuestraClave;
use App\Models\RegistroClave;
use App\Models\User;

/**
 * Claves temporales de un usuario existente: reenviar una clave nueva y marcarla como enviada.
 */
class ClavesUsuarioController extends Controller
{
    use MuestraClave;

    /** Genera una clave nueva (motivo «reenvío») y la muestra una sola vez. */
    public function store(User $user)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarClaves', $user);

        return $this->mostrarClave($user, RegistroClave::MOTIVO_REENVIO, $this->usuarioActual());
    }

    /** Quien envía el mensaje lo marca como enviado; el sistema no puede confirmar la entrega. */
    public function enviada(RegistroClave $registro)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->autorizar('gestionarClaves', $registro->user);

        if ($registro->estado === RegistroClave::PENDIENTE_ENVIO) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($registro) {
                $registro->update(['estado' => RegistroClave::ENVIADA, 'enviada_en' => now()]);
                app(\App\Services\Historial::class)->registrar($registro->user, 'clave_enviada');
            });
        }

        return redirect()->route('users.index')->with('success', 'Clave marcada como enviada.');
    }
}
