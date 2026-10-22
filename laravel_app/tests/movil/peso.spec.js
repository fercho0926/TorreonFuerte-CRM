import { readdirSync, statSync } from 'node:fs';
import { resolve } from 'node:path';
import { expect, test } from '@playwright/test';

/** Peso de lo que se descarga en cada pantalla (RF-014): JS inicial < 200 kB y CSS inicial < 450 kB sin comprimir. */

const ASSETS = resolve(import.meta.dirname, '..', '..', 'public', 'build', 'assets');
const kb = (archivo) => statSync(resolve(ASSETS, archivo)).size / 1024;

test.beforeEach(() => {
    test.skip(test.info().project.name !== 'movil-390', 'una sola ejecución');
});

test('el JavaScript y los estilos iniciales son ligeros', async ({ page }) => {
    await page.goto('/dashboard');
    const iniciales = await page.evaluate(() => [...document.querySelectorAll('script[src], link[rel="stylesheet"]')].map((e) => new URL(e.src || e.href).pathname.split('/').pop()));
    const js = iniciales.filter((a) => a.endsWith('.js')).reduce((s, a) => s + kb(a), 0);
    const css = iniciales.filter((a) => a.endsWith('.css')).reduce((s, a) => s + kb(a), 0);

    console.log(`inicial: JS ${js.toFixed(0)} kB, CSS ${css.toFixed(0)} kB`);
    expect(js, 'JavaScript inicial (kB)').toBeLessThan(200);
    expect(css, 'CSS inicial (kB)').toBeLessThan(450);
});

test('los plugins pesados no forman parte de la carga inicial', async ({ page }) => {
    await page.goto('/dashboard');
    const cargados = await page.evaluate(() => performance.getEntriesByType('resource').map((r) => r.name));
    const texto = cargados.join('\n');

    for (const pesado of ['sweetalert2', 'jquery', 'grilla']) {
        expect(texto, `${pesado} se carga bajo demanda`).not.toContain(pesado);
    }
    expect(readdirSync(ASSETS).some((a) => a.startsWith('grilla-'))).toBe(true);
});

test('las pantallas que los necesitan sí los descargan', async ({ page }) => {
    await page.goto('/personas');
    await page.waitForSelector('.dt-container');
    expect((await page.evaluate(() => performance.getEntriesByType('resource').map((r) => r.name))).join('\n')).toContain('grilla');
});
