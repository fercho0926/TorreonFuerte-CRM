/**
 * Arranque de los formularios: activa los comportamientos en cada <form data-form>.
 * Todo es mejora progresiva: sin este código los formularios siguen funcionando.
 */
import { avisoBreve, iniciarAvisos } from './avisos.js';
import { iniciarCopiar } from './copiar.js';
import { iniciarConfirmaciones } from './confirmar.js';
import { iniciarDependientes } from './dependientes.js';
import { iniciarEnvio } from './envio.js';
import { iniciarFechas } from './fechas.js';
import { iniciarMascaras } from './mascaras.js';
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

function seguro(nombre, funcion, form) {
    try {
        funcion(form);
    } catch (error) {
        // Un fallo en una mejora no debe impedir que el formulario se pueda enviar.
        console.error(`[formularios] ${nombre}`, error);
    }
}

export function iniciarFormulario(form) {
    // El orden importa: las dependencias fijan qué es obligatorio/visible antes de validar,
    // la validación se registra antes que el envío para poder cancelarlo.
    seguro('dependientes', iniciarDependientes, form);
    seguro('mascaras', iniciarMascaras, form);
    seguro('fechas', iniciarFechas, form);
    seguro('selectores', iniciarSelectores, form);
    seguro('claves', revelarClaves, form);
    seguro('validacion', iniciarValidacion, form);
    seguro('envio', iniciarEnvio, form);
}

export function iniciar() {
    document.querySelectorAll('form[data-form]').forEach(iniciarFormulario);
    iniciarConfirmaciones();
    iniciarAvisos();
    iniciarCopiar(document, avisoBreve);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
}
