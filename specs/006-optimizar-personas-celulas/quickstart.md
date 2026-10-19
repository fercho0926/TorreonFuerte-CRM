# Guía de validación: Personas, Líderes, Células y Ministerios

## Requisitos previos

- Base temporal sembrada (no usar `migrate:fresh` sobre la base local): `.env` de prueba con su `APP_KEY` y `php artisan migrate --seed`. Usuarios de ejemplo en el README.
- Para los escenarios de volumen, sembrar 500 personas de una sede (fábrica de personas).
- `npm install && npm run build`.
- Antes de aplicar la migración sobre datos reales, anotar los conteos de personas, líderes, células y ministerios para compararlos después (SC-007).

## Comandos

- `php artisan test` (incluye las pruebas nuevas de personas, líderes, células y ministerios).
- `npm test` (módulos del buscador remoto y de alta rápida).
- `npm run build`.

## Escenarios de validación

| # | Escenario | Pasos | Resultado esperado |
|---|-----------|-------|--------------------|
| 1 | Una tarjeta de datos personales (HU1) | Personas → Crear | La primera tarjeta contiene nombres, apellidos, nacimiento, celular, correo, estado, tipo y número de documento; no hay «Contacto» ni «Estado y documento» |
| 2 | Reglas de documento (HU1) | Elegir miembro activo / visitante / «En célula» | Documento obligatorio solo para miembro activo; opcional en los demás |
| 3 | Edición y ficha (HU1) | Editar una persona y abrir su ficha | Mismos campos en la misma tarjeta; la ficha agrupa contacto, estado y documento |
| 4 | Sin «Red» (HU2) | Recorrer menú, líderes, personas, filtros y tablero | Ninguna mención, campo, filtro, caja ni ruta de red |
| 5 | Datos intactos (HU2, SC-007) | Comparar conteos antes y después de migrar | Iguales (personas, líderes, células, ministerios) |
| 6 | Designar líder con autocompletado (HU3) | Líderes → Designar; escribir 2 letras | Sugerencias en < 1 s de personas no inactivas y no líderes, con documento y celular |
| 7 | Exclusiones (HU3) | Buscar una persona inactiva y un líder existente | No aparecen |
| 8 | Sin documento (HU3) | Elegir una persona sin documento | Aviso con enlace a editarla; no se designa |
| 9 | Grilla de a cargo (HU4) | Abrir la ficha de un líder con personas a cargo y de uno sin ninguna | Grilla con nombre, estado, celular y célula; vacío: «Aún no lidera a nadie» |
| 10 | Integrantes de célula (HU5) | Crear una célula y elegir integrantes escribiendo nombres | Se filtran y autocompletan; contador de seleccionadas; quedan guardados |
| 11 | Crear desde la célula (HU5) | «Crear persona nueva» con nombres, apellidos y celular | Queda con estado «En célula» y seleccionada; duplicado avisa y pide confirmar |
| 12 | Independencia del líder (D4) | Añadir a una célula una persona cuyo líder directo es otro | Se añade; su líder directo no cambia |
| 13 | Estado «En célula» (HU6) | Crear y filtrar por ese estado | Sin documento; insignia propia; no aparece en «Sin líder asignado» |
| 14 | Menú (HU7) | Abrir el menú | «Ministerios» dentro de Congregación; no en Actividades |
| 15 | Integrantes de ministerio (HU7) | Con 500 personas, escribir 3 letras | Filtra por nombre al instante y permite marcar varias |
| 16 | Aislamiento (FR-013) | Como pastor de otra sede, buscar personas de la primera | No aparecen en ninguna búsqueda ni selección |
| 17 | Rendimiento (SC-006) | Medir la respuesta de `personas/buscar` con 500 personas | < 1 s |

## Criterios de salida

- `php artisan test` y `npm test` en verde; `npm run build` sin errores.
- Escenarios 1–17 verificados con base temporal; limpiar `.env` de prueba, base y servidor temporales al terminar.

## Línea base de datos (copia de la base local, 2026-10-19)

Personas 11, líderes 3, células 3, ministerios 1. Tras aplicar la migración que retira `redes` estos conteos deben ser iguales (SC-007).

## Resultado de la migración sobre una copia de la base local (T030, 2026-10-19)

`php artisan migrate` sobre una copia de `database.sqlite` aplicó `2026_10_25_000001_drop_redes`. Después: personas 11, líderes 3, células 3, ministerios 1 (iguales a la línea base); la tabla `redes` y la columna `personas.red_id` ya no existen.

## Resultado del recorrido en navegador (2026-10-19, base temporal con 515 personas)

- **Designar líder:** al escribir «mar» aparecen en ~0,23 s sugerencias con documento y celular; se elige una y se designa sin pulsar «Buscar» (escenarios 6–8).
- **Células:** el multiselect de integrantes autocompleta, «Crear persona nueva» abre el diálogo, crea a la persona en estado «En célula» y la deja seleccionada; la célula se guarda con ese integrante y su ficha muestra el estado (escenarios 10, 11 y 13).
- **Ministerios:** el formulario usa el mismo buscador (escenario 15); el menú tiene Ministerios en Congregación (escenario 14).
- **Rendimiento (SC-006):** `personas/buscar` responde en 227–236 ms por consulta (con el servidor de desarrollo de PHP), muy por debajo de 1 s. No se midió una línea base previa de las pantallas porque el cambio no añade consultas pesadas; los formularios ya no vuelcan todas las personas en la página.
- **Hallazgo corregido:** Select2 no se iniciaba al cargar la página porque `app.js` configuraba jQuery después de importar los formularios (los `import` se evalúan antes que el código suelto). Ahora jQuery está en `resources/js/jquery-global.js`, importado antes de `forms/index.js`, y una prueba (`OrdenDeCargaJsTest`) lo protege. Esto afectaba también a los selectores de 003/004 (los selectores con búsqueda caían al `<select>` nativo).
- **CSS:** dentro de un grupo con icono, Select2 comparte la fila con el icono (`forms.css`).
