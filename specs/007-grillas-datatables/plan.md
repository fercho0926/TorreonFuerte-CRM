# Plan de Implementación: Grillas uniformes con ordenamiento, filtros, paginación y exportación

**Rama**: `007-grillas-datatables` (la rama git de trabajo sigue siendo `modulos`) | **Fecha**: 2026-10-19 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación en `/specs/007-grillas-datatables/spec.md`

## Resumen

Convertir en grilla estándar (la de personas ya entregada) los otros 9 listados principales y dejar la regla fijada: una **definición declarativa de grilla** en PHP (columnas, filtros, orden, celdas, exportación), un **componente Blade único** que pinta la tabla y un **módulo JS único** que activa DataTables en modo servidor, con respaldo sin JavaScript. Una prueba automática impide listados principales con tabla simple y la constitución recoge la regla.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; JavaScript ES modules (Vite)

**Dependencias Principales**: DataTables 2 (`datatables.net-bs5`) y Responsive (`datatables.net-responsive-bs5`), ya instaladas; AdminLTE 4 / Bootstrap 5; jQuery (ya cargado globalmente)

**Almacenamiento**: SQLite local / SQL Server o MySQL en producción; sin cambios de esquema

**Pruebas**: PHPUnit 12 (Feature/Unit), Vitest 5 + jsdom

**Plataforma de Destino**: aplicación web (escritorio y teléfono)

**Tipo de Proyecto**: aplicación web monolítica Laravel con Blade

**Metas de Rendimiento**: actualizar filtro u orden en menos de 1 s percibido hasta 5 000 filas; exportación por lotes sin cargar todo en memoria

**Restricciones**: respeto estricto del alcance por rol y sede (`AlcanceService`, `SedeScope`); orden y filtros siempre del lado del servidor sobre el conjunto completo; el orden y los filtros no admiten columnas arbitrarias (lista blanca)

**Escala/Alcance**: 9 listados más el refactor de personas; sedes y usuarios son conjuntos pequeños

## Verificación de la Constitución

El archivo `.specify/memory/constitution.md` es todavía la plantilla sin rellenar, así que no hay principios vigentes que puedan bloquear. Por RF-015 se **redacta una constitución mínima** (en español) con la regla de grillas estándar y los principios ya practicados (todo artefacto en español, aislamiento por sede, pruebas primero, mejora progresiva sin JavaScript). Resultado: **APROBADO**, sin violaciones.

Reevaluación tras el diseño: aprobada; no se agregan proyectos ni capas externas, solo una abstracción interna reutilizada por 10 listados.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/007-grillas-datatables/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas-y-componentes.md
└── tasks.md          # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio `laravel_app/`)

```text
app/
├── Grillas/
│   ├── Grilla.php               # clase base: columnas, consulta, filtros, orden, paginación, CSV
│   ├── Columna.php              # valor inmutable: clave, título, orden, filtro, celda, exportar, prioridad
│   ├── PersonasGrilla.php       # reemplaza a Services/GrillaPersonas
│   ├── LideresGrilla.php  CelulasGrilla.php  EncuentrosGrilla.php  EventosGrilla.php
│   ├── MinisteriosGrilla.php  SedesGrilla.php  SinLiderGrilla.php
│   └── UsuariosGrilla.php  SolicitudesClaveGrilla.php
├── Http/Controllers/Concerns/
│   └── ServicioGrilla.php       # acciones datos() y exportar() reutilizables por cada controlador
resources/
├── views/components/ui/grilla.blade.php      # tabla + barra + filtros por columna (con respaldo sin JS)
├── views/<modulo>/_celda.blade.php           # celdas específicas cuando no bastan las genéricas
├── js/grilla/grilla.js (+ .test.js)          # generaliza personas.js
└── css/grilla.css
routes/web.php                                # {modulo}.datos y {modulo}.exportar por listado
tests/
├── Feature/Grillas/                          # contrato común, alcance, exportación, permisos
└── Unit/Grillas/                             # Columna, filtros, orden, neutralización de fórmulas
.specify/memory/constitution.md               # regla de grillas estándar
README.md                                     # guía para grillas nuevas
```

**Decisión de estructura**: se mantiene el monolito Laravel. La lógica de cada listado vive en una clase `Grilla` declarativa; controladores, vista y JS dejan de repetirse.

## Seguimiento de Complejidad

Sin violaciones que justificar.
