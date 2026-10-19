/**
 * Autocompletado de personas con búsqueda en el servidor (spec 006). Convierte un <select> con
 * data-fuente en un Select2 que consulta la URL indicada mientras se escribe: mínimo 2 letras,
 * 250 ms de espera, 20 resultados por página y paginación al desplazarse.
 *
 * Atributos del <select>:
 *  - data-fuente:  URL de búsqueda (GET /personas/buscar)
 *  - data-filtros: JSON con filtros extra, p. ej. {"activas":1,"sin_lideres":1}
 */

export const MINIMO_LETRAS = 2;
export const ESPERA_MS = 250;

/** Filtros leídos de data-filtros; un JSON inválido se ignora. */
export function leerFiltros(texto) {
    try {
        const filtros = JSON.parse(texto || '{}');

        return filtros && typeof filtros === 'object' ? filtros : {};
    } catch {
        return {};
    }
}

/** Parámetros de la consulta. Los ids ya elegidos (y los de filtros.excluir) no se vuelven a ofrecer. */
export function parametrosDeBusqueda(termino, pagina, filtros = {}, seleccionados = []) {
    const parametros = { q: (termino ?? '').trim(), pagina: pagina || 1, ...filtros };
    const fijos = String(filtros.excluir ?? '').split(',').map((v) => v.trim()).filter(Boolean);
    const excluir = Array.from(new Set([...fijos, ...(seleccionados || []).map(String).filter(Boolean)]));

    if (excluir.length) parametros.excluir = excluir.join(',');
    else delete parametros.excluir;

    return parametros;
}

/** Adapta la respuesta del servidor al formato de Select2. */
export function procesarResultados(datos) {
    const resultados = Array.isArray(datos?.resultados) ? datos.resultados : [];

    return {
        results: resultados.map((p) => ({
            id: String(p.id),
            text: p.texto,
            apoyo: p.apoyo ?? '',
            estado: p.estado ?? '',
            sinDocumento: Boolean(p.sinDocumento),
        })),
        pagination: { more: Boolean(datos?.mas) },
    };
}

/** Ids seleccionados hoy en un <select> (para excluirlos de las sugerencias). */
export function idsSeleccionados(select) {
    return Array.from(select.selectedOptions ?? []).map((o) => o.value).filter(Boolean);
}

/** Opción de resultado con el dato de apoyo (documento y celular) debajo del nombre. */
export function plantillaResultado(opcion, documento = document) {
    if (!opcion.id) return opcion.text;

    const contenedor = documento.createElement('span');
    contenedor.textContent = opcion.text;

    const apoyo = opcion.apoyo ?? opcion.element?.dataset?.apoyo;
    if (apoyo) {
        const detalle = documento.createElement('span');
        detalle.className = 'fm-apoyo-opcion';
        detalle.textContent = apoyo;
        contenedor.appendChild(detalle);
    }

    return contenedor;
}

/** Configuración de Select2 para el modo remoto. */
export function configuracionRemota(select, $) {
    const filtros = leerFiltros(select.dataset.filtros);

    return {
        minimumInputLength: MINIMO_LETRAS,
        ajax: {
            url: select.dataset.fuente,
            delay: ESPERA_MS,
            dataType: 'json',
            cache: false,
            data: (parametros) => parametrosDeBusqueda(parametros.term, parametros.page, filtros, idsSeleccionados(select)),
            processResults: (datos) => procesarResultados(datos),
        },
        templateResult: (opcion) => plantillaResultado(opcion),
        templateSelection: (opcion) => opcion.text,
    };
}
