# Contrato: prueba permanente de adaptación a celular

## Ejecución

```bash
npm run test:movil      # Playwright: levanta el servidor con una base temporal sembrada y recorre las pantallas
php artisan test --filter=Movil   # guardas estáticas (sin navegador)
```

`test:movil` crea una base SQLite temporal, ejecuta `migrate:fresh --seed`, arranca `php artisan serve` en un puerto libre, inicia sesión con el usuario administrador de ejemplo (leído del `DatabaseSeeder`) y limpia todo al terminar. Nunca toca `database/database.sqlite`.

## Matriz

- **Anchos**: 320, 360, 390 y 430 px (vertical) y 740 px (horizontal en teléfono).
- **Pantallas**: las 38 del análisis (listados, `create`, `show`, `edit`, tablero, cuenta, cambiar clave, sede/pastores, traslado, designar/quitar líder), más inicio de sesión y recuperar clave sin sesión.

## Aserciones por pantalla y ancho

| # | Regla | Falla cuando |
|---|-------|--------------|
| 1 | Sin desplazamiento horizontal | `documentElement.scrollWidth > clientWidth` |
| 2 | Controles tocables ≥ 44 px | un `a[href], button, input, select, textarea, [role=button], .page-link` visible mide < 44 px de ancho o alto (se ignora el elemento oculto, el del menú colapsado y los de la lista de excepciones) |
| 3 | Letra de campos ≥ 16 px | un `input, select, textarea` visible tiene `font-size` < 16 px |
| 4 | Sin tabla sin adaptar | una `<table>` visible a < 768 px no tiene `.tabla-apilable` ni pertenece a la grilla estándar en modo tarjeta |
| 5 | Sin texto cortado | un elemento con `overflow:hidden` pierde más del 50 % de su texto sin puntos suspensivos |

## Aserciones de comportamiento

- Listado a 390 px: la primera fila de tarjeta es visible sin desplazar (borde superior < 700 px).
- «Filtros (n)» abre el panel; escribir un filtro cambia `n` y actualiza las filas.
- Menú lateral: se abre sobre el contenido y se cierra al elegir una opción o tocar fuera.
- Formulario de crear persona: el botón de guardar queda visible con el teclado simulado (viewport reducido a 60 %).
- Carga: con «4G lenta» emulada, tablero, personas y crear persona muestran contenido útil en < 4 s; recarga con caché del navegador < 2 s.
- Aplicación instalable: el manifiesto es válido, los iconos responden 200 y el *service worker* queda activo en `localhost`.
- Sin conexión: con la red cortada una navegación muestra «Sin conexión».
- Privacidad: tras recorrer la aplicación, la caché del dispositivo no contiene respuestas JSON, CSV ni HTML de pantallas.

## Lista de excepciones

`tests/movil/excepciones.json`: vacía por defecto. Cada entrada exige `pantalla`, `selector`, `regla` y `motivo`; una excepción sin motivo hace fallar la prueba.

## Guarda estática (PHPUnit, `tests/Feature/Movil/`)

- Toda `<table>` en `resources/views` fuera de `components/ui/grilla.blade.php` lleva `tabla-apilable` y cada `<td>` tiene `data-etiqueta`.
- `layouts/admin` y `layouts/auth` declaran `viewport-fit=cover`, `theme-color`, `manifest` y `apple-touch-icon`.
- `public/manifest.webmanifest` es JSON válido con los campos requeridos y los iconos existen.
- `public/sw.js` no contiene escritura en caché para rutas distintas de `/build/assets/`, `/iconos/` y `offline.html`.
