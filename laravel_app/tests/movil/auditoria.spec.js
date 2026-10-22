import { appendFileSync } from 'node:fs';
import { expect, test } from '@playwright/test';
import { cargarExcepciones, estaExceptuado } from './excepciones.mjs';
import { CON_SESION, SIN_SESION } from './pantallas.mjs';
import { medirPagina } from './reglas.mjs';

/**
 * Recorre todas las pantallas al ancho del proyecto (320, 360, 390, 430 y 740 horizontal) y falla ante:
 * desplazamiento horizontal, controles < 44 px, campos con letra < 16 px, tablas sin adaptar y texto cortado.
 */
const excepciones = cargarExcepciones();

async function abrir(page, ruta) {
    await page.goto(`/${ruta}`, { waitUntil: 'load' });
    // Las grillas pintan su contenedor al iniciar; se espera a que termine para medir el diseño final.
    if (await page.locator('table[data-grilla]').count()) await page.waitForSelector('.dt-container', { timeout: 10_000 });
    await page.waitForTimeout(250);
}

function filtrar(lista, ruta, regla) {
    return lista.filter((texto) => !estaExceptuado(excepciones, ruta || 'inicio', regla, texto));
}

function verificar(medida, ruta, ancho) {
    const informe = {
        desborde: medida.desborde.hay ? [`la página mide ${medida.desborde.ancho}px en ${ancho}px: ${medida.desborde.culpables.join('; ')}`] : [],
        controles: medida.controles,
        letras: medida.letras,
        tablas: medida.tablas,
        cortados: medida.cortados,
    };
    // Para analizar los hallazgos en conjunto: MOVIL_REPORTE=ruta.jsonl npm run test:movil
    if (process.env.MOVIL_REPORTE) appendFileSync(process.env.MOVIL_REPORTE, `${JSON.stringify({ ruta, ancho, informe })}
`);
    const reglas = { desborde: 'desborde', controles: 'tactil', letras: 'letra', tablas: 'tabla', cortados: 'cortado' };
    for (const [clave, regla] of Object.entries(reglas)) {
        const pendientes = filtrar(informe[clave], ruta, regla);
        expect.soft(pendientes, `[${regla}] /${ruta} a ${ancho}px`).toEqual([]);
    }
}

test.describe('con sesión', () => {
    for (const ruta of CON_SESION) {
        test(`/${ruta}`, async ({ page }, info) => {
            await abrir(page, ruta);
            verificar(await page.evaluate(medirPagina), ruta, page.viewportSize().width);
        });
    }
});

test.describe('sin sesión', () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    for (const ruta of SIN_SESION) {
        test(`/${ruta || '(inicio de sesión)'}`, async ({ page }) => {
            await abrir(page, ruta);
            verificar(await page.evaluate(medirPagina), ruta, page.viewportSize().width);
        });
    }
});
