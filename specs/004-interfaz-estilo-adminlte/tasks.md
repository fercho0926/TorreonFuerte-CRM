---

description: "Lista de tareas: Interfaz de toda la aplicación al estilo AdminLTE"
---

# Tareas: Interfaz de toda la aplicación al estilo AdminLTE

**Entrada**: documentos de diseño en `specs/004-interfaz-estilo-adminlte/`
**Requisitos previos**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/`, `quickstart.md`

**Pruebas**: se incluyen porque el plan (D13) y los criterios de éxito (SC-001, SC-004–SC-008) dependen de verificaciones automáticas, y porque 003 exige no tener regresiones.

**Formato**: `[ID] [P?] [Historia] Descripción con ruta`. `[P]` = se puede hacer en paralelo (archivos distintos, sin dependencia pendiente). Todas las rutas son relativas a la raíz del repositorio; el código está en `laravel_app/`.

**Regla de oro**: solo presentación (FR-016). No tocar rutas, permisos, controladores de negocio, modelos ni migraciones. Nunca ejecutar `migrate:fresh` sobre la base local.

## Fase 1: Preparación (línea base y dependencias)

- [X] T001 Medir la línea base antes de cambiar nada y anotarla en `specs/004-interfaz-estilo-adminlte/quickstart.md` (sección "Línea base"): tamaño de `laravel_app/public/build` y tiempos de respuesta de `/dashboard`, `/personas` y `/celulas` con la base temporal sembrada (SC-006)
- [X] T002 Instalar y retirar dependencias en `laravel_app/package.json`: añadir `admin-lte@^4.10`, `bootstrap@^5.3.8`, `overlayscrollbars@^2`, `select2-bootstrap-5-theme@^1.3`; retirar `admin-lte@3`, `bootstrap@4` y `@ttskch/select2-bootstrap4-theme`, `tailwindcss` y `@tailwindcss/vite`; ejecutar `npm install` y confirmar que `package-lock.json` queda consistente
- [X] T003 Quitar Tailwind de `laravel_app/vite.config.js` (importación y `tailwindcss()` de `plugins`) y de `laravel_app/resources/css/app.css` (`@import 'tailwindcss'`, `@source`, `@theme`)
- [X] T004 Reescribir las importaciones de `laravel_app/resources/css/app.css`: Font Awesome, CSS de AdminLTE 4 (`admin-lte/dist/css/adminlte.min.css`), OverlayScrollbars, Select2 con `select2-bootstrap-5-theme`, Flatpickr, SweetAlert2, `./forms.css` y `./marca.css`
- [X] T005 Reescribir `laravel_app/resources/js/app.js`: importar `bootstrap` (bundle con Popper), `overlayscrollbars`, el JS de AdminLTE 4, jQuery y Select2 (solo para `forms/selectores.js`, global `window.$`), `./forms/index.js` y `./layout/menu-recordado.js`
- [X] T006 [P] Configurar el paginador de Bootstrap 5 en `laravel_app/app/Providers/AppServiceProvider.php` (`Paginator::useBootstrapFive()`)
- [X] T007 [P] Definir el nombre del sistema: valor por defecto "CRM Torreón Fuerte" para `APP_NAME` en `laravel_app/.env.example` y en `laravel_app/config/app.php`

---

## Fase 2: Fundamentos (bloquea todas las historias)

**⚠️ CRÍTICO**: ninguna historia puede empezar hasta terminar esta fase.

- [X] T008 Crear `laravel_app/config/menu.php` con las secciones y opciones de `data-model.md`: Inicio (Dashboard); Congregación (Personas, Líderes, Sin líder asignado, Células, Redes); Actividades (Eventos, Encuentros, Ministerios); Administración (Sedes, Usuarios). Cada opción con `etiqueta`, `icono`, `ruta`, `activa_en` y `permiso` (las mismas habilidades de `Gate` que usa hoy `layouts/admin.blade.php`: `gestionarCatalogos`, `verUsuarios`, etc.)
- [X] T009 [P] Crear `laravel_app/app/Support/Menu.php`: devuelve el menú filtrado por permisos del usuario (omite secciones sin opciones visibles), marca la opción activa con `request()->routeIs(...)` y construye las migas por defecto (Inicio › Sección › Opción › título), con el último tramo sin enlace
- [X] T010 [P] Crear `laravel_app/app/Support/Estados.php` con el mapa de `data-model.md` (`success`, `info`, `warning`, `secondary`, `primary`) y un valor por defecto `secondary` con el texto del estado para claves desconocidas
- [X] T011 [P] Crear `laravel_app/resources/css/marca.css` moviendo del layout la marca de agua de la sede (abajo, sin inclinación) y añadiendo recorte con elipsis del nombre de sede en barra y menú (caso límite de la especificación)
- [X] T012 Reescribir `laravel_app/resources/views/layouts/admin.blade.php` con la estructura de AdminLTE 4 de `contracts/marco-de-pagina.md`: `app-wrapper`, `app-header`, `app-sidebar`, `app-main`, `app-content-header`, `app-content`, `app-footer`; clases de cuerpo `layout-fixed sidebar-expand-lg bg-body-tertiary`; botón `data-lte-toggle="sidebar"`; secciones `title`, `page-title`, `breadcrumbs`, `page-actions`, `content`; conservar el contenedor `[data-avisos]` y los mensajes de éxito/error de 003, el selector de sede activa, el aviso «Todas las sedes: solo consulta» y la marca de agua
- [X] T013 [P] Crear los componentes `laravel_app/resources/views/components/ui/tarjeta.blade.php`, `estado.blade.php`, `vacio.blade.php` y `acciones-fila.blade.php` según `contracts/componentes-ui.md` (propiedades, esquema de botones y `x-form.confirm` para eliminar)
- [X] T014 Escribir el script de conversión Bootstrap 4 → 5 en la carpeta temporal de la sesión (no se versiona) con las equivalencias de `research.md` D8 (`badge-*`→`text-bg-*`, `custom-control`/`custom-switch`→`form-check`/`form-switch`, `custom-select`→`form-select`, `font-weight-*`→`fw-*`, `ml-/mr-/pl-/pr-`→`ms-/me-/ps-/pe-`, `text-right/left`→`text-end/start`, `float-right`→`float-end`, `sr-only`→`visually-hidden`, `form-inline`/`form-row`, `data-widget`, `close`→`btn-close`); ejecutarlo sobre `laravel_app/resources/views/` y `laravel_app/resources/css/forms.css` y revisar el diff archivo por archivo
- [X] T015 [P] Ajustar `laravel_app/resources/css/forms.css` a variables de Bootstrap 5 (`--bs-primary`, `--bs-border-color`, `--bs-danger`), eliminando colores fijos como `#007bff`, y adaptar `laravel_app/resources/views/components/form/*.blade.php` (`switch`, `select`, `multiselect`, `_etiqueta`, `actions`, `layout`) a las clases de Bootstrap 5 sin cambiar propiedades ni atributos `data-*`
- [X] T016 [P] Ajustar SweetAlert2 en `laravel_app/resources/js/forms/` (módulo de diálogos) para usar botones `btn btn-primary` / `btn btn-outline-secondary` de Bootstrap 5 y comprobar que `laravel_app/resources/js/forms/*.test.js` sigue en verde con `npm test`
- [X] T017 [P] Prueba de guardia `laravel_app/tests/Feature/Interfaz/ClasesBootstrapTest.php`: recorre `resources/views` y `resources/css` y falla si encuentra clases exclusivas de Bootstrap 4 (lista de T014) o `data-toggle`/`data-dismiss`
- [X] T018 Ejecutar `npm run build` y `php artisan test`; corregir cualquier regresión de 003 (PHPUnit 218 y Vitest 45 deben seguir pasando) antes de continuar

