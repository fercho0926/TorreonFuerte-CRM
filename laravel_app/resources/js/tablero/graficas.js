import ApexCharts from 'apexcharts';

/**
 * Gráficas del tablero (estilo «Dashboard v2» de AdminLTE). Cada contenedor `[data-grafica]` trae su JSON:
 * - { tipo: 'mensual', categorias: [...], series: [{ name, data }] } → área; la 2.ª serie (dinero) usa su propio eje.
 * - { tipo: 'dona', etiquetas: [...], valores: [...] }
 */

const color = (nombre) => getComputedStyle(document.documentElement).getPropertyValue(`--bs-${nombre}`).trim();
const dinero = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });
const entero = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

function opcionesMensual({ categorias, series }) {
    const conDinero = series.length > 1;
    return {
        chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
        series,
        colors: [color('primary'), color('success')],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
        legend: { show: conDinero, position: 'top' },
        xaxis: { categories: categorias },
        yaxis: [
            { title: { text: 'Miembros' }, min: 0, forceNiceScale: true, labels: { formatter: (v) => entero.format(v) } },
            ...(conDinero ? [{ opposite: true, title: { text: 'Recaudo' }, min: 0, labels: { formatter: (v) => dinero.format(v) } }] : []),
        ],
        tooltip: { shared: true, y: { formatter: (v, { seriesIndex }) => (seriesIndex === 1 ? dinero.format(v) : entero.format(v)) } },
        grid: { borderColor: color('border-color') },
    };
}

function opcionesDona({ etiquetas, valores }) {
    return {
        chart: { type: 'donut', height: 240, fontFamily: 'inherit' },
        series: valores,
        labels: etiquetas,
        colors: [color('info'), color('primary'), color('warning'), color('success'), color('secondary')],
        legend: { show: false },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
    };
}

export function iniciarGraficas() {
    for (const contenedor of document.querySelectorAll('[data-grafica]')) {
        const datos = JSON.parse(contenedor.dataset.grafica);
        const opciones = datos.tipo === 'dona' ? opcionesDona(datos) : opcionesMensual(datos);
        new ApexCharts(contenedor, opciones).render();
    }
}
