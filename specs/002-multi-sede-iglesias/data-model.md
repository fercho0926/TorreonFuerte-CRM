# Modelo de datos (Fase 1): Multi-sede

Cambios sobre el esquema vigente (incluye lo creado en la funcionalidad 001). Los nombres de campos son identificadores de código y no se traducen.

## Sede (`sedes`) — nueva

| Campo | Tipo | Notas |
|-------|------|-------|
| id | clave | |
| nombre | texto, **único** | Obligatorio, máx. 255 |
| ciudad | texto | Obligatorio, máx. 255 |
| direccion, telefono, email | texto, nullable | Datos de contacto |
| activa | booleano | Defecto `true`; inactiva = sin acceso de sus usuarios y sin escrituras |
| pastor_persona_id | referencia a `personas`, nullable | Persona del pastor (en esta sede) |
| pastora_persona_id | referencia a `personas`, nullable | Persona de la pastora (opcional) |
| timestamps | | |

**Reglas**: no se elimina una sede que tenga datos (solo se desactiva); `pastor_persona_id` y `pastora_persona_id` deben pertenecer a la propia sede. El modelo `Sede` **no** lleva filtro global (es del módulo exclusivo del admin).

## Bitácora de sedes (`sede_cambios`) — nueva

| Campo | Tipo | Notas |
|-------|------|-------|
| id | clave | |
| sede_id | referencia a `sedes` | |
| user_id | referencia a `users`, nullable | Quién hizo el cambio |
| accion | texto | `creada`, `editada`, `desactivada`, `reactivada`, `pastor_cambiado`, `pastora_cambiada` |
| antes, despues | JSON, nullable | Solo campos modificados |
| created_at | fecha | |

Visible solo para el admin.

## Columna `sede_id` en las tablas de datos de la iglesia

Clave foránea a `sedes`, **no nula** (tras la migración), con índice. Modelos con el trait `PerteneceASede` (filtro global + asignación automática):

| Tabla | Índices / unicidad nuevos |
|-------|---------------------------|
| `personas` | `(sede_id, apellidos, nombres)`; `(sede_id, estado)`; **único** `(sede_id, numero_documento)` (reemplaza el único global) |
| `redes` | **único** `(sede_id, clave)` (reemplaza el único global de `clave`) |
| `zonas` | **único** `(sede_id, clave)` |
| `celulas` | `(sede_id, nombre)` |
| `celula_reuniones` | `(sede_id, fecha)` |
| `encuentros` | `(sede_id, fecha_realizacion)` |
| `eventos` | `(sede_id, fecha)` |
| `evento_persona` (inscripciones) | `sede_id` |
| `evento_pagos` | `sede_id` |
| `ministerios` | `(sede_id, nombre)` |
| `persona_cambios` | `(sede_id, persona_id)` |

**Tablas puente sin `sede_id`**: `celula_persona`, `encuentro_persona`, `ministerio_persona`, `celula_reunion_asistencia`. Se aíslan porque sus dos extremos tienen filtro; al asociar se valida que ambos pertenezcan a la misma sede.

**Reglas transversales**
- Un registro nuevo toma el `sede_id` de la sede efectiva del usuario; si no hay sede efectiva (admin en "Todas las sedes"), la creación se rechaza.
- Ninguna relación puede cruzar sedes (líder, célula, red, zona, ministerio, evento, persona): las claves foráneas lógicas se validan dentro de la sede.
- Los hijos heredan la sede del padre al crearse (reunión ← célula; inscripción ← evento; pago ← inscripción; cambio ← persona).

## Usuario (`users`) — modificado

| Campo | Tipo | Notas |
|-------|------|-------|
| **sede_id** | referencia a `sedes`, **nullable** | `null` solo para `admin`; obligatorio para el resto de roles |
| role | texto | Valores: `admin`, `pastor`, `lider_celula`, `user`. **`pastor` incluye a la pastora** (mismos permisos) |
| email | texto, **único global** | Sin cambios: es la credencial de acceso |

**Reglas**
- Un usuario no administrador pertenece a **una sola** sede; no se cambia su sede por formulario de pastor.
- Un pastor solo crea cuentas `lider_celula` y `user` de su propia sede; solo el admin crea o cambia cuentas `pastor` y `admin`.
- Un usuario no admin con sede inactiva o sin sede **no inicia sesión** (o se le cierra la sesión).
- `persona_id` del usuario debe pertenecer a su misma sede.

## Persona (`personas`) — campos adicionales por traslado

| Campo | Tipo | Notas |
|-------|------|-------|
| trasladada_a_sede_id | referencia a `sedes`, nullable | Se llena en el registro de **origen** tras un traslado |
| trasladada_en | fecha, nullable | Fecha del traslado |

**Traslado** (ver `research.md` D11): el registro de origen queda `inactivo` con estos campos y conserva su historial; en la sede destino se crea un registro nuevo (mismo nombre, documento y datos de contacto; mismo estado), **sin líder, sin célula y sin ministerios**.
**Validaciones del traslado**: la persona no puede ser líder con personas o células a cargo ni encargada de un ministerio; el documento no debe existir ya en la sede destino; la sede destino debe estar activa y ser distinta de la origen.

## Contexto de sede (no persistente)

| Concepto | Dónde vive | Regla |
|----------|------------|-------|
| Sede efectiva | `SedeContext` por petición | No admin: `users.sede_id`; admin: sede activa en sesión |
| Sede activa del admin | Sesión (`sede_activa_id`) | `null` = "Todas las sedes" (solo lectura) |

## Reglas de ámbito por rol (resumen)

| Rol | Sedes | Dentro de la sede |
|-----|-------|-------------------|
| admin | Todas (sede activa o "Todas las sedes" de solo consulta); único con módulo Sedes | Todo |
| pastor / pastora | Solo la suya | Todo; gestiona catálogos y usuarios (líderes y usuarios) |
| lider_celula | Solo la suya | Sus personas y células (como hoy) |
| user | Solo la suya | Solo su registro |

## Migración de datos existentes

1. Crear `sedes`, `sede_cambios` y las columnas `sede_id` nullable (y `trasladada_*` en personas).
2. Crear la **primera sede** (nombre configurable, defecto "Sede principal") y asignarle todas las filas existentes de las 11 tablas de datos y todos los usuarios que no sean `admin`.
3. Los usuarios `pastor` actuales quedan como pastores de la primera sede y dejan de ver otras sedes; `sedes.pastor_persona_id` se llena con la persona del primer pastor que tenga persona vinculada.
4. Volver `sede_id` no nulo (salvo en `users`), añadir índices y reemplazar las unicidades globales por las de arriba.
5. Reporte de conteos por tabla antes y después (SC-004) y verificación de cero filas sin sede (SC-007).
