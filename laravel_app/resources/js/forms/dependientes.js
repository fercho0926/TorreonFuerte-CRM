/**
 * Campos que reaccionan a otros:
 *   data-visible-si="campo:valor"          muestra/oculta (y deshabilita lo oculto para que no se envíe)
 *   data-requerido-si="campo:valor[,valor]" alterna "obligatorio"
 * Para casillas, el valor "1" significa marcada.
 */
import { escaparSelector } from './texto.js';


/** Valor actual de un campo del formulario por nombre ('' si es casilla desmarcada). */
export function valorDe(form, nombre) {
    const el = form.elements[nombre];
    if (!el) return '';
    const control = el instanceof RadioNodeList ? el[0] : el;
    if (control && control.type === 'checkbox') return control.checked ? (control.value || '1') : '';
    if (control && control.type === 'radio') return el.value ?? '';

    return el.value ?? '';
}

/**
 * Interpreta "campo:valor1,valor2" (o varias reglas separadas por "|": basta con que se cumpla una)
 * y evalúa la regla contra el formulario.
 */
export function cumple(form, regla) {
    if (!regla) return true;

    return regla.split('|').some((parte) => {
        const [campo, valores = ''] = parte.split(':');
        const actual = String(valorDe(form, campo.trim()));
        const aceptados = valores.split(',').map((v) => v.trim());

        return aceptados.includes(actual);
    });
}

const CONTROLES = 'input, select, textarea, button';

function fijarVisibilidad(elemento, visible) {
    const yaVisible = !elemento.classList.contains('fm-oculto');
    if (visible === yaVisible) return;

    elemento.classList.toggle('fm-oculto', !visible);
    elemento.setAttribute('aria-hidden', visible ? 'false' : 'true');
    if (visible) {
        elemento.classList.add('fm-aparece');
        setTimeout(() => elemento.classList.remove('fm-aparece'), 300);
    }

    // Lo oculto no se envía ni se valida; al volver a mostrarse recupera su estado.
    elemento.querySelectorAll(CONTROLES).forEach((c) => {
        if (!visible) {
            if (!c.disabled) {
                c.disabled = true;
                c.dataset.depDeshabilitado = '1';
            }
        } else if (c.dataset.depDeshabilitado) {
            c.disabled = false;
            delete c.dataset.depDeshabilitado;
        }
    });
}

function fijarRequerido(control, requerido) {
    control.required = requerido;
    if (requerido) control.setAttribute('aria-required', 'true');
    else control.removeAttribute('aria-required');

    const etiqueta = control.id ? document.querySelector(`label[for="${escaparSelector(control.id)}"]`) : null;
    etiqueta?.classList.toggle('fm-req', requerido);
}

export function evaluar(form) {
    form.querySelectorAll('[data-visible-si]').forEach((el) => {
        fijarVisibilidad(el, cumple(form, el.dataset.visibleSi));
    });
    form.querySelectorAll('[data-requerido-si]').forEach((el) => {
        fijarRequerido(el, cumple(form, el.dataset.requeridoSi));
    });
}

export function iniciarDependientes(form) {
    const reglas = form.querySelectorAll('[data-visible-si], [data-requerido-si]');
    if (!reglas.length) return;

    evaluar(form);
    ['input', 'change'].forEach((evento) => form.addEventListener(evento, () => evaluar(form)));
}
