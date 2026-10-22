// @vitest-environment jsdom
import { beforeEach, describe, expect, it } from 'vitest';
import { borrar, claveDe, guardar, leer, limpiarEnviados, PREFIJO, restaurar, serializar } from './borrador.js';

function formulario() {
    document.body.innerHTML = `<form id="f" method="post">
        <input type="hidden" name="_token" value="abc">
        <input name="nombres" value="Ana">
        <input name="apellidos" value="">
        <input type="password" name="password" value="secreta">
        <input name="clave_actual" value="otra">
        <input type="file" name="foto">
        <input type="checkbox" name="ministerios[]" value="1" checked>
        <input type="checkbox" name="ministerios[]" value="2">
        <select name="estado"><option value="a">A</option><option value="b" selected>B</option></select>
    </form>`;

    return document.getElementById('f');
}

describe('serializar', () => {
    it('guarda texto, selectores y casillas marcadas', () => {
        expect(serializar(formulario())).toEqual({ nombres: 'Ana', 'ministerios[]': ['1'], estado: 'b' });
    });

    it('excluye contraseñas, claves, tokens, archivos y campos vacíos', () => {
        const datos = serializar(formulario());
        ['password', 'clave_actual', '_token', 'foto', 'apellidos'].forEach((k) => expect(datos).not.toHaveProperty(k));
    });
});

describe('almacén', () => {
    let almacen;
    beforeEach(() => {
        sessionStorage.clear();
        almacen = sessionStorage;
    });

    it('guarda y lee con marca de envío', () => {
        guardar(almacen, claveDe('/personas/create'), { a: '1' }, true);
        expect(leer(almacen, claveDe('/personas/create'))).toMatchObject({ datos: { a: '1' }, enviado: true });
    });

    it('descarta lo vencido', () => {
        guardar(almacen, 'k', { a: '1' });
        expect(leer(almacen, 'k', Date.now() + 31 * 60 * 1000)).toBeNull();
        expect(almacen.getItem('k')).toBeNull();
    });

    it('borra', () => {
        guardar(almacen, 'k', { a: '1' });
        borrar(almacen, 'k');
        expect(leer(almacen, 'k')).toBeNull();
    });

    it('la clave ignora la barra final', () => {
        expect(claveDe('/personas/create/')).toBe(`${PREFIJO}/personas/create`);
    });
});

describe('restaurar', () => {
    it('rellena solo los campos vacíos y las casillas sin marcar', () => {
        const f = formulario();
        f.elements.nombres.value = 'Beto'; // ya escrito: no se pisa
        const n = restaurar(f, { nombres: 'Ana', apellidos: 'Pérez', 'ministerios[]': ['2'] });

        expect(f.elements.nombres.value).toBe('Beto');
        expect(f.elements.apellidos.value).toBe('Pérez');
        expect(f.querySelector('input[value="2"]').checked).toBe(true);
        expect(n).toBe(2);
    });
});

describe('limpiarEnviados', () => {
    it('borra los enviados de otras pantallas y conserva los de la actual y los no enviados', () => {
        sessionStorage.clear();
        guardar(sessionStorage, claveDe('/personas/create'), { a: '1' }, true);
        guardar(sessionStorage, claveDe('/eventos/create'), { b: '1' }, true);
        guardar(sessionStorage, claveDe('/celulas/create'), { c: '1' }, false);

        limpiarEnviados(sessionStorage, '/personas/create');

        expect(leer(sessionStorage, claveDe('/personas/create'))).not.toBeNull();
        expect(leer(sessionStorage, claveDe('/eventos/create'))).toBeNull();
        expect(leer(sessionStorage, claveDe('/celulas/create'))).not.toBeNull();
    });
});
