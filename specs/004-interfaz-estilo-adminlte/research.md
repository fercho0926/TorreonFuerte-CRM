# Investigación (Fase 0): Interfaz estilo AdminLTE

Todas las incógnitas del contexto técnico quedan resueltas; no hay marcadores `NEEDS CLARIFICATION`.

## D1. AdminLTE 4.10 con Bootstrap 5.3

- **Decisión**: instalar `admin-lte@^4.10`, `bootstrap@^5.3.8` (su dependencia par) y `overlayscrollbars@^2` (que AdminLTE 4 usa para el desplazamiento del menú); retirar `admin-lte@3`, `bootstrap@4` y `@ttskch/select2-bootstrap4-theme`. Se importa el CSS compilado y el JS de AdminLTE desde Vite.
- **Justificación**: es la decisión de la clarificación (versión 4). La versión 4 ya es estable (última 4.10) y su única dependencia par es Bootstrap 5.3.
- **Alternativas**: mantener AdminLTE 3 (descartada por el usuario); usar el origen SCSS de AdminLTE para personalizar (descartada: no se personalizan colores, solo se necesita el CSS compilado).

## D2. Iconos

- **Decisión**: conservar Font Awesome (ya usado en todas las vistas).
- **Justificación**: AdminLTE 4 no obliga a un set de iconos (su demo usa Bootstrap Icons); cambiar iconos obligaría a reescribir cada vista sin ganancia visible.
- **Alternativas**: pasar a Bootstrap Icons (descartada por costo/riesgo).

## D3. Tailwind en el empaquetado

- **Decisión**: quitar el plugin de Tailwind, su importación y las rutas `@source` de `resources/css/app.css` y `vite.config.js`; configurar el paginador con `Paginator::useBootstrapFive()`.
- **Justificación**: Tailwind incluye un reinicio global de estilos que compite con los de la plantilla (probable causa de que hoy no se vea como AdminLTE) y duplica utilidades de Bootstrap. Las únicas vistas que lo usan son la bienvenida de Laravel (sin ruta) y el paginador por defecto, que pasa a Bootstrap.
- **Alternativas**: conservar Tailwind sin reinicio (descartada: sigue duplicando utilidades y aumenta el tamaño del paquete).

## D4. Select2 con Bootstrap 5

- **Decisión**: mantener jQuery y Select2 (necesarios para los selectores de 003) con `select2-bootstrap-5-theme@^1.3`, y activarlos solo en `selectores.js`. AdminLTE 4 y Bootstrap 5 no necesitan jQuery.
- **Justificación**: reescribir los selectores sin Select2 queda fuera de alcance (FR-016) y exigiría rehacer pruebas de 003. El tema existe y es mantenido.
- **Alternativas**: sustituir Select2 por un selector nativo propio (descartada por alcance y riesgo).

## D5. Marco de página de AdminLTE 4

- **Decisión**: reescribir `layouts/admin.blade.php` con la estructura de la versión 4 (`app-wrapper`, `app-header`, `app-sidebar`, `app-main`, `app-content-header`, `app-content`, `app-footer`), clases de cuerpo `layout-fixed sidebar-expand-lg bg-body-tertiary` y el botón de menú con `data-lte-toggle="sidebar"`. Los mensajes de éxito/error y el contenedor `[data-avisos]` de 003 se mantienen en el área de contenido.
- **Justificación**: es el marco canónico de la plantilla; en pantallas pequeñas el menú se superpone al contenido y se cierra con el fondo, sin código propio.
- **Alternativas**: conservar la estructura de la versión 3 con las nuevas hojas de estilo (descartada: no funciona, los nombres de clases cambiaron).

## D6. Menú y migas de pan desde una sola configuración

- **Decisión**: `config/menu.php` define secciones (Inicio, Congregación, Actividades, Administración) y opciones (etiqueta, icono, ruta, patrón de ruta activo, permiso con las mismas habilidades de `Gate` que usa hoy el menú: `gestionarCatalogos`, `verUsuarios`, etc.). `App\Support\Menu` produce el menú ya filtrado por permisos, la opción activa y las migas por defecto (Inicio › sección › opción › título de la pantalla). Las vistas pueden sustituir el último tramo con `@section('breadcrumbs')`.
- **Justificación**: una sola fuente evita que el menú y las migas se desincronicen y permite probarlos sin navegador; las secciones sin opciones visibles se ocultan (caso límite de la especificación).
- **Alternativas**: dejar el menú escrito a mano en el layout (descartada: ya produce incoherencias); un paquete de migas (descartada: dependencia innecesaria).

## D7. Recordar el plegado del menú

- **Decisión**: módulo pequeño `layout/menu-recordado.js` que guarda "plegado/abierto" en `localStorage` (con `try/catch` por si está bloqueado) y aplica la clase antes del primer pintado mediante un script en línea mínimo para evitar parpadeo. Valor por defecto: abierto en ≥ 992 px y cerrado en móvil, como define la plantilla; en móvil no se recuerda (siempre cerrado).
- **Justificación**: cumple FR-019 sin servidor ni cambios de datos.
- **Alternativas**: guardarlo en base de datos por usuario (descartada: requeriría migración y rompería "solo presentación").

