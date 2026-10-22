/**
 * Envío seguro: botón con progreso (sin dobles envíos) y aviso al salir con cambios sin guardar.
 * La huella vive solo en memoria. El respaldo ante un envío fallido vive aparte (borrador.js, solo en la sesión).
 */

/** Huella de los valores actuales del formulario (solo en memoria). */
export function huella(form) {
    const datos = new FormData(form);
    const pares = [];
    datos.forEach((valor, clave) => {
        if (clave === '_token') return;
        pares.push([clave, typeof valor === 'string' ? valor : valor.name]);
    });

    return JSON.stringify(pares);
}

export function iniciarEnvio(form) {
    let enviando = false;
    let inicial = huella(form);
    const aviso = !form.hasAttribute('data-sin-aviso');
    const boton = () => form.querySelector('[data-boton-principal]');

    const restaurar = () => {
        const b = boton();
        enviando = false;
        if (!b) return;
        b.disabled = false;
        b.classList.remove('fm-enviando');
        if (b.dataset.textoOriginal) b.innerHTML = b.dataset.textoOriginal;
    };

    form.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;

        if (enviando) {
            e.preventDefault();
            return;
        }

        enviando = true;
        const b = boton();
        if (b && !b.disabled) {
            b.dataset.textoOriginal = b.innerHTML;
            b.classList.add('fm-enviando');
            b.setAttribute('aria-busy', 'true');
            b.innerHTML = '<span class="fm-spinner" aria-hidden="true"></span>Guardando…';
            // Se deshabilita después del envío para que el navegador no lo ignore.
            setTimeout(() => { b.disabled = true; }, 0);
        }
        form.dispatchEvent(new CustomEvent('form:enviando', { bubbles: true }));
    });

    // Si el envío se cancela por otro motivo (p. ej. diálogo de confirmación), el botón se libera.
    form.addEventListener('form:invalido', restaurar);
    window.addEventListener('pageshow', (e) => { if (e.persisted) restaurar(); });

    if (!aviso) return;

    const hayCambios = () => huella(form) !== inicial;
    let ultimo = false;
    const notificar = () => {
        const ahora = hayCambios();
        if (ahora !== ultimo) {
            ultimo = ahora;
            form.dispatchEvent(new CustomEvent('form:cambios', { detail: ahora, bubbles: true }));
        }
    };
    ['input', 'change'].forEach((evento) => form.addEventListener(evento, notificar));

    window.addEventListener('beforeunload', (e) => {
        if (enviando || !hayCambios()) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // Lo que los demás módulos cambian al cargar (máscaras, calendario) no cuenta como cambio del usuario.
    setTimeout(() => { inicial = huella(form); }, 0);
}
