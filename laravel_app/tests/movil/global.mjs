import { mkdirSync, rmSync } from 'node:fs';
import { resolve } from 'node:path';
import { chromium } from '@playwright/test';
import { USUARIO } from './pantallas.mjs';
import { detener, iniciar, URL_BASE } from './servidor.mjs';

export const SESION = resolve(import.meta.dirname, '.auth', 'admin.json');

/** Levanta el servidor temporal, inicia sesión una vez y guarda la sesión; devuelve la limpieza. */
export default async function globalSetup() {
    await iniciar();
    mkdirSync(resolve(import.meta.dirname, '.auth'), { recursive: true });

    const navegador = await chromium.launch({ channel: 'chrome' });
    const pagina = await navegador.newPage({ baseURL: URL_BASE });
    await pagina.goto('/');
    await pagina.fill('input[name="email"]', USUARIO.email);
    await pagina.fill('input[name="password"]', USUARIO.password);
    await Promise.all([pagina.waitForURL('**/dashboard'), pagina.click('button[type="submit"]')]);
    await pagina.context().storageState({ path: SESION });
    await navegador.close();

    return async () => {
        detener();
        rmSync(resolve(import.meta.dirname, '.auth'), { recursive: true, force: true });
    };
}
