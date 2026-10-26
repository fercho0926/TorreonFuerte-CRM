# Modelo de Datos: Autorregistro y actualización de miembros por código QR

Una migración: `2026_10_27_000001_add_registro_miembros_a_sedes.php` (columnas nuevas en `sedes` y tabla nueva `registros_miembros_qr`). `personas` y `persona_cambios` no cambian de estructura.

## Cambios en tablas existentes

### `sedes`

| Campo | Tipo | Reglas |
|-------|------|--------|
| `registro_token` | cadena de 32, única | Se rellena para las sedes existentes en la migración; se genera aleatorio al crear una sede; se renueva al regenerar. No adivinable. |
| `registro_abierto` | booleano, por defecto verdadero | El registro efectivo está abierto solo si es verdadero **y** la sede está activa. |

### `persona_cambios` (sin cambios de estructura)

Se añaden tres valores a `PersonaCambio::ACCIONES` (la columna `accion` es una cadena libre):

| Acción | Etiqueta | Cuándo |
|--------|----------|--------|
| `creada_qr` | Creado por QR de miembros | Miembro nuevo registrado desde el formulario |
| `actualizada_qr` | Actualizado por QR de miembros | Cambió al menos un dato de un miembro existente |
| `reactivada_qr` | Reactivado por QR de miembros | Miembro inactivo o eliminado vuelto a la actividad |

`user_id` es nulo (no hay sesión). `antes` y `despues` guardan solo los campos modificados.

## Tabla nueva: `registros_miembros_qr`

Bitácora de cada envío del formulario público.

| Campo | Tipo | Reglas |
|-------|------|--------|
| `id` | entero | |
| `sede_id` | entero, FK a `sedes` (borrado en cascada) | Con el filtro global de sede (`PerteneceASede`) |
| `persona_id` | entero nulo, FK a `personas` (a nulo al borrar) | Ficha creada, actualizada o reactivada; nulo si no hay ficha |
| `nombres` | cadena máx. 100 | Lo que escribió la persona (requerido) |
| `apellidos` | cadena máx. 100 | Lo que escribió la persona (requerido) |
| `celular` | cadena máx. 20 | Solo dígitos, sin indicativo 57 |
| `resultado` | cadena máx. 20 | `nueva`, `actualizada`, `reactivada` o `sin_cambios` |
| `cambios` | JSON nulo | `{campo: [antes, después]}` de lo que se modificó; nulo si `nueva` o `sin_cambios` |
| `created_at`, `updated_at` | marcas de tiempo | `created_at` es la fecha y hora del registro |

Índices: `(sede_id, created_at)` para la lista ordenada. No hay restricción de unicidad por celular (una persona puede actualizar varias veces).

## Entidades y relaciones

```text
Sede 1 ──── n RegistroMiembroQr n ──── 0..1 Persona
Sede 1 ──── n Persona 1 ──── n PersonaCambio (acciones *_qr)
```

## Datos del formulario (entrada, no persistentes por sí mismos)

| Campo | Obligatorio | Validación |
|-------|-------------|------------|
| `celular` | Sí | Formato de `Celular::regla()` (mínimo 10 dígitos); llave de búsqueda; no se cambia en un miembro existente |
| `numero_documento` | No | Máx. 30; con `tipo_documento` obligatorio si se escribe |
| `tipo_documento` | Con cédula | Uno de `Persona::TIPOS_DOCUMENTO` |
| `nombres`, `apellidos` | Sí | Máx. 100; se limpian espacios |
| `fecha_nacimiento` | Sí | Fecha válida, no futura, desde 1900-01-01 |
| `estado_inicial` | Solo miembro nuevo | `visitante` o `miembro_activo` |
| `email` | No | Correo válido, máx. 255 |
| `direccion` | No | Máx. 255 |
| `barrio` | No | Máx. 255 (columna actual de `personas`) |
| `ciudad` | No | Máx. 255 (columna actual de `personas`) |

`email`, `direccion`, `barrio` y `ciudad` son cadenas de 255 en `personas`; el formulario usa ese mismo máximo.

## Resultados del servicio (estado de la búsqueda → acción)

| Situación | Búsqueda | Resultado guardado | Efecto en `personas` |
|-----------|----------|--------------------|----------------------|
| No existe en la sede (ni en otra) | `nueva` | `nueva` | Crea con `estado_inicial` |
| No existe en la sede, existe en otra | `de_otra_sede` (se trata como nueva) | `nueva` | Crea en la sede del QR; la otra ficha no se toca |
| Existe y está activo, hay cambios | `existente` | `actualizada` | Actualiza solo los campos que cambiaron |
| Existe y está activo, sin cambios | `existente` | `sin_cambios` | Ninguno |
| Existe inactivo o eliminado | `pendiente` | `reactivada` | Restaura si estaba eliminado, reactiva (estado previo) y actualiza campos |
| Cédula y celular de miembros distintos, o celular repetido | `conflicto` | (no se guarda) | Ninguno; el formulario muestra el mensaje |

## Reglas de actualización (campos)

1. Se ignoran los campos vacíos (nunca se borra un dato guardado).
2. Se comparan sin mayúsculas ni espacios sobrantes; si son iguales no cuenta como cambio.
3. `tipo_documento` y `numero_documento` solo se escriben si la ficha no tiene cédula.
4. No se escriben `celular`, `estado` (salvo reactivación), `lider_id`, `es_lider`, células, ministerios ni `sede_id`.
5. Todo el proceso (búsqueda, escritura, historial y registro) va en una transacción dentro de `conSede(sede del QR)`.
