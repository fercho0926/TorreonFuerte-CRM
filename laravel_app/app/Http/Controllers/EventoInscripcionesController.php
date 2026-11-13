<?php

namespace App\Http\Controllers;

use App\Grillas\Grilla;
use App\Grillas\InscripcionesEventoGrilla;
use App\Http\Controllers\Concerns\ServicioGrilla;
use App\Models\Evento;
use Illuminate\Http\Request;

/**
 * Datos (DataTables) y exportación de la grilla «Miembros inscritos» de un evento (spec 019).
 * El evento viene de la ruta, nunca de un parámetro que el cliente elija.
 */
class EventoInscripcionesController extends Controller
{
    use ServicioGrilla {
        datos as protected datosJson;
        exportar as protected exportarCsv;
    }

    protected function grilla(): Grilla
    {
        return app(InscripcionesEventoGrilla::class);
    }

    /** La grilla vive dentro de la pestaña «Asistencia» de la ficha; esta ruta solo existe para que
     *  <x-ui.grilla> tenga un `{id}.index` al que volver (botón «Limpiar», acción del formulario de filtros). */
    public function index(Request $request, Evento $evento)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'] + $request->query());
    }

    public function datos(Request $request, Evento $evento)
    {
        $request->merge(['evento' => $evento->id]);

        return $this->datosJson($request);
    }

    public function exportar(Request $request, Evento $evento)
    {
        $request->merge(['evento' => $evento->id]);

        return $this->exportarCsv($request);
    }
}
