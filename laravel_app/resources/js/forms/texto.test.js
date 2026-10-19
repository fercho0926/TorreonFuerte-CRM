import { describe, expect, it } from 'vitest';
import { ayer, formatearCelular, formatearMiles, hoy, montoPlano, normalizar, soloAlfanumerico, soloDigitos, visibleDesdePlano } from './texto.js';

describe('normalizar', () => {
    it('ignora mayúsculas y tildes', () => {
        expect(normalizar('Ángela Pérez')).toBe('angela perez');
        expect(normalizar('  ÑANDÚ ')).toBe('nandu');
    });

    it('tolera valores vacíos', () => {
        expect(normalizar(null)).toBe('');
        expect(normalizar(undefined)).toBe('');
    });
});

describe('limpieza de valores pegados', () => {
    it('deja solo dígitos del celular', () => {
        expect(soloDigitos('(310) 555-9999')).toBe('3105559999');
        expect(soloDigitos(' +57 310 555 9999 ')).toBe('573105559999');
    });

    it('deja letras y dígitos del documento', () => {
        expect(soloAlfanumerico('ab-123 456')).toBe('AB123456');
    });
});

describe('formato visible', () => {
    it('agrupa el celular', () => {
        expect(formatearCelular('3105559999')).toBe('310 555 9999');
        expect(formatearCelular('310')).toBe('310');
        expect(formatearCelular('31055')).toBe('310 55');
    });

    it('agrega separador de miles al monto', () => {
        expect(formatearMiles(50000)).toBe('50.000');
        expect(formatearMiles('1234567')).toBe('1.234.567');
        expect(formatearMiles('')).toBe('');
        expect(formatearMiles('50000,5')).toBe('50.000,5');
        expect(formatearMiles('50.000,50')).toBe('50.000,50');
        expect(formatearMiles(',5')).toBe('0,5');
        expect(formatearMiles('1.0005')).toBe('10.005');
        expect(formatearMiles('10.00')).toBe('1.000');
    });
});

describe('valor enviado sin máscara', () => {
    it('el monto visible vuelve a número plano', () => {
        expect(montoPlano('50.000,50')).toBe('50000.50');
        expect(montoPlano('50.000')).toBe('50000');
        expect(montoPlano('$ 1.234.567')).toBe('1234567');
        expect(montoPlano(',5')).toBe('0.5');
        expect(montoPlano('')).toBe('');
    });

    it('un valor plano del servidor se muestra en formato visible y vuelve igual', () => {
        expect(visibleDesdePlano('50000.00')).toBe('50.000');
        expect(visibleDesdePlano('50000.50')).toBe('50.000,5');
        expect(visibleDesdePlano('1234567')).toBe('1.234.567');
        expect(visibleDesdePlano('0')).toBe('0');
        expect(montoPlano(visibleDesdePlano('50000.00'))).toBe('50000');
        expect(montoPlano(visibleDesdePlano('50000.25'))).toBe('50000.25');
    });
});

describe('fechas de atajo', () => {
    it('hoy y ayer en formato Y-m-d', () => {
        const base = new Date(2026, 9, 19, 15, 30);
        expect(hoy(base)).toBe('2026-10-19');
        expect(ayer(base)).toBe('2026-10-18');
        expect(ayer(new Date(2026, 2, 1))).toBe('2026-02-28');
    });
});
