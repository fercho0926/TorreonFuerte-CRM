/**
 * Grilla estándar con DataTables (modo servidor), con aspecto de tabla de PrimeNG:
 * ordenar por columna, filtros por columna, filas por página y exportar a CSV.
 * Se activa en toda `table[data-grilla]` que pinta `<x-ui.grilla>`.
 * Mejora progresiva: sin JavaScript la tabla del servidor sigue funcionando.
 */
import jQuery from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

export const ESPAÑOL = {
    emptyTable: 'No se encontraron resultados.',
    zeroRecords: 'No se encontraron resultados.',
    info: 'Mostrando _START_ a _END_ de _TOTAL_',
    infoEmpty: 'Sin resultados',
    infoFiltered: '(de _MAX_ en total)',
    lengthMenu: 'Filas por página: _MENU_',
    processing: 'Cargando…',
    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
    aria: { orderable: 'Ordenar por esta columna', orderableReverse: 'Ordenar en sentido inverso' },
};

/** Parámetros que se envían al servidor (también usados para el enlace de exportación). */
export function parametrosFiltro(estado) {
    const limpio = {};
    Object.entries(estado).forEach(([clave, valor]) => {
        if (valor !== '' && valor !== null && valor !== undefined && valor !== false) limpio[clave] = valor;
    });
    return limpio;
}

export function urlExportar(base, estado) {
    const consulta = new URLSearchParams(parametrosFiltro(estado)).toString();
    return consulta ? `${base}?${consulta}` : base;
}

export function retardo(fn, ms = 300) {
    let id;
    return (...args) => {
        clearTimeout(id);
        id = setTimeout(() => fn(...args), ms);
    };
}

/** Lee los campos con nombre del formulario de búsqueda (q y filtros propios del módulo). */
export function leerFormulario(formulario) {
    const valores = {};
    if (!formulario) return valores;
    Array.from(formulario.elements).forEach((campo) => {
        if (!campo.name || campo.name.startsWith('_') || campo.name === 'por_pagina') return;
        if (campo.type === 'checkbox' || campo.type === 'radio') {
            valores[campo.name] = campo.checked ? (campo.value || '1') : '';
        } else if (campo.type !== 'submit' && campo.type !== 'button') {
            valores[campo.name] = campo.value;
        }
    });
    return valores;
}

