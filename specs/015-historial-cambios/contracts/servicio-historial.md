# Contrato: Servicio de registro `App\Services\Historial`

**Funcionalidad**: `015-historial-cambios`

Es la única puerta de escritura del historial. Los modelos lo usan a través del rasgo `RegistraHistorial`; los controladores y servicios lo llaman directamente solo para lo que Eloquent no avisa (relaciones muchos a muchos) o para dar nombre de negocio a una acción.

## Operaciones

| Operación | Firma (resumen) | Comportamiento |
|-----------|-----------------|----------------|
| Registrar | `registrar(Model $sujeto, string $accion, array $detalle = [], ?Model $relacionado = null, ?string $lote = null): ?CambioHistorial` | Escribe una entrada con el actor actual, la sede del sujeto, el módulo y la categoría del catálogo y las fotos de nombres. Lanza una excepción si la acción no está en `AccionesHistorial`. |
| Diferencia | `diferencia(Model $modelo): array` | Devuelve la lista `cambios` (solo campos que cambiaron, con etiquetas y valores legibles; los secretos van marcados y sin valor). Si está vacía, el rasgo no escribe nada. |
| Sincronización | `sincronizacion(Model $principal, array $resultadoSync, string $accionResumen, string $accionAgregado, string $accionQuitado, array $extra = []): void` | Con el resultado de `sync()`/`attach()`/`detach()`: si no hubo agregados ni quitados, no hace nada; si hubo, escribe una entrada resumen en el principal (con `agregados` y `quitados`) y una entrada por cada persona afectada (relacionado = principal), todas con el mismo `lote`. |
| Acción con nombre | `accion(string $accion, Closure $operacion): mixed` | Mientras corre `$operacion`, las entradas `updated` que generen los modelos usan `$accion` en lugar de la acción por defecto. Se puede anidar. |
| Actor especial | `comoActor(string $actor, string $nombre, Closure $operacion, ?User $provocadoPor = null): mixed` | Mientras corre `$operacion`, las entradas usan ese actor (`qr`, `automatico`, `sistema`). |
| Silencio | `sinRegistro(Closure $operacion): mixed` | Solo para migraciones de datos y pruebas: no escribe entradas. |

## Garantías

1. **Atomicidad**: la entrada se escribe en la conexión y la transacción en curso; si la transacción se revierte, la entrada también (RF-005).
2. **Sin entradas vacías**: una edición sin diferencias no escribe nada (escenario 7 de la historia 2).
3. **Secretos**: nunca se guardan los valores de los campos secretos (RF-007).
4. **Fotos**: `actor_nombre`, `sujeto_nombre` y `relacionado_nombre` se guardan en el momento (RF-009).
5. **Idempotencia de acciones masivas**: guardar dos veces la misma asistencia no escribe entradas nuevas, porque `sync()` no devuelve diferencias.
6. **Inalterabilidad**: no hay operación para editar ni borrar entradas (RF-008).

## Puntos del código que pasan a usarlo

| Lugar | Hoy | Después |
|-------|-----|---------|
| `LiderazgoService::registrarCambio()` | Crea `PersonaCambio` | Se elimina; sus llamadas pasan a `Historial::registrar()` o a `Historial::accion()` + rasgo. |
| `SedeService`, `RolService`, `NivelesService` | Crean `SedeCambio`, `RolCambio`, `NivelCambio` | `Historial::registrar()` (los cambios de permisos de un rol se guardan como `cambios` por módulo). |
| `AvancesNivelService` | Escribe en `persona_cambios` | Rasgo en `AvanceNivel` + `comoActor('automatico', …)` en la sincronía con encuentros. |
| `EncuentrosController` (`store`, `update`, `actualizarAsistencia`) | Sin historial | `sincronizacion()` para inscritos; entradas `asistio_encuentro` / `no_asistio_encuentro` / `pago_encuentro` por persona y una entrada resumen `asistencia_actualizada`. |
| `MinisteriosController` (`store`, `update`, `actualizarIntegrantes`) | Sin historial | `sincronizacion()`. |
| `CelulasController` (miembros, reuniones, asistencia) | Sin historial | `sincronizacion()` y rasgo en `CelulaReunion`. |
| `EventosController` (inscripciones y abonos) | Sin historial | Rasgo en `EventoPersona` y `EventoPago` (doble sujeto: evento y persona). |
| `UsersController`, `ClavesUsuarioController`, `CuentaController`, `RecuperacionClaveController`, `SolicitudesClaveController` | Sin historial | Rasgo en `User` y `SolicitudClave`; claves como secreto. |
| `RegistroPorQr`, `RegistroMiembrosQr` | Parcial | `comoActor('qr', …)` + rasgo + `sincronizacion()`. |
| `TrasladoService` | `registrarCambio` | Acciones `trasladado_a` / `trasladado_desde` y `trasladada_desde_persona_id`. |
| `MigracionSedes`, `MigracionRedesZonas`, seeders | — | `sinRegistro()` o actor `sistema`. |

## Prueba de cobertura

Una prueba de contrato recorre una lista de **todas las acciones de escritura** (una por ruta `POST`, `PUT` o `DELETE` del archivo de rutas, más los registros públicos por QR), ejecuta cada una con datos válidos y comprueba que se creó al menos una entrada con el usuario, la acción y el sujeto esperados. Si se agrega una ruta de escritura nueva sin caso en la lista, la prueba falla (CE-001).
