/**
 * Registra el service worker de la aplicación instalable (spec 008). Solo en HTTPS o localhost:
 * en otros contextos el navegador no lo permite. Si falla, la aplicación funciona igual.
 */
export function puedeRegistrar(ubicacion, navegador) {
    const seguro = ubicacion.protocol === 'https:' || ['localhost', '127.0.0.1', '[::1]'].includes(ubicacion.hostname);

    return seguro && !!navegador && 'serviceWorker' in navegador;
}

export function registrarServiceWorker(doc = document, ubicacion = window.location, navegador = window.navigator) {
    if (!puedeRegistrar(ubicacion, navegador)) return null;

    const url = doc.querySelector('meta[name="crm-sw"]')?.getAttribute('content');
    if (!url) return null;

    return navegador.serviceWorker.register(url).catch(() => null);
}
