/**
 * Máscaras de presentación (celular, documento, monto). El valor que se envía al servidor es
 * siempre el de antes: dígitos para el celular, letras y dígitos para el documento y un número
 * plano para el monto. La máscara solo ayuda a leer y escribir.
 */
import { formatearCelular, formatearMiles, montoPlano, soloAlfanumerico, soloDigitos, visibleDesdePlano } from './texto.js';

const TIPOS = {
    celular: { formato: formatearCelular, limpio: soloDigitos },
    documento: { formato: soloAlfanumerico, limpio: soloAlfanumerico },
    monto: { formato: formatearMiles, limpio: montoPlano },
};

export function tipoDe(input) {
    if (input.dataset.mascara && TIPOS[input.dataset.mascara]) return TIPOS[input.dataset.mascara];
    if ('monto' in input.dataset) return TIPOS.monto;

    return null;
}

/** Valor que se enviará (sin máscara) para un campo con máscara. */
export function valorSinMascara(input) {
    const tipo = tipoDe(input);

    return tipo ? tipo.limpio(input.value) : input.value;
}

function aplicar(input) {
    const tipo = tipoDe(input);
    if (!tipo) return;

    const desdeElFinal = input.value.length - (input.selectionStart ?? input.value.length);
    const nuevo = tipo.formato(input.value);
    if (nuevo === input.value) return;

    input.value = nuevo;
    const pos = Math.max(0, nuevo.length - desdeElFinal);
    try {
        input.setSelectionRange(pos, pos);
    } catch {
        // Algunos tipos de campo no admiten selección; no es crítico.
    }
}

export function iniciarMascaras(form) {
    const campos = Array.from(form.querySelectorAll('[data-mascara], [data-monto]')).filter((el) => tipoDe(el));
    if (!campos.length) return;

    campos.forEach((input) => {
        // Valor inicial (editar un registro o volver con errores): los montos llegan en formato plano.
        if (tipoDe(input) === TIPOS.monto) input.value = visibleDesdePlano(input.value);
        aplicar(input);
        input.addEventListener('input', () => aplicar(input));
        input.addEventListener('paste', () => setTimeout(() => aplicar(input), 0));
    });

    // Al enviar se quita la máscara; si el envío se cancela, se vuelve a mostrar.
    form.addEventListener('submit', () => {
        campos.forEach((input) => {
            input.dataset.visible = input.value;
            input.value = valorSinMascara(input);
        });
        setTimeout(() => campos.forEach((input) => {
            if (input.dataset.visible !== undefined) {
                input.value = input.dataset.visible;
                delete input.dataset.visible;
            }
        }), 0);
    });
}
