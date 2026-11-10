# Modelo de Datos: Corregir errores del registro por QR y exigir celular y documento

Sin migraciones de esquema (ver research.md D1/D9). Todos los cambios son de validación y de nuevos valores permitidos en columnas `string` ya existentes.

## `personas` (sin cambios de estructura)

Las columnas relevantes ya existen y siguen nulas a nivel de base de datos:

| Campo | Tipo actual | Qué cambia |
|-------|-------------|------------|
| `celular` | cadena, nula, índice | Pasa a ser **obligatorio** en todo formulario de alta (QR o panel interno); en una ficha ya existente solo se rellena si está vacía, nunca se sobrescribe (research D3). |
| `tipo_documento` | cadena, nula | Pasa a ser **obligatorio** junto con `numero_documento` en todo formulario de alta; se rellena solo si la ficha no tiene documento (regla ya vigente desde la funcionalidad 012). |
| `numero_documento` | cadena, nula, única por `(sede_id, numero_documento)` | Igual que `tipo_documento`. La unicidad por sede ya existente no cambia. |

Fichas creadas **antes** de esta funcionalidad pueden seguir con estos campos vacíos hasta que alguien las encuentre por un QR o las edite desde el panel interno (sin migración retroactiva, por decisión del responsable).

## Datos de entrada de los formularios (no persistentes por sí mismos)

### QR de eventos/encuentros/células (`RegistroQrRequest`, funcionalidad 009)

| Campo | Antes | Ahora |
|-------|-------|-------|
| `celular` | Obligatorio | Sin cambio |
| `tipo_documento` | Obligatorio solo si se escribe `numero_documento` | **Obligatorio siempre** |
| `numero_documento` | Opcional | **Obligatorio siempre** |
| `nombres`, `apellidos`, `fecha_nacimiento` | Obligatorios | Sin cambio |

### QR de miembros (`RegistroMiembroQrRequest`, funcionalidad 012)

Misma tabla de cambios que arriba para `tipo_documento`/`numero_documento`; `email`, `direccion`, `barrio`, `ciudad` y `estado_inicial` no cambian.

### Panel interno — alta y edición de miembro (`PersonaRequest`)

| Campo | Antes | Ahora |
|-------|-------|-------|
| `celular` | Obligatorio solo si no hay `email` (alternativa) | **Siempre obligatorio** |
| `email` | Obligatorio solo si no hay `celular` (alternativa) | Sigue existiendo, pero **ya no sustituye** al celular; queda opcional sin más |
| `tipo_documento` | No existía en el formulario ni en la validación | **Nuevo, obligatorio** junto con `numero_documento` |
| `numero_documento` | No existía en el formulario ni en la validación | **Nuevo, obligatorio** |

`PersonaRequest::reglasDeAlta()` (reutilizada por `UsersController` para «Nuevo usuario», funcionalidad 016) gana las mismas reglas de `tipo_documento`/`numero_documento`.

`numero_documento` gana además una regla `unique` por sede en el propio formulario (`Rule::unique('personas', 'numero_documento')->where('sede_id', …)->ignore($persona)`), espejo de la restricción `personas_sede_documento_unique` que ya existe en la base de datos pero que, al no validarse nunca en `PersonaRequest` (el campo no existía en el formulario), hoy terminaría en una `QueryException` sin capturar si alguien escribe un documento repetido.

### Alta rápida de miembro desde célula (`PersonasController::rapida`)

Mismo cambio que en `PersonaRequest`: `celular` siempre obligatorio, `tipo_documento`/`numero_documento` obligatorios y nuevos en el formulario.

## `registros_qr` (funcionalidad 009) — sin cambios de columnas

| Campo | Cambia |
|-------|--------|
| `resultado` (`string`, máx. 20) | Gana dos valores nuevos: `ya_registrado` (envío repetido idéntico, research D7) y `dato_completado` (persona existente a la que se le completó celular o documento faltante, research D4). Los valores existentes (`nueva`, `existente`, `de_otra_sede`, `pendiente`) no cambian de significado. |
| `persona_id` | El caso `de_otra_sede` deja de guardarse siempre con `persona_id` nulo: ahora sí se crea la persona nueva en la sede del QR y se enlaza (research D5). |

`RegistroQr::RESULTADOS` pasa de `['nueva', 'existente', 'de_otra_sede', 'pendiente']` a incluir también `'ya_registrado'` y `'dato_completado'`, con sus etiquetas en `etiquetaResultado()`.

## `registros_miembros_qr` (funcionalidad 012) — sin cambios de columnas

| Campo | Cambia |
|-------|--------|
| `resultado` (`string`, máx. 20) | Gana el valor `dato_completado` (research D3/D9), con el mismo criterio: se usa cuando el motivo del cambio fue justamente completar el celular o el documento que faltaba, sin importar si además cambió algo más. |

`RegistroMiembroQr::RESULTADOS` gana la entrada `'dato_completado' => 'Dato completado'`.

## Reglas de relleno de huecos (ambos servicios)

1. Un campo de identificación (`celular`, `tipo_documento` + `numero_documento`) solo se escribe sobre una persona existente si **está vacío** en la ficha; si ya tiene un valor guardado, nunca se sobrescribe.
2. El resto de las reglas de actualización ya vigentes no cambian: en el QR de miembros (012) se siguen actualizando los demás campos editables cuando difieren; en el QR de eventos/encuentros/células (009) **ningún otro campo** de una persona existente se modifica — las diferencias siguen quedando solo como nota de corrección para el administrador.
3. Reactivar a un miembro inactivo o eliminado (012) sigue sin cambios; el relleno de celular/documento se aplica igual durante la reactivación si corresponde.

## Entidades y relaciones (sin cambios de forma)

```text
Sede 1 ──── n RegistroQr n ──── 0..1 Persona           (+ ya_registrado, dato_completado; de_otra_sede ahora sí enlaza persona_id)
Sede 1 ──── n RegistroMiembroQr n ──── 0..1 Persona    (+ dato_completado)
Sede 1 ──── n Persona 1 ──── n PersonaCambio            (celular y documento ahora también pueden aparecer como campo rellenado en acciones *_qr / editada)
```
