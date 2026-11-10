# Contrato (delta): Formularios públicos de registro por QR

Este documento describe **solo lo que cambia** respecto a los contratos ya existentes de las funcionalidades 009 (`specs/009-registro-qr-eventos/contracts/rutas-registro.md`) y 012 (`specs/012-registro-miembros-qr/contracts/formulario-publico.md`). Las rutas, nombres y demás comportamiento no mencionados aquí siguen igual.

## Validación de entrada — `POST …/buscar` y `POST …/{token}` (eventos/encuentros/células y miembros)

| Campo | Antes | Ahora |
|-------|-------|-------|
| `tipo_documento` | Obligatorio solo si se escribe `numero_documento` | **Obligatorio siempre** |
| `numero_documento` | Opcional | **Obligatorio siempre** |

`celular` ya era obligatorio y no cambia. Los mensajes de error siguen junto al campo, en español (sin cambios en el formato de respuesta 422).

## Respuesta de precarga — `POST …/buscar` (JSON)

Sin cambios de forma: sigue devolviendo solo los campos que la ficha tenga guardados (`array_filter` ya existente). Si a la ficha encontrada le falta `celular`, `tipo_documento` o `numero_documento`, esos campos simplemente no vienen en la respuesta — el formulario los muestra vacíos y, al ser ahora obligatorios, exige completarlos antes de enviar (research D2).

## Resultado del envío — `POST …/{token}` (eventos/encuentros/células)

Nuevo valor posible de `resultado`, además de los ya documentados (`nueva`, `existente`, `de_otra_sede`, `pendiente`):

- **`ya_registrado`**: el celular ya quedó inscrito a este mismo destino por un envío anterior (incluido un doble envío casi simultáneo, research D6/D7). Se trata como **éxito**, no como error: la redirección a `…/listo` y la pantalla de confirmación muestran «ya quedaste registrado», sin pedir reintentar nada.

Cambio de comportamiento en `de_otra_sede` (antes no creaba ni inscribía a nadie — bug corregido, research D5): ahora se crea la persona nueva en la sede del QR (con celular y documento, como cualquier persona nueva) y se inscribe, igual que `nueva`; la ficha de la otra sede sigue sin leerse ni modificarse.

Nuevo mensaje posible en una respuesta `422` con conflicto de identidad: sin cambios (se mantiene el mismo mensaje de `conflicto` ya documentado).

Nueva respuesta posible, **`503`** (o el código que ya use el proyecto para "intenta de nuevo"), cuando el sistema no logra resolver un choque temporal dentro del tiempo de espera del bloqueo (research D8, caso poco frecuente de RF-007):

```json
{ "estado": "reintentar", "mensaje": "Demasiados intentos; espera un momento y vuelve a intentarlo." }
```

Para peticiones no JSON, una respuesta de texto equivalente con `Retry-After`, reutilizando el mismo patrón ya usado para el límite de frecuencia (`demasiados()`). Este intento cuenta contra el límite de frecuencia ya existente (no hay contador nuevo).

## Resultado del envío — `POST …/{token}` (miembros)

Nuevo valor posible de `resultado`, además de los ya documentados (`nueva`, `actualizada`, `reactivada`, `sin_cambios`):

- **`dato_completado`**: el envío completó el celular o el documento que le faltaba a una persona existente (research D3/D4/D9), sin importar si además cambió algún otro campo. La pantalla de confirmación muestra un mensaje propio (análogo a «actualizada», con la lista de lo completado).

Mismo caso de `503`/reintento que en la tabla anterior, por el mismo motivo (research D8).

## Reglas transversales (sin cambios, se reafirman)

- Nunca se sobrescribe un dato ya guardado; el relleno de huecos (celular/documento) solo aplica cuando el campo estaba vacío.
- Ninguna consulta ni envío lee o modifica una ficha de otra sede.
- Las respuestas públicas siguen con `Cache-Control: no-store, private`.