## D8. Convertir las clases de Bootstrap 4 a 5

- **Decisión**: script único y revisable (`scratchpad`, no se versiona) que reemplaza en vistas y componentes: `badge-*`→`text-bg-*`, `custom-control`/`custom-switch`→`form-check`/`form-switch`, `custom-select`→`form-select`, `font-weight-*`→`fw-*`, `ml-/mr-/pl-/pr-`→`ms-/me-/ps-/pe-`, `text-right/left`→`text-end/start`, `float-right`→`float-end`, `sr-only`→`visually-hidden`, `form-inline`/`form-row`→utilidades `d-flex`/`row`, `data-widget`→`data-lte-*`/`data-bs-*`, `close`→`btn-close`. Una prueba de PHPUnit recorre `resources/views` y falla si reaparece alguna clase de Bootstrap 4.
- **Justificación**: el inventario real es pequeño (≈ 20 archivos y pocos cientos de usos); automatizar y proteger con prueba evita regresiones.
- **Alternativas**: edición manual archivo por archivo (descartada por riesgo de omisiones).

## D9. Insignias y botones con un único esquema

- **Decisión**: `App\Support\Estados` mapea cada estado de la aplicación a (etiqueta, color de Bootstrap) y el componente `x-ui.estado` lo dibuja con `text-bg-*`. Mapa: activo/miembro activo/realizado → `success`; planificado/en curso → `info`; visitante/pendiente → `warning`; inactivo/cancelado → `secondary`; líder → `primary`. Botones: principal `btn-primary`, secundaria `btn-outline-secondary`, peligrosa `btn-outline-danger`, con iconos fijos por acción.
- **Justificación**: FR-008 y FR-009 piden un esquema único y documentado; centralizarlo permite probar el 100 % de los usos (SC-005).
- **Alternativas**: clases sueltas por vista (estado actual; origen de la incoherencia).

## D10. Ajuste de los formularios de 003

- **Decisión**: `forms.css` y los componentes `x-form.*` pasan a variables CSS y clases de Bootstrap 5 (`--bs-primary`, `--bs-border-color`, `form-select`, `form-check form-switch`, `visually-hidden`); se elimina el color primario fijo `#007bff`. SweetAlert2 usa botones de Bootstrap 5; Flatpickr no cambia. Las pruebas de 003 (PHPUnit y Vitest) son la red de seguridad.
- **Justificación**: FR-017 y SC-008.
- **Alternativas**: reescribir los componentes de formulario (descartada: solo hay que ajustar clases).

## D11. Tablero

- **Decisión**: se conservan las cajas de resumen (`small-box`, que existen en la versión 4), se añaden accesos rápidos y tres tarjetas con listas de 5 filas: próximos eventos, personas sin líder y personas nuevas recientes, calculadas con `AlcanceService` para respetar el ámbito del rol. El de "sin líder" solo se muestra a quien puede verlo (admin y pastor).
- **Justificación**: clarificación 3 (datos existentes, sin gráficos); sin cambios de datos.
- **Alternativas**: gráficos (descartada por el usuario).

## D12. Acceso, errores y título

- **Decisión**: `layouts/auth.blade.php` usa la tarjeta de `login-page` de la versión 4. Errores 403, 404, 419, 422 y 500 comparten un parcial con icono, mensaje y botón "Volver al inicio", y usan el marco autenticado cuando hay sesión. El título de pestaña es «Pantalla · Nombre del sistema · Sede»; el nombre sale de `config('app.name')` (con valor por defecto "CRM Torreón Fuerte" en `.env.example`).
- **Justificación**: FR-012 y FR-013.
- **Alternativas**: dejar el texto "AdminLTE Panel" (no cumple FR-013).

## D13. Estrategia de verificación

- **Decisión**: (1) pruebas de PHPUnit que renderizan cada ruta como cada rol y comprueban marco, migas, menú, título, insignias y ausencia de clases de Bootstrap 4; (2) Vitest para el módulo del menú recordado; (3) recorrido en navegador a 360/768/1280 px con la guía de `quickstart.md`; (4) medir tamaño del paquete y tiempo de respuesta contra la línea base antes del cambio.
- **Justificación**: SC-001, SC-004, SC-006 y SC-007 son comprobables automáticamente; SC-002 y SC-003 requieren personas (fuera de las pruebas automáticas).
- **Alternativas**: pruebas visuales automáticas con capturas (descartada por costo de mantenimiento).

## Riesgos

| Riesgo | Mitigación |
|--------|-----------|
| Select2 se ve distinto con el tema de Bootstrap 5 | Probar los selectores de 003 (búsqueda, múltiple, contador) en el recorrido; ajustar solo CSS |
| Algún componente de Bootstrap 4 no tiene equivalente directo | Inventario ya hecho: solo conmutadores, selectores y utilidades; todos tienen equivalente |
| Parpadeo del menú al cargar | Script en línea mínimo que aplica la clase antes de pintar |
| Regresión de 003 | Pruebas PHPUnit/Vitest existentes deben seguir en verde en cada fase |
