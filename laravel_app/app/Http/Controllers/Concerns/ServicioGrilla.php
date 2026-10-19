<?php

namespace App\Http\Controllers\Concerns;

use App\Grillas\Grilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Acciones comunes de una grilla estándar: datos JSON (DataTables, modo servidor) y exportación CSV.
 * El controlador solo debe implementar `grilla()`.
 */
trait ServicioGrilla
{
    abstract protected function grilla(): Grilla;

    /** Datos de la grilla en el protocolo del lado del servidor de DataTables. */
    public function datos(Request $request)
    {
        if (!Session::get('is_logged_in')) {
            return response()->json(['mensaje' => 'Inicia sesión'], 401);
        }

        $grilla = $this->grilla();
        $usuario = $this->usuarioActual();

        // El manejador global de excepciones solo responde JSON en api/*, así que se responde aquí.
        if (!$grilla->puedeVer($usuario)) {
            return response()->json(['mensaje' => self::MENSAJE_SIN_PERMISO], 403);
        }

        $estado = $grilla->estado($request, $usuario);
        $columnas = $grilla->columnas($usuario);

        $indice = (int) $request->input('order.0.column', -1);
        if (isset($columnas[$indice]) && $columnas[$indice]->ordenable) {
            $estado['orden'] = $columnas[$indice]->clave;
            $estado['dir'] = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        }

        $largo = $grilla->porPagina($request->input('length'));
        $inicio = max(0, (int) $request->input('start', 0));
        $pagina = intdiv($inicio, $largo) + 1;

        $resultado = $grilla->paginar($usuario, $estado, $largo, $pagina);

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $grilla->total($usuario, $estado),
            'recordsFiltered' => $resultado->total(),
            'data' => $grilla->filas($resultado->items(), $usuario),
        ]);
    }

    /** Exporta a CSV todo lo que coincide con los filtros y el orden actuales. */
    public function exportar(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $grilla = $this->grilla();
        $usuario = $this->usuarioActual();

        abort_unless($grilla->puedeExportar($usuario), 403, self::MENSAJE_SIN_PERMISO);

        return $grilla->exportar($usuario, $grilla->estado($request, $usuario), $grilla->id());
    }

    /** Variables para `<x-ui.grilla>` en la página del listado. */
    protected function datosDeLaGrilla(Request $request): array
    {
        $grilla = $this->grilla();
        $usuario = $this->usuarioActual();
        $estado = $grilla->estado($request, $usuario);
        $porPagina = $grilla->porPagina($request->input('por_pagina'));

        $pagina = $grilla->paginar($usuario, $estado, $porPagina, max(1, (int) $request->input('page', 1)))
            ->withPath(route($grilla->id().'.index'))
            ->appends($request->query());

        return [
            'grilla' => $grilla,
            'estado' => $estado,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'total' => $grilla->total($usuario, $estado),
        ];
    }
}
