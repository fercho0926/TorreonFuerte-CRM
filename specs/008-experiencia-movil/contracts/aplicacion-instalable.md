# Contrato: aplicación instalable y pantalla de «sin conexión»

## Archivos públicos

| Ruta | Tipo | Contenido |
|------|------|-----------|
| `/manifest.webmanifest` | `application/manifest+json` | ver [data-model.md](../data-model.md) |
| `/sw.js` | `text/javascript` (alcance `/`, sin caché larga) | política de la caché del dispositivo |
| `/offline.html` | HTML estático | mensaje «Sin conexión», botón «Reintentar», marca |
| `/iconos/icono-192.png`, `icono-512.png`, `icono-maskable-512.png`, `apple-touch-180.png`, `favicon.ico` | imágenes | generadas desde `resources/iconos/marca.svg` |

## Etiquetas en el diseño (`layouts/admin` y `layouts/auth`)

```html
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#…">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="apple-touch-icon" href="/iconos/apple-touch-180.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Torreón">
```

## Service worker (`/sw.js`)

- `install`: guarda `offline.html` y los iconos; `skipWaiting`.
- `activate`: borra cachés de versiones anteriores; `clients.claim`.
- `fetch`:
  - `GET /build/assets/*` y `/iconos/*` → caché primero.
  - `navigate` → red; si falla → `offline.html`.
  - **Todo lo demás** → red, sin tocar la caché (incluye `*/datos`, `*/exportar`, `*/buscar`, POST).
- Nunca escribe en la caché una respuesta con `Set-Cookie`, de tipo JSON o CSV ni de una ruta autenticada.
- Se registra solo si `location.protocol === 'https:'` o `localhost`.

## Garantías verificables

1. Tras recorrer la aplicación, la caché del dispositivo contiene únicamente `offline.html`, `/build/assets/*` e `/iconos/*`.
2. Con la red cortada, abrir cualquier ruta muestra `offline.html`, no el error del navegador.
3. Cerrar sesión no deja datos en el dispositivo.
