import { describe, expect, it } from 'vitest';
import { iniciarFechas } from './fechas.js';

function armar(conErrorDelServidor = false) {
    document.body.innerHTML = `
        <form data-form="1">
            <div class="fm-campo${conErrorDelServidor ? ' tiene-error' : ''}">
                <label for="fm-fecha_nacimiento">Fecha de nacimiento</label>
                <div class="input-group">
                    <input type="date" id="fm-fecha_nacimiento" name="fecha_nacimiento" data-fecha required max="2026-11-10"
                        ${conErrorDelServidor ? 'class="form-control is-invalid" aria-invalid="true"' : 'class="form-control"'}>
                </div>
                <div id="fm-fecha_nacimiento-error" class="invalid-feedback" role="alert">${conErrorDelServidor ? 'Este campo es obligatorio.' : ''}</div>
            </div>
        </form>`;

    return document.querySelector('form');
}

describe('sincronización de Flatpickr con la validación (RF-004)', () => {
    it('si el servidor ya marcó el campo como inválido, elegir una fecha en el calendario limpia el error', async () => {
        const form = armar(true);
        const mainInput = form.querySelector('[name="fecha_nacimiento"]');
        await iniciarFechas(form);

        expect(mainInput._flatpickr).toBeTruthy();
        expect(mainInput.type).toBe('hidden'); // confirma el supuesto: Flatpickr lo oculta con altInput

        // Simula elegir un día en el calendario (no escribir a mano en el campo visible).
        mainInput._flatpickr.setDate('2000-01-01', true);

        expect(mainInput.value).toBe('2000-01-01');
        expect(document.getElementById('fm-fecha_nacimiento-error').textContent).toBe('');
        expect(mainInput.classList.contains('is-invalid')).toBe(false);

        const altInput = form.querySelector('input[type="text"]');
        expect(altInput.classList.contains('is-invalid')).toBe(false);
        expect(altInput.classList.contains('is-valid')).toBe(true);
        expect(altInput.hasAttribute('aria-invalid')).toBe(false);
    });

    it('sin error previo, elegir una fecha deja el campo en estado correcto', async () => {
        const form = armar(false);
        const mainInput = form.querySelector('[name="fecha_nacimiento"]');
        await iniciarFechas(form);

        mainInput._flatpickr.setDate('1990-05-05', true);

        expect(mainInput.value).toBe('1990-05-05');
        expect(document.getElementById('fm-fecha_nacimiento-error').textContent).toBe('');
        const altInput = form.querySelector('input[type="text"]');
        expect(altInput.classList.contains('is-valid')).toBe(true);
    });
});
