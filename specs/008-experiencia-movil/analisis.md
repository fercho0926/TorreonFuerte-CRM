# Análisis de la aplicación: funcionalidad y experiencia en celular

**Fecha**: 2026-10-19 | **Alcance**: las 38 pantallas navegables (listados, fichas, formularios, tablero, cuenta) | **Método**: revisión de código y recorrido automatizado de cada pantalla en un ancho de 360 px (sesión de administrador, datos de ejemplo), más capturas a 390 px.

## 1. Lo que ya está bien

| Aspecto | Resultado |
|---------|-----------|
| Desbordamiento horizontal de la página | **0 de 38** pantallas se desbordan a 360 px |
| Etiqueta de ancho de pantalla (`viewport`) | Presente en el diseño principal y en el de acceso |
| Listados | Los 10 listados principales usan la grilla estándar: filas plegables, filtros, paginación, exportación (spec 007) |
| Formularios | Acciones fijas al pie, botones a ancho completo en teléfono, lista de asistencia convertida en tarjetas |
| Áreas de pulsación en tablas | Los botones de acción de fila ya miden al menos 36 px |
| Foco visible y confirmaciones | Presentes; sin dependencia de JavaScript para guardar |
| Funcionalidad | 496 pruebas del servidor y 76 de JavaScript en verde |

## 2. Hallazgos que afectan el uso en celular

Severidad: **A** = estorba una tarea diaria, **M** = incomoda, **B** = pulido.

| # | Sev. | Hallazgo | Dónde | Evidencia |
|---|------|----------|-------|-----------|
| H1 | A | **Áreas de pulsación pequeñas en la barra superior**: botón del menú de 30×40 px, selector de sede de 31 px de alto y enlace de marca de 38×21 px; se repiten en las 38 pantallas | Barra superior y menú lateral | Recorrido a 360 px: 38 de 38 pantallas |
| H2 | A | **Campos con letra menor de 16 px**: en iPhone el navegador acerca la pantalla al tocarlos y desacomoda la vista | Selector de sede (todas las pantallas), filtros por columna y buscadores de las grillas (3 a 5 por listado) | 1 a 5 campos por pantalla |
| H3 | A | **Controles pequeños dentro de los listados**: botones «Llamar», «WhatsApp», «Ver», «Inactivar», paginador y «Exportar CSV» por debajo de 32 px en varias pantallas; hasta 23 elementos pequeños en personas y 18 en sin líder y cambiar clave | Listados, sin líder, cambiar clave | Recorrido a 360 px |
| H4 | A | **El tablero abre con una tabla ancha** («Resumen por sede») que obliga a desplazarla de lado dentro de su tarjeta, y los accesos rápidos quedan debajo de las tarjetas de totales | Tablero | Captura a 390 px |
| H5 | M | **Fichas con tablas anchas** (persona: historial y traslados con `text-nowrap`; líder; sede; célula): requieren desplazamiento horizontal interno y no se ven como lista | Fichas de persona, líder, célula, evento, sede | Revisión de código |
| H6 | M | **Una tabla sin contenedor adaptable**: la tabla de abonos/inscritos del evento no está dentro de un contenedor que se desplace | Ficha de evento | 1 tabla sin contenedor |
| H7 | M | **Grilla en teléfono**: con las columnas plegadas quedan solo «Nombre» y «Acciones»; el detalle exige tocar el signo + de cada fila; los filtros por columna se pierden porque ocupan una fila de cabecera que en teléfono no se ve ni se usa bien | Todos los listados | Captura de personas a 390 px |
| H8 | M | **El formulario de búsqueda ocupa mucho alto** antes de ver la primera fila (campo, interruptor, botón y enlace «Limpiar» en filas separadas) | Listados | Captura a 390 px |
| H9 | M | **Peso de la aplicación**: ≈547 kB de JavaScript (167 kB comprimido) y ≈497 kB de estilos (80 kB comprimido) en cada pantalla, más cuatro familias de iconos; en datos móviles lentos la primera carga se siente pesada | Todas | Compilación de producción |
| H10 | M | **Sin experiencia de «aplicación»**: no hay icono para la pantalla de inicio, color de barra del navegador ni nombre corto; el usuario debe abrir el navegador y escribir o buscar la dirección | Toda la aplicación | Revisión del diseño principal |
| H11 | M | **Selector de sede del administrador** muestra «Todas» truncado y la insignia «solo consulta» comprime la barra | Barra superior (administrador) | Captura a 390 px |
| H12 | B | Teclados: no se verifica que celular, correo y documento pidan el teclado adecuado (numérico, de correo) | Formularios de persona y usuario | Revisión pendiente |
| H13 | B | Falta una prueba automática que impida volver a introducir desbordes o controles pequeños | Calidad | No existe |

