/**
 * «Crear persona nueva» dentro de un multiselect de personas (por ejemplo, los integrantes de una
 * célula). Abre un diálogo con los datos mínimos, crea la persona en el servidor
 * (POST data-crear) y la deja seleccionada. Si el servidor avisa de un posible duplicado, pide
 * confirmar antes de crearla.
 */
import Swal from 'sweetalert2';

/** Datos del diálogo listos para enviar: sin espacios sobrantes y sin campos vacíos. */
export function datosDeAlta(valores) {
    const limpios = {};
    ['nombres', 'apellidos', 'celular', 'email'].forEach((campo) => {
        const valor = String(valores?.[campo] ?? '').replace(/\s+/g, ' ').trim();
        if (valor !== '') limpios[campo] = valor;
    });

    return limpios;
}

/** Interpreta la respuesta del servidor. */
export function interpretarRespuesta(estado, cuerpo) {
    if (estado === 201) return { tipo: 'ok', persona: cuerpo };
    if (estado === 409) return { tipo: 'duplicado', duplicado: cuerpo?.duplicado ?? {} };
    if (estado === 422) {
        const mensajes = Object.values(cuerpo?.errors ?? {}).flat();

        return { tipo: 'errores', mensajes };
    }
    if (estado === 401) return { tipo: 'error', mensaje: 'Tu sesión terminó; vuelve a iniciar sesión.' };
    if (estado === 403) return { tipo: 'error', mensaje: 'No tienes permiso para crear personas.' };

    return { tipo: 'error', mensaje: 'No se pudo crear la persona. Inténtalo de nuevo.' };
}

async function enviar(url, token, datos) {
    const respuesta = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify(datos),
        credentials: 'same-origin',
    });
    const cuerpo = await respuesta.json().catch(() => ({}));

    return interpretarRespuesta(respuesta.status, cuerpo);
}

const HTML_DIALOGO = `
    <div class="text-start">
        <label class="form-label mt-1" for="alta-nombres">Nombres *</label>
        <input id="alta-nombres" class="form-control" autocomplete="off">
        <label class="form-label mt-2" for="alta-apellidos">Apellidos *</label>
        <input id="alta-apellidos" class="form-control" autocomplete="off">
        <label class="form-label mt-2" for="alta-celular">Celular</label>
        <input id="alta-celular" class="form-control" inputmode="tel" autocomplete="off">
        <label class="form-label mt-2" for="alta-email">Correo</label>
        <input id="alta-email" class="form-control" type="email" autocomplete="off">
        <small class="text-muted d-block mt-2">Basta con el celular o el correo. Quedará con el estado «En célula».</small>
        <div id="alta-errores" class="text-danger small mt-2" role="alert"></div>
    </div>`;

function leerDialogo() {
    const valor = (id) => document.getElementById(id)?.value ?? '';

    return datosDeAlta({ nombres: valor('alta-nombres'), apellidos: valor('alta-apellidos'), celular: valor('alta-celular'), email: valor('alta-email') });
}

function agregarOpcion(select, persona, $) {
    const opcion = new Option(persona.texto, String(persona.id), true, true);
    if (persona.apoyo) opcion.dataset.apoyo = persona.apoyo;
    select.appendChild(opcion);
    $(select).trigger('change');
}

export async function abrirAltaRapida(select, $) {
    const token = select.form?.querySelector('input[name="_token"]')?.value ?? '';
    let confirmar = false;

    for (;;) {
        const resultado = await Swal.fire({
            title: 'Crear persona nueva',
            html: HTML_DIALOGO,
            showCancelButton: true,
            confirmButtonText: 'Crear y añadir',
            cancelButtonText: 'Cancelar',
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-primary ms-2', cancelButton: 'btn btn-outline-secondary' },
            focusConfirm: false,
            didOpen: () => document.getElementById('alta-nombres')?.focus(),
            preConfirm: async () => {
                const datos = leerDialogo();
                if (confirmar) datos.confirmar_duplicado = true;
                const respuesta = await enviar(select.dataset.crear, token, datos);

                if (respuesta.tipo === 'errores' || respuesta.tipo === 'error') {
                    const mensajes = respuesta.mensajes ?? [respuesta.mensaje];
                    const caja = document.getElementById('alta-errores');
                    if (caja) caja.textContent = mensajes.join(' ');

                    return false;
                }

                return respuesta;
            },
        });

        if (!resultado.isConfirmed) return;

        const respuesta = resultado.value;
        if (respuesta.tipo === 'ok') {
            agregarOpcion(select, respuesta.persona, $);

            return;
        }

        if (respuesta.tipo === 'duplicado') {
            const decision = await Swal.fire({
                icon: 'warning',
                title: 'Posible duplicado',
                text: `Ya existe «${respuesta.duplicado.nombre ?? 'una persona'}» con los mismos datos. ¿Crear otra de todos modos?`,
                showCancelButton: true,
                confirmButtonText: 'Sí, crear otra',
                cancelButtonText: 'Volver',
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-primary ms-2', cancelButton: 'btn btn-outline-secondary' },
            });
            if (!decision.isConfirmed) return;
            confirmar = true;
        }
    }
}

/** Añade el botón «Crear persona nueva» a los multiselect con data-crear. */
export function iniciarAltaRapida(select, $) {
    if (!select.dataset.crear || select.dataset.altaRapida) return;
    select.dataset.altaRapida = '1';

    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'btn btn-outline-primary btn-sm mt-2';
    boton.innerHTML = '<i class="fas fa-user-plus me-1" aria-hidden="true"></i>Crear persona nueva';
    boton.addEventListener('click', () => abrirAltaRapida(select, $));

    const campo = select.closest('.fm-campo');
    (campo?.querySelector('.invalid-feedback') ?? select).insertAdjacentElement('beforebegin', boton);
}
