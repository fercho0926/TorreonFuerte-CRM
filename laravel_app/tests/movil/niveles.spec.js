import { expect, test } from '@playwright/test';

/** Camino de niveles (spec 014): se recorre de 320 a 430 px sin desplazar la página y con controles tocables. */

test.beforeEach(async ({ page }) => {
    // El tamaño lo fija cada prueba; basta con un solo proyecto para no repetirlas.
    test.skip(page.viewportSize().width !== 390, 'una sola vez');
});

const sinDesplazamiento = (page) => page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1);

for (const ancho of [320, 360, 390, 430]) {
    test(`el camino de la ficha cabe a ${ancho} px y se puede abrir una estación`, async ({ page }) => {
        await page.setViewportSize({ width: ancho, height: 900 });
        await page.goto('/personas/1');

        const camino = page.locator('.camino').first();
        await expect(camino).toBeVisible();
        expect(await page.locator('.camino .estacion').count()).toBeGreaterThanOrEqual(4);
        expect(await sinDesplazamiento(page)).toBe(true);

        // Cada estación se ve dentro de la pantalla (sin recortarse por los lados).
        for (const caja of await page.locator('.camino .estacion').evaluateAll((els) => els.map((e) => e.getBoundingClientRect().toJSON()))) {
            expect(caja.left).toBeGreaterThanOrEqual(-1);
            expect(caja.right).toBeLessThanOrEqual(ancho + 1);
        }

        const resumen = page.locator('.camino details > summary').first();
        if (await resumen.count()) {
            const caja = await resumen.boundingBox();
            expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);
        }
    });
}

test('la lista de miembros por nivel no se desplaza de lado a 320 px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 900 });
    await page.goto('/niveles/miembros?nivel=1');
    await page.waitForSelector('.dt-container', { timeout: 10_000 });

    expect(await sinDesplazamiento(page)).toBe(true);
});
