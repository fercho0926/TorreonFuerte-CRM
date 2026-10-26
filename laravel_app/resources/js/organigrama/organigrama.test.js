import { beforeEach, describe, expect, it } from 'vitest';
import { iniciarOrganigrama } from './organigrama.js';

const HTML = `
<section data-organigrama>
  <div class="organigrama-acciones" hidden>
    <button type="button" data-organigrama-accion="expandir">Expandir todo</button>
    <button type="button" data-organigrama-accion="colapsar">Colapsar todo</button>
  </div>
  <ul><li><details><summary>Sede</summary><ul><li><details><summary>Líder</summary><ul><li>Miembro</li></ul></details></li></ul></details></li></ul>
</section>`;

describe('organigrama', () => {
    beforeEach(() => { document.body.innerHTML = HTML; });

    it('muestra las acciones al iniciar', () => {
        iniciarOrganigrama();
        expect(document.querySelector('.organigrama-acciones').hidden).toBe(false);
    });

    it('expande y colapsa todos los nodos', () => {
        iniciarOrganigrama();
        document.querySelector('[data-organigrama-accion="expandir"]').click();
        expect([...document.querySelectorAll('details')].every((d) => d.open)).toBe(true);
        document.querySelector('[data-organigrama-accion="colapsar"]').click();
        expect([...document.querySelectorAll('details')].some((d) => d.open)).toBe(false);
    });

    it('no falla si la pantalla no tiene organigrama', () => {
        document.body.innerHTML = '<p>otra pantalla</p>';
        expect(() => iniciarOrganigrama()).not.toThrow();
    });
});
