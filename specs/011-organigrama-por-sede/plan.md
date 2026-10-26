# Plan de Implementación: Organigrama interactivo por sede

**Rama**: `011-organigrama-por-sede` | **Fecha**: 2026-10-22 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de funcionalidad en `/specs/011-organigrama-por-sede/spec.md`

## Resumen

Una pantalla de solo lectura «Organigrama» (`/organigrama`) que dibuja la jerarquía de la sede activa: la cabeza con los pastores de la sede, debajo los líderes y bajo cada líder sus miembros activos (más un grupo «Sin líder asignado»). El servidor construye el árbol ya filtrado por el alcance del usuario y lo entrega como HTML anidado semántico (`<ul>` con `<details>/<summary>`), de modo que expandir y colapsar funcione incluso sin JavaScript. Una mejora progresiva pequeña en JavaScript agrega «Expandir todo / Colapsar todo». El CSS presenta el mismo marcado como árbol vertical con sangría en pantallas estrechas y como organigrama horizontal con conectores desde 992 px. No se agregan tablas ni migraciones: se reutilizan `sedes.pastor_persona_id` / `pastora_persona_id`, `personas.es_lider`, `personas.lider_id` y la tabla pivote `celula_persona`. Ver decisiones en [research.md](research.md).

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; JavaScript ES modules (Vite 8)

**Dependencias Principales**: Blade, AdminLTE 4 / Bootstrap 5, Font Awesome (ya instaladas); sin librerías nuevas

**Almacenamiento**: Base de datos existente; solo lectura, sin migraciones

**Pruebas**: PHPUnit (aislamiento, alcance por rol, estructura del árbol), Vitest (comportamiento de expandir/colapsar todo), Playwright `npm run test:movil` (320–1920 px, 44 px)

**Plataforma Objetivo**: Web responsive e instalable (PWA); prioridad celular

**Tipo de Proyecto**: Aplicación web monolítica Laravel (`laravel_app/`)

**Objetivos de Rendimiento**: Pantalla inicial < 3 s con sede de 500 miembros; expandir/colapsar < 0,3 s (CE-004)

**Restricciones**: Sin desplazamiento horizontal de la página de 320 a 1920 px; funciona sin JS; sin datos de personas en el service worker; alcance aplicado en la consulta

**Escala/Alcance**: Una pantalla, un controlador, un servicio, una vista parcial recursiva, ~1 archivo JS y CSS; sedes de hasta cientos de miembros

## Verificación de la Constitución

*PUERTA: debe pasar antes de la investigación de la Fase 0. Se revisa de nuevo tras el diseño de la Fase 1.*

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple: todos los documentos de esta funcionalidad están en español. |
| II. Aislamiento por sede y alcance por rol | Cumple: el servicio construye el árbol desde `AlcanceService` y el filtro global de sede (`SedeScope`); no hay `withoutGlobalScopes()`. Se agregan pruebas de aislamiento entre sedes y entre roles, y se registra la ruta en la matriz de rutas (`MatrizRutasTest`). |
| III. Grillas estándar | No aplica: es un diagrama, no un listado principal. La prueba que detecta tablas simples no se ve afectada porque no se usa `<table>`. |
| IV. Pruebas primero | Cumple: PHPUnit + Vitest + `test:movil` definidos en el plan de tareas antes del código. |
| V. Mejora progresiva | Cumple: árbol con `<details>` nativo y HTML renderizado en el servidor; el JS solo añade «Expandir/Colapsar todo». |
| VI. Simplicidad | Cumple: se reutilizan `AlcanceService`, `SedeContext`, menú de `config/menu.php`, selector de sede de la barra superior y políticas; no se crean tablas ni abstracciones nuevas. |
| VII. Diseño primero para celular | Cumple: diseño base vertical, controles ≥ 44 px, letra ≥ 16 px; la pantalla se agrega a `tests/movil/pantallas.mjs`; el service worker no guarda la ruta (no se cachean datos de personas). |

**Resultado de la puerta**: sin violaciones; no se requiere Seguimiento de Complejidad.

**Reevaluación tras el diseño (Fase 1)**: se mantiene sin violaciones. El diseño no introduce consultas fuera de `SedeScope` ni almacenamiento en el dispositivo.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/011-organigrama-por-sede/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── pantalla-organigrama.md
├── checklists/
│   └── requirements.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código Fuente (raíz del repositorio, `laravel_app/`)

```text
laravel_app/
├── app/
│   ├── Http/Controllers/OrganigramaController.php      # nuevo: index (solo lectura)
│   ├── Services/OrganigramaService.php                 # nuevo: arma el árbol con el alcance del usuario
│   └── Policies/PersonaPolicy.php                      # + habilidad verOrganigrama
├── config/menu.php                                     # + opción «Organigrama» en Congregación
├── routes/web.php                                      # + GET /organigrama (organigrama.index)
├── resources/
│   ├── views/organigrama/
│   │   ├── index.blade.php                             # página, barra de acciones, estado vacío
│   │   └── _nodo.blade.php                             # parcial recursivo de un nodo
│   ├── css/organigrama.css                             # vertical (base) y horizontal (≥ 992 px)
│   └── js/organigrama/
│       ├── organigrama.js                              # expandir/colapsar todo (mejora progresiva)
│       └── organigrama.test.js
└── tests/
    ├── Feature/Organigrama/
    │   ├── OrganigramaEstructuraTest.php
    │   ├── OrganigramaAlcanceTest.php                  # roles y aislamiento entre sedes
    │   └── OrganigramaPantallaTest.php                 # menú, vacío, sin JS (marcado)
    └── movil/pantallas.mjs                             # + 'organigrama'
```

**Decisión de Estructura**: monolito Laravel existente. Un servicio (`OrganigramaService`) concentra la lógica de armado y alcance para poder probarla sin pasar por la vista; el controlador solo autoriza y renderiza. El JavaScript se carga solo en esta pantalla (importación dinámica, igual que las grillas).

## Seguimiento de Complejidad

Sin violaciones de la constitución que justificar.
