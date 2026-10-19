import { describe, expect, it } from 'vitest';
import { iniciarMascaras, tipoDe, valorSinMascara } from './mascaras.js';

function armar(html) {
    document.body.innerHTML = `<form>${html}</form>`;
    const form = document.querySelector('form');
    iniciarMascaras(form);

    return form;
}

const escribir = (input, valor) => {
    input.value = valor;
    input.dispatchEvent(new Event('input', { bubbles: true }));
};

describe('valor enviado sin máscara', () => {
    it('el celular se envía solo con dígitos', () => {
        const form = armar('<input id="c" name="celular" data-mascara="celular">');
        const celular = form.querySelector('#c');

        escribir(celular, '(310) 555-9999');

        expect(celular.value).toBe('310 555 9999');
        expect(valorSinMascara(celular)).toBe('3105559999');
    });

    it('el documento conserva solo letras y dígitos', () => {
        const form = armar('<input id="d" name="numero_documento" data-mascara="documento">');
        const doc = form.querySelector('#d');

        escribir(doc, 'ab-123 456');

        expect(doc.value).toBe('AB123456');
        expect(valorSinMascara(doc)).toBe('AB123456');
    });

    it('el monto visible vuelve a número plano al enviar', () => {
        const form = armar('<input id="m" name="costo" data-monto>');
        const monto = form.querySelector('#m');

        escribir(monto, '50000');
        expect(monto.value).toBe('50.000');
        expect(valorSinMascara(monto)).toBe('50000');

        escribir(monto, '50.000,50');
        expect(valorSinMascara(monto)).toBe('50000.50');
    });

    it('un monto plano que llega del servidor se muestra con separador de miles', () => {
        const form = armar('<input id="m" name="costo" data-monto value="50000.00">');
        const monto = form.querySelector('#m');

        expect(monto.value).toBe('50.000');
        expect(valorSinMascara(monto)).toBe('50000');
    });

    it('pegar valores con espacios y símbolos no pierde información', () => {
        const form = armar('<input id="c" name="celular" data-mascara="celular">');
        const celular = form.querySelector('#c');

        escribir(celular, ' +57 310-555.9999 ');

        expect(valorSinMascara(celular)).toBe('573105559999');
    });

    it('al enviar se quita la máscara y, si el envío se cancela, se vuelve a mostrar', async () => {
        const form = armar('<input id="c" name="celular" data-mascara="celular" value="3105559999">');
        const celular = form.querySelector('#c');
        expect(celular.value).toBe('310 555 9999'); // valor inicial formateado

        let enviado = null;
        form.addEventListener('submit', (e) => { enviado = celular.value; e.preventDefault(); });
        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));

        expect(enviado).toBe('3105559999');
        await new Promise((resolver) => setTimeout(resolver, 5));
        expect(celular.value).toBe('310 555 9999');
    });

    it('los campos sin máscara no se tocan', () => {
        const form = armar('<input id="n" name="nombres" value="Ana  Pérez">');
        const nombres = form.querySelector('#n');

        expect(tipoDe(nombres)).toBeNull();
        expect(valorSinMascara(nombres)).toBe('Ana  Pérez');
    });
});
