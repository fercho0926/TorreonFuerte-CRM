# Investigación: Experiencia móvil

## D1. Cómo medir «100 % responsive» de forma repetible
- **Decisión**: dos capas. (a) **Playwright** (devDependency) recorre las 38 pantallas con un usuario administrador de la base sembrada a 320, 360, 390 y 430 px y falla ante: desplazamiento horizontal de la página, control tocable < 44 px, campo con letra < 16 px y tabla sin formato apilable. (b) **Guarda estática en PHPUnit** que revisa vistas y CSS (toda `<table>` fuera del componente de grilla lleva `tabla-apilable`; el diseño declara `viewport`, color de tema y manifiesto) para que falle en `php artisan test` sin navegador.
- **Razón**: jsdom no calcula diseño, así que solo un navegador real mide tamaños; la guarda estática cubre el día a día.
- **Alternativas**: Lighthouse CI (mide rendimiento pero no pulsaciones por pantalla); pruebas manuales (no repetibles).

## D2. Tamaños táctiles y de letra
- **Decisión**: reglas globales en `movil.css` bajo `@media (max-width: 767.98px), (pointer: coarse)`: `min-height: 44px` y `min-width: 44px` para `.btn`, `.nav-link`, `.page-link`, enlaces de acción, `.form-select`, `.form-control`, interruptores y casillas (área de pulsación ampliada con relleno, no con el dibujo); `font-size: 16px` en `input, select, textarea` (incluye Select2 y los filtros de columna). En escritorio no cambia nada.
- **Razón**: 44 px es la guía de iOS/Material; 16 px evita el acercamiento automático de Safari.
- **Alternativa**: `maximum-scale=1` en el `viewport` (rechazada: impide el zoom y daña la accesibilidad).

## D3. Grillas en teléfono
- **Decisión**: por debajo de 768 px se desactiva la extensión Responsive y la tabla se muestra como **lista de tarjetas** con CSS (`display:block` en filas y celdas, etiqueta por celda con `data-etiqueta`). Se muestran las columnas de prioridad 1 a 3 y las acciones; las demás quedan bajo un botón «Ver más» por tarjeta. Los **filtros por columna** pasan a un panel colapsable abierto con un botón «Filtros (n)» en la barra de la grilla; `n` cuenta los filtros activos. Sin JavaScript se ve la tabla del servidor (ya apilada por CSS) y el formulario de búsqueda.
- **Razón**: la extensión Responsive deja solo «Nombre» y «Acciones» y oculta los filtros; las tarjetas muestran lo esencial a un vistazo (RF-005, RF-006).
- **Alternativas**: seguir con Responsive y mejorar el signo «+» (no soluciona filtros ni lectura); una segunda lista paralela solo para teléfono (duplica marcado y rutas).

## D4. Búsqueda compacta
- **Decisión**: el formulario de filtro de la grilla usa una sola franja en teléfono: campo de búsqueda a todo el ancho, y bajo él, en la misma línea, el interruptor «Incluir inactivos» y un botón «Limpiar» con icono; el botón «Buscar» ya se oculta con JavaScript y la búsqueda es en vivo.
- **Razón**: RF-006; recupera media pantalla.

## D5. Tablero y fichas
- **Decisión**: clase `tabla-apilable` (CSS ya probado en `fm-lista__tabla`) con atributo `data-etiqueta` en cada `<td>`; el tablero reordena con clases de orden para que los **accesos rápidos** salgan antes que los totales en teléfono; el resumen por sede se apila. En la ficha de persona, «Llamar» y «WhatsApp» suben a la franja de acciones del encabezado.
- **Razón**: RF-010, RF-011; no requiere tocar controladores ni datos.
- **Alternativa**: convertir cada tabla en tarjetas Blade separadas (duplica marcado).

