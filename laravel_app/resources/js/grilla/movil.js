/**
 * Funciones puras del modo tarjeta de la grilla en teléfono (spec 008): contar filtros activos,
 * decidir qué columnas van bajo «Ver más» y armar las opciones de orden.
 */

/** Prioridad máxima de las columnas que se ven siempre en la tarjeta (las demás van bajo «Ver más»). */
export const PRIORIDAD_VISIBLE = 3;

/** Cantidad de filtros de columna con valor (ignora vacíos y espacios). */
export function contarFiltrosActivos(valores) {
    return Object.values(valores || {}).filter((v) => v !== null && v !== undefined && String(v).trim() !== '').length;
}

/** Índices de las columnas secundarias: no son acciones y su prioridad supera el límite. */
export function columnasSecundarias(columnas, maxPrioridad = PRIORIDAD_VISIBLE) {
    return columnas.reduce((indices, c, i) => (!c.acciones && c.prioridad > maxPrioridad ? [...indices, i] : indices), []);
}

/** Título de la columna `i`, usado como etiqueta de la celda en la tarjeta. */
export function etiquetaDeCelda(columnas, i) {
    return columnas[i]?.titulo ?? '';
}

/** Opciones del selector «Ordenar por»: una ascendente y una descendente por columna ordenable. */
export function opcionesOrden(columnas) {
    return columnas.flatMap((c, i) => (c.ordenable
        ? [
            { valor: `${i}:asc`, texto: `${c.titulo} (A–Z)` },
            { valor: `${i}:desc`, texto: `${c.titulo} (Z–A)` },
        ]
        : []));
}

/** Interpreta el valor de una opción de orden («2:desc») como [índice, sentido]. */
export function leerOrden(valor) {
    const [indice, dir] = String(valor).split(':');

    return [Number(indice), dir === 'desc' ? 'desc' : 'asc'];
}