export function iniciarGrilla(tabla) {
    if (!tabla || tabla.dataset.listo) return null;
    tabla.dataset.listo = '1';
    window.jQuery = window.jQuery || jQuery;

    const columnas = JSON.parse(tabla.dataset.columnas);
    const opcionesPagina = JSON.parse(tabla.dataset.opcionesPagina);
    const [ordenIndice, ordenDir] = JSON.parse(tabla.dataset.orden);
    const estado = JSON.parse(tabla.dataset.estado);

    const tarjeta = tabla.closest('.grilla-card');
    const formulario = document.querySelector('[data-grilla-form]');
    Object.assign(estado, leerFormulario(formulario));

    tarjeta.querySelector('.grilla-pie-servidor')?.remove();
    // La fila de «sin resultados» del servidor no es un dato: DataTables pinta el suyo.
    tabla.querySelector('tbody .grilla-vacio-servidor')?.remove();
    tarjeta.classList.add('grilla-js');
    // Los formularios de las filas del servidor ya los inició el arranque de formularios.
    tabla.querySelectorAll('tbody form[data-form]').forEach((f) => { f.dataset.grillaIniciado = '1'; });

    const consultar = retardo(() => api.ajax.reload());

    // Filtros por columna (segunda fila de la cabecera, pintada por el servidor).
    tabla.querySelectorAll('[data-filtro]').forEach((campo) => {
        campo.addEventListener(campo.tagName === 'SELECT' ? 'change' : 'input', () => {
            estado[campo.dataset.filtro] = campo.value;
            consultar();
        });
    });
    const filtros = tabla.querySelector('tr.grilla-filtros');

    const api = new DataTable(tabla, {
        serverSide: true,
        processing: true,
        deferLoading: [Number(tabla.dataset.filtradas), Number(tabla.dataset.total)],
        ajax: {
            url: tabla.dataset.url,
            data: (d) => Object.assign(d, parametrosFiltro(estado)),
            error: () => {
                tarjeta.querySelector('.grilla-error')?.remove();
                tarjeta.insertAdjacentHTML('afterbegin', '<div class="alert alert-danger grilla-error m-2" role="alert">No se pudo cargar la lista. Intenta de nuevo.</div>');
            },
        },
        pageLength: Number(tabla.dataset.porPagina),
        lengthMenu: opcionesPagina,
        order: [[ordenIndice, ordenDir]],
        orderCellsTop: true,
        searching: false,
        responsive: true,
        autoWidth: false,
        language: { ...ESPAÑOL, emptyTable: tabla.dataset.vacio || ESPAÑOL.emptyTable, zeroRecords: tabla.dataset.vacio || ESPAÑOL.zeroRecords },
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: ['pageLength', 'info'],
            bottomEnd: 'paging',
        },
        columnDefs: columnas.map((c, i) => ({
            targets: i,
            orderable: c.ordenable,
            responsivePriority: c.prioridad,
            ...(c.acciones ? { className: 'text-end text-nowrap' } : {}),
        })),
    });

    tarjeta.querySelector('.grilla-error')?.remove();

    const exportar = tarjeta.querySelector('[data-grilla-exportar]');
    const estadoExportar = () => {
        const orden = api.order()[0] || [ordenIndice, ordenDir];
        return { ...estado, orden: columnas[orden[0]]?.clave, dir: orden[1] };
    };
    const actualizarExportar = () => { if (exportar) exportar.href = urlExportar(tabla.dataset.exportar, estadoExportar()); };
    const contador = tarjeta.querySelector('[data-grilla-contador]');
    api.on('xhr', () => tarjeta.querySelector('.grilla-error')?.remove());
    api.on('order.dt xhr', actualizarExportar);
    api.on('draw', () => {
        const total = api.page.info().recordsDisplay;
        if (contador) contador.textContent = `${total} ${total === 1 ? 'resultado' : 'resultados'}`;
    });
    actualizarExportar();

    // Las filas nuevas pueden traer formularios (selectores, confirmaciones): se inician al dibujarlas.
    api.on('draw', () => {
        const pendientes = Array.from(tabla.querySelectorAll('tbody form[data-form]:not([data-grilla-iniciado])'));
        if (!pendientes.length) return;
        import('../forms/index.js').then(({ iniciarFormulario }) => pendientes.forEach((f) => {
            f.dataset.grillaIniciado = '1';
            iniciarFormulario(f);
        }));
    });

    if (formulario) {
        // La búsqueda es en vivo: el botón «Buscar» solo hace falta sin JavaScript (Enter también funciona).
        formulario.classList.add('grilla-form-js');
        formulario.addEventListener('submit', (e) => { e.preventDefault(); Object.assign(estado, leerFormulario(formulario)); api.ajax.reload(); });
        formulario.addEventListener('input', (e) => {
            if (e.target.type === 'search' || e.target.type === 'text') { Object.assign(estado, leerFormulario(formulario)); consultar(); }
        });
        formulario.addEventListener('change', (e) => {
            if (e.target.type !== 'search' && e.target.type !== 'text') { Object.assign(estado, leerFormulario(formulario)); api.ajax.reload(); }
        });
        // Select2 dispara `change` con jQuery; se escucha también por ese canal.
        window.jQuery?.(formulario).on('change', 'select', () => { Object.assign(estado, leerFormulario(formulario)); api.ajax.reload(); });
    }

    const limpiar = document.querySelector('[data-grilla-limpiar]');
    limpiar?.addEventListener('click', (e) => {
        e.preventDefault();
        Object.keys(estado).forEach((clave) => { estado[clave] = ''; });
        formulario?.querySelectorAll('input,select').forEach((campo) => {
            if (campo.type === 'checkbox') campo.checked = false;
            else if (campo.name && !campo.name.startsWith('_')) campo.value = '';
        });
        window.jQuery?.(formulario).find('select').trigger('change.select2');
        filtros?.querySelectorAll('input,select').forEach((campo) => { campo.value = ''; });
        api.ajax.reload();
    });

    return api;
}

export function iniciarGrillas() {
    document.querySelectorAll('table[data-grilla]').forEach(iniciarGrilla);
}
