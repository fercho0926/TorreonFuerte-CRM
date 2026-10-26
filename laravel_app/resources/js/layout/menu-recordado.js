/**
 * Recuerda si el miembro plegó el menú lateral. Solo aplica en pantallas grandes: en móvil el menú
 * siempre empieza cerrado. Todo es opcional: si el navegador bloquea el almacenamiento, el menú
 * funciona igual, solo que sin recordar la elección.
 */

export const CLAVE = 'crm.menu.plegado';
export const ANCHO_ESCRITORIO = 992;

export function leerPlegado(almacen) {
    try {
        return almacen.getItem(CLAVE) === '1';
    } catch {
        return false;
    }
}

export function guardarPlegado(almacen, plegado) {
    try {
        almacen.setItem(CLAVE, plegado ? '1' : '0');
    } catch {
        // Almacenamiento bloqueado: se ignora.
    }
}

/** Aplica el plegado recordado al cuerpo de la página (solo en escritorio). */
export function aplicarInicial(cuerpo, ancho, almacen) {
    if (ancho >= ANCHO_ESCRITORIO && leerPlegado(almacen)) {
        cuerpo.classList.add('sidebar-collapse');
    }
}

export function iniciarMenuRecordado(doc = document, ventana = window) {
    const almacen = (() => {
        try {
            return ventana.localStorage;
        } catch {
            return { getItem: () => null, setItem: () => {} };
        }
    })();

    aplicarInicial(doc.body, ventana.innerWidth, almacen);

    doc.addEventListener('click', (evento) => {
        if (!evento.target.closest?.('[data-lte-toggle="sidebar"]')) return;
        if (ventana.innerWidth < ANCHO_ESCRITORIO) return;

        // AdminLTE cambia la clase en el mismo clic; se lee ya con el nuevo valor.
        setTimeout(() => guardarPlegado(almacen, doc.body.classList.contains('sidebar-collapse')), 0);
    });
}
