---

description: "Lista de tareas de la funcionalidad 008: aplicación funcional y 100 % adaptada a celular"
---

# Tareas: Aplicación funcional y 100 % adaptada a celular

**Entrada**: documentos de diseño en `/specs/008-experiencia-movil/`

**Requisitos previos**: plan.md, spec.md, analisis.md, research.md, data-model.md, contracts/ (css-y-componentes-moviles, aplicacion-instalable, prueba-movil)

**Pruebas**: se incluyen (RF-017, RF-018, CE-009). La prueba en navegador y las guardas se escriben **antes** de los cambios que verifican; empiezan en rojo y se ponen en verde historia por historia.

**Organización**: por historia de usuario. Todas las rutas son relativas a `laravel_app/`.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[HU1..HU6]**: historia de usuario a la que pertenece (spec.md)

---

## Fase 1: Preparación

- [X] T001 Confirmar la línea base antes de empezar: `php artisan test` (496), `npm test` (76) y `npm run build` en verde; anotar el peso actual de `public/build/assets/app-*.js` y `app-*.css` en `specs/008-experiencia-movil/analisis.md` (sección «Línea base de peso»)
- [X] T002 [P] Agregar las dependencias de desarrollo `@playwright/test` y `sharp` a `package.json` y ejecutar `npx playwright install chromium`; agregar los scripts `test:movil` y `iconos` a `package.json`
- [X] T003 [P] Crear los directorios `tests/movil/`, `tests/Feature/Movil/`, `scripts/`, `resources/iconos/` y `public/iconos/`

---

## Fase 2: Fundamentos (bloquea todas las historias)

**Propósito**: arnés de la prueba en navegador, valores de diseño y regla en la constitución.

- [X] T004 Crear la configuración de Playwright (proyectos 320, 360, 390, 430 y 740 px horizontal; arranque de servidor propio) en `tests/movil/playwright.config.js` y el preparador `tests/movil/servidor.mjs`: crea una base SQLite temporal en el directorio temporal del sistema, ejecuta `migrate:fresh --seed --force` con `DB_DATABASE` apuntando a ella (nunca `database/database.sqlite`), arranca `php artisan serve` en un puerto libre y lo apaga y borra todo al terminar
- [X] T005 Crear el inicio de sesión y la lista de pantallas (las 38 del análisis más inicio de sesión y recuperar clave) en `tests/movil/pantallas.mjs`, con el usuario administrador de ejemplo leído de `database/seeders/DatabaseSeeder.php`
- [X] T006 Crear la lista de excepciones vacía por defecto en `tests/movil/excepciones.json`, con la validación de que cada entrada exige `pantalla`, `selector`, `regla` y `motivo` (una excepción sin motivo hace fallar la prueba) en `tests/movil/excepciones.mjs`
- [X] T007 Crear `resources/css/movil.css` con las variables `--tactil-min: 44px`, `--campo-letra: 16px`, `--quiebre-movil: 768px` y los márgenes `--seguro-*` con `env(safe-area-inset-*)`, e importarlo en `resources/css/app.css` después de `grilla.css`
- [X] T008 Enmendar la constitución a la versión 1.1.0 agregando el principio «VII. Diseño primero para celular» (sin desplazamiento lateral entre 320 y 1920 px, controles de al menos 44 px, letra de campos de al menos 16 px, tablas apilables, prueba `npm run test:movil` en verde antes de cerrar una funcionalidad) en `.specify/memory/constitution.md`, y mencionarlo en la sección «Grillas» y en una sección nueva «Diseño para celular» de `README.md`

**Punto de control**: `npm run test:movil` arranca, recorre las pantallas y ejecuta las reglas del contrato (aún con fallas esperadas).

---

## Fase 3: Historia de Usuario 1 - Manejar la aplicación con el pulgar (Prioridad: P1) MVP

**Objetivo**: todo control tocable mide al menos 44 px, ningún campo tiene letra menor de 16 px, la barra superior y el menú son cómodos y no hay desplazamiento lateral (RF-001 a RF-004, RF-015, RF-016).

**Prueba independiente**: recorrer las 38 pantallas a 360 px y comprobar las reglas 1 a 3 del contrato (`npm run test:movil -- --grep "tactil|letra|desborde"`).

### Pruebas