## 3. Funcionalidad: cobertura por flujo diario

| Flujo | Estado en celular | Observación |
|-------|-------------------|-------------|
| Iniciar sesión / recuperar clave | Funciona | Revisar tamaño de campos (H2) |
| Buscar a una persona y llamarla o escribirle por WhatsApp | Funciona, incómodo | H3, H7, H8 |
| Registrar una persona nueva | Funciona | H12 |
| Asignar un líder a quien no tiene | Funciona | Selector dentro de fila de grilla; ocupa mucho alto (H7) |
| Pasar lista en una célula | Bien (tarjetas con interruptor grande) | Mantener |
| Ver ficha de persona, líder o célula | Funciona con desplazamiento lateral | H5 |
| Consultar el tablero | Funciona | H4 |
| Cambiar de sede (administrador) | Funciona | H1, H2, H11 |
| Generar y enviar una clave por WhatsApp | Funciona | H3 |

## 4. Prioridad recomendada

1. Barra superior y controles táctiles (H1, H2, H3, H11): afecta todas las pantallas y es de bajo riesgo.
2. Grillas y listados pensados para teléfono (H7, H8): es la tarea más frecuente.
3. Tablero y fichas en formato de lista (H4, H5, H6).
4. Peso y experiencia de aplicación (H9, H10).
5. Teclados y prueba permanente (H12, H13).

## 5. Línea base de peso (antes de los cambios)

| Archivo | Tamaño | Comprimido |
|---------|--------|-----------|
| `app-*.js` (todo el JavaScript, en cada pantalla) | 549 kB | 168 kB |
| `app-*.css` (todos los estilos, en cada pantalla) | 497 kB | 80 kB |

## 6. Resultado final (implementación)

Todos los hallazgos quedaron cerrados y verificados por la prueba permanente `npm run test:movil` (315 pruebas en verde a 320, 360, 390, 430 y 740 px horizontal; 45 omitidas por aplicar solo a teléfono vertical; lista de excepciones vacía), 518 pruebas PHPUnit y 96 de Vitest.

| # | Hallazgo | Resultado |
|---|----------|-----------|
| H1 | Controles de la barra superior pequeños | Cerrado: botón de menú, selector de sede y menú de usuario de 44 px; aviso «solo consulta» en franja propia |
| H2 | Campos con letra menor de 16 px | Cerrado: 16 px en todos los campos, selectores, buscadores y filtros de grilla en teléfono |
| H3 | Controles pequeños en listados | Cerrado: 44 px en acciones de fila, contacto, paginador, exportar y casillas |
| H4 | Tablero con tabla ancha y accesos rápidos al final | Cerrado: accesos rápidos primero en teléfono; resumen por sede como tarjetas |
| H5 | Fichas con tablas anchas | Cerrado: todas las tablas de fichas son `tabla-apilable` |
| H6 | Tabla de evento sin contenedor | Cerrado: apilable con etiquetas |
| H7 | Grilla en teléfono con casi todo oculto | Cerrado: tarjetas con nombre, estado y dato clave; «Ver más», «Filtros (n)» y «Ordenar por» |
| H8 | Búsqueda de media pantalla | Cerrado: franja compacta; el botón «Buscar» se oculta con JavaScript (búsqueda en vivo) |
| H9 | Aplicación pesada | Cerrado en JavaScript: inicial 134 kB (antes 549 kB) con DataTables, jQuery/Select2, calendario y SweetAlert2 bajo demanda; CSS inicial 417 kB (antes 497 kB; AdminLTE solo pesa 312 kB); compresión y caché larga en `.htaccess` |
| H10 | Sin experiencia de aplicación | Cerrado: manifiesto, iconos, color de tema, *service worker* sin datos y pantalla «Sin conexión» |
| H11 | Selector de sede truncado | Cerrado |
| H12 | Teclados | Cerrado: tipos `celular`, `correo` y `numero` |
| H13 | Sin prueba permanente | Cerrado: `npm run test:movil` y guardas estáticas en `php artisan test` |

Hallazgos adicionales detectados y corregidos por la prueba: cifras grandes del tablero y nombres largos en tablas apiladas que ensanchaban la página a 320 px; cajas de información en horizontal a 740 px; el respaldo de formularios se borraba solo cuando se abría un formulario (ahora en cada pantalla).
