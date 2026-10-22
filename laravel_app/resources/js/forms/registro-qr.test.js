import { describe, expect, it, vi } from 'vitest';
import { consultar, iniciar, precargar } from './registro-qr.js';

function formulario() {
    document.body.innerHTML = `
        <form data-registro-buscar="/registro/abc/buscar">
            <input type="hidden" name="_token" value="tok">
            <input name="celular"><input name="numero_documento">
            <input name="nombres"><input name="apellidos"><input name="fecha_nacimiento">
        </form>`;

    return document.querySelector('form');
}

describe('registro por QR', () => {
    it('consulta con el token CSRF y pide JSON', async () => {
        const fetch = vi.fn().mockResolvedValue({ json: async () => ({ estado: 'nueva' }) });

        const datos = await consultar('/x', { celular: '310', numero_documento: '' }, 'tok', { fetch });

        expect(datos).toEqual({ estado: 'nueva' });
        const [, opciones] = fetch.mock.calls[0];
        expect(opciones.headers.Accept).toBe('application/json');
        expect(opciones.headers['X-CSRF-TOKEN']).toBe('tok');
        expect(opciones.body.toString()).toBe('celular=310'); // los valores vacíos no se envían
    });

    it('devuelve nulo si no hay red', async () => {
        expect(await consultar('/x', { celular: '1' }, 't', { fetch: vi.fn().mockRejectedValue(new Error('sin red')) })).toBeNull();
    });

    it('precarga solo los campos vacíos', () => {
        const form = formulario();
        form.elements.apellidos.value = 'Escrito por ella';

        const llenos = precargar(form, { nombres: 'Lucía', apellidos: 'Torres', fecha_nacimiento: '1988-02-03' });

        expect(llenos).toBe(2);
        expect(form.elements.nombres.value).toBe('Lucía');
        expect(form.elements.apellidos.value).toBe('Escrito por ella');
        expect(form.elements.fecha_nacimiento.value).toBe('1988-02-03');
    });

    it('al salir del celular consulta y completa los datos de quien ya existe', async () => {
        const form = formulario();
        window.fetch = vi.fn().mockResolvedValue({ json: async () => ({ estado: 'existente', nombres: 'Lucía', apellidos: 'Torres', fecha_nacimiento: '1988-02-03' }) });

        iniciar(document);
        form.elements.celular.value = '3004445555';
        form.elements.celular.dispatchEvent(new Event('change'));
        await new Promise((r) => setTimeout(r, 5));

        expect(form.elements.nombres.value).toBe('Lucía');
        expect(form.querySelector('[data-registro-aviso]').textContent).toContain('Encontramos tus datos');
    });

    it('muestra el mensaje del servidor ante un conflicto y no toca los campos', async () => {
        const form = formulario();
        window.fetch = vi.fn().mockResolvedValue({ json: async () => ({ estado: 'conflicto', mensaje: 'Contacta al administrador.' }) });

        iniciar(document);
        form.elements.numero_documento.value = '123';
        form.elements.numero_documento.dispatchEvent(new Event('change'));
        await new Promise((r) => setTimeout(r, 5));

        expect(form.querySelector('[data-registro-aviso]').textContent).toBe('Contacta al administrador.');
        expect(form.elements.nombres.value).toBe('');
    });

    it('no consulta si ambos campos están vacíos', async () => {
        const form = formulario();
        window.fetch = vi.fn();

        iniciar(document);
        form.elements.celular.dispatchEvent(new Event('change'));

        expect(window.fetch).not.toHaveBeenCalled();
    });
});
