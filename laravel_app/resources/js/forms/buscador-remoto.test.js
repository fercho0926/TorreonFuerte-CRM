import { describe, expect, it } from 'vitest';
import { configuracionRemota, idsSeleccionados, leerFiltros, parametrosDeBusqueda, plantillaResultado, procesarResultados } from './buscador-remoto.js';

describe('parámetros de búsqueda', () => {
    it('arma la consulta con texto, página y filtros', () => {
        expect(parametrosDeBusqueda('  ana  ', 2, { activas: 1, sin_lideres: 1 })).toEqual({ q: 'ana', pagina: 2, activas: 1, sin_lideres: 1 });
    });

    it('usa la página 1 por defecto y tolera texto vacío', () => {
        expect(parametrosDeBusqueda(undefined, undefined)).toEqual({ q: '', pagina: 1 });
    });

    it('combina los ids fijos de los filtros con los ya seleccionados sin repetir', () => {
        expect(parametrosDeBusqueda('ana', 1, { excluir: '3,4' }, ['4', '9'])).toEqual({ q: 'ana', pagina: 1, excluir: '3,4,9' });
        expect(parametrosDeBusqueda('ana', 1, { excluir: '3' })).toEqual({ q: 'ana', pagina: 1, excluir: '3' });
    });

    it('excluye lo ya seleccionado', () => {
        expect(parametrosDeBusqueda('ana', 1, {}, ['3', '', '9'])).toEqual({ q: 'ana', pagina: 1, excluir: '3,9' });
    });
});

describe('filtros y resultados', () => {
    it('lee el JSON de data-filtros y ignora uno inválido', () => {
        expect(leerFiltros('{"activas":1}')).toEqual({ activas: 1 });
        expect(leerFiltros('no es json')).toEqual({});
        expect(leerFiltros(undefined)).toEqual({});
    });

    it('adapta la respuesta del servidor al formato de Select2', () => {
        const salida = procesarResultados({
            resultados: [{ id: 5, texto: 'Ana Pérez', apoyo: 'CC 1 · 310', estado: 'celula', sinDocumento: true }],
            mas: true,
        });

        expect(salida.results).toEqual([{ id: '5', text: 'Ana Pérez', apoyo: 'CC 1 · 310', estado: 'celula', sinDocumento: true }]);
        expect(salida.pagination.more).toBe(true);
    });

    it('una respuesta vacía o rara no rompe', () => {
        expect(procesarResultados({})).toEqual({ results: [], pagination: { more: false } });
        expect(procesarResultados(null).results).toEqual([]);
    });
});

describe('plantilla de resultado', () => {
    it('muestra el apoyo debajo del nombre', () => {
        const nodo = plantillaResultado({ id: '1', text: 'Ana', apoyo: 'CC 99' });

        expect(nodo.textContent).toContain('Ana');
        expect(nodo.querySelector('.fm-apoyo-opcion').textContent).toBe('CC 99');
    });

    it('sin id (el placeholder) devuelve el texto tal cual', () => {
        expect(plantillaResultado({ text: 'Buscando…' })).toBe('Buscando…');
    });
});

describe('configuración de Select2', () => {
    it('pide al menos 2 letras y espera 250 ms', () => {
        document.body.innerHTML = '<select id="s" multiple data-fuente="/personas/buscar" data-filtros=\'{"activas":1}\'><option value="7" selected>Luz</option></select>';
        const select = document.getElementById('s');
        const config = configuracionRemota(select, null);

        expect(config.minimumInputLength).toBe(2);
        expect(config.ajax.delay).toBe(250);
        expect(config.ajax.url).toBe('/personas/buscar');
        expect(config.ajax.data({ term: 'lu', page: 1 })).toEqual({ q: 'lu', pagina: 1, activas: 1, excluir: '7' });
        expect(idsSeleccionados(select)).toEqual(['7']);
    });
});