**Punto de control**: dependencias nuevas, marco base, componentes y vistas convertidas a Bootstrap 5 funcionando.

---

## Fase 3: Historia de usuario 1 - Marco común reconocible (Prioridad: P1) 🎯 MVP

**Objetivo**: todas las pantallas autenticadas con barra superior, menú agrupado, migas de pan y pie (FR-001–FR-005, FR-013, FR-019).

**Prueba independiente**: abrir una pantalla de cada módulo y comprobar marco, migas, menú por rol, datos de sesión y título.

### Pruebas de la historia 1

- [X] T019 [P] [US1] Prueba `laravel_app/tests/Feature/Interfaz/MarcoDePaginaTest.php`: para admin, pastor y líder, cada ruta GET autenticada responde 200 y contiene `app-header`, `app-sidebar`, migas de pan, el título `{pantalla} · {sistema}` y (si hay sede) el nombre de sede; las secciones del menú sin opciones permitidas no aparecen
- [X] T020 [P] [US1] Prueba `laravel_app/tests/Feature/Interfaz/MenuTest.php` (unitaria de `App\Support\Menu`): opción activa por patrón de ruta, filtrado por permisos por rol, migas por defecto y sustitución con `@section('breadcrumbs')`
- [X] T021 [P] [US1] Prueba Vitest `laravel_app/resources/js/layout/menu-recordado.test.js`: guarda y restaura "plegado/abierto", no falla si `localStorage` está bloqueado y no recuerda en móvil

