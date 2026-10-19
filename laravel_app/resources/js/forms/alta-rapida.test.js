import { describe, expect, it } from 'vitest';
import { datosDeAlta, iniciarAltaRapida, interpretarRespuesta } from './alta-rapida.js';

describe('datos del alta rápida', () => {
    it('quita espacios sobrantes y campos vacíos', () => {
        expect(datosDeAlta({ nombres: '  Ana   María ', apellidos: 'Pérez', celular: ' ', email: '' })).toEqual({ nombres: 'Ana María', apellidos: 'Pérez' });
    });

    it('tolera valores ausentes', () => {
        expect(datosDeAlta(undefined)).toEqual({});
    });
});

describe('respuesta del servidor', () => {
    it('201: persona creada', () => {
        expect(interpretarRespuesta(201, { id: 3, texto: 'Ana Pérez' })).toEqual({ tipo: 'ok', persona: { id: 3, texto: 'Ana Pérez' } });
    });

    it('409: posible duplicado', () => {
        expect(interpretarRespuesta(409, { duplicado: { id: 1, nombre: 'Ana Pérez' } })).toEqual({ tipo: 'duplicado', duplicado: { id: 1, nombre: 'Ana Pérez' } });
    });

    it('422: junta los mensajes de error', () => {
        const r = interpretarRespuesta(422, { errors: { celular: ['Indica un medio de contacto.'], email: ['Indica un medio de contacto.'] } });

        expect(r.tipo).toBe('errores');
        expect(r.mensajes).toHaveLength(2);
    });

    it('401, 403 y otros errores dan un mensaje claro', () => {
        expect(interpretarRespuesta(401, {}).mensaje).toContain('sesión');
        expect(interpretarRespuesta(403, {}).mensaje).toContain('permiso');
        expect(interpretarRespuesta(500, {}).tipo).toBe('error');
    });
});

describe('botón «Crear persona nueva»', () => {
    it('se añade una sola vez y solo con data-crear', () => {
        document.body.innerHTML = '<div class="fm-campo"><select id="a" multiple data-crear="/personas/rapida"></select><div class="invalid-feedback"></div></div><div class="fm-campo"><select id="b" multiple></select></div>';
        const $ = () => ({ trigger() {} });

        iniciarAltaRapida(document.getElementById('a'), $);
        iniciarAltaRapida(document.getElementById('a'), $);
        iniciarAltaRapida(document.getElementById('b'), $);

        expect(document.querySelectorAll('button').length).toBe(1);
        expect(document.querySelector('button').textContent).toContain('Crear persona nueva');
    });
});