- [X] T009 [P] [HU1] Escribir las aserciones 1 (sin desplazamiento horizontal), 2 (controles ≥ 44 px) y 3 (letra de campos ≥ 16 px) del contrato, con listado de elemento y pantalla en el mensaje de falla, en `tests/movil/auditoria.spec.js`
- [X] T010 [P] [HU1] Escribir la prueba de comportamiento del menú lateral (se abre sobre el contenido; se cierra al elegir una opción o al tocar fuera) y de la barra superior del administrador (nombre de sede legible, franja «solo consulta» sin comprimir) en `tests/movil/barra-menu.spec.js`

### Implementación

- [X] T011 [HU1] Implementar las reglas globales de tamaño táctil y de letra bajo `@media (max-width: 767.98px), (pointer: coarse)` en `resources/css/movil.css`: `min-height` y `min-width` de `var(--tactil-min)` para `.btn`, `.nav-link`, `.page-link`, `.dropdown-item`, `a.btn-sm`, `.form-select`, `.form-control`; área de pulsación ≥ 44 px para casillas e interruptores; `font-size: var(--campo-letra)` en `input`, `select`, `textarea`, `.select2-selection` y `.dt-container select`; sin ningún cambio en escritorio
- [X] T012 [HU1] Rehacer la barra superior en `resources/views/layouts/admin.blade.php`: botón de menú de 44 × 44, selector `#sede-activa` de 44 px de alto con texto recortado con puntos suspensivos, menú de usuario táctil, y mover la insignia «Todas las sedes: solo consulta» a una franja `.aviso-sede-movil` bajo la barra en teléfono (la insignia actual solo en escritorio); estilos en `resources/css/movil.css`
- [X] T013 [HU1] Ajustar el enlace de la marca del menú (`.brand-link`) y las opciones del menú lateral a 44 px y verificar la superposición del menú en teléfono (AdminLTE ya cierra el menú al elegir opción o tocar fuera; se comprobó con `tests/movil/barra-menu.spec.js`, sin código nuevo)
- [X] T014 [P] [HU1] Aplicar los tamaños táctiles a los botones de contacto («Llamar», «WhatsApp») en `resources/views/partials/acciones-contacto.blade.php`, a «Ver» en `resources/views/partials/persona-tarjeta.blade.php` y a los botones de `resources/views/components/ui/acciones-fila.blade.php` (separación entre botones contiguos)
- [X] T015 [P] [HU1] Aplicar la regla táctil y de letra a las pantallas de acceso en `resources/views/layouts/auth.blade.php` y `resources/views/auth/*.blade.php` (inicio de sesión, recuperar clave) y a `resources/views/cuenta/*.blade.php`
- [X] T016 [HU1] Ejecutar `npm run test:movil -- --grep "tactil|letra|desborde|menu"` y corregir las pantallas que falten (sin agregar excepciones)

**Punto de control**: las aserciones 1 a 3 y el comportamiento del menú pasan en las 38 pantallas.

---

## Fase 4: Historia de Usuario 2 - Listados cómodos en celular (Prioridad: P1)

**Objetivo**: listados como tarjetas con lo esencial a la vista, panel «Filtros (n)», búsqueda en una franja y contacto con un toque (RF-005 a RF-007).

**Prueba independiente**: en un teléfono abrir personas, líderes, células y sin líder; ver nombre, estado y dato clave sin pulsar, filtrar desde el panel y llamar, con la primera tarjeta visible sin desplazarse.

### Pruebas

- [X] T017 [P] [HU2] Escribir la prueba de comportamiento del listado a 390 px (primera tarjeta con borde superior por debajo de 700 px; «Filtros (n)» abre el panel, cambia `n` al escribir y actualiza las filas; «Ver más» muestra las columnas secundarias; paginador, filas por página y contador tocables y sin desbordar) en `tests/movil/listados.spec.js`
- [X] T018 [P] [HU2] Escribir las pruebas Vitest de las funciones puras del modo tarjeta (`contarFiltrosActivos`, `columnasSecundarias`, `etiquetaDeCelda`) en `resources/js/grilla/movil.test.js`
- [X] T019 [P] [HU2] Escribir la guarda PHPUnit que verifica que `<x-ui.grilla>` emite `data-etiqueta` en cada celda, el botón `[data-grilla-filtros]` y la clase `grilla-sec` en las columnas de prioridad mayor a 3 en `tests/Feature/Movil/GrillaMovilTest.php`

### Implementación

