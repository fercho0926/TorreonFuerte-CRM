# Investigación: Historial de cambios en toda la plataforma

**Funcionalidad**: `015-historial-cambios` | **Fecha**: 2026-10-23

Este documento resuelve las incógnitas técnicas del plan a partir del código actual de `laravel_app/`.

## Punto de partida (lo que existe hoy)

| Historial actual | Tabla | Cómo se escribe | Problemas frente a la spec |
|------------------|-------|-----------------|----------------------------|
| Miembros | `persona_cambios` (`PerteneceASede`) | `LiderazgoService::registrarCambio()` llamado a mano desde controladores y servicios | `cascadeOnDelete`: si se borra la persona se borra su historial (viola RF-008). El detalle es el JSON `despues` crudo. Solo lo ve `verBitacora` (acceso total). |
| Sedes | `sede_cambios` | `SedeService` | Mismo esquema con `cascadeOnDelete`. |
| Roles | `rol_cambios` | `RolService` | Guarda `rol_clave`/`rol_nombre` (sobrevive al borrado). |
| Niveles | `nivel_cambios` | `NivelesService` | Guarda `nombre`, `nullOnDelete`. |

No registran nada: células (miembros, reuniones, asistencia a reuniones), ministerios (integrantes), eventos (inscripciones, abonos), encuentros (asistencia), usuarios (alta, edición, rol, sede, clave), solicitudes de clave, QR de registro y registro público por QR. Las relaciones muchos a muchos se escriben con `sync()`, `attach()`, `detach()`, `updateExistingPivot()` y `syncWithoutDetaching()`, que **no disparan eventos de Eloquent**.

El traslado de sede (`TrasladoService`) **no mueve la fila**: crea una persona nueva en la sede destino y deja inactiva la de origen; el único vínculo entre ambas es `persona_origen_id` dentro del JSON del historial.

---

## D1. Una sola tabla de historial para toda la plataforma

- **Decisión**: Crear la tabla `historial_cambios` con un registro por entrada, polimórfica sobre el registro afectado (`sujeto_tipo`, `sujeto_id`), con una relación opcional a un segundo registro (`relacionado_tipo`, `relacionado_id`) y fotos de los nombres en el momento del cambio (`actor_nombre`, `sujeto_nombre`, `relacionado_nombre`). Detalle en `data-model.md`.
- **Justificación**: Una tabla única permite la pestaña «Histórico» de cualquier ficha y el listado «Control de cambios» con una sola consulta paginada, filtros indexados y un único componente de presentación (principio VI). Las fotos de nombres cumplen RF-009 y permiten consultar registros eliminados (RF-018).
- **Alternativas descartadas**: (a) una tabla `*_cambios` por módulo, como hoy: once tablas más, y «Control de cambios» necesitaría `UNION` sobre todas. (b) Un paquete externo (p. ej. `spatie/laravel-activitylog` u `owen-it/laravel-auditing`): agrega una dependencia, sus tablas y textos están en inglés, no conoce el aislamiento por sede ni los actores especiales y de todos modos habría que escribir a mano los cambios de relaciones. La constitución prefiere no agregar dependencias si lo existente alcanza.

## D2. Cómo se captura cada cambio: híbrido (eventos de modelo + llamadas explícitas)

- **Decisión**:
  1. **Rasgo `RegistraHistorial`** en cada modelo con ficha o con datos propios (`Persona`, `Celula`, `CelulaReunion`, `Ministerio`, `Evento`, `EventoPersona`, `EventoPago`, `Encuentro`, `NivelFormacion`, `AvanceNivel`, `Sede`, `User`, `Rol`, `SolicitudClave`, `RegistroQr`, `RegistroMiembroQr`). Escucha `created`, `updated`, `deleted` y `restored`, calcula la diferencia campo a campo y escribe la entrada. Cada modelo declara sus campos con su etiqueta legible, sus campos secretos y cómo traducir claves foráneas a nombres (`lider_id` → nombre del líder).
  2. **Llamadas explícitas** al servicio `Historial` para las relaciones que no disparan eventos: asistencia a encuentros, integrantes de ministerios, miembros de células, asistencia a reuniones de célula, inscripciones por QR. Un ayudante `Historial::sincronizacion()` recibe el resultado de `sync()` (`attached`, `detached`, `updated`) y escribe la entrada resumen en el registro principal y una entrada por cada miembro afectado (RF-006).
  3. **Acción con nombre de negocio**: `Historial::accion('inactivada', fn () => …)` envuelve una operación para que las entradas que generen los eventos lleven esa acción («Cambio de líder», «Inactivada») en lugar de «Datos editados».
