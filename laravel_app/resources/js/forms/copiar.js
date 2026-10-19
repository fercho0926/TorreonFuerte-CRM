/**
 * Botones «Copiar mensaje»: copian al portapapeles el texto de data-copiar y avisan al usuario.
 * Todo es mejora progresiva: si el navegador no permite copiar, se muestra el texto para copiarlo a mano.
 */

/** Copia el texto; devuelve verdadero si se logró. */
export async function copiarTexto(texto, ventana = window) {
    try {
        if (ventana.navigator?.clipboard?.writeText) {
            await ventana.navigator.clipboard.writeText(texto);

            return true;
        }
    } catch {
        // Se prueba el respaldo.
    }

    try {
        const doc = ventana.document;
        const area = doc.createElement('textarea');
        area.value = texto;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        doc.body.appendChild(area);
        area.select();
        const ok = doc.execCommand('copy');
        doc.body.removeChild(area);

        return Boolean(ok);
    } catch {
        return false;
    }
}

export function iniciarCopiar(raiz = document, avisar = () => {}) {
    raiz.querySelectorAll('[data-copiar]').forEach((boton) => {
        boton.addEventListener('click', async () => {
            const copiado = await copiarTexto(boton.dataset.copiar);
            avisar(copiado ? 'Mensaje copiado' : 'No se pudo copiar; selecciona el texto y cópialo');
            boton.dataset.estado = copiado ? 'copiado' : 'error';
        });
    });
}