- [X] T020 [HU2] Agregar al componente `resources/views/components/ui/grilla.blade.php`: atributo `data-etiqueta` con el título de la columna en cada `<td>`, clase `grilla-sec` en las celdas de columnas de prioridad mayor a 3, botón «Filtros» (`data-grilla-filtros`) con contador en la barra, y el contenedor `.grilla-panel-filtros` para la fila de filtros
- [X] T021 [HU2] Implementar el modo tarjeta por debajo de 768 px en `resources/css/grilla.css`: `table.grilla` en bloque, filas como tarjetas, etiqueta antepuesta a cada celda, columnas `grilla-sec` ocultas hasta `.abierta`, encabezado oculto accesiblemente, panel de filtros desplegable, y sin JavaScript todas las columnas visibles
- [X] T022 [HU2] Implementar en `resources/js/grilla/grilla.js`: desactivar la extensión Responsive por debajo de 768 px y reactivarla al volver a escritorio (escucha de `matchMedia`), poner `data-etiqueta` y el botón «Ver más» en cada fila dibujada (`createdRow`), y el botón «Filtros (n)» que abre el panel y cuenta los filtros activos; extraer las funciones puras a `resources/js/grilla/movil.js`
- [X] T023 [HU2] Compactar el formulario de búsqueda de la grilla en teléfono (campo a todo el ancho; interruptor y «Limpiar» con icono en la misma franja; alto reducido) en `resources/css/grilla.css` y `resources/css/movil.css`, conservando el diseño de escritorio
- [X] T024 [P] [HU2] Verificar y ajustar el listado «sin líder» (formulario de asignar líder dentro de la tarjeta: selector y botón caben en el ancho) en `resources/views/sin-lider/_celda.blade.php` y `resources/css/movil.css`
- [X] T025 [P] [HU2] Ajustar las prioridades de columna de cada grilla para que en teléfono la tarjeta muestre nombre, estado y un dato clave (celular o líder) en `app/Grillas/PersonasGrilla.php`, `LideresGrilla.php`, `CelulasGrilla.php`, `EncuentrosGrilla.php`, `EventosGrilla.php`, `MinisteriosGrilla.php`, `SedesGrilla.php`, `SinLiderGrilla.php`, `UsuariosGrilla.php` y `SolicitudesClaveGrilla.php`
- [X] T026 [HU2] Ejecutar `npm run test:movil -- --grep "listados"`, `npm test` y `php artisan test --filter=Grilla`; corregir lo que falle

**Punto de control**: los 10 listados funcionan como tarjetas en teléfono y como tablas en escritorio.

---

## Fase 5: Historia de Usuario 3 - Formularios que ayudan a escribir en celular (Prioridad: P1)

**Objetivo**: teclado correcto por tipo de dato, botón de guardar siempre visible, aviso claro y datos conservados ante fallos (RF-008, RF-009).

**Prueba independiente**: abrir crear persona, crear usuario, cambiar clave y recuperar clave en un teléfono y comprobar teclado, errores y guardado.

### Pruebas

- [X] T027 [P] [HU3] Escribir la prueba PHPUnit de los tipos de teclado (`celular` → `type="tel" inputmode="numeric" autocomplete="tel"`; `correo` → `type="email" inputmode="email" autocomplete="email"`; `numero` → `inputmode="numeric"`; `moneda` → `inputmode="decimal"`) en `tests/Feature/Movil/TeclasFormularioTest.php`
- [X] T028 [P] [HU3] Escribir las pruebas Vitest del respaldo de formulario (guarda con retardo, excluye contraseñas, tokens y archivos, restaura tras fallo, borra al enviar con éxito) en `resources/js/forms/borrador.test.js`
- [X] T029 [P] [HU3] Escribir la prueba en navegador: botón «Guardar» visible con el viewport reducido al 60 % (teclado simulado) y llegada al primer campo con error en `tests/movil/formularios.spec.js`

### Implementación

