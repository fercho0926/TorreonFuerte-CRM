import { expect, test } from '@playwright/test';

/** Organigrama, pestaña del detalle de sede (spec 011, HU3): se recorre de 320 a 1920 px sin desplazar la página. */

test.beforeEach(async ({ page }) => {
    // El tamaño lo fija cada prueba; basta con un solo proyecto para no repetirlas.
    test.skip(page.viewportSize().width !== 390, 'una sola vez');
});

const sinDesplazamiento = (page) => page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1);

for (const ancho of [320, 768, 1920]) {
    test(`a ${ancho} px no se desplaza de lado y se puede expandir un líder`, async ({ page }) => {
        await page.setViewportSize({ width: ancho, height: 900 });
        await page.goto('/sedes/1?pestana=organigrama');

        const sede = page.locator('.organigrama-nodo[data-tipo="sede"] > details > summary');
        await expect(sede).toBeVisible();
        // El nombre del pastor es un enlace a su ficha: se expande con el contador (el resto de la tarjeta).
        await sede.locator('.organigrama-contador').click();

        const lider = page.locator('.organigrama-nodo[data-tipo="lider"] > details > summary').first();
        await expect(lider).toBeVisible();
        const caja = await lider.boundingBox();
        expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);
        await lider.locator('.organigrama-contador').click();

        await expect(page.locator('.organigrama-nodo[data-tipo="miembro"]').first()).toBeVisible();
        expect(await sinDesplazamiento(page)).toBe(true);
    });
}

test('«Expandir todo» y «Colapsar todo» abren y cierran el árbol', async ({ page }) => {
    await page.goto('/sedes/1?pestana=organigrama');

    const expandir = page.getByRole('button', { name: 'Expandir todo' });
    await expect(expandir).toBeVisible();
    const caja = await expandir.boundingBox();
    expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);

    await expandir.click();
    expect(await page.locator('.organigrama details:not([open])').count()).toBe(0);

    await page.getByRole('button', { name: 'Colapsar todo' }).click();
    expect(await page.locator('.organigrama details[open]').count()).toBe(0);
});

test('a 1920 px los líderes se disponen en horizontal bajo la sede', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 900 });
    await page.goto('/sedes/1?pestana=organigrama');
    await page.getByRole('button', { name: 'Expandir todo' }).click();

    const lideres = page.locator('.organigrama-nodo[data-tipo="sede"] > details > .organigrama-hijos > .organigrama-nodo[data-tipo="lider"]');
    if ((await lideres.count()) < 2) test.skip(true, 'la base de prueba tiene un solo líder');

    const a = await lideres.nth(0).boundingBox();
    const b = await lideres.nth(1).boundingBox();
    expect(Math.abs(a.y - b.y)).toBeLessThan(5);
    expect(b.x).toBeGreaterThan(a.x);
});