### Implementación de la historia 1

- [X] T022 [US1] Conectar `laravel_app/resources/views/layouts/admin.blade.php` con `App\Support\Menu` (composición de vista en `laravel_app/app/Providers/AppServiceProvider.php`): dibujar secciones con encabezado, opción activa resaltada, pie del menú con nombre y rol, y migas en el encabezado de página
- [X] T023 [US1] Dibujar en la barra superior de `laravel_app/resources/views/layouts/admin.blade.php` el menú de usuario (nombre, rol, sede, «Cerrar sesión») y mostrar «Todas las sedes» cuando el administrador no tiene sede (FR-004, caso límite)
- [X] T024 [P] [US1] Crear `laravel_app/resources/js/layout/menu-recordado.js`: recordar el plegado en `localStorage` con `try/catch`, abierto por defecto en ≥ 992 px y cerrado en móvil (FR-019), y añadir en el layout el script en línea mínimo que aplica la clase antes del primer pintado (D7)
- [X] T025 [US1] Añadir `@section('title')` y `@section('page-title')` a toda vista que aún no los defina y `@section('page-actions')` con el botón "Nuevo" donde exista la acción de crear, en las carpetas `laravel_app/resources/views/{personas,lideres,celulas,eventos,encuentros,ministerios,redes,users,sedes,sin-lider,traslados}/`
- [X] T026 [US1] Ejecutar `php artisan test --filter=Interfaz` y `npm test`; corregir hasta verde

**Punto de control**: el marco funciona y se puede demostrar (MVP).

---

## Fase 4: Historia de usuario 2 - Listados y fichas uniformes (Prioridad: P1)

**Objetivo**: los 10 listados y las 7 fichas con la misma disposición, insignias y botones (FR-006–FR-009, SC-005).

**Prueba independiente**: comparar listado y ficha de tres módulos y confirmar disposición, colores y botones iguales.

### Pruebas de la historia 2

- [X] T027 [P] [US2] Prueba `laravel_app/tests/Feature/Interfaz/EstadosTest.php`: cada estado definido en `App\Support\Estados` produce la clase de color esperada y un estado desconocido usa `secondary`; ningún listado ni ficha usa `badge` con colores sueltos fuera de `x-ui.estado` (búsqueda en vistas)
- [X] T028 [P] [US2] Prueba `laravel_app/tests/Feature/Interfaz/ListadosTest.php`: cada listado renderiza tarjeta de filtros, tarjeta de tabla con contador, `table-responsive` y paginación de Bootstrap 5; con filtro sin resultados muestra `x-ui.vacio` con mensaje y, si el usuario puede, el botón de crear