- [X] T030 [HU3] Implementar los tipos `celular`, `correo`, `numero` y `moneda` en `resources/views/components/form/input.blade.php` (y alinear `money.blade.php`) según `contracts/css-y-componentes-moviles.md`
- [X] T031 [HU3] Aplicar los tipos nuevos a los campos de celular, correo, documento y valores de `resources/views/personas/_form.blade.php`, `resources/views/users/*.blade.php`, `resources/views/auth/*.blade.php`, `resources/views/sedes/*.blade.php`, `resources/views/eventos/*.blade.php`, `resources/views/encuentros/*.blade.php` y `resources/views/celulas/_form.blade.php`
- [X] T032 [HU3] Implementar el respaldo de formulario en `resources/js/forms/borrador.js` (`sessionStorage`, clave `crm.borrador.<ruta>`, retardo de guardado, restauración tras fallo de red o recarga, borrado al enviar con éxito) y conectarlo en `resources/js/forms/index.js` y `resources/js/forms/envio.js` con aviso claro de fallo de conexión
- [X] T033 [HU3] Ajustar `resources/css/forms.css`: acciones fijas con `padding-bottom: env(safe-area-inset-bottom)`, contenedor con `100dvh`, mensaje de error legible sin acercar la pantalla y buscador interno de Select2 que no queda tapado por el teclado
- [X] T034 [HU3] Ejecutar `npm run test:movil -- --grep "formularios"`, `npm test` y `php artisan test --filter=Formularios`; corregir lo que falle

**Punto de control**: los formularios principales cumplen la historia; las pruebas de formularios existentes siguen en verde.

---

## Fase 6: Historia de Usuario 4 - Tablero y fichas en formato de lista (Prioridad: P2)

**Objetivo**: tablero con accesos rápidos primero y tablas apilables como tarjetas, sin desplazamiento lateral (RF-010, RF-011).

**Prueba independiente**: abrir el tablero y una ficha de persona, líder, célula, evento y sede en un teléfono sin desplazarse de lado.

### Pruebas

- [X] T035 [P] [HU4] Escribir la guarda PHPUnit: toda `<table>` en `resources/views` fuera de `components/ui/grilla.blade.php` lleva la clase `tabla-apilable` y cada `<td>` tiene `data-etiqueta` en `tests/Feature/Movil/TablasApilablesTest.php`
- [X] T036 [P] [HU4] Escribir la aserción 4 del contrato (sin tabla sin adaptar a menos de 768 px) y la prueba del orden del tablero (accesos rápidos antes que los totales a 390 px) en `tests/movil/auditoria.spec.js` y `tests/movil/tablero.spec.js`

### Implementación

- [X] T037 [HU4] Implementar `.tabla-apilable` por debajo de 768 px (encabezado oculto accesiblemente, filas como tarjetas, etiqueta antepuesta, celda `data-primaria` como título) en `resources/css/movil.css`, reutilizando lo ya probado de `fm-lista__tabla` en `resources/css/forms.css`
- [X] T038 [P] [HU4] Convertir el tablero: reordenar con clases de orden para que los accesos rápidos salgan primero en teléfono, y aplicar `tabla-apilable` con `data-etiqueta` a «Resumen por sede» y «Próximos eventos» en `resources/views/dashboard.blade.php`
- [X] T039 [P] [HU4] Aplicar `tabla-apilable` y `data-etiqueta` a las tablas de historial y traslados de `resources/views/personas/show.blade.php` y mover «Llamar» y «WhatsApp» a la franja de acciones del encabezado de la ficha
- [X] T040 [P] [HU4] Aplicar `tabla-apilable` y `data-etiqueta` a la tabla de «Personas a su cargo» de `resources/views/lideres/show.blade.php` (se conserva la altura máxima) y a las de `resources/views/sedes/show.blade.php`
- [X] T041 [P] [HU4] Aplicar `tabla-apilable` y `data-etiqueta` a las tablas de `resources/views/celulas/show.blade.php` y a la tabla de inscritos y abonos de `resources/views/eventos/show.blade.php` (hoy sin contenedor adaptable)
- [X] T042 [HU4] Ejecutar `npm run test:movil -- --grep "tablas|tablero"` y `php artisan test --filter=Movil`; corregir lo que falle

**Punto de control**: no queda ninguna tabla sin formato adaptado y el tablero abre con lo más usado.

---

## Fase 7: Historia de Usuario 5 - Rápida y como una aplicación instalada (Prioridad: P2)

**Objetivo**: carga ligera y acceso desde la pantalla de inicio, con pantalla de «sin conexión» y sin guardar datos en el teléfono (RF-012 a RF-014, RF-013b).

**Prueba independiente**: medir la primera carga en «4G lenta», instalar la aplicación y comprobar que la caché del dispositivo solo contiene archivos de la interfaz.

### Pruebas