## D6. Barra superior y menú
- **Decisión**: en teléfono la barra muestra el botón del menú (44×44), la **sede activa** como selector de 44 px con el nombre completo en una línea recortada con puntos suspensivos y el menú de usuario; el aviso «Todas las sedes: solo consulta» pasa a una franja delgada bajo la barra. El menú lateral usa el comportamiento de superposición de AdminLTE (cierra al elegir opción o tocar fuera), que se verifica con la prueba en navegador.
- **Razón**: RF-004, RF-015 (H1, H11).

## D7. Teclados y conservación de datos
- **Decisión**: el componente `x-form.input` mapea su tipo a `type`/`inputmode`/`autocomplete` (`tel`+`numeric` para celular; `email`; `numeric` para documento y valores; `date` ya lo resuelve el calendario). Los datos escritos ya se devuelven por `old()` ante errores de validación; para fallos de red se agrega un respaldo en `sessionStorage` por formulario (solo campos que no son contraseña ni archivos) que se restaura al volver y se borra al enviar con éxito.
- **Razón**: RF-008, RF-009. No se guarda nada sensible: es almacenamiento de sesión del navegador y se limpia al cerrar.
- **Alternativa**: guardado en servidor de borradores (excesivo y cambia el modelo de datos).

## D8. Peso y carga
- **Decisión**: (a) **carga diferida** con `import()` de DataTables/Responsive (solo en páginas con grilla), Select2, Flatpickr y SweetAlert2 (solo si la pantalla los usa; marcadores `data-*` ya existen); (b) **Font Awesome recortado** a los iconos realmente usados (se elimina la capa de compatibilidad v4 y las fuentes `.ttf` del paquete), cargando solo `solid` y `brands`; (c) compresión gzip/brotli y caché larga e inmutable para `/build` en `.htaccess`; (d) fuentes con `font-display: swap`. Meta: JS inicial < 200 kB y CSS < 300 kB sin comprimir.
- **Razón**: RF-014, H9.
- **Alternativas**: reemplazar DataTables o AdminLTE (rediseño fuera de alcance); renderizado en servidor sin JS (rompe la spec 007).

## D9. Aplicación instalable sin datos sin conexión
- **Decisión**: `manifest.webmanifest` (nombre, nombre corto, `display: standalone`, `theme_color` de la marca, iconos 192/512/maskable), meta para iPhone (`apple-touch-icon`, `apple-mobile-web-app-capable`), `viewport-fit=cover`, color de tema, y **`sw.js`** con política cerrada: *cache-first* solo para `/build/assets/*` y `/iconos/*`; para **navegaciones**, red primero y, si falla, `offline.html`; **todo lo demás** (HTML, JSON de grillas, CSV, formularios) va siempre a la red y **nunca se almacena**. Se registra solo en HTTPS. La pantalla de «sin conexión» se genera desde una vista.
- **Razón**: RF-012, RF-013, RF-013b, CE-008b.
- **Alternativas**: sin *service worker* (Chrome no ofrece instalar ni hay pantalla propia sin conexión); caché de lectura de datos (rechazada en la clarificación por privacidad).

## D10. Áreas seguras y teclado en pantalla
- **Decisión**: `viewport-fit=cover` y `padding` con `env(safe-area-inset-*)` en la barra superior y en las acciones fijas al pie; el contenedor principal usa `100dvh` donde aplica para que el teclado no tape el botón de guardar.
- **Razón**: casos límite de muesca y teclado.

## D11. Calidad del servicio móvil (prueba de carga)
- **Decisión**: la prueba de Playwright emula «4G lenta» (CDP: 1.6 Mbps, 150 ms) en tres pantallas representativas (tablero, personas, crear persona) y exige contenido útil en < 4 s la primera vez y < 2 s con caché del navegador.
- **Razón**: CE-006.

## D12. Gobernanza
- **Decisión**: enmendar la constitución a v1.1.0 con el principio VII «Diseño primero para celular» (RF-019) y reflejarlo en el README.
