import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

/**
 * Lista de excepciones (tests/movil/excepciones.json). Vacía por defecto.
 * Cada entrada exige `pantalla`, `selector`, `regla` y `motivo`: una excepción sin motivo hace fallar la prueba.
 */
export function cargarExcepciones() {
    const lista = JSON.parse(readFileSync(resolve(import.meta.dirname, 'excepciones.json'), 'utf-8'));
    if (!Array.isArray(lista)) throw new Error('excepciones.json debe ser una lista');

    lista.forEach((e, i) => {
        ['pantalla', 'selector', 'regla', 'motivo'].forEach((campo) => {
            if (!e[campo] || !String(e[campo]).trim()) throw new Error(`excepciones.json[${i}]: falta «${campo}» (toda excepción necesita motivo)`);
        });
    });

    return lista;
}

export function estaExceptuado(excepciones, pantalla, regla, selector) {
    return excepciones.some((e) => e.regla === regla && (e.pantalla === '*' || e.pantalla === pantalla) && selector.includes(e.selector));
}
