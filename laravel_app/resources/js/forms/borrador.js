/**
 * Respaldo de lo escrito cuando un envío no llega al servidor (spec 008, RF-009).
 *
 * Se guarda en `sessionStorage` (solo la pestaña actual, se borra al cerrarla) lo que la persona escribió,
 * excluyendo contraseñas, tokens y archivos. Solo se restaura si se intentó enviar y se vuelve a la misma
 * pantalla con el formulario vacío (por ejemplo, tras cortarse la conexión). Si el envío tuvo éxito, el
 * respaldo se borra al abrir cualquier otra pantalla. Todo es opcional: sin almacenamiento, el formulario
 * funciona igual.
 */

export const PREFIJO = 'crm.borrador.';
const VIGENCIA_MS = 30 * 60 * 1000;

/** Campos que nunca se respaldan. */
const EXCLUIDOS = ['password', 'file', 'hidden', 'submit', 'button', 'reset'];

export function claveDe(ruta) {
    return `${PREFIJO}${String(ruta).replace(/\/+$/, '') || '/'}`;
}

/** Valores respaldables del formulario: texto, selectores y casillas (sin contraseñas, tokens ni archivos). */
export function serializar(form) {
    const datos = {};
    Array.from(form.elements).forEach((campo) => {
        if (!campo.name || campo.name.startsWith('_') || campo.disabled || EXCLUIDOS.includes(campo.type)) return;
        if (/pass|clave|token/i.test(campo.name)) return;

        if (campo.type === 'checkbox' || campo.type === 'radio') {
            if (campo.checked) (datos[campo.name] ||= []).push(campo.value);
        } else if (campo.tagName === 'SELECT' && campo.multiple) {
            const elegidos = Array.from(campo.selectedOptions).map((o) => o.value);
            if (elegidos.length) datos[campo.name] = elegidos;
        } else if (campo.value !== '') {
            datos[campo.name] = campo.value;
        }
    });

    return datos;
}

export function guardar(almacen, clave, datos, enviado = false) {
    try {
        almacen.setItem(clave, JSON.stringify({ datos, enviado, en: Date.now() }));
    } catch {
        // Almacenamiento lleno o bloqueado: se ignora.
    }
}

export function leer(almacen, clave, ahora = Date.now()) {
    try {
        const crudo = almacen.getItem(clave);
        if (!crudo) return null;
        const borrador = JSON.parse(crudo);
        if (!borrador || ahora - borrador.en > VIGENCIA_MS) {
            almacen.removeItem(clave);

            return null;
        }

        return borrador;
    } catch {
        return null;
    }
}

export function borrar(almacen, clave) {
    try {
        almacen.removeItem(clave);
    } catch {
        // Se ignora.
    }
}

/** Rellena solo los campos vacíos con lo respaldado. Devuelve cuántos recuperó. */
export function restaurar(form, datos) {
    let recuperados = 0;
    Object.entries(datos).forEach(([nombre, valor]) => {
        const campos = Array.from(form.elements).filter((c) => c.name === nombre);
        campos.forEach((campo) => {
            if (campo.type === 'checkbox' || campo.type === 'radio') {
                if (!campo.checked && [].concat(valor).includes(campo.value)) { campo.checked = true; recuperados += 1; }
            } else if (campo.tagName === 'SELECT' && campo.multiple) {
                Array.from(campo.options).forEach((o) => { if ([].concat(valor).includes(o.value) && !o.selected) { o.selected = true; recuperados += 1; } });
            } else if (campo.value === '' && typeof valor === 'string') {
                campo.value = valor;
                recuperados += 1;
            }
            if (recuperados) campo.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    return recuperados;
}

/** Borra los respaldos enviados de otras pantallas: si se abre otra ruta, el envío tuvo éxito. */
export function limpiarEnviados(almacen, rutaActual) {
    try {
        const actual = claveDe(rutaActual);
        Object.keys(almacen)
            .filter((k) => k.startsWith(PREFIJO) && k !== actual)
            .forEach((k) => {
                const b = leer(almacen, k);
                if (b?.enviado) almacen.removeItem(k);
            });
    } catch {
        // Se ignora.
    }
}

/** Se llama en cada pantalla: si se abrió otra ruta distinta de la del envío, el envío tuvo éxito y se borra el respaldo. */
export function iniciarLimpiezaDeBorradores(ventana = window) {
    try {
        limpiarEnviados(ventana.sessionStorage, ventana.location.pathname);
    } catch {
        // Sin almacenamiento: nada que limpiar.
    }
}

const ESPERA_MS = 400;

export function iniciarBorrador(form, ventana = window, aviso = () => {}) {
    const modo = form.dataset.modo;
    if (!form || form.method.toLowerCase() !== 'post' || ['filtro', 'login', 'linea'].includes(modo)) return;

    let almacen;
    try {
        almacen = ventana.sessionStorage;
    } catch {
        return;
    }

    const clave = claveDe(ventana.location.pathname);
    limpiarEnviados(almacen, ventana.location.pathname);

    // Recupera lo escrito solo si se intentó enviar, se volvió aquí y el servidor no devolvió errores propios.
    const previo = leer(almacen, clave);
    if (previo?.enviado && !form.querySelector('[data-resumen-servidor]')) {
        const n = restaurar(form, previo.datos);
        if (n > 0) aviso('Recuperamos lo que habías escrito');
    }

    let temporizador;
    const respaldar = () => {
        clearTimeout(temporizador);
        temporizador = setTimeout(() => guardar(almacen, clave, serializar(form), false), ESPERA_MS);
    };

    form.addEventListener('input', respaldar);
    form.addEventListener('change', respaldar);
    form.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        clearTimeout(temporizador);
        guardar(almacen, clave, serializar(form), true);
    });
}
