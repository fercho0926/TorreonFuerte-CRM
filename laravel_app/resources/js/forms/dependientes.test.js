import { describe, expect, it } from 'vitest';
import { cumple, iniciarDependientes, valorDe } from './dependientes.js';

function armar(html) {
    document.body.innerHTML = `<form>${html}</form>`;
    const form = document.querySelector('form');
    iniciarDependientes(form);

    return form;
}

const marcar = (el, marcado) => {
    el.checked = marcado;
    el.dispatchEvent(new Event('change', { bubbles: true }));
};

describe('campos visibles según otros', () => {
    const html = `
        <input type="checkbox" id="lider" name="es_lider" value="1">
        <div id="red" data-visible-si="es_lider:1"><label for="r">Red</label><select id="r" name="red_id"><option value="">-</option></select></div>`;

    it('muestra la sección al marcar la casilla y la oculta al desmarcar', () => {
        const form = armar(html);
        const seccion = form.querySelector('#red');
        const lider = form.querySelector('#lider');

        expect(seccion.classList.contains('fm-oculto')).toBe(true);

        marcar(lider, true);
        expect(seccion.classList.contains('fm-oculto')).toBe(false);
        expect(form.querySelector('#r').disabled).toBe(false);

        marcar(lider, false);
        expect(seccion.classList.contains('fm-oculto')).toBe(true);
    });

    it('lo oculto queda deshabilitado y no se envía', () => {
        const form = armar(html);

        expect(form.querySelector('#r').disabled).toBe(true);
        expect(new FormData(form).has('red_id')).toBe(false);

        marcar(form.querySelector('#lider'), true);
        expect(new FormData(form).has('red_id')).toBe(true);
    });

    it('no vuelve a habilitar lo que ya estaba deshabilitado por otra razón', () => {
        const form = armar(`<input type="checkbox" name="es_lider" value="1" checked>
            <div data-visible-si="es_lider:1"><input id="x" name="x" disabled></div>`);

        expect(form.querySelector('#x').disabled).toBe(true);
    });
});

describe('campos obligatorios según otros', () => {
    const html = `
        <select id="estado" name="estado"><option value="visitante">V</option><option value="miembro_activo">M</option></select>
        <label for="doc">Documento</label><input id="doc" name="numero_documento" data-requerido-si="estado:miembro_activo,nuevo_creyente|es_lider:1">
        <input type="checkbox" id="lider" name="es_lider" value="1">`;

    it('activa y desactiva required según el valor del estado', () => {
        const form = armar(html);
        const estado = form.querySelector('#estado');
        const doc = form.querySelector('#doc');

        expect(doc.required).toBe(false);

        estado.value = 'miembro_activo';
        estado.dispatchEvent(new Event('change', { bubbles: true }));
        expect(doc.required).toBe(true);
        expect(doc.getAttribute('aria-required')).toBe('true');
        expect(form.querySelector('label[for="doc"]').classList.contains('fm-req')).toBe(true);

        estado.value = 'visitante';
        estado.dispatchEvent(new Event('change', { bubbles: true }));
        expect(doc.required).toBe(false);
        expect(doc.hasAttribute('aria-required')).toBe(false);
    });

    it('basta con que se cumpla una de varias reglas separadas por |', () => {
        const form = armar(html);

        marcar(form.querySelector('#lider'), true);

        expect(form.querySelector('#doc').required).toBe(true);
    });
});

describe('evaluación de reglas', () => {
    it('lee el valor de casillas, selects y campos de texto', () => {
        document.body.innerHTML = '<form><input name="a" value="x"><input type="checkbox" name="b" value="1"><input type="checkbox" name="c" value="1" checked></form>';
        const form = document.querySelector('form');

        expect(valorDe(form, 'a')).toBe('x');
        expect(valorDe(form, 'b')).toBe('');
        expect(valorDe(form, 'c')).toBe('1');
        expect(valorDe(form, 'no_existe')).toBe('');
        expect(cumple(form, 'a:x')).toBe(true);
        expect(cumple(form, 'a:y,z')).toBe(false);
        expect(cumple(form, '')).toBe(true);
    });
});
