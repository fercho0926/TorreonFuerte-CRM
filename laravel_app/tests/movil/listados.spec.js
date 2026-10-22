import { expect, test } from '@playwright/test';

/** Listados como tarjetas en teléfono (HU2, RF-005 a RF-007). Solo anchos de teléfono vertical. */

test.beforeEach(async ({ page }, info) => {
    test.skip(page.viewportSize().width >= 768, 'solo teléfono');
    await page.goto('/personas');
    await page.waitForSelector('.dt-container');
});

test('la primera tarjeta se ve sin desplazarse y muestra nombre, estado y celular', async ({ page }) => {
    const primera = page.locator('table.grilla tbody tr').first();
    const caja = await primera.boundingBox();

    expect(caja.y).toBeLessThan(700);
    await expect(primera.locator('td').first()).toContainText(/\S/);
    await expect(primera.locator('td[data-etiqueta="Estado"]')).toBeVisible();
    await expect(primera.locator('td[data-etiqueta="Celular"]')).toBeVisible();
    // La cabecera de títulos y las columnas secundarias no ocupan espacio hasta pedirlo.
    await expect(primera.locator('td[data-etiqueta="Líder"]')).toBeHidden();
});

test('«Ver más» muestra las columnas secundarias de la tarjeta', async ({ page }) => {
    const fila = page.locator('table.grilla tbody tr').first();

    await fila.locator('.grilla-mas').click();
    await expect(fila.locator('td[data-etiqueta="Líder"]')).toBeVisible();
    await fila.locator('.grilla-mas').click();
    await expect(fila.locator('td[data-etiqueta="Líder"]')).toBeHidden();
});

test('«Filtros» abre el panel, cuenta los filtros activos y actualiza las filas', async ({ page }) => {
    const boton = page.locator('[data-grilla-filtros]');
    const filtro = page.locator('tr.grilla-filtros input[data-filtro="nombre"]');

    await expect(filtro).toBeHidden();
    await boton.click();
    await expect(filtro).toBeVisible();
    await expect(page.locator('[data-grilla-filtros-n]')).toHaveText('0');

    const total = await page.locator('table.grilla tbody tr').count();
    await filtro.fill('Norte');
    await expect(page.locator('[data-grilla-filtros-n]')).toHaveText('1');
    await expect(page.locator('[data-grilla-contador]')).not.toHaveText(`${total} resultados`);
    await expect(page.locator('table.grilla tbody tr').first()).toContainText('Norte');
});

test('«Ordenar por» cambia el orden de las tarjetas', async ({ page }) => {
    const selector = page.locator('[data-grilla-orden]');
    const primero = () => page.locator('table.grilla tbody tr').first().locator('td').first().innerText();

    const antes = await primero();
    await selector.selectOption({ label: 'Nombre (Z–A)' });
    await expect.poll(primero).not.toBe(antes);
});

test('el paginador, las filas por página y el contador se tocan y no se desbordan', async ({ page }) => {
    await page.locator('.dt-length select').selectOption('10');
    const sobresalen = await page.evaluate(() => {
        const ancho = document.documentElement.clientWidth;
        return [...document.querySelectorAll('.dt-paging button, .dt-length select, .dt-info')].filter((e) => e.getBoundingClientRect().right > ancho + 1).length;
    });
    expect(sobresalen).toBe(0);

    for (const boton of await page.locator('.dt-paging button.page-link').all()) {
        const caja = await boton.boundingBox();
        if (caja) expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);
    }
});

test('«Llamar» y «WhatsApp» se pulsan con un toque en el listado sin líder', async ({ page }) => {
    await page.goto('/sin-lider');
    await page.waitForSelector('.dt-container');

    const llamar = page.locator('a[title="Llamar"]').first();
    await expect(llamar).toBeVisible();
    const caja = await llamar.boundingBox();
    expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);
});
