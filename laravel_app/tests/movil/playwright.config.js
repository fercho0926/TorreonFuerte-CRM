import { defineConfig } from '@playwright/test';
import { ANCHOS } from './pantallas.mjs';
import { URL_BASE } from './servidor.mjs';
import { SESION } from './global.mjs';

/**
 * Auditoría de adaptación a celular (specs/008-experiencia-movil/contracts/prueba-movil.md).
 * Usa el Chrome instalado (`channel: 'chrome'`), una base SQLite temporal y un servidor PHP propio.
 * Se ejecuta con `npm run test:movil`.
 */
const comun = { channel: 'chrome', actionTimeout: 10_000, navigationTimeout: 30_000, baseURL: URL_BASE, storageState: SESION, isMobile: true, hasTouch: true, deviceScaleFactor: 2 };

export default defineConfig({
    testDir: '.',
    testMatch: '**/*.spec.js',
    globalSetup: './global.mjs',
    workers: 1,
    timeout: 120_000,
    expect: { timeout: 10_000 },
    reporter: [['list']],
    projects: [
        ...ANCHOS.map((ancho) => ({
            name: `movil-${ancho}`,
            use: { ...comun, viewport: { width: ancho, height: 800 } },
        })),
        { name: 'movil-horizontal-740', use: { ...comun, viewport: { width: 740, height: 360 } } },
    ],
});
