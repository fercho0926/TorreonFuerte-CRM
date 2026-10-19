# Plan de implementación: Revisión y mejora de los módulos de Personas y Líderes

**Rama**: `001-revision-personas-lideres` | **Fecha**: 2026-10-19 | **Spec**: [spec.md](spec.md)

**Entrada**: Especificación de la funcionalidad en `/specs/001-revision-personas-lideres/spec.md`

## Resumen

Se corrige el modelo y la experiencia de Personas y Líderes de la aplicación Laravel existente (`laravel_app/`) sin reescribirla:

- **Un solo rol "Líder"** con **Red** y **Zona** (opcional) como catálogos administrables (en lugar de texto libre).
- **El líder directo es la fuente de verdad** del vínculo persona–líder; la célula es opcional y solo de ese líder.
- **Estado de la persona** (visitante, nuevo creyente, miembro activo, inactivo) con inactivación en lugar de borrado; documento opcional hasta ser miembro/líder.
- **Alcance por rol** centralizado en un único componente reutilizable (consultas filtradas en base de datos, no listas de IDs), con políticas para ocultar acciones no permitidas.
- **Usabilidad**: búsqueda, paginación, vista de tarjetas en móvil, acciones de llamar/WhatsApp, fechas legibles, sin IDs técnicos, formulario único de persona con sección de liderazgo.
- **Seguimiento**: vista "Sin líder asignado" y bitácora de cambios de persona/liderazgo.
- **Migración de datos** que conserva todo lo existente (normaliza redes; los usuarios con el rol retirado de líder de zona pasan a líder).

## Contexto técnico

**Lenguaje/Versión**: PHP ^8.3

**Dependencias principales**: Laravel ^13.8, plantilla AdminLTE (Bootstrap 4) con Blade, Font Awesome

**Almacenamiento**: SQLite por defecto en desarrollo (`DB_CONNECTION=sqlite`); producción en MySQL vía cPanel (según `DEPLOY_CPanel.md`). Las migraciones deben ser compatibles con ambos.

**Pruebas**: PHPUnit ^12.5 (`tests/Feature`, `tests/Unit`); hoy solo existen las pruebas de ejemplo.

**Plataforma objetivo**: Aplicación web server-rendered, usada en computador y, sobre todo, en teléfono; hospedaje compartido con cPanel.

**Tipo de proyecto**: Aplicación web monolítica (Laravel con vistas Blade, sesión propia en lugar de `Auth`).

**Objetivos de rendimiento**: búsqueda y listado con 1.000+ personas en menos de 1 s percibido (SC-002); paginación de 25 por página.

**Restricciones**: sin dependencias nuevas pesadas (hospedaje compartido); sin romper Células, Ministerios, Encuentros ni Eventos; migración sin pérdida de datos; interfaz en español.

**Escala/Alcance**: iglesia local (cientos a pocos miles de personas); ~6 pantallas modificadas, 2 nuevas (catálogos y "Sin líder asignado").

## Verificación contra la constitución

`.specify/memory/constitution.md` es aún la plantilla sin completar: **no hay principios que evaluar, así que no hay puertas que fallar**. Se aplican como principios de trabajo los criterios de la propia spec (privacidad por ámbito, sin pérdida de datos, simplicidad). Recomendación: ejecutar `/speckit-constitution` antes de la siguiente funcionalidad.

Re-evaluación tras el diseño: sin cambios; el diseño no introduce complejidad que requiera justificación.

## Estructura del proyecto

### Documentación (esta funcionalidad)

```text
specs/001-revision-personas-lideres/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas-ui.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio)

```text
laravel_app/
├── app/
│   ├── Models/
│   │   ├── Persona.php            # +estado, +zona/red del líder, soft deletes, scopes (buscar, visiblesPara)
│   │   ├── Red.php                # nuevo
│   │   ├── Zona.php               # nuevo
│   │   └── PersonaCambio.php      # nuevo (bitácora)
│   ├── Policies/
│   │   └── PersonaPolicy.php      # nuevo: ver / editar / designar líder / inactivar / eliminar
│   ├── Services/
│   │   ├── AlcanceService.php     # nuevo: ámbito por rol (consultas), reemplaza helpers del Controller base
│   │   └── LiderazgoService.php   # nuevo: designar/quitar líder, reasignar, ciclos, coherencia con células
│   └── Http/
│       ├── Controllers/
│       │   ├── PersonasController.php    # búsqueda, paginación, estado, inactivar
│       │   ├── LideresController.php     # designar existente, quitar con reasignación
│       │   ├── SinLiderController.php    # nuevo
│       │   ├── RedesController.php       # nuevo (catálogo)
│       │   └── ZonasController.php       # nuevo (catálogo)
│       └── Requests/
│           └── PersonaRequest.php        # reglas únicas compartidas por persona y líder
├── database/migrations/                   # nuevas migraciones (esquema + migración de datos)
├── resources/views/
│   ├── personas/ (index, _form, show, create, edit)   # formulario único con sección Liderazgo
│   ├── lideres/ (index, show, designar)
│   ├── sin-lider/index.blade.php
│   ├── redes/ y zonas/
│   └── partials/ (persona-tarjeta, acciones-contacto, ayuda-terminos)
├── routes/web.php
└── tests/
    ├── Feature/ (PersonasBusquedaTest, AlcancePorRolTest, LiderazgoTest, SinLiderTest, MigracionDatosTest)
    └── Unit/ (LiderazgoServiceTest)
```

**Decisión de estructura**: se mantiene la arquitectura monolítica actual (controladores + Blade). Se extraen dos servicios (alcance y liderazgo) para eliminar la lógica duplicada entre `Controller` base, `PersonasController`, `LideresController` y `CelulasController`; no se introduce SPA ni API.

## Seguimiento de complejidad

Sin violaciones de la constitución que justificar.
