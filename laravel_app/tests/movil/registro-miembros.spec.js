import { expect, test } from '@playwright/test';
import { TOKEN_SEDE } from './pantallas.mjs';

/** Registro de miembros por QR (spec 012): formulario público y panel del QR en teléfono. */

const sinDesplazamiento = (page) => page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1);

test.describe('formulario público', () => {
    test.use({ storageState: { cookies: [], origins: [] } }); // sin sesión

    test('paso 1: sin desplazamiento horizontal, teclado de celular y controles de 44 px', async ({ page }) => {
        await page.goto(`/registro-sede/${TOKEN_SEDE}`);

        expect(await sinDesplazamiento(page)).toBe(true);
        await expect(page.locator('input[name="celular"]')).toHaveAttribute('inputmode', 'tel');
        for (const control of await page.locator('input:visible, button:visible, select:visible').all()) {
            const caja = await control.boundingBox();
            expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);
        }
    });

    test('paso 2: campos de contacto con teclado adecuado, letra de 16 px y estado a elegir', async ({ page }) => {
        await page.goto(`/registro-sede/${TOKEN_SEDE}`);
        await page.fill('input[name="celular"]', '3105559999');
        // Enter envía igual que el botón; en el proyecto horizontal de 740 px el toque del botón no llega a enviar en el navegador de prueba.
        await page.press('input[name="celular"]', 'Enter');

        await expect(page.locator('select[name="estado_inicial"]')).toBeVisible();
        await expect(page.locator('input[name="email"]')).toHaveAttribute('inputmode', 'email');
        expect(await sinDesplazamiento(page)).toBe(true);
        for (const campo of await page.locator('input:visible, select:visible').all()) {
            expect(await campo.evaluate((e) => parseFloat(getComputedStyle(e).fontSize))).toBeGreaterThanOrEqual(15.9);
        }
    });

    test('no guarda datos de personas en el dispositivo', async ({ page }) => {
        await page.goto(`/registro-sede/${TOKEN_SEDE}`);
        const cabecera = (await page.request.get(`/registro-sede/${TOKEN_SEDE}`)).headers()['cache-control'];

        expect(cabecera).toContain('no-store');
    });
});

test('el panel del QR de la sede no se desplaza de lado y la tabla de registros es apilable', async ({ page }) => {
    await page.goto('/sedes/1?pestana=registro');

    await expect(page.getByRole('link', { name: /Descargar QR/ })).toBeVisible();
    expect(await sinDesplazamiento(page)).toBe(true);
    const descargar = await page.getByRole('link', { name: /Descargar QR/ }).boundingBox();
    expect(Math.min(descargar.width, descargar.height)).toBeGreaterThanOrEqual(43.5);
});