- [X] T043 [P] [HU5] Escribir la guarda PHPUnit: `public/manifest.webmanifest` es JSON válido con `name`, `short_name`, `start_url`, `display: standalone`, `theme_color`, `lang` e iconos existentes; `layouts/admin` y `layouts/auth` declaran `viewport-fit=cover`, `theme-color`, `manifest`, `apple-touch-icon`; `public/sw.js` solo escribe en caché rutas de `/build/assets/`, `/iconos/` y `offline.html` en `tests/Feature/Movil/AplicacionInstalableTest.php`
- [X] T044 [P] [HU5] Escribir la prueba en navegador de carga (emulación «4G lenta» por CDP: contenido útil en menos de 4 s en tablero, personas y crear persona; menos de 2 s en la recarga), de instalabilidad (manifiesto válido, iconos 200, *service worker* activo en `localhost`), de «sin conexión» (red cortada muestra `offline.html`) y de privacidad (la caché solo contiene `offline.html`, `/build/assets/*` e `/iconos/*`; ninguna respuesta JSON, CSV ni HTML de pantallas) en `tests/movil/instalable.spec.js` y `tests/movil/carga.spec.js`
- [X] T045 [P] [HU5] Escribir una prueba que mide el peso de `public/build/assets` tras compilar (JS inicial por debajo de 200 kB y CSS inicial por debajo de 450 kB sin comprimir; el CSS de AdminLTE solo ya pesa 312 kB, por eso el umbral de estilos se fijó en 450 kB) en `tests/movil/peso.spec.js`

### Implementación

- [X] T046 [HU5] Crear el SVG de marca en `resources/iconos/marca.svg` y el generador `scripts/generar-iconos.mjs` (con `sharp`) que produce `public/iconos/icono-192.png`, `icono-512.png`, `icono-maskable-512.png`, `apple-touch-180.png` y `favicon.ico`; ejecutar `npm run iconos`
- [X] T047 [HU5] Crear `public/manifest.webmanifest` con los campos de `data-model.md` (nombre desde la configuración del sistema, `short_name` «Torreón», `start_url` `/dashboard`, `display` `standalone`, color de marca, `lang` `es`, iconos)
- [X] T048 [HU5] Agregar las etiquetas de `contracts/aplicacion-instalable.md` (`viewport-fit=cover`, `theme-color`, `manifest`, `apple-touch-icon`, metas de iPhone) en `resources/views/layouts/admin.blade.php` y `resources/views/layouts/auth.blade.php`
- [X] T049 [HU5] Crear `public/offline.html` («Sin conexión», botón «Reintentar», marca), autocontenido y sin dependencias de `/build` (se escribió directamente como archivo estático en lugar de generarlo desde una vista, para que no dependa de la aplicación)
- [X] T050 [HU5] Crear `public/sw.js` con la política cerrada del contrato (caché primero solo para `/build/assets/*` e `/iconos/*`; navegaciones por red con respaldo `offline.html`; todo lo demás solo red y nunca almacenado; sin escribir respuestas con `Set-Cookie`, JSON ni CSV; limpieza de versiones anteriores) y `resources/js/pwa/registrar.js` que lo registra solo en HTTPS o `localhost`, importado desde `resources/js/app.js`
- [X] T051 [HU5] Carga diferida con `import()` de DataTables y Responsive (solo en páginas con `table[data-grilla]`) en `resources/js/grilla/grilla.js` y `resources/js/app.js`
- [X] T052 [HU5] Carga diferida de Select2, Flatpickr y SweetAlert2 (solo cuando la pantalla tiene `data-buscar`, campos de fecha o confirmaciones) en `resources/js/jquery-global.js`, `resources/js/forms/selectores.js`, `resources/js/forms/fechas.js` y `resources/js/forms/confirmar.js`, conservando el orden de carga de jQuery antes de los formularios (prueba `OrdenDeCargaJsTest` existente)
- [X] T053 [HU5] Recortar Font Awesome a los iconos realmente usados (solo `solid` y `brands`; sin capa de compatibilidad v4 ni archivos `.ttf`) en `resources/css/app.css`, comprobando con `grep` que ningún icono usado en `resources/views` desaparece
- [X] T054 [P] [HU5] Agregar compresión (gzip/brotli) y caché larga e inmutable para `/build`, y `sw.js` sin caché larga, en `public/.htaccess`; documentar el requisito de HTTPS en `DEPLOY_CPanel.md`
- [X] T055 [HU5] Ejecutar `npm run build`, `npm run test:movil -- --grep "instalable|carga|peso"` y `php artisan test --filter=Movil`; corregir lo que falle

