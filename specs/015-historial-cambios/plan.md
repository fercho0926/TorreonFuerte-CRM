# Plan de Implementación: Historial de cambios en toda la plataforma (pestaña «Histórico»)

**Rama**: `015-historial-cambios` (la rama git de trabajo sigue siendo `modulos`) | **Fecha**: 2026-10-23 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación en `/specs/015-historial-cambios/spec.md`

## Resumen

Hoy hay cuatro historiales sueltos (miembros, sedes, roles y niveles) y la mayoría de los módulos no registra nada. Esta funcionalidad los reemplaza por **un solo historial inalterable para toda la plataforma**:

- Toda acción de escritura deja una entrada con fecha, hora, actor, acción, registro afectado y el detalle «campo: antes → después».
- Cada ficha (miembro, líder, célula, ministerio, evento, encuentro, nivel, sede, usuario y rol) tiene una pestaña **«Histórico»**. La del miembro se organiza en secciones: datos, encuentros y retiros, células y ministerios, liderazgo, niveles y sede.
- Un módulo nuevo, **«Control de cambios»**, por defecto solo para el administrador, lista todo con la grilla estándar y **sin exportación**.

Enfoque técnico en una línea:
- una tabla `historial_cambios`, polimórfica, sin claves foráneas y con fotos de los nombres;
- un servicio `Historial` como única puerta de escritura;
- un rasgo `RegistraHistorial` que captura altas, ediciones y bajas de los modelos con diferencias campo a campo;
- llamadas explícitas para las relaciones muchos a muchos (`sync()`), que Eloquent no avisa;
- pestañas por parámetro `?pestana=historico`, que funcionan sin JavaScript;
- una migración que integra los cuatro historiales actuales.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13 (Blade). JavaScript solo como mejora (la grilla estándar existente).

**Dependencias Principales**: las ya instaladas (Bootstrap 5, AdminLTE 4, DataTables 2 vía la grilla estándar). **No se agregan dependencias**; se descartaron paquetes de auditoría (ver research.md, D1).

**Almacenamiento**: SQLite local y en pruebas, MySQL en producción. Migraciones nuevas:
- tabla `historial_cambios`;
- columna `personas.trasladada_desde_persona_id`;
- permiso del módulo `control_cambios` en los roles existentes;
- copia de `persona_cambios`, `sede_cambios`, `rol_cambios` y `nivel_cambios` al historial nuevo, y borrado posterior de esas tablas si los conteos coinciden.

**Pruebas**:
- PHPUnit 12, Feature y Unit, nueva carpeta `tests/Feature/Historial/`;
- Vitest solo si se agrega JavaScript;
- Playwright (`npm run test:movil`), con las pestañas nuevas y `control-cambios` en `tests/movil/pantallas.mjs`.

**Plataforma de Destino**: aplicación web; uso principal en celular (principio VII).

**Tipo de Proyecto**: aplicación web monolítica Laravel con Blade.

**Metas de Rendimiento**:
- Primera página de la pestaña «Histórico» en menos de 2 s con 5 000 entradas (CE-004): consultas indexadas, 20 filas por sección, sin uniones gracias a las fotos de nombres.
- La escritura agrega como máximo una inserción por entrada dentro de la transacción de la acción.
- Una asistencia masiva de 100 personas escribe 101 entradas con una inserción por lotes.

**Restricciones**:
- Inalterable: sin rutas ni métodos de edición o borrado; el modelo prohíbe `update` y `delete`.
- Sin claves foráneas que puedan borrar o anular entradas.
- Nunca guardar valores secretos.
- Sin exportación.
- Funciona sin JavaScript.
- Pasa la auditoría móvil (44 px, 16 px, sin desborde de 320 a 430 px).

**Escala/Alcance**:
- 16 modelos con historial, unas 45 rutas de escritura y alrededor de 70 acciones en el catálogo.
- 10 fichas con pestaña y un módulo nuevo.
- Miles de miembros por sede; decenas de miles de entradas por año.

## Verificación de la Constitución

