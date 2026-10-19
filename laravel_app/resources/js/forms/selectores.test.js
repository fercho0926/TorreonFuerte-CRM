import { describe, expect, it } from 'vitest';
import { coincide, textoContador } from './selectores.js';

describe('búsqueda en selectores', () => {
    it('ignora mayúsculas y tildes', () => {
        expect(coincide('angela', 'Ángela Pérez')).toBe(true);
        expect(coincide('PEREZ', 'Ángela Pérez')).toBe(true);
        expect(coincide('maria jose', 'María José Soto')).toBe(true);
    });

    it('exige que aparezcan todas las palabras escritas', () => {
        expect(coincide('ana soto', 'Ana María Soto')).toBe(true);
        expect(coincide('ana gomez', 'Ana María Soto')).toBe(false);
    });

    it('también busca en el dato de apoyo (documento o celular)', () => {
        expect(coincide('555999', 'Ana Pérez', 'Cel. 3105559999')).toBe(true);
        expect(coincide('1234567', 'Ana Pérez', 'Doc. 1234567')).toBe(true);
        expect(coincide('999999', 'Ana Pérez', 'Doc. 1234567')).toBe(false);
    });

    it('sin texto devuelve todas las opciones', () => {
        expect(coincide('', 'Ana')).toBe(true);
        expect(coincide('   ', 'Ana')).toBe(true);
    });
});

describe('contador de seleccionados', () => {
    it('refleja el número de opciones elegidas', () => {
        expect(textoContador(0)).toBe('Ninguna seleccionada');
        expect(textoContador(1)).toBe('1 seleccionada');
        expect(textoContador(10)).toBe('10 seleccionadas');
    });
});
