import { beforeEach, describe, expect, it } from 'vitest';
import { iniciarMatriz } from './matriz.js';

const HTML = `
<form>
  <select name="alcance"><option value="sede">sede</option></select>
  <div data-matriz-permisos>
    <div data-matriz-atajos hidden>
      <button type="button" data-matriz-todo="1">Todo en lectura</button>
      <button type="button" data-matriz-todo="0">Todo sin acceso</button>
    </div>
    <input type="radio" name="permisos[tablero]" value="0"><input type="radio" name="permisos[tablero]" value="1" checked>
    <input type="radio" name="permisos[eventos]" value="0"><input type="radio" name="permisos[eventos]" value="1"><input type="radio" name="permisos[eventos]" value="2" checked>
  </div>
</form>`;

const valor = (nombre) => document.querySelector(`input[name="${nombre}"]:checked`)?.value;

describe('matriz de permisos', () => {
    beforeEach(() => { document.body.innerHTML = HTML; });

    it('muestra los atajos al iniciar', () => {
        iniciarMatriz();
        expect(document.querySelector('[data-matriz-atajos]').hidden).toBe(false);
    });

    it('«Todo en lectura» pone lectura en cada módulo', () => {
        iniciarMatriz();
        document.querySelector('[data-matriz-todo="1"]').click();
        expect(valor('permisos[tablero]')).toBe('1');
        expect(valor('permisos[eventos]')).toBe('1');
    });

    it('«Todo sin acceso» pone 0 en cada módulo', () => {
        iniciarMatriz();
        document.querySelector('[data-matriz-todo="0"]').click();
        expect(valor('permisos[tablero]')).toBe('0');
        expect(valor('permisos[eventos]')).toBe('0');
    });

    it('no toca el alcance de datos', () => {
        iniciarMatriz();
        document.querySelector('[data-matriz-todo="0"]').click();
        expect(document.querySelector('select[name="alcance"]').value).toBe('sede');
    });

    it('no falla en pantallas sin matriz', () => {
        document.body.innerHTML = '<p>otra pantalla</p>';
        expect(() => iniciarMatriz()).not.toThrow();
    });
});