- **Justificación**: Los eventos de modelo garantizan que ninguna edición de atributos se escape (CE-001) aunque se agreguen pantallas nuevas; las llamadas explícitas cubren lo que Eloquent no avisa. Se reemplaza `LiderazgoService::registrarCambio()` y los escritores de `SedeService`, `RolService` y `NivelesService` por el servicio nuevo, sin duplicar entradas.
- **Alternativas descartadas**: (a) Solo llamadas explícitas, como hoy: fácil de olvidar en cada acción nueva, que es justo el problema actual. (b) Disparadores de base de datos: no conocen al usuario ni al actor, y SQLite y MySQL tienen sintaxis distinta. (c) Modelos de tabla intermedia (`Pivot` con eventos) para todas las relaciones: obliga a cambiar todas las llamadas a `sync()` por operaciones fila a fila y no da la entrada resumen.

## D3. Atomicidad: sin cambio no hay entrada, y sin entrada no hay cambio

- **Decisión**: Las entradas se escriben en la misma transacción que el cambio. Las acciones de controlador que hacen varias escrituras se envuelven en `DB::transaction()` (muchas ya lo hacen). Una edición sin diferencias no escribe nada; una validación fallida no llega a escribir.
- **Justificación**: Cumple RF-005 y evita entradas huérfanas si algo falla a mitad de camino.
- **Alternativas descartadas**: Escribir el historial en una cola: más piezas, y en el hosting compartido no hay un proceso de colas garantizado.

## D4. Actor: usuario, «Registro público por QR» o «Automático»

- **Decisión**: Columna `actor` con los valores `usuario`, `qr`, `automatico` y `sistema`, además de `user_id` (puede ser nulo) y la foto `actor_nombre`. El servicio toma el usuario autenticado por defecto. `RegistroPorQr` y `RegistroMiembrosQr` envuelven su trabajo en `Historial::comoActor('qr', …)` con el nombre del QR de origen. `AvancesNivelService` usa `automatico` y conserva el `user_id` de quien marcó la asistencia. Las migraciones y comandos usan `sistema`.
- **Justificación**: Escenarios 4 y 5 de la historia 2, y RF-002.

## D5. Campos secretos

- **Decisión**: Lista global de campos que nunca se guardan (`password`, `remember_token`, cualquier campo que empiece por `clave`, `token`, `codigo` o termine en `_hash`), más los que declare cada modelo. Si uno cambia, el detalle guarda `{campo, etiqueta, secreto: true}` y la vista muestra «Clave cambiada». Una prueba recorre todas las entradas generadas por la suite y falla si encuentra un valor de esos campos (CE-005).
- **Justificación**: RF-007.

## D6. Valores legibles y fotos de nombres

- **Decisión**: El detalle guarda, por cada campo cambiado, `antes` y `despues` **ya traducidos a texto legible** (nombre del líder, etiqueta del estado, fecha en formato local, «Sí/No», «(vacío)»), además del valor crudo (`antes_valor` y `despues_valor`) por si hace falta en el futuro. No se vuelve a resolver nada al leer.
- **Justificación**: RF-003 y RF-009: si después se renombra el líder o se elimina, la entrada sigue diciendo lo que era. Además, leer el historial no necesita uniones con otras tablas (CE-004).

## D7. Secciones del histórico del miembro

- **Decisión**: Columna `categoria` calculada al escribir, a partir del módulo y la acción: `datos`, `encuentros_retiros`, `celulas_ministerios`, `liderazgo`, `niveles` y `sede`. La pestaña del miembro hace **una consulta paginada por sección** (20 por página, parámetro `pagina_<categoria>`) y una consulta agregada para los contadores. Las demás fichas usan una sola lista con filtro por acción.
- **Justificación**: Clarificación 4 (secciones separadas) con paginación independiente y un índice `(sujeto_tipo, sujeto_id, categoria, created_at)`.

## D8. Histórico del miembro trasladado

- **Decisión**: Agregar `personas.trasladada_desde_persona_id` (se llena en `TrasladoService` y se rellena para traslados antiguos a partir de `persona_origen_id` del historial). El histórico de un miembro reúne las entradas de **toda su cadena** de personas (la actual y las de origen) y las lee **sin el filtro global de sede** (`SedeContext::sinFiltro`), solo para esa cadena. Las fichas de la otra sede (células, eventos, encuentros) se muestran sin enlace.
- **Justificación**: Clarificación 3 (la sede nueva ve todo el histórico anterior). Es una excepción acotada al principio II, declarada en RF-023 y en el seguimiento de complejidad del plan: solo se abre la cadena del miembro que la sede ya puede ver, no otras personas ni otros registros.
- **Alternativas descartadas**: Copiar las entradas a la sede destino al trasladar: duplica datos y rompe la inalterabilidad (habría dos versiones de la misma entrada).

## D9. Quién ve qué (lectura)

