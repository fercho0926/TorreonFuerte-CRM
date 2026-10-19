/**
 * Diálogos de confirmación propios (en lugar del cuadro del navegador) para acciones delicadas.
 * Cualquier <form> o botón con data-confirm pide confirmación con el detalle de las consecuencias.
 * Sin JavaScript el formulario se envía directamente (el servidor mantiene permisos y reglas).
 */
import Swal from 'sweetalert2';

function opcionesDe(el) {
    const peligro = el.dataset.confirmPeligro !== undefined && el.dataset.confirmPeligro !== 'false' && el.dataset.confirmPeligro !== '0';

    return {
        title: el.dataset.confirmTitulo || '¿Continuar?',
        text: el.dataset.confirmMensaje || (el.dataset.confirm !== '1' ? el.dataset.confirm : ''),
        icon: peligro ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: el.dataset.confirmBoton || 'Continuar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: peligro,
        // Botones de Bootstrap 5 en lugar de los estilos propios de SweetAlert2.
        buttonsStyling: false,
        customClass: {
            confirmButton: peligro ? 'btn btn-danger ms-2' : 'btn btn-primary ms-2',
            cancelButton: 'btn btn-outline-secondary',
        },
    };
}

async function confirmar(el) {
    const resultado = await Swal.fire(opcionesDe(el));

    return resultado.isConfirmed;
}

export function iniciarConfirmaciones() {
    // Formularios con data-confirm: se intercepta el envío (fase de captura, antes que el resto).
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.confirm === undefined) return;
        if (form.dataset.confirmado === '1') {
            delete form.dataset.confirmado;
            return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();
        if (await confirmar(form)) {
            form.dataset.confirmado = '1';
            form.requestSubmit(e.submitter ?? undefined);
        }
    }, true);

    // Botones con data-confirm fuera de un formulario de confirmación.
    document.addEventListener('click', async (e) => {
        const boton = e.target.closest?.('button[data-confirm], a[data-confirm]');
        if (!boton || boton.closest('form[data-confirm]')) return;
        if (boton.dataset.confirmado === '1') {
            delete boton.dataset.confirmado;
            return;
        }

        e.preventDefault();
        if (await confirmar(boton)) {
            boton.dataset.confirmado = '1';
            boton.click();
        }
    });
}
