/**
 * Formulario público de registro por QR: al salir del celular o de la cédula consulta si la persona ya
 * existe y, si es así, completa los campos vacíos. Es solo una mejora: sin JavaScript el formulario
 * tiene un paso 1 (celular o cédula) que hace la misma búsqueda en el servidor.
 * No se guarda nada en el navegador y la respuesta del servidor solo trae nombres, apellidos y fecha de nacimiento.
 */

const CAMPOS_PRECARGA = ['nombres', 'apellidos', 'fecha_nacimiento'];

/** Consulta al servidor; devuelve el JSON de la respuesta (con `estado`) o nulo si no hay red. */
export async function consultar(url, valores, token, ventana = window) {
    try {
        const cuerpo = new URLSearchParams();
        Object.entries(valores).forEach(([clave, valor]) => {
            if (valor) cuerpo.append(clave, valor);
        });

        const respuesta = await ventana.fetch(url, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'Content-Type': 'application/x-www-form-urlencoded' },
            body: cuerpo,
            credentials: 'same-origin',
        });

        return await respuesta.json();
    } catch {
        return null;
    }
}

/** Completa solo los campos vacíos con los datos de la persona encontrada. Devuelve cuántos llenó. */
export function precargar(form, datos) {
    let llenos = 0;
    CAMPOS_PRECARGA.forEach((nombre) => {
        const campo = form.elements[nombre];
        if (campo && !campo.value && datos[nombre]) {
            campo.value = datos[nombre];
            campo.dispatchEvent(new Event('input', { bubbles: true }));
            llenos += 1;
        }
    });

    return llenos;
}

function mostrarAviso(form, mensaje) {
    let aviso = form.querySelector('[data-registro-aviso]');
    if (!aviso) {
        aviso = document.createElement('div');
        aviso.setAttribute('data-registro-aviso', '');
        aviso.setAttribute('role', 'status');
        aviso.className = 'alert alert-info';
        form.prepend(aviso);
    }
    aviso.textContent = mensaje;
    aviso.hidden = !mensaje;
}

export function iniciarRegistroQr(form, ventana = window) {
    const url = form.dataset.registroBuscar;
    const token = form.querySelector('input[name="_token"]')?.value;
    if (!url || !token) return;

    const consultarYPrecargar = async () => {
        const celular = form.elements.celular?.value.trim();
        const documento = form.elements.numero_documento?.value.trim();
        if (!celular && !documento) return;

        const datos = await consultar(url, { celular, numero_documento: documento }, token, ventana);
        if (!datos) return;

        if (datos.estado === 'existente') {
            const llenos = precargar(form, datos);
            mostrarAviso(form, llenos > 0 ? 'Encontramos tus datos. Revísalos y confirma.' : '');
        } else if (datos.estado === 'conflicto' || datos.estado === 'pendiente' || datos.estado === 'limite') {
            mostrarAviso(form, datos.mensaje || '');
        } else {
            mostrarAviso(form, '');
        }
    };

    ['celular', 'numero_documento'].forEach((nombre) => {
        form.elements[nombre]?.addEventListener('change', consultarYPrecargar);
    });
}

export function iniciar(raiz = document) {
    raiz.querySelectorAll('form[data-registro-buscar]').forEach((form) => iniciarRegistroQr(form));
}