### Implementación de la historia 2

- [X] T029 [P] [US2] Migrar `laravel_app/resources/views/personas/index.blade.php` y `laravel_app/resources/views/personas/show.blade.php` a `x-ui.tarjeta`, `x-ui.estado`, `x-ui.vacio` y `x-ui.acciones-fila`
- [X] T030 [P] [US2] Migrar `laravel_app/resources/views/lideres/index.blade.php`, `laravel_app/resources/views/lideres/show.blade.php` y `laravel_app/resources/views/sin-lider/index.blade.php` a los mismos componentes
- [X] T031 [P] [US2] Migrar `laravel_app/resources/views/celulas/index.blade.php` y `laravel_app/resources/views/celulas/show.blade.php` a los mismos componentes
- [X] T032 [P] [US2] Migrar `laravel_app/resources/views/eventos/index.blade.php` y `laravel_app/resources/views/eventos/show.blade.php` a los mismos componentes
- [X] T033 [P] [US2] Migrar `laravel_app/resources/views/encuentros/index.blade.php` y `laravel_app/resources/views/encuentros/show.blade.php` a los mismos componentes
- [X] T034 [P] [US2] Migrar `laravel_app/resources/views/ministerios/index.blade.php` y `laravel_app/resources/views/ministerios/show.blade.php` a los mismos componentes
- [X] T035 [P] [US2] Migrar `laravel_app/resources/views/redes/index.blade.php`, `laravel_app/resources/views/users/index.blade.php` y `laravel_app/resources/views/sedes/index.blade.php` a los mismos componentes
- [X] T036 [P] [US2] Migrar `laravel_app/resources/views/sedes/show.blade.php`, `laravel_app/resources/views/partials/persona-tarjeta.blade.php` y `laravel_app/resources/views/partials/acciones-contacto.blade.php` al esquema de tarjetas y botones de `contracts/componentes-ui.md`
- [X] T037 [US2] Unificar el esquema de botones (principal `btn-primary`, secundaria `btn-outline-secondary`, peligrosa `btn-outline-danger`) y los iconos fijos por acción en todas las vistas de `laravel_app/resources/views/` (búsqueda de `btn-` sin respaldo en el esquema) y en `laravel_app/resources/views/components/form/actions.blade.php`
- [X] T038 [US2] Ejecutar `php artisan test` completo; corregir hasta verde

**Punto de control**: listados y fichas uniformes; las historias 1 y 2 funcionan juntas.

---

## Fase 5: Historia de usuario 3 - Tablero con cajas y bloques (Prioridad: P2)

**Objetivo**: tablero con cajas clicables, accesos rápidos y tres bloques con datos existentes (FR-010).

**Prueba independiente**: iniciar sesión con cada rol y verificar bloques permitidos y cifras coherentes.

### Pruebas de la historia 3

- [X] T039 [P] [US3] Prueba `laravel_app/tests/Feature/Interfaz/TableroTest.php`: el administrador y el pastor ven los tres bloques (próximos eventos, personas sin líder, personas nuevas recientes) con máximo 5 filas; el líder no ve «sin líder» y solo ve su ámbito; las cajas enlazan al listado y sus cifras coinciden con los listados; el tablero no hace más de 3 consultas adicionales (contador de consultas)

### Implementación de la historia 3

- [X] T040 [US3] Calcular los tres bloques en `laravel_app/app/Http/Controllers/DashboardController.php` (o el controlador del tablero) usando `AlcanceService` y límite de 5 filas, sin consultas nuevas pesadas ni cambios de datos
- [X] T041 [US3] Rediseñar `laravel_app/resources/views/dashboard.blade.php` con las cajas `small-box` clicables, accesos rápidos a tareas frecuentes (según permisos) y los tres bloques con `x-ui.tarjeta` y `x-ui.vacio`; conservar el resumen por sede del administrador
- [X] T042 [US3] Comprobar que la marca de agua y el nombre de sede se conservan sin tapar el contenido en el tablero (escenario 3 de la historia 3) y ejecutar `php artisan test --filter=Tablero`

