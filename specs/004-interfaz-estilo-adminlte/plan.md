# Plan de Implementación: Interfaz de toda la aplicación al estilo AdminLTE

**Rama**: `004-interfaz-estilo-adminlte` | **Fecha**: 2026-10-19 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de la funcionalidad en `specs/004-interfaz-estilo-adminlte/spec.md`

## Resumen

Migrar toda la interfaz de AdminLTE 3 (Bootstrap 4) a **AdminLTE 4 (Bootstrap 5)** y completar sus convenciones en todas las pantallas: marco común (barra superior, menú lateral agrupado, encabezado con migas de pan, pie), listados y fichas con la misma disposición, insignias de estado con un único esquema de colores, tablero con tres bloques informativos, inicio de sesión y páginas de error con el estilo del panel, y menú que recuerda su estado. La marca de agua y el nombre de la sede se conservan; los colores son los estándar de la plantilla.

Enfoque técnico (detalle en [research.md](research.md)):

- Sustituir `admin-lte@3`, `bootstrap@4` y el tema de Select2 para Bootstrap 4 por `admin-lte@4`, `bootstrap@5` y `select2-bootstrap-5-theme`; conservar Font Awesome, jQuery (solo para Select2), Flatpickr y SweetAlert2.
- Eliminar Tailwind del empaquetado (compite con los estilos de la plantilla) y paginar con la vista de Bootstrap 5.
- Definir **el menú en un único archivo de configuración** (`config/menu.php`) que alimenta el menú lateral, la opción activa y las migas de pan por defecto.
- Añadir componentes Blade de presentación reutilizables (`x-ui.*`) para tarjeta, insignia de estado, estado vacío y acciones por fila, y migrar las vistas a ellos.
- Convertir las clases de Bootstrap 4 a Bootstrap 5 con un script revisado y una prueba que impida volver a introducirlas.
- No se modifican rutas, permisos, controladores de negocio ni datos.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; JavaScript (módulos ES) con Vite 8

**Dependencias principales**: AdminLTE 4.10, Bootstrap 5.3.8 (con Popper), OverlayScrollbars 2.x (requerido por AdminLTE 4), Font Awesome 6 (se conserva), jQuery 3.7 + Select2 4.0 + `select2-bootstrap-5-theme` 1.3, Flatpickr, SweetAlert2

**Almacenamiento**: N/A (sin cambios de datos; la preferencia de menú se guarda en el navegador del usuario)

**Pruebas**: PHPUnit 12 (pruebas de funcionalidad sobre HTML renderizado), Vitest 5 + jsdom (módulos JS), recorrido manual en navegador a 360/768/1280 px

**Plataforma de destino**: aplicación web servida por Laravel (hosting compartido cPanel; `public/build` se genera con `npm run build` y se sube)

**Tipo de proyecto**: aplicación web monolítica (Laravel + Blade)

**Objetivos de rendimiento**: tiempo de carga de pantallas principales sin aumentar más de 10 % (SC-006); el tablero añade como máximo 3 consultas pequeñas (5 filas cada una)

**Restricciones**: solo presentación (FR-016); los componentes de formularios de 003 siguen funcionando (FR-017, SC-008); sin dependencias de servicios externos; tema claro; textos en español

**Escala/Alcance**: ~60 rutas GET/POST, ~45 vistas Blade, 15 componentes de formulario, 1 layout autenticado, 1 layout de acceso, 5 páginas de error

## Verificación de la Constitución

*PUERTA: debe pasar antes de la investigación de la Fase 0. Se revisa de nuevo tras el diseño de la Fase 1.*

`.specify/memory/constitution.md` es todavía la plantilla sin completar: no define principios ni puertas de calidad. Se aplican como criterios de trabajo los acuerdos ya vigentes del proyecto:

| Criterio | Estado |
|----------|--------|
| Artefactos de spec-kit en español | Cumple |
| Pruebas existentes (PHPUnit 218, Vitest 45) siguen pasando | Se verifica en cada fase |
| Sin cambios de reglas de negocio ni de aislamiento por sede | Cumple (solo vistas, estilos y configuración de menú) |
| No ejecutar `migrate:fresh` sobre la base local | Cumple (no hay migraciones) |

Reevaluación tras el diseño: sin violaciones. No se requiere la tabla de seguimiento de complejidad.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/004-interfaz-estilo-adminlte/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── marco-de-pagina.md
│   └── componentes-ui.md
├── checklists/requirements.md
└── tasks.md              # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio)

```text
laravel_app/
├── config/
│   └── menu.php                          # NUEVO: secciones y opciones del menú (ruta, icono, permiso, patrón activo)
├── app/
│   ├── Support/
│   │   ├── Menu.php                      # NUEVO: filtra el menú por permisos y calcula opción activa y migas
│   │   └── Estados.php                   # NUEVO: estado -> etiqueta y color (insignias)
│   └── Providers/AppServiceProvider.php  # paginador Bootstrap 5; composición del menú para el layout
├── resources/
│   ├── css/
│   │   ├── app.css                       # AdminLTE 4 + Select2 BS5 + Flatpickr + SweetAlert2 (sin Tailwind)
│   │   ├── forms.css                     # ajustado a variables de Bootstrap 5
│   │   └── marca.css                     # NUEVO: marca de agua y recortes de nombre de sede
│   ├── js/
│   │   ├── app.js                        # Bootstrap 5, AdminLTE 4, jQuery solo para Select2
│   │   └── layout/menu-recordado.js      # NUEVO: recuerda el plegado del menú (+ prueba)
│   └── views/
│       ├── layouts/{admin,auth}.blade.php  # reescritos con la estructura de AdminLTE 4
│       ├── components/ui/                # NUEVO: tarjeta, estado, vacio, acciones-fila
│       ├── components/form/              # ajustes de clases a Bootstrap 5
│       ├── errors/{403,404,419,422,500}.blade.php
│       ├── dashboard.blade.php           # cajas de resumen + 3 bloques
│       └── */                            # todas las vistas migradas a Bootstrap 5 y a x-ui.*
├── vite.config.js                        # sin plugin de Tailwind
├── package.json                          # dependencias nuevas/retiradas
└── tests/
    ├── Feature/Interfaz/                 # NUEVO: marco, menú por rol, migas, título, insignias, errores, guardia BS4
    └── Feature/Formularios/              # existentes, deben seguir en verde
```

**Decisión de estructura**: aplicación Laravel monolítica existente; no se crean proyectos nuevos. Los elementos nuevos se limitan a configuración de menú, dos clases de apoyo de presentación, componentes Blade `x-ui.*` y pruebas.

## Seguimiento de Complejidad

Sin violaciones que justificar.
