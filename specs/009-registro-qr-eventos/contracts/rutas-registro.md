# Contrato de rutas: Registro por QR

## Rutas públicas (sin sesión, con límite de frecuencia)

| Método | Ruta | Nombre | Descripción |
|---|---|---|---|
| GET | `/registro/{token}` | `registro.show` | Formulario. Si el destino está cerrado → vista «registro cerrado»; token inexistente → 404 genérico («enlace no válido») |
| POST | `/registro/{token}/buscar` | `registro.buscar` | Paso 1 sin JS y precarga con JS. Entrada: `celular`, `numero_documento` (opcional) |
| POST | `/registro/{token}` | `registro.store` | Envía el registro |
| GET | `/registro/{token}/listo` | `registro.listo` | Confirmación con el nombre del destino |

### `POST /registro/{token}/buscar`

Entrada: `celular` (obligatorio si no hay cédula), `numero_documento` (opcional).

Respuesta JSON (si `Accept: application/json`) o paso 2 del formulario (sin JS):

```json
{ "estado": "nueva" }
{ "estado": "existente", "nombres": "Ana", "apellidos": "Pérez", "fecha_nacimiento": "1990-05-04", "celular": "3105559999" }
{ "estado": "conflicto", "mensaje": "La cédula y el celular pertenecen a personas distintas; contacta al administrador." }
{ "estado": "pendiente", "mensaje": "El administrador te contactará." }
```

- Para una persona que existe solo en otra sede responde `{ "estado": "nueva" }` (no revela que existe).
- Nunca devuelve documento, correo ni dirección.
- Errores de validación y de límite (429) responden en JSON cuando se pide JSON.

### `POST /registro/{token}`

Entrada: `celular`, `nombres`, `apellidos`, `fecha_nacimiento`, `tipo_documento`, `numero_documento`.

Salidas: redirección a `registro.listo`; errores de validación (422/redirección con errores); 409 lógico → mensaje de «ya estás registrado» (FR-008); 429 al exceder el límite.

## Rutas de administración (con sesión)

| Método | Ruta | Nombre | Descripción |
|---|---|---|---|
| GET | `/registro-qr/{tipo}/{id}/qr.svg` | `registro-qr.svg` | Descarga del QR (SVG) |
| POST | `/registro-qr/{tipo}/{id}/abrir` | `registro-qr.abrir` | Abre o cierra el registro |
| POST | `/registro-qr/{tipo}/{id}/regenerar` | `registro-qr.regenerar` | Nuevo token; el anterior deja de funcionar |

`{tipo}` ∈ `evento`, `encuentro`, `celula`. Exigen la habilidad `gestionarRegistroQr`; fuera del alcance de sede/rol → 404 o 403. El panel con QR y lista de registrados está dentro de las fichas existentes (`eventos.show`, `encuentros.show`, `celulas.show`).

## Cabeceras y caché

Las respuestas de `/registro/*` llevan `Cache-Control: no-store` y el service worker no las guarda.
