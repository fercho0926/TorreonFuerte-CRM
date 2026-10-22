import { expect, test } from '@playwright/test';

/** Tablero y fichas en teléfono (HU4, RF-010, RF-011). */

test.beforeEach(async ({ page }) => {
    test.skip(page.viewportSize().width >= 768, 'solo teléfono');
});

test('los accesos rápidos salen antes que los totales y el resumen por sede se apila', async ({ page }) => {
    await page.goto('/dashboard');

    const accesos = page.locator('.d-lg-none .card', { hasText: 'Accesos rápidos' });
    await expect(accesos).toBeVisible();
    const cajaAccesos = await accesos.boundingBox();
    const cajaTotales = await page.locator('.small-box').first().boundingBox();
    expect(cajaAccesos.y).toBeLessThan(cajaTotales.y);

    // Solo se ve un bloque de accesos rápidos (el de escritorio queda oculto).
    await expect(page.locator('.card', { hasText: 'Accesos rápidos' }).locator('visible=true')).toHaveCount(1);

    const fila = page.locator('table.tabla-apilable tbody tr').first();
    expect(await fila.evaluate((e) => getComputedStyle(e).display)).not.toBe('table-row');
    await expect(fila.locator('td[data-etiqueta="Personas"]')).toBeVisible();
});

for (const ruta of ['personas/1', 'lideres/3', 'celulas/1', 'eventos/1', 'sedes/1']) {
    test(`la ficha /${ruta} no se desplaza de lado y sus tablas son tarjetas`, async ({ page }) => {
        await page.goto(`/${ruta}`);

        expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1)).toBe(true);
        for (const tabla of await page.locator('table.tabla-apilable').all()) {
            if (!(await tabla.isVisible())) continue;
            const fila = tabla.locator('tbody tr').first();
            if (await fila.count()) expect(await fila.evaluate((e) => getComputedStyle(e).display)).not.toBe('table-row');
        }
    });
}

test('en la ficha de la persona «Llamar» y «WhatsApp» están arriba y a la vista', async ({ page }) => {
    await page.goto('/personas/3'); // el líder de ejemplo tiene celular

    const llamar = page.locator('a[title="Llamar"]').first();
    await expect(llamar).toBeVisible();
    const caja = await llamar.boundingBox();
    expect(caja.y).toBeLessThan(500);
    expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);
});
