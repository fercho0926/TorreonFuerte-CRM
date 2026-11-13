import { beforeEach, describe, expect, it } from 'vitest';
import { iniciarLimpiarAlCargar } from './limpiar-al-cargar.js';

describe('limpiar-al-cargar', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <input data-limpiar-al-cargar value="100000">
            <input data-limpiar-al-cargar value="50000" class="is-invalid">
            <input value="no-se-toca">
        `;
    });

    it('vacía los campos marcados al iniciar', () => {
        iniciarLimpiarAlCargar();

        const [marcado, invalido, otro] = document.querySelectorAll('input');
        expect(marcado.value).toBe('');
        expect(otro.value).toBe('no-se-toca');
    });

    it('no toca un campo marcado si el servidor lo dejó inválido (old())', () => {
        iniciarLimpiarAlCargar();

        const invalido = document.querySelectorAll('input')[1];
        expect(invalido.value).toBe('50000');
    });

    it('también vacía al restaurar desde bfcache (evento pageshow)', () => {
        iniciarLimpiarAlCargar();
        const marcado = document.querySelector('[data-limpiar-al-cargar]:not(.is-invalid)');
        marcado.value = '999999';

        window.dispatchEvent(new Event('pageshow'));

        expect(marcado.value).toBe('');
    });
});
