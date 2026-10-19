import { describe, expect, it, vi } from 'vitest';
import { leerFormulario, parametrosFiltro, retardo, urlExportar } from './grilla.js';

describe('parámetros de filtro', () => {
    it('descarta vacíos, nulos y falsos', () => {
        expect(parametrosFiltro({ q: 'ana', nombre: '', estado: null, inactivos: false, lider: undefined, celular: '311' })).toEqual({ q: 'ana', celular: '311' });
    });
});

describe('enlace de exportación', () => {
    it('sin filtros devuelve la base', () => {
        expect(urlExportar('/personas/exportar', { q: '' })).toBe('/personas/exportar');
    });

    it('incluye filtros y orden codificados', () => {
        const url = urlExportar('/personas/exportar', { q: 'ana maría', orden: 'lider', dir: 'desc' });
        expect(url).toContain('q=ana+mar%C3%ADa');
        expect(url).toContain('orden=lider');
        expect(url).toContain('dir=desc');
    });
});

describe('retardo', () => {
    it('agrupa llamadas seguidas en una sola', () => {
        vi.useFakeTimers();
        const fn = vi.fn();
        const r = retardo(fn, 300);
        r(1); r(2); r(3);
        vi.advanceTimersByTime(300);
        expect(fn).toHaveBeenCalledTimes(1);
        expect(fn).toHaveBeenCalledWith(3);
        vi.useRealTimers();
    });
});

describe('lectura del formulario de búsqueda', () => {
    it('toma campos con nombre, casillas marcadas y descarta el token y por_pagina', () => {
        document.body.innerHTML = `<form id="f">
            <input type="hidden" name="_token" value="abc">
            <input type="search" name="q" value="ana">
            <input type="checkbox" name="inactivos" value="1" checked>
            <input type="checkbox" name="otro" value="1">
            <select name="sede_id"><option value="">Todas</option><option value="3" selected>Beta</option></select>
            <input name="por_pagina" value="50">
            <button type="submit" name="enviar">Buscar</button>
        </form>`;

        expect(leerFormulario(document.getElementById('f'))).toEqual({ q: 'ana', inactivos: '1', otro: '', sede_id: '3' });
    });

    it('tolera la ausencia de formulario', () => {
        expect(leerFormulario(null)).toEqual({});
    });
});
