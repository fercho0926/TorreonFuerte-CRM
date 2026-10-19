/**
 * Validación inmediata de formularios (mejora progresiva sobre la validación del servidor).
 * Usa la API de validación del navegador, muestra el mensaje bajo el campo y arma un resumen
 * de errores con salto al primer campo con problema. El servidor sigue siendo la fuente de verdad.
 */
import { escaparSelector } from './texto.js';


const MENSAJES = {
    obligatorio: 'Este campo es obligatorio.',
    correo: 'Escribe un correo válido, por ejemplo nombre@dominio.com.',
    formato: 'El formato no es válido.',
    numero: 'Escribe un número válido.',
};

/** Mensaje en español para el estado de validez de un control (o '' si es válido). */
export function mensajeDe(el) {
    const v = el.validity;
    if (!v || v.valid) return '';

    if (el.dataset.mensaje && (v.patternMismatch || v.customError)) return el.dataset.mensaje;
    if (v.valueMissing) return el.type === 'checkbox' ? 'Debes marcar esta casilla.' : MENSAJES.obligatorio;
    if (v.typeMismatch) return el.type === 'email' ? MENSAJES.correo : MENSAJES.formato;
    if (v.tooShort) return `Escribe al menos ${el.minLength} caracteres.`;
    if (v.tooLong) return `Escribe como máximo ${el.maxLength} caracteres.`;
    if (v.rangeOverflow) return el.type === 'date' ? 'La fecha no puede ser posterior a la permitida.' : `El valor no puede ser mayor que ${el.max}.`;
    if (v.rangeUnderflow) return el.type === 'date' ? 'La fecha no puede ser anterior a la permitida.' : `El valor no puede ser menor que ${el.min}.`;
    if (v.badInput) return MENSAJES.numero;
    if (v.patternMismatch) return MENSAJES.formato;

    return el.validationMessage || MENSAJES.formato;
}

