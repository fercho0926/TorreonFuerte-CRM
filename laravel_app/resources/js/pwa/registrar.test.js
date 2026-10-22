import { describe, expect, it, vi } from 'vitest';
import { puedeRegistrar, registrarServiceWorker } from './registrar.js';

const conSw = { serviceWorker: { register: vi.fn(() => Promise.resolve({})) } };

describe('puedeRegistrar', () => {
    it('acepta HTTPS y localhost', () => {
        expect(puedeRegistrar({ protocol: 'https:', hostname: 'iglesia.org' }, conSw)).toBe(true);
        expect(puedeRegistrar({ protocol: 'http:', hostname: 'localhost' }, conSw)).toBe(true);
        expect(puedeRegistrar({ protocol: 'http:', hostname: '127.0.0.1' }, conSw)).toBe(true);
    });

    it('rechaza HTTP en otros anfitriones o navegadores sin soporte', () => {
        expect(puedeRegistrar({ protocol: 'http:', hostname: 'iglesia.org' }, conSw)).toBe(false);
        expect(puedeRegistrar({ protocol: 'https:', hostname: 'iglesia.org' }, {})).toBe(false);
        expect(puedeRegistrar({ protocol: 'https:', hostname: 'iglesia.org' }, null)).toBe(false);
    });
});

describe('registrarServiceWorker', () => {
    it('registra la dirección declarada en la etiqueta', async () => {
        document.head.innerHTML = '<meta name="crm-sw" content="/sw.js">';
        const registrar = vi.fn(() => Promise.resolve({}));

        await registrarServiceWorker(document, { protocol: 'https:', hostname: 'a.org' }, { serviceWorker: { register: registrar } });

        expect(registrar).toHaveBeenCalledWith('/sw.js');
    });

    it('no hace nada sin la etiqueta', () => {
        document.head.innerHTML = '';

        expect(registrarServiceWorker(document, { protocol: 'https:', hostname: 'a.org' }, conSw)).toBeNull();
    });

    it('un fallo del registro no rompe la página', async () => {
        document.head.innerHTML = '<meta name="crm-sw" content="/sw.js">';
        const registrar = vi.fn(() => Promise.reject(new Error('x')));

        await expect(registrarServiceWorker(document, { protocol: 'https:', hostname: 'a.org' }, { serviceWorker: { register: registrar } })).resolves.toBeNull();
    });
});
