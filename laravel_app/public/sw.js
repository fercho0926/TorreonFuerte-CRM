/*
 * Service worker mínimo de la aplicación instalable (spec 008, contrato «aplicacion-instalable»).
 *
 * Política cerrada — NUNCA se guardan datos:
 *   - Solo se guardan archivos de la interfaz: /build/assets/*, /iconos/* y offline.html.
 *   - Las navegaciones van a la red; si falla, se muestra offline.html.
 *   - Todo lo demás (JSON de grillas, CSV, búsquedas, formularios, cualquier otra respuesta) va solo a la red
 *     y jamás se escribe en la caché del dispositivo.
 */
const VERSION = 'v1';
const CACHE = `crm-interfaz-${VERSION}`;
const BASE = new URL('./', self.registration.scope).pathname; // funciona también si la aplicación vive en una subcarpeta
const OFFLINE = `${BASE}offline.html`;
const PRECACHE = [OFFLINE, `${BASE}iconos/icono-192.png`, `${BASE}iconos/icono-512.png`];

self.addEventListener('install', (evento) => {
    evento.waitUntil(caches.open(CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (evento) => {
    evento.waitUntil(
        caches.keys()
            .then((nombres) => Promise.all(nombres.filter((n) => n.startsWith('crm-interfaz-') && n !== CACHE).map((n) => caches.delete(n))))
            .then(() => self.clients.claim())
    );
});

/** ¿Es un archivo de la interfaz (con huella en el nombre o icono)? Es lo único que se guarda. */
function esInterfaz(url) {
    return url.origin === self.location.origin && (url.pathname.startsWith(`${BASE}build/assets/`) || url.pathname.startsWith(`${BASE}iconos/`));
}

/** Nunca se guarda una respuesta con cookies, de datos (JSON/CSV) ni una redirección. */
function sePuedeGuardar(respuesta) {
    const tipo = respuesta.headers.get('content-type') || '';
    return respuesta.ok && respuesta.type === 'basic' && !respuesta.headers.has('set-cookie') && !/json|csv|html/i.test(tipo);
}

self.addEventListener('fetch', (evento) => {
    const peticion = evento.request;
    if (peticion.method !== 'GET') return; // formularios y envíos: directo a la red

    const url = new URL(peticion.url);

    if (esInterfaz(url)) {
        evento.respondWith(
            caches.match(peticion).then((guardado) => guardado || fetch(peticion).then((respuesta) => {
                if (sePuedeGuardar(respuesta)) {
                    const copia = respuesta.clone();
                    caches.open(CACHE).then((c) => c.put(peticion, copia));
                }

                return respuesta;
            }))
        );

        return;
    }

    if (peticion.mode === 'navigate') {
        evento.respondWith(fetch(peticion).catch(() => caches.match(OFFLINE)));
    }
    // Cualquier otra petición: sin intervención (red normal, sin caché).
});
