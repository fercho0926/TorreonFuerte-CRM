import { describe, expect, it } from 'vitest';
import { ANCHO_ESCRITORIO, CLAVE, aplicarInicial, guardarPlegado, iniciarMenuRecordado, leerPlegado } from './menu-recordado.js';

const almacenEnMemoria = () => {
    const datos = new Map();

    return { getItem: (k) => datos.get(k) ?? null, setItem: (k, v) => datos.set(k, String(v)) };
};

const almacenBloqueado = () => ({
    getItem: () => { throw new Error('bloqueado'); },
    setItem: () => { throw new Error('bloqueado'); },
});

describe('menú recordado', () => {
    it('guarda y lee la elección de plegado', () => {
        const almacen = almacenEnMemoria();

        expect(leerPlegado(almacen)).toBe(false);
        guardarPlegado(almacen, true);
        expect(almacen.getItem(CLAVE)).toBe('1');
        expect(leerPlegado(almacen)).toBe(true);
        guardarPlegado(almacen, false);
        expect(leerPlegado(almacen)).toBe(false);
    });

    it('no falla si el almacenamiento está bloqueado', () => {
        const almacen = almacenBloqueado();

        expect(leerPlegado(almacen)).toBe(false);
        expect(() => guardarPlegado(almacen, true)).not.toThrow();
    });

    it('pliega el menú al cargar solo en pantallas grandes', () => {
        const almacen = almacenEnMemoria();
        guardarPlegado(almacen, true);

        const grande = document.createElement('body');
        aplicarInicial(grande, ANCHO_ESCRITORIO, almacen);
        expect(grande.classList.contains('sidebar-collapse')).toBe(true);

        const movil = document.createElement('body');
        aplicarInicial(movil, ANCHO_ESCRITORIO - 1, almacen);
        expect(movil.classList.contains('sidebar-collapse')).toBe(false);
    });

    it('recuerda al pulsar el botón del menú en escritorio y no en móvil', async () => {
        document.body.innerHTML = '<a id="b" data-lte-toggle="sidebar" href="#"></a>';
        document.body.className = '';
        const almacen = almacenEnMemoria();
        const ventana = { innerWidth: 1280, localStorage: almacen };

        iniciarMenuRecordado(document, ventana);
        document.body.classList.add('sidebar-collapse'); // lo que hace AdminLTE al pulsar
        document.getElementById('b').click();
        await new Promise((r) => setTimeout(r, 5));
        expect(leerPlegado(almacen)).toBe(true);

        ventana.innerWidth = 400;
        document.body.classList.remove('sidebar-collapse');
        document.getElementById('b').click();
        await new Promise((r) => setTimeout(r, 5));
        expect(leerPlegado(almacen)).toBe(true); // en móvil no se modifica lo recordado
    });
});
