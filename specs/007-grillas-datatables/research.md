# Investigación: Grillas uniformes

## D1. Una definición declarativa por listado
- **Decisión**: clase `Grilla` con lista de `Columna` (clave, título, `ordenable`, `filtro` = texto, lista o ninguno, expresión SQL o closure para ordenar y filtrar, celda HTML, valor de exportación, prioridad en teléfono). Cada módulo hereda y solo declara columnas y la consulta base con el alcance.
- **Razón**: CE-005 (agregar un listado definiendo solo columnas y datos) y RF-013.
- **Alternativas**: paquete yajra/laravel-datatables (dependencia extra, abstrae poco el alcance por sede y las celdas Blade); copiar `GrillaPersonas` a cada módulo (repetición y deriva).

## D2. Protocolo del servidor
- **Decisión**: mantener el modo servidor de DataTables ya usado en personas (`draw/start/length/order[0]` más parámetros de filtro propios), una ruta `*.datos` y una `*.exportar` por listado, con nombre predecible (`{modulo}.datos`).
- **Razón**: ya probado en personas; orden y filtros sobre todo el conjunto (RF-002).
- **Alternativas**: DataTables en modo cliente (no escala a miles de filas ni respeta la paginación del servidor); una API REST aparte (excesivo).

## D3. Orden y filtros seguros
- **Decisión**: el orden y los filtros se resuelven solo con la lista blanca de columnas de la definición; las expresiones son subconsultas construidas por la clase, nunca texto del cliente. Longitud de página limitada a 10/25/50/100.
- **Razón**: evita inyección y consultas costosas.

## D4. Fuentes que no son Eloquent
- **Decisión**: la grilla acepta un `Builder` o una `Collection` en memoria (sedes usa `SedeService::resumen()`, con pocas filas); para colecciones, orden, filtro y paginado se hacen en PHP con las mismas reglas.
- **Razón**: no reescribir el resumen agregado de sedes.
- **Alternativa**: convertir el resumen a consulta SQL con subconsultas (más riesgo y poco beneficio con menos de 50 filas).

## D5. Exportación CSV
- **Decisión**: igual que personas: `;` como separador, BOM UTF-8, `chunk(500)`, neutralización de `= + - @`. Cada columna puede declarar su valor exportable (texto plano, sin HTML). Usuarios y solicitudes de clave: solo administrador, comprobado en la ruta (política) y en la vista.
- **Razón**: RF-005 y RF-005b.

## D6. Celdas y acciones por fila
- **Decisión**: las celdas se renderizan en el servidor con Blade (`<x-ui.acciones-fila>`, `<x-form.confirm>`), de modo que permisos, tokens CSRF y confirmaciones siguen igual; los manejadores de `confirmar.js` ya son delegados y funcionan con filas dinámicas.
- **Alternativa**: renderizar en el cliente desde JSON (duplicaría permisos y marcado).

## D7. Respaldo sin JavaScript
- **Decisión**: la vista pinta la primera página con la tabla del servidor y su paginador; el JS reemplaza el paginador. Los parámetros GET existentes (`q`, `estado`, `sede_id`, etc.) siguen funcionando (RF-011).

## D8. Teléfono
- **Decisión**: extensión Responsive con `responsivePriority` por columna; la columna de acciones y la primera tienen prioridad máxima.

## D9. Regla permanente (RF-014, RF-015)
- **Decisión**: prueba Feature que recorre `resources/views/*/index.blade.php` y exige el componente de grilla (lista de excepciones vacía); constitución mínima con la regla; guía en el README.
- **Alternativa**: solo documentación (no se hace cumplir).

## D10. Migración incremental y personas
- **Decisión**: construir primero la base (`Grilla`, componente, JS, rutas y acciones comunes) refactorizando personas sobre ella sin cambiar su comportamiento; luego migrar los demás listados de a uno, cada uno con su prueba de contrato.
