/**
 * Selectores con búsqueda (Select2): una opción o selección múltiple como etiquetas removibles,
 * con contador. La búsqueda ignora mayúsculas y tildes y también mira el dato de apoyo
 * (documento o celular). Sin JavaScript, el <select> nativo sigue funcionando.
 */
import { normalizar } from './texto.js';

const MINIMO_PARA_BUSCADOR = 10;

const IDIOMA = {
    noResults: () => 'No se encontraron resultados',
    searching: () => 'Buscando…',
    removeAllItems: () => 'Quitar todos',
    inputTooShort: () => 'Escribe para buscar',
};

/** ¿La opción coincide con lo escrito? Todas las palabras deben aparecer en el texto o en el apoyo. */
export function coincide(termino, texto, apoyo = '') {
    const palabras = normalizar(termino).split(/\s+/).filter(Boolean);
    if (!palabras.length) return true;
    const pajar = `${normalizar(texto)} ${normalizar(apoyo)}`;

    return palabras.every((p) => pajar.includes(p));
}

export function textoContador(total) {
    if (total === 0) return 'Ninguna seleccionada';

    return total === 1 ? '1 seleccionada' : `${total} seleccionadas`;
}

function matcher(parametros, datos) {
    if (!parametros.term || parametros.term.trim() === '') return datos;
    if (datos.children) return null;

    return coincide(parametros.term, datos.text, datos.element?.dataset?.apoyo ?? '') ? datos : null;
}

function plantillaResultado(opcion) {
    if (!opcion.id) return opcion.text;
    const apoyo = opcion.element?.dataset?.apoyo;
    if (!apoyo) return opcion.text;

    const contenedor = document.createElement('span');
    contenedor.textContent = opcion.text;
    const detalle = document.createElement('span');
    detalle.className = 'fm-apoyo-opcion';
    detalle.textContent = apoyo;
    contenedor.appendChild(detalle);

    return contenedor;
}

function barraMultiple(select, $) {
    const barra = document.createElement('div');
    barra.className = 'fm-multi-barra';
    barra.dataset.multiBarra = '1';

    const contador = document.createElement('span');
    contador.className = 'fm-multi-contador';
    contador.setAttribute('aria-live', 'polite');

    const todos = document.createElement('button');
    todos.type = 'button';
    todos.className = 'btn btn-link';
    todos.textContent = 'Seleccionar resultados';

    const limpiar = document.createElement('button');
    limpiar.type = 'button';
    limpiar.className = 'btn btn-link text-danger';
    limpiar.textContent = 'Limpiar';

    const actualizar = () => { contador.textContent = textoContador($(select).val()?.length ?? 0); };

    todos.addEventListener('click', () => {
        const instancia = $(select).data('select2');
        const termino = instancia?.dropdown?.$search?.val?.() ?? '';
        const valores = new Set($(select).val() ?? []);
        Array.from(select.options).forEach((o) => {
            if (o.value && coincide(termino, o.text, o.dataset.apoyo ?? '')) valores.add(o.value);
        });
        $(select).val(Array.from(valores)).trigger('change');
    });
    limpiar.addEventListener('click', () => $(select).val(null).trigger('change'));
    $(select).on('change', actualizar);
    actualizar();

    barra.append(contador, todos, limpiar);
    select.closest('.fm-campo')?.querySelector('.invalid-feedback')?.insertAdjacentElement('beforebegin', barra);
}

export function iniciarSelectores(form) {
    const $ = window.jQuery;
    if (!$ || !$.fn || !$.fn.select2) return;

    form.querySelectorAll('select').forEach((select) => {
        const multiple = select.multiple;
        const conBuscador = multiple || select.hasAttribute('data-buscar') || select.options.length > MINIMO_PARA_BUSCADOR;
        if (!conBuscador || select.dataset.sinSelect2 !== undefined) return;

        const vacia = Array.from(select.options).find((o) => o.value === '');

        $(select).select2({
            theme: 'bootstrap-5',
            width: '100%',
            language: IDIOMA,
            matcher,
            templateResult: plantillaResultado,
            placeholder: vacia?.textContent || (multiple ? 'Escribe para buscar y elegir' : 'Seleccione...'),
            allowClear: !multiple && !!vacia && !select.required,
            closeOnSelect: !multiple,
            dropdownParent: $(select.closest('.fm-campo') ?? document.body),
        });

        if (multiple) barraMultiple(select, $);
    });
}
