# Plan de Implementación: Renombrar "Personas" a "Miembros"

**Rama**: `modulos` (directorio `010-renombrar-personas-miembros`) | **Fecha**: 2026-10-22 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de `/specs/010-renombrar-personas-miembros/spec.md`

## Resumen

Cambio de vocabulario transversal: "Persona(s)" pasa a "Miembro(s)" en interfaz, mensajes, exportaciones, direcciones web, documentación, código y base de datos, sin perder datos ni alterar el aislamiento por sede ni los permisos.

Enfoque técnico (ver [research.md](research.md)), en tres etapas que dejan las pruebas en verde al terminar cada una:

1. **Texto visible y direcciones** (valor inmediato, riesgo bajo): vistas, menú, `lang/es`, mensajes de controladores/servicios, grillas, exportaciones, manifiesto; rutas `/miembros/*` con redirección 301 desde `/personas/*`.
2. **Nomenclatura interna**: modelo `Persona` → `Miembro`, `PersonaCambio` → `MiembroCambio`, `EventoPersona` → `EventoMiembro`, controladores, políticas, servicios, vistas, factories, nombres de rutas (`personas.*` → `miembros.*`), y pruebas.
3. **Base de datos**: una migración nueva y reversible que renombra tablas y columnas (`personas` → `miembros`, `persona_id` → `miembro_id`, tablas pivote); las migraciones históricas NO se editan.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13

**Dependencias principales**: Blade, AdminLTE 4/Bootstrap 5, Vite, DataTables 2 (sin dependencias nuevas)

**Almacenamiento**: base relacional existente (SQLite local/pruebas; hosting cPanel con otro motor en producción); 1 migración de renombrado, sin tablas nuevas

**Pruebas**: PHPUnit, Vitest, `npm run test:movil`; se renombran/actualizan las existentes y se agregan pruebas de redirección y de ausencia de la palabra "persona" en texto visible

**Plataforma de destino**: web móvil primero, despliegue en cPanel (los activos de `public/build` se recompilan)

**Tipo de proyecto**: aplicación web monolítica Laravel (`laravel_app/`)

**Metas de rendimiento**: sin cambios; la migración de renombrado es de metadatos (instantánea, sin copiar filas)

**Restricciones**: nunca `migrate:fresh` sobre la base local; migración reversible; el service worker y el manifiesto no deben dejar rutas antiguas en caché que rompan la navegación; sin pérdida de datos

**Escala/Alcance**: ≈280 archivos con ≈2.200 apariciones de "persona"; 142 archivos en `resources`, `routes`, `lang`, `tests`, `config`; 10 migraciones tocan tablas afectadas

## Verificación de la Constitución

| Principio | Cumplimiento |
|---|---|
| I. Artefactos en español | Sí. |
| II. Aislamiento por sede y rol | Sí. El `SedeScope` y las políticas se renombran sin cambiar lógica. Las pruebas de aislamiento existentes (`AislamientoPersonasTest`, `MatrizRutasTest`, etc.) se renombran y DEBEN seguir verdes; la matriz de rutas cubre `/miembros/*` y las redirecciones antiguas (una redirección NO debe filtrar si el miembro existe en otra sede: redirige solo el prefijo y la ruta nueva decide acceso). |
| III. Grillas estándar | Sí. `PersonasGrilla` → `MiembrosGrilla` conserva columnas, filtros y exportación; la prueba que exige grilla estándar sigue vigente. |
| IV. Pruebas primero | Se actualizan pruebas antes de cada etapa y se agrega una prueba guardiana de terminología. |
| V. Mejora progresiva | Sin cambios de comportamiento; redirecciones del lado del servidor, sin JS. |
| VI. Simplicidad | Renombrado mecánico, sin abstracciones nuevas; las redirecciones antiguas son un único grupo de rutas. |
| VII. Celular primero | `npm run test:movil` DEBE pasar; se revisa que "Miembros" no desborde controles de 44 px. La constitución dice "no guarda datos de personas…": se enmienda a "miembros". |

Resultado: **sin violaciones**. Enmienda menor a la constitución (VII) por terminología, versión 1.1.1.

**Re-evaluación tras el diseño (Fase 1)**: sin cambios; el diseño no añade riesgos al aislamiento.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/010-renombrar-personas-miembros/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas.md
├── checklists/requirements.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código fuente (`laravel_app/`)

```text
app/Models/            Persona→Miembro, PersonaCambio→MiembroCambio, EventoPersona→EventoMiembro (+ relaciones en Celula, Evento, Encuentro, Ministerio, User, Sede, RegistroQr)
app/Http/Controllers/  PersonasController→MiembrosController (+ referencias en los demás)
app/Http/Requests/     PersonaRequest→MiembroRequest
app/Policies/          PersonaPolicy→MiembroPolicy
app/Grillas/           PersonasGrilla→MiembrosGrilla (+ SinLider, Líderes, Células, Encuentros)
app/Services/          BuscadorPersonas→BuscadorMiembros, AlcanceService (personasVisibles→miembrosVisibles), TrasladoService, LiderazgoService, RegistroPorQr, SedeService
database/migrations/   NUEVA: renombrado de tablas y columnas (las históricas no se tocan)
database/factories/    PersonaFactory→MiembroFactory
resources/views/       personas/→miembros/, partials/persona-tarjeta→miembro-tarjeta, textos en el resto
resources/js/          URLs /personas/* → /miembros/* y sus pruebas Vitest
routes/web.php         rutas miembros.* + grupo de redirección 301 de /personas/*
lang/es/validation.php atributos «miembro(s)»
config/menu.php        etiqueta «Miembros», rutas miembros.*
public/                manifest.webmanifest (descripción), sw.js (versión de caché)
tests/                 Feature/Personas→Miembros, Aislamiento/AislamientoPersonasTest→…Miembros, movil/pantallas.mjs, etc.
```

**Decisión de estructura**: se mantiene la arquitectura monolítica existente; no hay carpetas nuevas salvo el renombrado de `personas/` a `miembros/` en vistas y pruebas.

## Seguimiento de Complejidad

| Decisión | Por qué es necesaria | Alternativa más simple descartada |
|---|---|---|
| Colisión del nombre `miembros()` | `Persona::miembros()` ya existe (los miembros que tiene a cargo un líder) y `Celula::miembros()` (integrantes). Al renombrar el modelo a `Miembro`, `Miembro::miembros()` sería confuso | Se renombra la relación del líder a `liderados()` (y `miembros_count` → `liderados_count`); `Celula::miembros()` se conserva |
| Estado `miembro_activo` | Es un valor guardado en la base de datos con etiqueta «Miembro activo»; coexiste con la entidad «Miembro» | Se conserva el valor guardado y su etiqueta (es un estado, no la entidad); se documenta en el glosario del README |
| Migración de renombrado en vez de editar las históricas | Las históricas ya se ejecutaron en entornos reales | Reescribirlas obligaría a `migrate:fresh`, prohibido por la constitución |