function esValidable(el) {
    if (!(el instanceof HTMLElement)) return false;
    if (!['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) return false;
    if (['hidden', 'submit', 'button', 'reset', 'file', 'image'].includes(el.type)) return false;

    return !el.disabled;
}

function elementoError(el) {
    if (!el.id) return null;

    let aviso = document.getElementById(`${el.id}-error`);
    if (!aviso) {
        aviso = document.createElement('div');
        aviso.id = `${el.id}-error`;
        aviso.className = 'invalid-feedback';
        aviso.setAttribute('role', 'alert');
        (el.closest('.input-group') ?? el).insertAdjacentElement('afterend', aviso);
    }

    return aviso;
}

/** Marca un control como correcto o con error y muestra su mensaje. */
export function marcar(el, mensaje) {
    const aviso = elementoError(el);
    const contenedor = el.closest('.fm-campo');
    const conValor = el.type === 'checkbox' ? el.checked : String(el.value ?? '').trim() !== '';

    if (mensaje) {
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
        el.setAttribute('aria-invalid', 'true');
        if (aviso) aviso.textContent = mensaje;
        contenedor?.classList.remove('is-valido');
    } else {
        el.classList.remove('is-invalid');
        el.setAttribute('aria-invalid', 'false');
        el.removeAttribute('aria-invalid');
        if (aviso) aviso.textContent = '';
        const esCorrecto = conValor && el.type !== 'checkbox';
        el.classList.toggle('is-valid', esCorrecto);
        contenedor?.classList.toggle('is-valido', esCorrecto);
    }
}

/** Valida un solo campo; devuelve el mensaje de error ('' si está bien). */
export function validarCampo(el) {
    if (!esValidable(el)) return '';
    const mensaje = mensajeDe(el);
    marcar(el, mensaje);

    return mensaje;
}

function etiquetaDe(el) {
    const etiqueta = el.id ? document.querySelector(`label[for="${escaparSelector(el.id)}"]`) : null;

    return etiqueta ? etiqueta.textContent.replace('*', '').trim() : el.name;
}

/** Enfoca un campo; si está mejorado con un selector con búsqueda, enfoca su caja visible. */
export function enfocar(el) {
    const mejorado = el.nextElementSibling?.classList?.contains('select2') ? el.nextElementSibling.querySelector('.select2-selection') : null;
    const destino = mejorado ?? el;
    destino.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
    destino.focus?.({ preventScroll: true });
}

function resumen(form, errores) {
    form.querySelector('[data-resumen-cliente]')?.remove();
    form.querySelector('[data-resumen-servidor]')?.remove();
    if (!errores.length) return null;

    const caja = document.createElement('div');
    caja.className = 'alert alert-danger fm-resumen';
    caja.setAttribute('role', 'alert');
    caja.dataset.resumenCliente = '1';
    caja.tabIndex = -1;

    const titulo = document.createElement('strong');
    titulo.textContent = 'Revisa los campos marcados:';
    const lista = document.createElement('ul');
    errores.forEach(({ el, mensaje }) => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = `#${el.id}`;
        a.dataset.irACampo = '1';
        a.textContent = `${etiquetaDe(el)}: ${mensaje}`;
        li.appendChild(a);
        lista.appendChild(li);
    });
    caja.append(titulo, lista);
    form.insertBefore(caja, form.firstChild);

    return caja;
}

/** Valida todo el formulario; devuelve la lista de campos con error. */
export function validarFormulario(form) {
    const errores = [];
    Array.from(form.elements).forEach((el) => {
        if (!esValidable(el)) return;
        // Un grupo de casillas o botones de opción se valida una sola vez.
        const mensaje = validarCampo(el);
        if (mensaje) errores.push({ el, mensaje });
    });

    return errores;
}

export function iniciarValidacion(form) {
    // La validación nativa del navegador se reemplaza por la nuestra (mensajes en español, resumen).
    form.noValidate = true;

    form.addEventListener('focusout', (e) => {
        // Al salir de un campo se valida (incluido un obligatorio que quedó vacío).
        if (!esValidable(e.target)) return;
        e.target.dataset.tocado = '1';
        validarCampo(e.target);
    });

    form.addEventListener('input', (e) => {
        // Si el campo ya tenía error, se revalida mientras el usuario corrige.
        if (e.target.classList?.contains('is-invalid')) validarCampo(e.target);
    });
    form.addEventListener('change', (e) => {
        if (e.target.dataset?.tocado !== undefined || e.target.classList?.contains('is-invalid')) validarCampo(e.target);
    });

    form.addEventListener('submit', (e) => {
        const errores = validarFormulario(form);
        if (!errores.length) return;

        e.preventDefault();
        e.stopImmediatePropagation();
        const caja = resumen(form, errores);
        enfocar(errores[0].el);
        form.dispatchEvent(new CustomEvent('form:invalido', { detail: errores.map(({ el }) => el.name), bubbles: true }));
        caja?.scrollIntoView?.({ behavior: 'smooth', block: 'nearest' });
    });

    // Los enlaces del resumen de errores llevan al campo.
    form.addEventListener('click', (e) => {
        const enlace = e.target.closest?.('[data-ir-a-campo]');
        if (!enlace) return;
        const destino = form.querySelector(enlace.getAttribute('href'));
        if (destino) {
            e.preventDefault();
            enfocar(destino);
        }
    });

    // Errores que devolvió el servidor: marcar los campos y llevar al primero.
    const delServidor = Array.from(form.querySelectorAll('.is-invalid, [aria-invalid="true"]')).filter(esValidable);
    delServidor.forEach((el) => {
        el.classList.add('is-invalid');
        el.dataset.tocado = '1';
    });
    if (delServidor.length) {
        enfocar(delServidor[0]);
        form.dispatchEvent(new CustomEvent('form:invalido', { detail: delServidor.map((el) => el.name), bubbles: true }));
    }
}