---

## Fase 6: Historia de usuario 4 - Pantallas pequeñas y accesibilidad (Prioridad: P2)

**Objetivo**: uso cómodo desde 360 px y foco visible (FR-014, FR-018, SC-004).

**Prueba independiente**: abrir las pantallas principales a 360, 768 y 1280 px.

- [X] T043 [P] [US4] Prueba `laravel_app/tests/Feature/Interfaz/AccesibilidadInterfazTest.php`: botones de solo icono con `aria-label` o `title`, tablas dentro de `table-responsive`, enlaces del menú con texto, `lang="es"` y `meta viewport` en ambos layouts
- [X] T044 [US4] Revisar `laravel_app/resources/css/forms.css` y `laravel_app/resources/css/marca.css` para que el foco de teclado sea visible (`:focus-visible`) y no se anule el contorno de la plantilla; ajustar área de pulsación mínima de botones de acción en listados
- [X] T045 [US4] Recorrido en navegador a 360, 768 y 1280 px de las pantallas principales (escenarios 7, 8 y 9 de `quickstart.md`) con una base temporal (`.env.pruebaforms` nueva con `APP_KEY`, borrada al terminar); corregir cualquier desbordamiento horizontal o control cortado

---

## Fase 7: Historia de usuario 5 - Acceso y páginas de error (Prioridad: P3)

**Objetivo**: inicio de sesión y errores con el estilo del panel (FR-012).

**Prueba independiente**: cerrar sesión, abrir el inicio de sesión y provocar 403 y 404.

- [X] T046 [P] [US5] Prueba `laravel_app/tests/Feature/Interfaz/AccesoYErroresTest.php`: el inicio de sesión muestra la tarjeta de acceso con el nombre del sistema; `/ruta-inexistente` devuelve 404, una ruta sin permiso 403 y una página caducada 419 con mensaje claro y botón «Volver al inicio»; con sesión conservan el marco
- [X] T047 [P] [US5] Reescribir `laravel_app/resources/views/layouts/auth.blade.php` y `laravel_app/resources/views/auth/login.blade.php` con la tarjeta de `login-page` de AdminLTE 4 (nombre del sistema, campos con `x-form.*` en modo `login`, botón principal)
- [X] T048 [P] [US5] Crear el parcial `laravel_app/resources/views/errors/_pagina.blade.php` y las vistas `laravel_app/resources/views/errors/403.blade.php`, `404.blade.php`, `419.blade.php`, `422.blade.php` y `500.blade.php` con icono, mensaje y botón «Volver al inicio»; usar el marco autenticado cuando hay sesión y el layout de acceso cuando no
- [X] T049 [US5] Ejecutar `php artisan test --filter=AccesoYErrores`

---

## Fase 8: Pulido y verificación final