**Punto de control**: instalable, ligera y sin datos en el dispositivo.

---

## Fase 8: Historia de Usuario 6 - Que no se pierda en el futuro (Prioridad: P3)

**Objetivo**: la prueba recorre todas las pantallas y falla ante cualquier regresión (RF-018, RF-019).

**Prueba independiente**: introducir a propósito una tabla ancha o un botón de 20 px y ver que la prueba falla.

- [X] T056 [HU6] Completar la aserción 5 del contrato (texto cortado sin puntos suspensivos) y la ejecución de todas las pantallas en los cinco tamaños en `tests/movil/auditoria.spec.js`, con el listado de pantalla, tamaño, regla y selector en cada falla
- [X] T057 [P] [HU6] Agregar al flujo de trabajo de integración continua la ejecución de `php artisan test` (incluye `tests/Feature/Movil`) en `.github/workflows/*.yml` y un paso opcional de `npm run test:movil`
- [X] T058 [P] [HU6] Documentar en la sección «Diseño para celular» del `README.md` cómo ejecutar `npm run test:movil`, cómo se agregan excepciones (con motivo) y las reglas de la constitución
- [X] T059 [HU6] Verificar la prueba de regresión: introducir temporalmente una tabla sin `tabla-apilable` y un botón de 20 px, comprobar que `php artisan test --filter=Movil` y `npm run test:movil` fallan señalando la pantalla, y revertir el cambio

---

## Fase 9: Pulido y verificación

- [X] T060 Ejecutar `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` completos; todo en verde con la lista de excepciones vacía (496 PHPUnit y 76 Vitest como mínimo, más las nuevas)
- [X] T061 Verificar `quickstart.md` en navegador (ancho de teléfono con una copia de la base local o la base temporal sembrada): los 10 pasos manuales; confirmar que escritorio no tiene regresiones (RF-016, CE-010); borrar archivos temporales (bases SQLite, `.env.pruebaNNN`, procesos del servidor)
- [X] T062 [P] Registrar en `specs/008-experiencia-movil/analisis.md` el resultado final (hallazgos H1 a H13 cerrados, pesos antes y después) y actualizar la nota de arquitectura en la memoria del proyecto con la regla «diseño primero para celular»

---

## Dependencias y orden de ejecución

- **Fase 1 → Fase 2 → Fases 3 a 8 → Fase 9**.
- La Fase 2 bloquea todo: T004 y T005 antes de cualquier prueba de navegador; T007 antes de T011.
- **HU1** (Fase 3) primero: sus reglas globales y la barra superior benefician a todas las demás.
- **HU2** depende de T007 y T011; **HU3** y **HU4** dependen de T007 y T011 y son independientes entre sí y de HU2.
- **HU5** puede avanzar en paralelo desde la Fase 2 (archivos distintos), salvo T051 y T052, que tocan `grilla.js` y los módulos de formularios y deben ir después de HU2 y HU3.
- **HU6** cierra el trabajo: T056 depende de que HU1 a HU5 estén completas para quedar en verde.

## Ejemplos de ejecución en paralelo

- Fase 1: T002 y T003.
- HU1: T009 y T010 (pruebas), luego T014 y T015 en paralelo.
- HU2: T017, T018 y T019 (pruebas); T024 y T025 en paralelo con T021 a T023.
- HU4: T038, T039, T040 y T041 en paralelo (vistas distintas).
- HU5: T043, T044 y T045 (pruebas) y luego T046 a T050 en paralelo parcial (iconos, manifiesto, `sw.js`).

## Estrategia de implementación

1. **MVP**: Fases 1 y 2 más **HU1** (reglas táctiles, barra superior y menú). Beneficia las 38 pantallas y se valida con la prueba en navegador.
2. **Entrega incremental**: HU2 (listados), HU3 (formularios) y HU4 (tablero y fichas) son los tres siguientes por frecuencia de uso; cada una se cierra con su prueba antes de seguir.
3. **Cierre**: HU5 (ligereza e instalable), HU6 (prueba permanente) y pulido. Las excepciones permanecen vacías; cualquier regla incumplida se corrige en la pantalla, no en la lista.
