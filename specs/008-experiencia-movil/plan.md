# Plan de Implementación: Aplicación funcional y 100 % adaptada a celular

**Rama**: `008-experiencia-movil` (la rama git de trabajo sigue siendo `modulos`) | **Fecha**: 2026-10-19 | **Especificación**: [spec.md](spec.md) | **Análisis**: [analisis.md](analisis.md)

**Entrada**: Especificación en `/specs/008-experiencia-movil/spec.md`

## Resumen

Cerrar los 13 hallazgos del análisis (H1 a H13) con cambios de **presentación**, sin tocar datos ni permisos:

1. **Reglas táctiles globales** (44 px de control, 16 px de letra en campos) aplicadas por CSS en teléfono, y una barra superior rehecha para pulgar (H1, H2, H3, H11).
2. **Grilla estándar en modo «tarjeta»** en teléfono (nombre, estado y dato clave visibles, el resto bajo «Ver más», filtros en un panel «Filtros») y búsqueda compacta en una franja (H7, H8).
3. **Tablas apilables**: toda tabla de tablero y fichas se convierte en lista de tarjetas por debajo de 768 px, con una prueba que impide tablas sin adaptar (H4, H5, H6).
4. **Formularios con el teclado correcto** y datos conservados ante fallos de envío (H12, RF-008, RF-009).
5. **Ligereza y aplicación instalable**: carga diferida de DataTables, selectores, calendario y diálogos; iconos recortados; compresión y caché; manifiesto, iconos, color de marca y un *service worker* mínimo que solo guarda archivos de la interfaz y una pantalla de «sin conexión», **nunca datos** (H9, H10, RF-013b).
6. **Prueba permanente en navegador real** que recorre todas las pantallas a 320, 360, 390 y 430 px, más una guarda estática en PHPUnit, y la regla «diseño primero para celular» en la constitución (H13).

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13 (Blade); JavaScript ES modules (Vite 7); CSS con variables de Bootstrap 5 / AdminLTE 4

**Dependencias Principales**: ya instaladas (Bootstrap 5, AdminLTE 4, DataTables 2 + Responsive, Select2, Flatpickr, SweetAlert2, Font Awesome). Nuevas **solo de desarrollo**: `@playwright/test` (prueba en navegador real) y `sharp` (generar iconos desde un SVG)

**Almacenamiento**: sin cambios de esquema; el teléfono solo guarda archivos de la interfaz (caché del *service worker*), nunca datos

**Pruebas**: PHPUnit 12 (guardas estáticas y cabeceras), Vitest 5 (lógica JS), Playwright (recorrido a 320/360/390/430 px, controles táctiles, desbordes, carga)

**Plataforma de Destino**: Android (Chrome) e iPhone (Safari) de los últimos 4 años; tabletas y escritorio sin regresiones; hosting compartido con cPanel (HTTPS necesario para instalar la aplicación)

**Tipo de Proyecto**: aplicación web monolítica Laravel con Blade

**Metas de Rendimiento**: contenido útil en menos de 4 s (4G lenta) y 2 s en visitas siguientes; JS inicial por debajo de 200 kB sin comprimir (hoy 547 kB) y CSS por debajo de 300 kB (hoy 497 kB)

**Restricciones**: sin rediseño de marca; sin cambios de datos, rutas ni permisos; ninguna caché de HTML, JSON ni CSV; escritorio conserva su presentación ancha; todo sigue funcionando sin JavaScript (CSS primero)

**Escala/Alcance**: 38 pantallas navegables; 10 grillas; 11 tablas en fichas y tablero

## Verificación de la Constitución

Constitución vigente (v1.0.0, `.specify/memory/constitution.md`):

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple (todo el diseño está en español). |
| II. Aislamiento por sede y rol | Cumple: no se tocan consultas; el *service worker* no guarda respuestas con datos (RF-013b) y se prueba. |
| III. Grillas estándar | Cumple y se refuerza: el modo tarjeta es una variante del componente `<x-ui.grilla>`, no una tabla nueva. |
| IV. Pruebas primero | Cumple: la prueba en navegador y las guardas se escriben antes de los cambios que verifican. |
| V. Mejora progresiva | Cumple: la adaptación es CSS; el panel «Filtros» y «Ver más» degradan a mostrar todo sin JavaScript. |
| VI. Simplicidad | Cumple: CSS global con variables en vez de componentes nuevos; sin framework nuevo; dependencias nuevas solo de desarrollo. |

Resultado: **APROBADO**. Por RF-019 se **enmienda la constitución a v1.1.0** agregando el principio «VII. Diseño primero para celular». Reevaluación tras el diseño: aprobada, sin violaciones.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/008-experiencia-movil/
├── plan.md
├── analisis.md          # hallazgos H1 a H13 (entrada)
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── css-y-componentes-moviles.md
│   ├── aplicacion-instalable.md
│   └── prueba-movil.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio `laravel_app/`)

```text
resources/
├── css/
│   ├── movil.css                    # NUEVO: tokens táctiles, barra superior, grilla en tarjetas, tablas apilables
│   ├── grilla.css                   # ajustes del modo tarjeta y panel de filtros
│   ├── forms.css                    # ajustes de formularios (safe-area, teclado)
│   └── app.css                      # importa movil.css
├── js/
│   ├── app.js                       # carga diferida de plugins pesados
│   ├── grilla/grilla.js             # modo tarjeta, botón «Filtros», «Ver más», etiquetas de celda
│   ├── pwa/registrar.js             # NUEVO: registra el service worker
│   └── forms/index.js               # carga perezosa de selectores, fechas y diálogos
├── views/
│   ├── layouts/admin.blade.php      # barra superior para pulgar, meta de aplicación, manifiesto
│   ├── layouts/auth.blade.php       # meta de aplicación
│   ├── dashboard.blade.php          # accesos rápidos primero, resumen por sede apilable
│   ├── {personas,lideres,celulas,eventos,sedes}/show.blade.php  # tablas apilables, contacto arriba
│   ├── components/ui/tabla-apilable.blade.php   # NUEVO (opcional): envoltura con etiquetas
│   ├── components/form/input.blade.php          # tipo de teclado (inputmode/autocomplete)
│   └── errors/sin-conexion.blade.php            # NUEVO: base de offline.html
public/
├── manifest.webmanifest             # NUEVO
├── sw.js                            # NUEVO: caché solo de interfaz + pantalla sin conexión
├── offline.html                     # NUEVO (generado desde la vista)
└── iconos/                          # NUEVO: 192, 512, maskable, apple-touch 180, favicon
scripts/generar-iconos.mjs           # NUEVO: genera los PNG desde un SVG
tests/
├── Feature/Movil/                   # guardas estáticas, cabeceras, manifiesto, ausencia de datos en caché
└── movil/                           # NUEVO: Playwright (auditoria.spec.js, playwright.config.js)
.htaccess / public/.htaccess         # compresión y caché larga de /build
```

**Decisión de estructura**: se mantiene el monolito; los cambios son principalmente de capa de presentación. El comportamiento «tarjeta» de las grillas se resuelve en CSS con unos pocos ganchos de JavaScript; las tablas de fichas usan una clase CSS común (`tabla-apilable`) en lugar de un componente por tabla.

## Seguimiento de Complejidad

Sin violaciones. Se acepta una complejidad puntual y justificada: un *service worker* mínimo (necesario para que el navegador ofrezca instalar y para mostrar la pantalla de «sin conexión»); está acotado por contrato a no guardar datos.
