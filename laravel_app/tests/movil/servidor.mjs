/**
 * Servidor de prueba para la auditoría móvil: base SQLite temporal sembrada + servidor PHP propio.
 * Nunca toca database/database.sqlite. Todo se borra al terminar (ver `detener`).
 */
import { spawn, spawnSync } from 'node:child_process';
import { existsSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { TOKEN_REGISTRO } from './pantallas.mjs';

export const PUERTO = Number(process.env.MOVIL_PUERTO || 8199);
export const URL_BASE = `http://127.0.0.1:${PUERTO}`;
const RAIZ = resolve(import.meta.dirname, '..', '..');

let carpeta = null;
let proceso = null;

function php(args, env) {
    const r = spawnSync('php', args, { cwd: RAIZ, env: { ...process.env, ...env }, encoding: 'utf-8' });
    if (r.status !== 0) throw new Error(`php ${args.join(' ')} falló:\n${r.stdout}\n${r.stderr}`);
}

export async function iniciar() {
    carpeta = mkdtempSync(join(tmpdir(), 'crm-movil-'));
    const base = join(carpeta, 'movil.sqlite');
    writeFileSync(base, '');
    const env = { DB_CONNECTION: 'sqlite', DB_DATABASE: base, APP_ENV: 'local', SESSION_DRIVER: 'file', CACHE_STORE: 'file' };

    php(['artisan', 'migrate:fresh', '--seed', '--force'], env);
    // El formulario público del QR necesita un token conocido para poder auditarse.
    php(['artisan', 'tinker', `--execute=\\App\\Models\\Evento::withoutGlobalScopes()->whereKey(1)->update(['registro_token' => '${TOKEN_REGISTRO}']);`], env);

    proceso = spawn('php', ['-S', `127.0.0.1:${PUERTO}`, '-t', '.', join('..', 'vendor', 'laravel', 'framework', 'src', 'Illuminate', 'Foundation', 'resources', 'server.php')], {
        cwd: join(RAIZ, 'public'),
        env: { ...process.env, ...env },
        stdio: 'ignore',
    });

    for (let i = 0; i < 60; i++) {
        try {
            const r = await fetch(`${URL_BASE}/`);
            if (r.status < 500) return;
        } catch { /* aún arrancando */ }
        await new Promise((r) => setTimeout(r, 250));
    }
    throw new Error('El servidor de prueba no arrancó');
}

export function detener() {
    if (proceso && proceso.pid) {
        if (process.platform === 'win32') spawnSync('taskkill', ['/pid', String(proceso.pid), '/t', '/f']);
        else proceso.kill('SIGKILL');
    }
    proceso = null;
    if (carpeta && existsSync(carpeta)) rmSync(carpeta, { recursive: true, force: true });
    carpeta = null;
}