- [X] T050 [P] Retirar `laravel_app/resources/views/welcome.blade.php` si no tiene ruta ni referencias (verificar con búsqueda) o dejarlo anotado como no usado
- [X] T051 [P] Actualizar `README.md` y `laravel_app/DEPLOY_CPanel.md` (si existe): stack de interfaz (AdminLTE 4 + Bootstrap 5), `npm install && npm run build`, subir `public/build`, cómo añadir una opción al menú en `config/menu.php` y cómo usar `x-ui.*` y `x-ui.estado`
- [X] T052 [P] Documentar en `specs/004-interfaz-estilo-adminlte/contracts/componentes-ui.md` cualquier ajuste surgido en la implementación (propiedades añadidas) para que contrato y código coincidan
- [X] T053 Medir tamaño de `laravel_app/public/build` y tiempos de respuesta de `/dashboard`, `/personas` y `/celulas`, comparar con la línea base de T001 y anotar el resultado en `quickstart.md` (SC-006: aumento ≤ 10 %)
- [X] T054 Recorrer los 14 escenarios de `specs/004-interfaz-estilo-adminlte/quickstart.md` con base temporal y herramientas de Chrome, incluyendo los formularios de 003 (escenario 12); luego apagar el servidor temporal y borrar `.env.pruebaforms` y la base temporal
- [X] T055 Ejecutar `php artisan test`, `npm test` y `npm run build` completos y marcar las tareas de este archivo como [X]; dejar anotadas en el informe final las verificaciones que requieren personas (SC-002 y SC-003)

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias; T002 antes de T003–T005.
- **Fundamentos (Fase 2)**: depende de la Fase 1 y **bloquea** las historias. Orden: T008 → T009; T012 depende de T008–T011; T013 antes de las migraciones de vistas; T014 antes de T015 y T017; T018 cierra la fase.
- **Historia 1 (P1)**: tras Fundamentos; es el MVP y la base visual de las demás.
- **Historia 2 (P1)**: tras Fundamentos; usa componentes de T013 y conviene hacerla después de la historia 1 para ver migas y menú reales.
- **Historia 3 (P2)**: tras Fundamentos; independiente de la historia 2 salvo por los componentes.
- **Historia 4 (P2)**: tras las historias 1 y 2 (necesita pantallas reales para verificar).
- **Historia 5 (P3)**: tras Fundamentos; independiente del resto.
- **Pulido (Fase 8)**: al final.

### Ejemplos de paralelismo

- Fase 2: T009, T010, T011, T013, T015, T016 y T017 en paralelo tras T008 y T014 donde corresponda.
- Historia 1: T019, T020, T021 y T024 en paralelo.
- Historia 2: T029–T036 en paralelo (cada una toca vistas distintas).
- Historia 5: T046, T047 y T048 en paralelo.

## Estrategia de implementación

1. **MVP**: Fases 1 y 2 y la Historia 1 → pantalla con el marco de AdminLTE 4 funcionando en todos los módulos; validar y demostrar.
2. **Incremental**: añadir Historia 2 (uniformidad) → Historia 3 (tablero) → Historia 4 (móvil y accesibilidad) → Historia 5 (acceso y errores), verificando `php artisan test` y `npm test` al final de cada fase.
3. **Control de riesgo**: la Fase 2 cierra con T018; si 003 se rompe, se corrige ahí y no se avanza. Hacer commit al final de cada fase para poder volver atrás.

## Notas

- Las tareas no versionan el script de conversión (T014); su resultado se revisa con `git diff`.
- Ninguna tarea modifica `database/`, `routes/` ni políticas; si aparece una necesidad funcional se anota para una funcionalidad posterior.
- El análisis `/speckit-analyze` puede ejecutarse antes de `/speckit-implement` para comprobar la coherencia entre spec, plan y tareas.

## Notas de implementación (desvíos respecto al plan)

- **OverlayScrollbars no se usó**: la compilación de AdminLTE 4.10 no lo requiere; se retiró la dependencia.
- **Fichas y listados** conservan las clases de tarjeta de AdminLTE (`card`, `card-outline`) en lugar de `<x-ui.tarjeta>`; el aspecto es el mismo. El componente queda para bloques nuevos. Las insignias, botones de fila, estados vacíos y cajas de resumen sí usan los componentes `x-ui.*`.
- **Nombre del sistema**: se añadió `config('app.sistema')` (`APP_SISTEMA`) en lugar de usar `APP_NAME`, porque el `.env` existente tiene «Laravel».
- **Marca en el menú**: se mantiene «CRM Admin - {sede}», como pidió el usuario antes.
- **Recorrido T045/T054**: se verificó en navegador (tablero, listados, fichas, formulario de persona con calendario, diálogo de confirmación, selector con búsqueda, menú recordado, 404) y con iframes de 360/768/1280 px sin desbordes en 15 pantallas. SC-002 y SC-003 (pruebas con personas) quedan pendientes fuera de la sesión.
- **Rendimiento (SC-006)**: ver `quickstart.md`; con vistas cacheadas el aumento es de 5–7 % en los listados y el tablero mejora.
