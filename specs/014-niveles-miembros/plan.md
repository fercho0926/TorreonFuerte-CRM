# Plan de Implementación: Niveles de formación de los miembros (camino de retiros)

**Rama**: `014-niveles-miembros` (la rama git de trabajo sigue siendo `modulos`) | **Fecha**: 2026-10-23 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación en `/specs/014-niveles-miembros/spec.md`

## Resumen

Agregar el plan de formación de la iglesia (cuatro niveles ordenados, editables y comunes a todas las sedes) y mostrar el avance de cada miembro como un **camino gráfico tipo mapa de juego**. El avance se registra de dos formas: a mano, o de forma automática cuando el miembro asiste a un encuentro realizado que esté vinculado a un nivel. Un módulo «Niveles» permite administrar el plan y responder «¿quién está en qué nivel?» con una grilla estándar por nivel, una columna y un filtro «Nivel» en Miembros y una tarjeta en el tablero.

Enfoque técnico en una línea: dos tablas nuevas (`niveles_formacion`, `avances_nivel`) más una columna `nivel_id` en `encuentros`; un servicio que sincroniza avances desde la asistencia; un componente Blade del camino hecho con HTML y SVG generados en el servidor (sin librerías de gráficos ni JavaScript obligatorio); un módulo nuevo del catálogo de permisos de la spec 013; y una grilla estándar de la spec 007.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13 (Blade); CSS (sin JavaScript obligatorio para el camino)

**Dependencias Principales**: las ya instaladas (Bootstrap 5, AdminLTE 4, DataTables vía la grilla estándar). **No se agregan dependencias**.

**Almacenamiento**: SQLite local / MySQL en producción. Migraciones nuevas: `niveles_formacion`, `avances_nivel`, `nivel_cambios`, `encuentros.nivel_id`, permisos del módulo `niveles` en los roles existentes y carga de los 4 niveles.

**Pruebas**: PHPUnit 12 (Feature y Unit), Vitest 5 (solo si se agrega JS), Playwright (`npm run test:movil`, con las pantallas nuevas añadidas a la lista de auditoría).

**Plataforma de Destino**: aplicación web, uso principal en celular (constitución, principio VII).

**Tipo de Proyecto**: aplicación web monolítica Laravel con Blade.

**Metas de Rendimiento**: ficha del miembro con camino en menos de 1 s en el servidor (una consulta adicional, sin N+1); conteos por nivel con una sola consulta agregada; listas por nivel paginadas de 25 en 25 con las mismas metas de la grilla estándar (spec 007).

**Restricciones**: sin librerías nuevas; el camino debe funcionar sin JavaScript, ser accesible (texto, no solo color; `prefers-reduced-motion`) y pasar la auditoría móvil (44 px, 16 px, sin desborde de 320 a 430 px); el plan es común a todas las sedes pero los avances se aíslan por sede y alcance del rol.

**Escala/Alcance**: 4 niveles iniciales (hasta unas 12 estaciones razonables); miles de miembros por sede; un avance por miembro y nivel.

## Verificación de la Constitución

Constitución vigente v1.1.0 (`.specify/memory/constitution.md`):

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple. |
| II. Aislamiento por sede y alcance por rol (no negociable) | Cumple: `avances_nivel` lleva `sede_id` y el filtro global de sede; todas las consultas por nivel parten de `AlcanceService::personasVisibles`. El plan de niveles (nombre y orden) es común y no contiene datos de miembros. Se probará aislamiento entre sedes y entre roles. |
| III. Grillas estándar | Cumple: la lista de miembros por nivel es una `Grilla` (`niveles.miembros`); la lista de niveles es una lista corta (no una tabla) con controles de orden. Se agregan columna y filtro «Nivel» a la grilla de miembros y «Nivel» a la de encuentros. |
| IV. Pruebas primero | Cumple: se escribe cada prueba antes de su implementación. |
| V. Mejora progresiva | Cumple: el camino, el registro de avances y el orden de niveles funcionan sin JavaScript (formularios normales y `<details>`). |
| VI. Simplicidad | Cumple: reutiliza `Grilla`, `Modulos`, `AlcanceService`, `LiderazgoService::registrarCambio` y componentes `x-form.*`; el camino se dibuja con HTML y SVG propios. El nombre `Niveles` ya existe para los niveles de permiso (sin acceso, lectura, escritura): el modelo nuevo se llama `NivelFormacion` para no confundirlos. |
| VII. Diseño primero para celular | Cumple: el camino es una columna en zigzag pensada para teléfono; toda pantalla nueva entra en `npm run test:movil` con la lista de excepciones vacía. |

Resultado: **APROBADO**, sin violaciones. Reevaluación tras el diseño: aprobada.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/014-niveles-miembros/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── rutas-y-permisos.md
│   └── camino-y-componentes.md
└── tasks.md          # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio `laravel_app/`)

```text
app/
├── Models/
│   ├── NivelFormacion.php          # NUEVO: nivel del plan (tabla niveles_formacion)
│   ├── AvanceNivel.php             # NUEVO: nivel completado por un miembro (tabla avances_nivel, PerteneceASede)
│   └── NivelCambio.php             # NUEVO: historial de cambios de niveles
├── Services/
│   ├── NivelesService.php          # NUEVO: crear/editar/reordenar/desactivar/eliminar niveles y su historial
│   ├── AvancesNivelService.php     # NUEVO: registrar/quitar a mano, recalcular desde encuentros, copiar al trasladar
│   └── CaminoService.php           # NUEVO: estaciones, nivel actual/siguiente y geometría del camino de un miembro
├── Grillas/
│   └── NivelesMiembrosGrilla.php   # NUEVO: miembros por nivel (actual, completaron, siguiente)
├── Http/
│   ├── Controllers/NivelesController.php          # NUEVO: módulo (tablero, CRUD, orden, miembros)
│   ├── Controllers/AvancesNivelController.php     # NUEVO: registrar y quitar avances
│   └── Requests/NivelRequest.php                  # NUEVO
├── Support/Modulos.php             # + módulo `niveles` (catálogo, rutas)
├── Support/Estados.php             # (sin cambios)
database/
├── migrations/…niveles_formacion, …avances_nivel, …nivel_cambios, …encuentros_nivel, …permisos_niveles
└── factories/NivelFormacionFactory.php, AvanceNivelFactory.php
resources/
├── views/niveles/{index,miembros,create,edit,_form}.blade.php
├── views/components/niveles/{camino,puntos}.blade.php
├── css/niveles.css                 # NUEVO: camino, estaciones, estados y animación
└── views/personas/show.blade.php   # + camino y registro de avance
config/menu.php                     # + opción «Niveles» en Congregación
tests/
├── Unit/Niveles/                   # CaminoService, geometría, estado actual/siguiente
├── Feature/Niveles/                # módulo, avances manuales, sincronía con encuentros, aislamiento, permisos, grilla, tablero
└── movil/pantallas.mjs             # + pantallas nuevas en la auditoría
```

**Decisión de estructura**: se mantiene el monolito. El camino es un componente de presentación alimentado por un servicio puro y testeable; el cálculo de nivel actual vive en un solo lugar (`CaminoService` para una persona y un alcance SQL en `Persona` para listas).

## Seguimiento de Complejidad

Sin violaciones que justificar.
