import { expect, test } from '@playwright/test';

/** Aplicación instalable y sin datos en el dispositivo (HU5, RF-012, RF-013, RF-013b). Solo un ancho: el comportamiento no depende de él. */

test.beforeEach(() => {
    test.skip(test.info().project.name !== 'movil-390', 'una sola ejecución');
});

async function activarServiceWorker(page) {
    await page.goto('/dashboard');
    await page.evaluate(async () => {
        await navigator.serviceWorker.ready;
    });
    // Se recarga para que el service worker controle la página.
    await page.reload();
    await page.waitForFunction(() => !!navigator.serviceWorker.controller, null, { timeout: 15_000 });
}

test('el manifiesto es válido y los iconos responden', async ({ page, request }) => {
    await page.goto('/dashboard');
    const href = await page.locator('link[rel="manifest"]').getAttribute('href');
    const manifiesto = await (await request.get(href)).json();

    expect(manifiesto.display).toBe('standalone');
    for (const icono of manifiesto.icons) {
        const respuesta = await request.get(new URL(icono.src, href).toString());
        expect(respuesta.status(), icono.src).toBe(200);
    }
});

test('el service worker queda activo', async ({ page }) => {
    await activarServiceWorker(page);

    const estado = await page.evaluate(async () => (await navigator.serviceWorker.getRegistration())?.active?.state);
    expect(estado).toBe('activated');
});

test('la caché del dispositivo solo contiene archivos de la interfaz, nunca datos', async ({ page }) => {
    await activarServiceWorker(page);

    // Se recorre una pantalla con grilla, su JSON y su exportación, y una ficha.
    await page.goto('/personas');
    await page.waitForSelector('.dt-container');
    await page.evaluate(async () => {
        await fetch('/personas/datos?length=10');
        await fetch('/personas/exportar');
        await fetch('/personas/buscar?q=ana');
    });
    await page.goto('/personas/1');

    const guardado = await page.evaluate(async () => {
        const urls = [];
        for (const nombre of await caches.keys()) {
            for (const peticion of await (await caches.open(nombre)).keys()) urls.push(new URL(peticion.url).pathname);
        }
        return urls;
    });

    expect(guardado.length).toBeGreaterThan(0);
    for (const ruta of guardado) {
        expect(ruta, `no debería guardarse ${ruta}`).toMatch(/^\/(build\/assets\/|iconos\/|offline\.html$)/);
    }
});

test('sin conexión se muestra «Sin conexión» y no la página de error del navegador', async ({ page, context }) => {
    await activarServiceWorker(page);

    await context.setOffline(true);
    await page.goto('/personas').catch(() => {});
    await expect(page.getByRole('heading', { name: 'Sin conexión' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Reintentar' })).toBeVisible();
    await context.setOffline(false);
});
