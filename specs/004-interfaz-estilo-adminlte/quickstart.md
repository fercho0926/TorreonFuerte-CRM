# Guía de validación: Interfaz estilo AdminLTE

## Requisitos previos

- Línea base antes de empezar: anotar tamaño de `public/build` y tiempos de respuesta de `/dashboard`, `/personas` y `/celulas` (para SC-006).
- Copia de seguridad de la base local si se va a probar con datos reales; no ejecutar `migrate:fresh` sobre ella. Para recorridos usar una base temporal con `DatabaseSeeder` (usuarios en README).
- Dependencias: `npm install` y luego `npm run build` (o `npm run dev`).

## Comandos

- Pruebas de PHP: `php artisan test` (incluye `tests/Feature/Interfaz/` y las de 003).
- Pruebas de JavaScript: `npm test`.
- Compilación: `npm run build` y revisar que no haya advertencias.

## Escenarios de validación

| # | Escenario | Pasos | Resultado esperado |
|---|-----------|-------|--------------------|
| 1 | Marco común (HU1, FR-001/002) | Abrir una pantalla de cada módulo con un usuario administrador | Misma barra superior, menú, encabezado con migas, tarjeta de contenido y pie |
| 2 | Menú por rol (FR-003) | Entrar como administrador, pastor y líder | Cada rol ve solo sus opciones; secciones vacías no aparecen; opción activa resaltada |
| 3 | Datos de sesión (FR-004/005) | Revisar barra y menú; como administrador cambiar de sede y elegir "Todas las sedes" | Nombre, rol y sede visibles; selector y aviso «solo consulta» integrados |
| 4 | Listados (HU2, FR-006/008) | Abrir los 10 listados, filtrar hasta quedar sin resultados | Misma disposición, contador, insignias del mismo color, estado vacío con acción |
| 5 | Fichas (FR-007) | Abrir ficha de persona, célula, evento, encuentro, ministerio, sede | Tarjetas con acciones en la misma posición |
| 6 | Tablero (HU3, FR-010) | Iniciar sesión con cada rol | Cajas clicables y tres bloques; el líder solo ve su ámbito; cifras coinciden con listados |
| 7 | Menú recordado (FR-019) | En escritorio plegar el menú, recargar y navegar | Sigue plegado; en móvil siempre cerrado |
| 8 | Pantallas pequeñas (HU4, FR-014) | Redimensionar a 360, 768 y 1280 px | Sin desplazamiento horizontal de la página; tablas se desplazan dentro de su tarjeta |
| 9 | Teclado (FR-018) | Recorrer con Tab menú, filtros y botones | Foco visible en todos |
| 10 | Acceso y errores (HU5, FR-012) | Cerrar sesión; abrir `/ruta-inexistente`; entrar a una ruta sin permiso | Tarjeta de acceso y páginas 404/403 con el estilo del panel y botón «Volver al inicio» |
| 11 | Título (FR-013) | Mirar la pestaña en 5 pantallas | «Pantalla · Sistema · Sede» |
| 12 | Formularios de 003 (FR-017, SC-008) | En persona (crear) probar validación, selector con búsqueda, máscara, calendario, diálogo de confirmación | Todo funciona igual y se ve integrado |
| 13 | Guardia Bootstrap 4 | `php artisan test --filter=ClasesBootstrap` | Pasa: ninguna vista usa clases de Bootstrap 4 |
| 14 | Rendimiento (SC-006) | Repetir mediciones de línea base | Aumento ≤ 10 % |

## Criterios de salida

- `php artisan test` y `npm test` en verde; `npm run build` sin errores.
- Escenarios 1–14 verificados; SC-002 y SC-003 requieren una prueba con 5 personas (se programa fuera de la sesión de implementación).

## Línea base (medida el 2026-10-19, antes de migrar a AdminLTE 4)

- `public/build`: 3056 KB en total; `app-*.css` 1 485 601 B y `app-*.js` 439 463 B.
- Tiempo de respuesta promedio de 5 peticiones como administrador (servidor `php artisan serve`, base temporal sembrada): `/dashboard` 393 ms, `/personas` 382 ms, `/celulas` 308 ms.

## Resultado de la medición posterior (2026-10-19, AdminLTE 4)

- `public/build`: 1896 KB (antes 3056 KB); `app-*.css` 470 682 B (antes 1 485 601 B) y `app-*.js` 418 197 B (antes 439 463 B).
- Tiempo de respuesta promedio de 15 peticiones (servidor `php artisan serve`, vistas sin cachear): `/dashboard` 368 ms (antes 393), `/personas` 441–448 ms (antes 382), `/celulas` 327–344 ms (antes 308). Con `php artisan view:cache` (como se despliega): `/dashboard` 371 ms, `/personas` 402 ms, `/celulas` 329 ms.
- Lectura: el tablero mejora y las dos pantallas con más filas quedan entre +7 % y +5 % con las vistas cacheadas (en el servidor de desarrollo sin caché, `/personas` sube hasta +15 % por el costo de dibujar los componentes de cada fila). La línea base original se midió con 5 peticiones, así que el margen de error es de varios puntos.
