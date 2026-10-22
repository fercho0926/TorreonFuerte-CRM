import { expect, test } from '@playwright/test';

/** Carga en «4G lenta» (HU5, RF-014, CE-006): contenido útil en menos de 4 s la primera vez y 2 s en las visitas siguientes. */

const RED_4G_LENTA = { offline: false, latency: 150, downloadThroughput: (1.6 * 1024 * 1024) / 8, uploadThroughput: (750 * 1024) / 8 };
const PANTALLAS = [
    { ruta: '/dashboard', util: '.small-box, .card' },
    { ruta: '/personas', util: 'table.grilla tbody tr' },
    { ruta: '/personas/create', util: 'input[name="nombres"]' },
];

test.beforeEach(() => {
    test.skip(test.info().project.name !== 'movil-390', 'una sola ejecución');
});

for (const { ruta, util } of PANTALLAS) {
    test(`${ruta} muestra contenido útil rápido con 4G lenta`, async ({ page, context }) => {
        const sesion = await context.newCDPSession(page);
        await sesion.send('Network.enable');
        await sesion.send('Network.clearBrowserCache');
        await sesion.send('Network.emulateNetworkConditions', RED_4G_LENTA);

        const t0 = Date.now();
        await page.goto(ruta, { waitUntil: 'commit' });
        await page.locator(util).first().waitFor({ state: 'visible', timeout: 15_000 });
        const primera = Date.now() - t0;

        const t1 = Date.now();
        await page.reload({ waitUntil: 'commit' });
        await page.locator(util).first().waitFor({ state: 'visible', timeout: 15_000 });
        const segunda = Date.now() - t1;

        console.log(`${ruta}: primera ${primera} ms, siguiente ${segunda} ms`);
        expect(primera, 'primera carga').toBeLessThan(4000);
        expect(segunda, 'visita siguiente').toBeLessThan(2000);
    });
}
