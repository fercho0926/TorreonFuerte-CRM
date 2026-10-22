import { expect, test } from '@playwright/test';

/** Barra superior y menú lateral en teléfono (RF-004, RF-015). */

async function abrirMenu(page) {
    await page.goto('/dashboard');
    page.anchoAntes = await page.evaluate(() => document.documentElement.scrollWidth);
    await page.locator('[data-lte-toggle="sidebar"]').first().click();
    await expect(page.locator('body')).toHaveClass(/sidebar-open/);
}

test('el menú se abre sobre el contenido y no desplaza la página', async ({ page }) => {
    await abrirMenu(page);

    const menu = await page.locator('.app-sidebar').boundingBox();
    expect(menu.x).toBeGreaterThanOrEqual(-1);
    expect(menu.width).toBeGreaterThan(150);
    // Abrir el menú no ensancha la página (el desborde de la página lo mide la auditoría).
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(page.anchoAntes);
    await expect(page.locator('.sidebar-overlay')).toBeVisible();
});

test('el menú se cierra al tocar fuera', async ({ page }) => {
    await abrirMenu(page);

    // El menú ocupa la parte izquierda: se toca el contenido que queda visible a su derecha.
    const ancho = page.viewportSize().width;
    await page.mouse.click(ancho - 4, Math.min(400, page.viewportSize().height - 10));
    await expect(page.locator('body')).not.toHaveClass(/sidebar-open/);
});

test('el menú se cierra al elegir una opción', async ({ page }) => {
    await abrirMenu(page);

    await page.locator('.app-sidebar .nav-link', { hasText: 'Personas' }).click();
    await page.waitForURL('**/personas');
    await expect(page.locator('body')).not.toHaveClass(/sidebar-open/);
});

test('la barra del administrador muestra la sede legible y el aviso «solo consulta» sin comprimirse', async ({ page }) => {
    await page.goto('/dashboard');

    const selector = await page.locator('#sede-activa').boundingBox();
    expect(selector.height).toBeGreaterThanOrEqual(44);
    expect(selector.width).toBeGreaterThan(90);

    // En teléfono el aviso va en una franja propia, no dentro de la barra.
    await expect(page.locator('.aviso-sede-movil')).toBeVisible();
    await expect(page.locator('.app-header .badge', { hasText: 'solo consulta' })).toBeHidden();

    const barra = await page.locator('.app-header').boundingBox();
    expect(barra.height).toBeLessThan(80);
});
