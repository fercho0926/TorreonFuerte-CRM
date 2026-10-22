/**
 * Calendario amigable con atajos "Hoy" y "Ayer". El campo base sigue siendo <input type="date">
 * (funciona sin JavaScript); aquí se muestra como d/m/Y y se envía como Y-m-d.
 */
import { ayer, hoy } from './texto.js';

// Estilos del calendario: se descargan junto con él (solo pantallas con campos de fecha).
import 'flatpickr/dist/flatpickr.min.css';

function limite(valor) {
    if (!valor) return undefined;

    return valor === 'hoy' ? 'today' : valor;
}

function agregarAtajos(instancia, atajos) {
    const pie = document.createElement('div');
    pie.className = 'fm-atajos-fecha d-flex justify-content-between px-2 pb-2';

    const definiciones = { hoy: ['Hoy', hoy], ayer: ['Ayer', ayer] };
    atajos.forEach((clave) => {
        const [texto, calcular] = definiciones[clave] ?? [];
        if (!texto) return;

        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn btn-sm btn-outline-primary';
        boton.textContent = texto;
        boton.addEventListener('click', () => {
            instancia.setDate(calcular(), true);
            instancia.close();
        });
        pie.appendChild(boton);
    });

    if (pie.children.length) instancia.calendarContainer.appendChild(pie);
}

export async function iniciarFechas(form) {
    const campos = form.querySelectorAll('input[data-fecha]');
    if (!campos.length) return;

    // El calendario solo se descarga en las pantallas que tienen campos de fecha (spec 008).
    const [{ default: flatpickr }, { Spanish }] = await Promise.all([import('flatpickr'), import('flatpickr/dist/l10n/es.js')]);

    campos.forEach((input) => {
        const atajos = (input.dataset.atajos ?? '').split(',').map((a) => a.trim()).filter(Boolean);
        const idOriginal = input.id;

        const instancia = flatpickr(input, {
            locale: Spanish,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true,
            maxDate: limite(input.dataset.max),
            minDate: limite(input.dataset.min),
            onReady: (_, __, fp) => {
                // El campo visible hereda el id y los atributos de accesibilidad del original.
                const visible = fp.altInput;
                if (idOriginal) {
                    fp.input.id = `${idOriginal}-valor`;
                    visible.id = idOriginal;
                }
                ['aria-describedby', 'aria-required', 'aria-invalid'].forEach((a) => {
                    if (fp.input.hasAttribute(a)) visible.setAttribute(a, fp.input.getAttribute(a));
                });
                visible.setAttribute('inputmode', 'numeric');
                visible.placeholder = 'dd/mm/aaaa';
                visible.classList.add('form-control');
                if (fp.input.classList.contains('is-invalid')) visible.classList.add('is-invalid');
                agregarAtajos(fp, atajos);
            },
        });

        input._flatpickr = instancia;
    });
}
