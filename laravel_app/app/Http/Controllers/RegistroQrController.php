<?php

namespace App\Http\Controllers;

use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Evento;
use App\Services\GeneradorQr;
use Illuminate\Database\Eloquent\Model;

/**
 * Gestión del QR de un evento, encuentro o célula desde su ficha: descarga, abrir/cerrar y regenerar.
 */
class RegistroQrController extends Controller
{
    private const TIPOS = ['evento' => Evento::class, 'encuentro' => Encuentro::class, 'celula' => Celula::class];

    public function svg(GeneradorQr $generador, string $tipo, int $id)
    {
        $destino = $this->destino($tipo, $id);

        return response($generador->svg($destino->urlRegistro(), 640), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-'.$tipo.'-'.$destino->getKey().'.svg"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function abrir(string $tipo, int $id)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $destino = $this->destino($tipo, $id);
        $destino->update(['registro_abierto' => !$destino->registro_abierto]);

        return back()->with('success', $destino->registro_abierto ? 'Registro por QR abierto.' : 'Registro por QR cerrado.');
    }

    public function regenerar(string $tipo, int $id)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $this->destino($tipo, $id)->regenerarToken();

        return back()->with('success', 'Se generó un enlace nuevo; el anterior ya no funciona.');
    }

    private function destino(string $tipo, int $id): Model
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);
        abort_unless($this->usuarioActual(), 403, self::MENSAJE_SIN_PERMISO);

        $destino = self::TIPOS[$tipo]::findOrFail($id); // con el filtro de sede: otra sede responde 404
        $this->autorizar('gestionarRegistroQr', $destino);

        return $destino;
    }
}
