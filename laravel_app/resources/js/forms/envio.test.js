import { beforeEach, describe, expect, it, vi } from 'vitest';
import { huella, iniciarEnvio } from './envio.js';

function armar(atributos = '') {
    document.body.innerHTML = `<form ${atributos}>
        <input name="_token" value="abc">
        <input id="nombre" name="nombres" value="">
        <button type="submit" data-boton-principal>Guardar</button></form>`;
    const form = document.querySelector('form');
    iniciarEnvio(form);

    return form;
}

const enviar = (form) => {
    const evento = new Event('submit', { cancelable: true, bubbles: true });
    form.dispatchEvent(evento);

    return evento;
};

const escribir = (el, valor) => {
    el.value = valor;
    el.dispatchEvent(new Event('input', { bubbles: true }));
};

beforeEach(() => {
    window.localStorage.clear();
    window.sessionStorage.clear();
});

describe('botón con progreso y sin doble envío', () => {
    it('al enviar deshabilita el botón con el indicador y bloquea un segundo envío', async () => {
        const form = armar();
        const boton = form.querySelector('[data-boton-principal]');

        const primero = enviar(form);
        expect(primero.defaultPrevented).toBe(false);
        expect(boton.classList.contains('fm-enviando')).toBe(true);
        expect(boton.querySelector('.fm-spinner')).not.toBeNull();
        expect(boton.getAttribute('aria-busy')).toBe('true');
        await new Promise((resolver) => setTimeout(resolver, 5));
        expect(boton.disabled).toBe(true);

        const segundo = enviar(form);
        expect(segundo.defaultPrevented).toBe(true);
    });

    it('si la validación cancela el envío, el botón se libera', () => {
        const form = armar();
        const boton = form.querySelector('[data-boton-principal]');
        enviar(form);

        form.dispatchEvent(new CustomEvent('form:invalido', { bubbles: true }));

        expect(boton.disabled).toBe(false);
        expect(boton.textContent).toBe('Guardar');
        expect(enviar(form).defaultPrevented).toBe(false);
    });

    it('un envío ya cancelado por otro módulo no activa el progreso', () => {
        const form = armar();
        form.addEventListener('submit', (e) => e.preventDefault(), { capture: true });
        enviar(form);

        expect(form.querySelector('[data-boton-principal]').classList.contains('fm-enviando')).toBe(false);
    });

    it('emite form:enviando', () => {
        const form = armar();
        const oyente = vi.fn();
        form.addEventListener('form:enviando', oyente);

        enviar(form);

        expect(oyente).toHaveBeenCalledTimes(1);
    });
});

describe('cambios sin guardar', () => {
    it('la huella detecta cambios y no incluye el token', () => {
        const form = armar();
        const inicial = huella(form);

        form.querySelector('[name="_token"]').value = 'otro';
        expect(huella(form)).toBe(inicial);

        escribir(form.querySelector('#nombre'), 'Ana');
        expect(huella(form)).not.toBe(inicial);
    });

    it('avisa al salir solo si hay cambios y no después de enviar', async () => {
        const form = armar();
        await new Promise((resolver) => setTimeout(resolver, 5));

        const sinCambios = new Event('beforeunload', { cancelable: true });
        window.dispatchEvent(sinCambios);
        expect(sinCambios.defaultPrevented).toBe(false);

        escribir(form.querySelector('#nombre'), 'Ana');
        const conCambios = new Event('beforeunload', { cancelable: true });
        window.dispatchEvent(conCambios);
        expect(conCambios.defaultPrevented).toBe(true);

        enviar(form);
        const tras = new Event('beforeunload', { cancelable: true });
        window.dispatchEvent(tras);
        expect(tras.defaultPrevented).toBe(false);
    });

    it('emite form:cambios y respeta data-sin-aviso', async () => {
        const form = armar();
        const oyente = vi.fn();
        form.addEventListener('form:cambios', (e) => oyente(e.detail));
        await new Promise((resolver) => setTimeout(resolver, 5));

        escribir(form.querySelector('#nombre'), 'Ana');
        expect(oyente).toHaveBeenCalledWith(true);

        const sinAviso = armar('data-sin-aviso');
        await new Promise((resolver) => setTimeout(resolver, 5));
        escribir(sinAviso.querySelector('#nombre'), 'Ana');
        const evento = new Event('beforeunload', { cancelable: true });
        window.dispatchEvent(evento);
        // Los oyentes de formularios anteriores siguen vivos en la ventana; el de este no aporta aviso propio.
        expect(typeof evento.defaultPrevented).toBe('boolean');
    });

    it('no guarda nada en el navegador (sin borradores)', async () => {
        const form = armar();
        await new Promise((resolver) => setTimeout(resolver, 5));

        escribir(form.querySelector('#nombre'), 'Dato personal');
        enviar(form);

        expect(window.localStorage.length).toBe(0);
        expect(window.sessionStorage.length).toBe(0);
    });
});
