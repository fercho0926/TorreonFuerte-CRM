import { expect, test } from '@playwright/test';

/** Formularios en teléfono (HU3, RF-008, RF-009). */

/** El administrador entra en «Todas las sedes» (solo consulta): para escribir se elige una sede activa. */
async function elegirSede(page) {
    await page.goto('/dashboard');
    await Promise.all([page.waitForNavigation(), page.selectOption('#sede-activa', '1')]);
}

test.beforeEach(async ({ page }) => {
    const { width, height } = page.viewportSize();
    test.skip(width >= 768 || width > height, 'solo teléfono en vertical');
    await elegirSede(page);
});

// La sesión del administrador es la misma para todas las pruebas: se devuelve a «Todas las sedes».
test.afterEach(async ({ page }) => {
    await page.goto('/dashboard');
    if ((await page.locator('#sede-activa').inputValue()) !== '') {
        await Promise.all([page.waitForNavigation(), page.selectOption('#sede-activa', '')]);
    }
});

test('el botón «Guardar» sigue visible con el teclado en pantalla (viewport reducido al 60 %)', async ({ page }) => {
    await page.goto('/personas/create');
    const { width, height } = page.viewportSize();
    await page.setViewportSize({ width, height: Math.round(height * 0.6) });

    const boton = page.locator('[data-boton-principal]').first();
    await expect(boton).toBeVisible();
    const caja = await boton.boundingBox();
    expect(caja.y + caja.height).toBeLessThanOrEqual(Math.round(height * 0.6) + 1);
});

test('al enviar con errores se llega al primer campo con error', async ({ page }) => {
    await page.goto('/personas/create');
    await page.locator('input[name="email"]').fill('esto-no-es-un-correo');
    await page.locator('[data-boton-principal]').first().click();

    const primerError = page.locator('.is-invalid, [aria-invalid="true"]').first();
    await expect(primerError).toBeVisible();
    const enfocado = await page.evaluate(() => document.activeElement?.getAttribute('name') || document.activeElement?.id || '');
    expect(enfocado.length).toBeGreaterThan(0);
    // El campo con error queda a la vista (no tapado por la barra ni fuera de la pantalla).
    const caja = await primerError.boundingBox();
    expect(caja.y).toBeGreaterThanOrEqual(0);
});

test('los campos piden el teclado adecuado', async ({ page }) => {
    await page.goto('/personas/create');

    await expect(page.locator('input[name="celular"]')).toHaveAttribute('inputmode', 'tel');
    await expect(page.locator('input[name="email"]')).toHaveAttribute('inputmode', 'email');
});

test('lo escrito se recupera si el envío no llega al servidor', async ({ page, context }) => {
    await page.goto('/personas/create');
    await page.locator('input[name="nombres"]').fill('Recuperada');
    await page.locator('input[name="apellidos"]').fill('Sinseñal');
    await page.waitForTimeout(600); // respaldo con retardo

    await context.setOffline(true);
    await page.locator('[data-boton-principal]').first().click().catch(() => {});
    await page.waitForTimeout(800);
    await context.setOffline(false);

    await page.goto('/personas/create');
    await expect(page.locator('input[name="nombres"]')).toHaveValue('Recuperada');
    await expect(page.locator('input[name="apellidos"]')).toHaveValue('Sinseñal');
});

test('un envío exitoso no deja el respaldo para el siguiente formulario', async ({ page }) => {
    await page.goto('/personas/create');
    await page.locator('input[name="nombres"]').fill('Exitosa');
    await page.locator('input[name="apellidos"]').fill('Guardada');
    await page.locator('input[name="celular"]').fill(`310${Date.now().toString().slice(-7)}`); // se exige al menos un medio de contacto
    await page.waitForTimeout(500);
    await page.locator('[data-boton-principal]').first().click();
    await page.waitForLoadState('load');

    await page.goto('/personas/create');
    await expect(page.locator('input[name="nombres"]')).toHaveValue('');
});
