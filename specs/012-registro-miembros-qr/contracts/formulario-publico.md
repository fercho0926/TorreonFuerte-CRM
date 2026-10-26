# Contrato: Formulario público de registro de miembros

Rutas públicas (sin sesión). El token de la sede (32 caracteres) identifica la sede y es la única llave.

| Método | Ruta | Nombre | Descripción |
|--------|------|--------|-------------|
| GET | `/registro-sede/{token}` | `registro-sede.show` | Paso 1: pide celular y cédula opcional. 404 si el token no existe; página «registro cerrado» si está cerrado o la sede inactiva. |
| POST | `/registro-sede/{token}/buscar` | `registro-sede.buscar` | Busca al miembro en la sede. Con `Accept: application/json` devuelve JSON (precarga); sin JavaScript devuelve el paso 2 renderizado. |
| POST | `/registro-sede/{token}` | `registro-sede.store` | Crea, actualiza o reactiva. Redirige a `listo`. |
| GET | `/registro-sede/{token}/listo` | `registro-sede.listo` | Confirmación según el resultado guardado en sesión. |

Todas las respuestas llevan `Cache-Control: no-store, private`. Los errores de las rutas JSON responden JSON (el manejador global solo lo hace para `api/*`).

## POST `…/buscar`

Entrada: `celular` (obligatorio si no hay `numero_documento`), `numero_documento` (opcional).

Respuesta JSON 200:

```json
{
  "estado": "existente",
  "nombres": "Ana", "apellidos": "Pérez",
  "fecha_nacimiento": "1990-05-04",
  "celular": "3105559999",
  "tipo_documento": "CC", "numero_documento": "1020304050",
  "email": "ana@example.com", "direccion": "Calle 1 # 2-3", "barrio": "Centro", "ciudad": "Bogotá"
}
```

`estado`: `nueva` (incluye a quien solo existe en otra sede; sin datos precargados), `existente` (con todos los campos arriba; los vacíos se omiten), `pendiente` (inactivo o eliminado: devuelve también sus datos para que los revise y se reactivará al enviar), `conflicto` (con `mensaje`; sin datos).

Otras respuestas: `409 {"estado":"cerrado"}`, `404 {"estado":"no_valido"}`, `422` con `errors` por campo, `429 {"estado":"limite"}` con `Retry-After`.

## POST `…/{token}`

Campos: `celular`*, `nombres`*, `apellidos`*, `fecha_nacimiento`*, `estado_inicial`* (solo se exige si el miembro no existe), `tipo_documento`, `numero_documento`, `email`, `direccion`, `barrio`, `ciudad`. Validaciones en [data-model.md](../data-model.md).

Resultado guardado en sesión y mostrado en `listo`: `nueva` («Listo, te registramos»), `actualizada` (lista de datos actualizados), `reactivada` («Bienvenido de nuevo» + datos actualizados), `sin_cambios` («tus datos ya estaban al día»).

Errores: 422 con mensajes junto al campo (conflicto de identificación en `celular`); 429 al superar el límite.

## Límite de intentos

5 peticiones por minuto por conexión entre `buscar` y `store` (clave única `registro-miembros:{ip}`). Constante única configurable.

## Reglas transversales

- Toda consulta de miembros corre en la sede del token; un miembro de otra sede nunca se lee, precarga ni modifica y no se revela que existe.
- Nunca se vacía un dato guardado; nunca se cambia celular, estado (salvo reactivación), líder, células, rol de liderazgo ni sede.
- Sin JavaScript el flujo de dos pasos funciona completo; con JavaScript la precarga ocurre al salir del campo de celular o cédula.
- El formulario respeta las reglas móviles (controles ≥ 44 px, letra ≥ 16 px, teclado `tel`/`numeric`/`email` por campo) y no guarda datos en el dispositivo.
