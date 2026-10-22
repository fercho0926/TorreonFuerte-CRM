import { describe, expect, it } from 'vitest';
import { columnasSecundarias, contarFiltrosActivos, etiquetaDeCelda, leerOrden, opcionesOrden } from './movil.js';

const columnas = [
    { clave: 'nombre', titulo: 'Nombre', ordenable: true, prioridad: 1, acciones: false },
    { clave: 'estado', titulo: 'Estado', ordenable: true, prioridad: 3, acciones: false },
    { clave: 'lider', titulo: 'Líder', ordenable: true, prioridad: 4, acciones: false },
    { clave: 'sede', titulo: 'Sede', ordenable: false, prioridad: 50, acciones: false },
    { clave: 'acciones', titulo: 'Acciones', ordenable: false, prioridad: 1, acciones: true },
];

describe('contarFiltrosActivos', () => {
    it('cuenta solo los filtros con valor', () => {
        expect(contarFiltrosActivos({ nombre: 'ana', estado: '', lider: '  ', celular: 'x' })).toBe(2);
    });

    it('tolera nulos y ausencias', () => {
        expect(contarFiltrosActivos({ a: null, b: undefined })).toBe(0);
        expect(contarFiltrosActivos(undefined)).toBe(0);
    });
});

describe('columnasSecundarias', () => {
    it('devuelve las de prioridad mayor a 3 que no son acciones', () => {
        expect(columnasSecundarias(columnas)).toEqual([2, 3]);
    });

    it('acepta otro límite', () => {
        expect(columnasSecundarias(columnas, 1)).toEqual([1, 2, 3]);
    });
});

describe('etiquetaDeCelda', () => {
    it('devuelve el título de la columna', () => {
        expect(etiquetaDeCelda(columnas, 2)).toBe('Líder');
        expect(etiquetaDeCelda(columnas, 99)).toBe('');
    });
});

describe('opciones de orden', () => {
    it('ofrece ascendente y descendente solo para columnas ordenables', () => {
        const opciones = opcionesOrden(columnas);
        expect(opciones.map((o) => o.valor)).toEqual(['0:asc', '0:desc', '1:asc', '1:desc', '2:asc', '2:desc']);
        expect(opciones[1].texto).toBe('Nombre (Z–A)');
    });

    it('interpreta el valor elegido', () => {
        expect(leerOrden('2:desc')).toEqual([2, 'desc']);
        expect(leerOrden('0:otra')).toEqual([0, 'asc']);
    });
});