Constitución vigente v1.1.0 (`.specify/memory/constitution.md`):

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple. |
| II. Aislamiento por sede y alcance por rol (no negociable) | **Cumple con una excepción declarada.** Cada entrada lleva `sede_id` y se lee con el filtro global de sede; la pestaña se autoriza con la misma regla que la ficha; «Control de cambios» aplica el contexto de sede y, sin acceso total, el alcance de `AlcanceService`. **Excepción** (clarificación 3, RF-023): el histórico de un miembro trasladado incluye las entradas de sus registros de origen en otra sede, leídas sin el filtro de sede **solo para esa cadena de personas**. Las fichas de la otra sede siguen sin enlace. Se prueba que ningún otro dato cruza de sede. |
| III. Grillas estándar | **Cumple con una excepción declarada.** «Control de cambios» usa `<x-ui.grilla>` con `CambiosGrilla` (orden, filtros por columna, búsqueda, 10/25/50/100, paginador, tarjetas, respaldo sin JS), **sin exportación** por decisión del responsable (clarificación 1, RF-020). Las pruebas de contrato de grillas se ajustan con una lista explícita de grillas sin exportación. Las listas dentro de las fichas no son listados principales. |
| IV. Pruebas primero | Cumple: cada pieza (servicio, rasgo, migración, pestaña, grilla, cobertura de rutas) se escribe con su prueba antes. Prueba de cobertura que falla si una ruta de escritura no registra historial. |
| V. Mejora progresiva | Cumple: pestañas con enlaces reales (`?pestana=historico`), filtros por `GET`, paginación del servidor y `<details>` para textos largos. |
| VI. Simplicidad | Cumple: una tabla, un servicio y un rasgo reemplazan cuatro tablas, cuatro modelos y cuatro escritores. Se reutilizan `Grilla`, `Modulos`, `AlcanceService`, `SedeContext`, `PerteneceASede`, `Estados` y el patrón de pestañas de `sedes/show`. Sin dependencias nuevas. |
| VII. Diseño primero para celular | Cumple: entradas como tarjetas apiladas, secciones una debajo de otra y filtros plegables. Las pantallas nuevas entran en `npm run test:movil` sin excepciones. |

Resultado: **APROBADO** con dos excepciones justificadas (ver «Seguimiento de Complejidad»). Reevaluación tras el diseño (data-model y contratos): aprobada, sin cambios.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/015-historial-cambios/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── rutas-y-permisos.md
│   └── servicio-historial.md
├── checklists/requirements.md
└── tasks.md          # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio `laravel_app/`)

```text
app/
├── Models/
│   ├── CambioHistorial.php              # NUEVO: entrada inalterable (tabla historial_cambios)
│   ├── Concerns/RegistraHistorial.php   # NUEVO: rasgo de altas/ediciones/bajas con diferencias
│   ├── PersonaCambio.php, SedeCambio.php, RolCambio.php, NivelCambio.php   # SE ELIMINAN
│   └── Persona, Celula, CelulaReunion, Ministerio, Evento, EventoPersona, EventoPago, Encuentro,
│       NivelFormacion, AvanceNivel, Sede, User, Rol, SolicitudClave, RegistroQr, RegistroMiembroQr
│                                        # + RegistraHistorial y su definición de campos
├── Services/
│   ├── Historial.php                    # NUEVO: registrar, diferencia, sincronizacion, accion, comoActor
│   ├── LecturaHistorial.php             # NUEVO: pestaña (secciones, filtros, cadena de traslados) y visibilidad
│   ├── LiderazgoService.php             # − registrarCambio(); usa Historial
│   ├── SedeService.php, RolService.php, NivelesService.php, AvancesNivelService.php,
│   │   TrasladoService.php, RegistroPorQr.php, RegistroMiembrosQr.php   # usan Historial
├── Support/
│   ├── AccionesHistorial.php            # NUEVO: catálogo de acciones (etiqueta, módulo, categoría, icono)
│   └── Modulos.php                      # + control_cambios
├── Grillas/CambiosGrilla.php            # NUEVO: grilla «Control de cambios» (sin exportación)
├── Http/Controllers/
│   ├── ControlCambiosController.php     # NUEVO: index y datos
│   ├── Concerns/PestanaHistorico.php    # NUEVO: arma la pestaña para cualquier ficha
│   └── Personas, Lideres, Celulas, Ministerios, Eventos, Encuentros, Niveles, Sedes, Users, Roles,
│       ClavesUsuario, Cuenta, RecuperacionClave, SolicitudesClave, RegistroQr, RegistroMiembrosQr
│                                        # + pestaña y/o llamadas a Historial en relaciones
└── Policies/PersonaPolicy.php           # − verBitacora
database/migrations/
├── …_create_historial_cambios_table.php
├── …_add_trasladada_desde_persona_id_to_personas.php
├── …_permisos_modulo_control_cambios.php
├── …_migrar_historiales_existentes.php
└── …_drop_historiales_antiguos.php
resources/views/
├── historial/_pestanas.blade.php        # NUEVO: «Datos | … | Histórico»
├── historial/_secciones.blade.php       # NUEVO: secciones del miembro
├── components/historial/{lista,entrada,filtros}.blade.php   # NUEVOS
├── control-cambios/index.blade.php      # NUEVO
├── control-cambios/_celda.blade.php      # NUEVO: celda de la grilla (id control-cambios)
└── personas/show, lideres/show, celulas/show, ministerios/show, eventos/show, encuentros/show,
    niveles/edit, sedes/show, users/edit, roles/show   # + pestaña
config/menu.php                          # + «Control de cambios»
routes/web.php                           # + control-cambios.index / .datos
tests/
├── Unit/Historial/                      # diferencia, secretos, catálogo, categorías
├── Feature/Historial/                   # servicio, rasgo, cobertura de rutas, pestañas, secciones,
│                                        # control de cambios, aislamiento, traslado, migración, inalterable, rendimiento
├── Feature/Grillas/*                    # lista de grillas sin exportación
└── movil/pantallas.mjs                  # + pestañas y control-cambios
```

