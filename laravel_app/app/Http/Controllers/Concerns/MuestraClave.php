<?php

namespace App\Http\Controllers\Concerns;

use App\Models\SolicitudClave;
use App\Models\User;
use App\Services\ClavesTemporales;
use App\Support\Celular;

/**
 * Genera una clave temporal y responde con la pantalla «Clave generada».
 *
 * La respuesta NO se redirige ni se guarda en la sesión: la clave en claro solo existe en esta
 * respuesta (research D4) y se marca con Cache-Control: no-store.
 */
trait MuestraClave
{
    protected function mostrarClave(User $objetivo, string $motivo, User $preparadaPor, ?SolicitudClave $solicitud = null, ?User $solicitadaPor = null)
    {
        $servicio = app(ClavesTemporales::class);

        // Sin celular de WhatsApp no hay a dónde enviar la clave: se lleva a quien administra a
        // completar el celular en la ficha del miembro, que es su único dueño (spec 016).
        if (Celular::limpio($objetivo->celular) === null) {
            return redirect()->route('personas.edit', $objetivo->persona_id)
                ->with('error', "{$objetivo->name} no tiene celular de WhatsApp. Escríbelo en su ficha de miembro, guarda y vuelve a generar la clave.");
        }

        $generada = $servicio->generar($objetivo, $motivo, $preparadaPor, $solicitud, $solicitadaPor);
        $mensaje = $servicio->mensaje($objetivo, $generada['clave']);

        return response()->view('claves.mostrar', [
            'usuario' => $objetivo,
            'clave' => $generada['clave'],
            'registro' => $generada['registro'],
            'mensaje' => $mensaje,
            'enlace' => $servicio->enlaceWhatsapp($objetivo, $mensaje),
            'celularLegible' => Celular::formatoLegible($objetivo->celular),
        ])->header('Cache-Control', 'no-store, private');
    }
}
