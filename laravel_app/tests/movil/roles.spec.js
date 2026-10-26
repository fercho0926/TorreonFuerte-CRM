import { expect, test } from '@playwright/test';

/** Pestaña «Roles» y su formulario (spec 013): de 320 a 1920 px sin desplazar la página. */

test.beforeEach(async ({ page }) => {
    // El tamaño lo fija cada prueba; basta con un solo proyecto para no repetirlas.
    test.skip(page.viewportSize().width !== 390, 'una sola vez');
});

const sinDesplazamiento = (page) => page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1);

for (const ancho of [320, 768, 1920]) {
    test(`a ${ancho} px la pestaña Roles no se desplaza de lado y el formulario tiene la matriz`, async ({ page }) => {
        await page.setViewportSize({ width: ancho, height: 900 });

        await page.goto('/roles');
        await expect(page.getByRole('link', { name: /Crear rol/ })).toBeVisible();
        expect(await sinDesplazamiento(page)).toBe(true);

        await page.goto('/roles/create');
        expect(await sinDesplazamiento(page)).toBe(true);

        const opciones = page.locator('input[name="permisos[eventos]"] + label');
        await expect(opciones).toHaveCount(3);
        for (const opcion of await opciones.all()) {
            const caja = await opcion.boundingBox();
            expect(Math.min(caja.width, caja.height)).toBeGreaterThanOrEqual(43.5);
        }
    });
}

test('los atajos de la matriz marcan todos los módulos', async ({ page }) => {
    await page.goto('/roles/create');

    await page.getByRole('button', { name: 'Todo en lectura' }).click();
    for (const modulo of ['miembros', 'eventos', 'usuarios']) {
        await expect(page.locator(`input[name="permisos[${modulo}]"][value="1"]`)).toBeChecked();
    }

    await page.getByRole('button', { name: 'Todo sin acceso' }).click();
    await expect(page.locator('input[name="permisos[eventos]"][value="0"]')).toBeChecked();
});

test('un rol nuevo se crea desde la pestaña y aparece en la lista', async ({ page }) => {
    await page.goto('/roles/create');

    await page.fill('input[name="nombre"]', 'Rol de prueba móvil');
    await page.selectOption('select[name="alcance"]', 'rama');
    await page.locator('label[for="permiso-eventos-1"]').click();
    await page.getByRole('button', { name: /Crear rol/ }).click();

    await expect(page).toHaveURL(/\/roles\/rol_de_prueba_movil$/);
    await expect(page.getByText('Rol de prueba móvil').first()).toBeVisible();
});