- **Decisión**:
  - **Pestaña «Histórico»**: se autoriza con la misma regla que la ficha (la política `view` del modelo o `AlcanceService` para personas y células). Esto **amplía** el acceso de hoy (`verBitacora` era solo acceso total) a quien puede ver la ficha (RF-021); `verBitacora` se retira.
  - **Dentro de una entrada**: el enlace al registro relacionado solo se dibuja si el usuario tiene lectura en el módulo de ese registro y puede verlo (misma sede o alcance). Los campos marcados como `modulo_sensible` (montos de abonos → `eventos`) se ocultan si el usuario no tiene lectura en ese módulo (escenario 9 de la historia 1).
  - **«Control de cambios»**: módulo nuevo `control_cambios`, de solo lectura, con lectura para `admin` y sin acceso para los demás roles (clarificación 2). Aplica el filtro de sede del contexto (modo «Todas» para el administrador). Si un rol sin acceso total recibe el módulo, solo ve las entradas cuyo sujeto es una persona de `personasVisibles` o una célula de `celulasVisibles`.
- **Justificación**: RF-021 a RF-023.

## D10. Sin exportación (excepción al principio III)

- **Decisión**: `CambiosGrilla::puedeExportar()` devuelve siempre `false`, no se registra la ruta `control-cambios.exportar` y la pestaña «Histórico» no tiene botón de exportar. Las pruebas de contrato de grillas (`ContratoGrillaTest`, `ExportacionGrillasTest`, `AlcanceGrillasTest`) aceptan una lista explícita de grillas **sin exportación** (hoy solo `control-cambios`) y prueban que la grilla no muestra el botón y que la ruta no existe.
- **Justificación**: Clarificación 1 y RF-020. Se mantiene el resto del contrato de la grilla estándar (orden, filtros por columna, filas por página, tarjetas en teléfono, respaldo sin JavaScript).

## D11. Integración de los historiales existentes

- **Decisión**: Una migración de datos copia `persona_cambios`, `sede_cambios`, `rol_cambios` y `nivel_cambios` a `historial_cambios`, conservando `created_at`, `user_id` y el JSON `antes`/`despues` en `detalle.legado`, y traduciendo las acciones al catálogo nuevo. Se cuentan las filas de origen y destino (CE-007). Los modelos viejos se eliminan y una **segunda migración** borra las cuatro tablas, solo si los conteos coinciden; si no coinciden, aborta con un mensaje.
- **Justificación**: Una sola fuente de verdad. Las entradas antiguas se muestran con su etiqueta y un resumen de su JSON, sin inventar diferencias campo a campo que no se guardaron (caso límite de la spec).
- **Alternativas descartadas**: Leer las tablas viejas con `UNION` para siempre: complica cada consulta y el índice.

## D12. Eliminaciones y borrado lógico

- **Decisión**: La entrada de eliminación guarda en `detalle.datos` los campos principales legibles del registro. `historial_cambios` **no tiene claves foráneas** hacia los sujetos (solo índices) para que ningún `cascadeOnDelete` ni `nullOnDelete` la toque; `user_id` tampoco tiene clave foránea, y su nombre queda en `actor_nombre`. `Persona` usa `SoftDeletes`, así que `deleted` y `restored` se registran como «Eliminada» y «Restaurada».
- **Justificación**: RF-004 y RF-008.

## D13. Inalterabilidad

- **Decisión**: El modelo `CambioHistorial` lanza una excepción en `updating` y `deleting`. No hay rutas de edición ni de borrado. Una prueba recorre `route:list` y comprueba que no existe ningún `PUT`, `PATCH` o `DELETE` bajo `control-cambios` ni `historico`.
- **Justificación**: RF-008 e historia 5.

## D14. Pestañas en las fichas

- **Decisión**: Mismo patrón que `sedes/show.blade.php`: `<ul class="nav nav-tabs">` con enlaces reales y el parámetro `?pestana=historico`. No necesita JavaScript (principio V), se puede abrir con un enlace (RF-016) y la pestaña por defecto sigue siendo «Datos». Un parcial común `historial/_pestanas.blade.php` y un componente `<x-historial.lista>` (tarjetas apiladas en teléfono, `<details>` para textos largos). Fichas: personas, líderes, células, ministerios, eventos, encuentros, sedes y roles (`show`); niveles y usuarios no tienen `show`, así que la pestaña va en su pantalla de edición (`niveles/{id}/edit`, `users/{id}/edit`).
- **Justificación**: Reutiliza el patrón existente y cumple los principios V y VII.

## D15. Rendimiento

- **Decisión**: Índices `(sujeto_tipo, sujeto_id, categoria, created_at)`, `(sede_id, created_at)`, `(user_id, created_at)`, `(modulo, created_at)` y `(relacionado_tipo, relacionado_id)`. Paginación del servidor de 20 por sección en la pestaña y la grilla estándar en «Control de cambios». Prueba con 5 000 entradas sembradas para un miembro: la primera página debe responder en menos de 2 s en la suite (CE-004).
- **Justificación**: CE-004.

## D16. Fechas y horas

- **Decisión**: Se guarda `created_at` con la zona horaria de la aplicación y se muestra como «23 oct 2026, 3:45 p. m.». Las asistencias muestran además la fecha del encuentro (`detalle.fecha_evento`), que puede ser distinta de la fecha en que se marcó.
- **Justificación**: Escenario 3 de la historia 1: el miembro «asistió el día X», aunque la asistencia se marque después.
