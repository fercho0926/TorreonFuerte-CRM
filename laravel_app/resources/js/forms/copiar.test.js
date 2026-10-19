import { describe, expect, it, vi } from 'vitest';
import { copiarTexto, iniciarCopiar } from './copiar.js';

describe('copiar mensaje', () => {
    it('usa el portapapeles cuando existe', async () => {
        const escribir = vi.fn().mockResolvedValue();
        const ventana = { navigator: { clipboard: { writeText: escribir } }, document };

        expect(await copiarTexto('hola', ventana)).toBe(true);
        expect(escribir).toHaveBeenCalledWith('hola');
    });

    it('usa el respaldo con execCommand si el portapapeles falla', async () => {
        const ventana = { navigator: { clipboard: { writeText: vi.fn().mockRejectedValue(new Error('no')) } }, document };
        document.execCommand = vi.fn().mockReturnValue(true);

        expect(await copiarTexto('hola', ventana)).toBe(true);
        expect(document.execCommand).toHaveBeenCalledWith('copy');
    });

    it('devuelve falso si nada funciona', async () => {
        const ventana = { navigator: {}, document };
        document.execCommand = vi.fn().mockReturnValue(false);

        expect(await copiarTexto('hola', ventana)).toBe(false);
    });

    it('al pulsar el botón avisa del resultado', async () => {
        document.body.innerHTML = '<button data-copiar="mensaje de prueba">Copiar</button>';
        window.navigator.clipboard = { writeText: vi.fn().mockResolvedValue() };
        const avisar = vi.fn();

        iniciarCopiar(document, avisar);
        document.querySelector('button').click();
        await new Promise((r) => setTimeout(r, 5));

        expect(avisar).toHaveBeenCalledWith('Mensaje copiado');
        expect(document.querySelector('button').dataset.estado).toBe('copiado');
    });
});
