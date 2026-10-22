/**
 * Arranque de los formularios: activa los comportamientos en cada <form data-form>.
 * Todo es mejora progresiva: sin este código los formularios siguen funcionando.
 */
import { avisoBreve, iniciarAvisos } from './avisos.js';
import { iniciarCopiar } from './copiar.js';
import { iniciarBorrador, iniciarLimpiezaDeBorradores } from './borrador.js';
import { iniciarConfirmaciones } from './confirmar.js';
import { iniciarDependientes } from './dependientes.js';
import { iniciarEnvio } from './envio.js';
import { iniciarFechas } from './fechas.js';
import { iniciarMascaras } from './mascaras.js';
import { iniciar as iniciarRegistroQr } from './registro-qr.js';
import { iniciarSelectores } from './selectores.js';
import { iniciarValidacion } from './validar.js';

/** Botón para mostrar u ocultar contraseñas marcadas con data-revelable. */
function revelarClaves(form) {
    form.querySelectorAll('input[type="password"][data-revelable]').forEach((input) => {
        const grupo = input.closest('.input-group');
        if (!grupo) return;

        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn btn-outline-secondary';
        boton.setAttribute('aria-label', 'Mostrar contraseña');
        boton.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';
        boton.addEventListener('click', () => {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            boton.firstElementChild.className = visible ? 'fas fa-eye' : 'fas fa-eye-slash';
        });

        grupo.appendChild(boton);
    });
}

async function seguro(nombre, funcion, form) {
    try {
        await funcion(form);
    } catch (error) {
        // Un fallo en una mejora no debe impedir que el formulario se pueda enviar.
        console.error(`[formularios] ${nombre}`, error);
    }
}

/** ¿Algún selector de la pantalla necesita Select2 (y por tanto jQuery)? Si no, no se descargan. */
export function necesitaSelect2(form) {
    return Array.from(form.querySelectorAll('select')).some((select) => (
        select.dataset.sinSelect2 === undefined
        && (Boolean(select.dataset.fuente) || select.multiple || select.hasAttribute('data-buscar') || select.options.length > 10)
    ));
}

async function conSelect2(form) {
    if (!necesitaSelect2(form)) return;
    // jQuery y Select2 se cargan bajo demanda; jquery-global.js los deja en `window` antes de iniciar los selectores.
    await import('../jquery-global.js');
    iniciarSelectores(form);
}

export async function iniciarFormulario(form) {
    // El orden importa: las dependencias fijan qué es obligatorio/visible antes de validar,
    // la validación se registra antes que el envío para poder cancelarlo.
    await seguro('dependientes', iniciarDependientes, form);
    await seguro('mascaras', iniciarMascaras, form);
    // Calendario y selectores se descargan bajo demanda y no bloquean el resto del arranque.
    const diferidos = Promise.all([seguro('fechas', iniciarFechas, form), seguro('selectores', conSelect2, form)]);
    await seguro('claves', revelarClaves, form);
    await seguro('validacion', iniciarValidacion, form);
    await seguro('envio', iniciarEnvio, form);
    await seguro('borrador', (f) => iniciarBorrador(f, window, avisoBreve), form);
    await diferidos;
}

export function iniciar() {
    iniciarLimpiezaDeBorradores();
    document.querySelectorAll('form[data-form]').forEach(iniciarFormulario);
    iniciarConfirmaciones();
    iniciarAvisos();
    iniciarCopiar(document, avisoBreve);
    iniciarRegistroQr(document);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
}