**Decisión de estructura**: se mantiene el monolito. La escritura vive en `Historial` (con el rasgo como único enganche automático) y la lectura en `LecturaHistorial`. Las vistas comparten un componente de entrada, así que agregar la pestaña a una ficha nueva es incluir un parcial y llamar a un método del controlador.

## Orden de implementación sugerido

1. Tabla, modelo `CambioHistorial`, catálogo y servicio `Historial`, con sus pruebas unitarias.
2. Rasgo `RegistraHistorial` en `Persona`, reemplazo de `LiderazgoService::registrarCambio()` y pestaña del miembro con secciones. Es el MVP de la historia 1.
3. Relaciones: encuentros (asistencia), eventos (inscripciones y abonos), células, ministerios y QR.
4. Resto de modelos y fichas (historia 3), y migración de los historiales existentes.
5. Módulo «Control de cambios» y permiso (historia 4).
6. Inalterabilidad, traslado, prueba de cobertura de rutas, rendimiento y auditoría móvil.

## Seguimiento de Complejidad

| Violación | Por qué es necesaria | Alternativa más simple descartada porque |
|-----------|----------------------|------------------------------------------|
| Principio II: el histórico de un miembro trasladado muestra entradas de su sede anterior | El responsable decidió que la sede nueva vea todo el histórico anterior (clarificación 3, RF-023). | Copiar las entradas a la sede nueva duplicaría datos y rompería la inalterabilidad; mostrar solo lo posterior al traslado contradice la decisión. La excepción se limita a la cadena `trasladada_desde_persona_id` del miembro visible y tiene su propia prueba de aislamiento. |
| Principio III: «Control de cambios» es un listado principal sin exportación a CSV | El responsable prohibió exportar el historial (clarificación 1, RF-020): reúne datos personales y de acceso de toda la plataforma. | Restringir la exportación al administrador, como en usuarios, sigue permitiendo sacar el historial completo. Se conserva el resto del contrato de la grilla estándar. |
| Se amplía quién ve el historial del miembro (antes solo acceso total con `verBitacora`) | RF-021: quien ve la ficha ve su «Histórico». | No es una violación de la constitución, pero cambia un comportamiento existente; se documenta y se prueba por rol. |

## Ajustes durante la implementación (2026-10-23)

Cambios respecto al diseño original; los documentos de arriba se conservan como registro de la decisión inicial.

| Diseño | Implementado | Motivo |
|--------|--------------|--------|
| Un rasgo `RegistraHistorial` en 16 modelos | Dos rasgos: `TieneHistorial` (definición de campos y relación `historial()`) y `RegistraHistorial` (además registra solo altas, ediciones y bajas). Registro automático en `Celula`, `Ministerio`, `Evento`, `Encuentro`, `User` y `SolicitudClave`. Miembros, sedes, roles y niveles siguen con su registro explícito (ya cubría todas sus acciones, con pruebas) y solo cambian de destino. | No duplicar entradas ni perder el nombre de negocio de acciones que ya existían. |
| Catálogo plano de acciones con claves nuevas | Catálogo por tipo de registro que conserva las claves anteriores | Migración directa de los historiales viejos y menos cambios en pruebas existentes. |
| `LiderazgoService::registrarCambio()` se elimina | Se conserva como atajo que delega en `Historial::registrar()` | Evita tocar ~20 llamadas; ya no escribe en una tabla propia. |
| 16 tipos de sujeto (incluidos inscripción, abono, reunión, avance, QR) | 10 tipos con ficha; inscripciones, abonos, reuniones y avances se registran en el evento, la célula o el miembro con el otro registro como relacionado. Los QR se registran como `qr_cambiado` en su evento, encuentro o célula, y en la sede para el QR de miembros. | Cada entrada aparece donde la busca el usuario, sin fichas que no existen. |
| Solo `detalle` | Además las columnas `antes` y `despues` con los valores crudos | Compatibilidad con los historiales anteriores y con las pruebas existentes. |
| `CambioHistorial` con el filtro global de sede | Sin filtro global; la pestaña se autoriza con la ficha y «Control de cambios» filtra la sede explícitamente | La cadena de traslados necesita leer otra sede de forma acotada. |
| Borrar las tablas viejas al final | Se borraron en cuanto los escritores pasaron al historial único (migración `2026_10_30_000005`, que verifica conteos) | Ningún código las usaba ya. |
| Mismo formulario de filtros | Fechas `datetime-local` en «Control de cambios» y `date` en la pestaña | Precisión de hora donde se audita. |

Hallazgo corregido en el camino: `celulas.sede` (texto «lugar o sector») choca con la relación `sede()`; leer `getAttribute('sede')` con la columna vacía devolvía la sede completa (con su token de QR). El historial lee solo valores crudos de columnas.
