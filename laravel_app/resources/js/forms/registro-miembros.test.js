import { describe, expect, it, vi } from 'vitest';
import { iniciar, precargar } from './registro-qr.js';

const CAMPOS = 'nombres,apellidos,fecha_nacimiento,tipo_documento,numero_documento,email,direccion,barrio,ciudad';

function formulario() {
    document.body.innerHTML = `
        <form data-registro-buscar="/registro-sede/abc/buscar" data-registro-campos="${CAMPOS}">
            <input type="hidden" name="_token" value="tok">
            <input name="celular"><input name="numero_documento">
            <input name="nombres"><input name="apellidos"><input name="fecha_nacimiento">
            <select name="tipo_documento"><option value=""></option><option value="CC">CC</option></select>
            <input name="email"><input name="direccion"><input name="barrio"><input name="ciudad">
        </form>`;

    return document.querySelector('form');
}

describe('registro de miembros por QR', () => {
    it('precarga todos los campos permitidos del miembro encontrado', () => {
        const form = formulario();

        const llenos = precargar(form, {
            nombres: 'Ana', apellidos: 'Pérez', fecha_nacimiento: '1990-05-04', tipo_documento: 'CC', numero_documento: '10203',
            email: 'ana@correo.com', direccion: 'Calle 1', barrio: 'Centro', ciudad: 'Bogotá',
        }, CAMPOS.split(','));

        expect(llenos).toBe(9);
        expect(form.elements.email.value).toBe('ana@correo.com');
        expect(form.elements.tipo_documento.value).toBe('CC');
        expect(form.elements.ciudad.value).toBe('Bogotá');
    });

    it('no pisa lo que la persona ya escribió', () => {
        const form = formulario();
        form.elements.direccion.value = 'Escrita por ella';

        precargar(form, { direccion: 'Guardada', barrio: 'Centro' }, CAMPOS.split(','));

        expect(form.elements.direccion.value).toBe('Escrita por ella');
        expect(form.elements.barrio.value).toBe('Centro');
    });

    it('al salir del celular consulta y completa los campos ampliados con un miembro existente', async () => {
        const form = formulario();
        const fetch = vi.fn().mockResolvedValue({ json: async () => ({ estado: 'existente', nombres: 'Ana', email: 'ana@correo.com', barrio: 'Centro' }) });
        vi.stubGlobal('fetch', fetch);
        window.fetch = fetch;

        iniciar(document);
        form.elements.celular.value = '3105559999';
        form.elements.celular.dispatchEvent(new Event('change'));
        await new Promise((r) => setTimeout(r, 0));

        expect(fetch).toHaveBeenCalledTimes(1);
        expect(form.elements.email.value).toBe('ana@correo.com');
        expect(form.elements.barrio.value).toBe('Centro');
        expect(document.querySelector('[data-registro-aviso]').textContent).toContain('Encontramos tus datos');
    });

    it('con un miembro nuevo no llena nada', async () => {
        const form = formulario();
        window.fetch = vi.fn().mockResolvedValue({ json: async () => ({ estado: 'nueva' }) });

        iniciar(document);
        form.elements.celular.value = '3105559999';
        form.elements.celular.dispatchEvent(new Event('change'));
        await new Promise((r) => setTimeout(r, 0));

        expect(form.elements.email.value).toBe('');
        expect(form.elements.nombres.value).toBe('');
    });
});
