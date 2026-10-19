# Modelo de datos (Fase 1)

Cambios sobre el esquema actual de `personas`, `celulas`, `celula_persona` y `users`. Los nombres de campos son identificadores de código y no se traducen.

## Persona (`personas`) — modificada

| Campo | Tipo | Notas |
|-------|------|-------|
| id | clave | Interno; no se muestra al usuario |
| nombres, apellidos | texto | Obligatorios |
| tipo_documento | texto, **nullable** | Valores: CC, TI, CE, Pasaporte |
| numero_documento | texto, **nullable**, único | Obligatorio si `estado = miembro_activo` o `es_lider` |
| fecha_nacimiento | fecha, nullable | Se muestra legible + edad |
| celular, email | texto, nullable | Al menos uno obligatorio al crear (FR-005) |
| direccion, barrio, ciudad | texto, nullable | Sin cambios |
| **estado** | texto | `visitante` (defecto al crear), `nuevo_creyente`, `miembro_activo`, `inactivo`; índice |
| lider_id | referencia a persona, nullable | **Fuente de verdad** del vínculo persona–líder; sin ciclos |
| es_lider | booleano | Sin cambios |
| **red_id** | referencia a `redes`, nullable | Solo si `es_lider`; reemplaza el texto `red` |
| **zona_id** | referencia a `zonas`, nullable | Solo líderes; opcional |
| **inactivado_en** | fecha, nullable | Fecha en que pasó a `inactivo` |
| deleted_at | fecha, nullable | Borrado definitivo lógico (solo admin, solo sin historial) |
| timestamps | | Sin cambios |

**Eliminado (en migración posterior)**: columna de texto `red`.

**Validaciones**
- `lider_id` ≠ `id` y sin ciclos al recorrer la cadena de líderes.
- `lider_id` debe apuntar a una persona con `es_lider = true`.
- Duplicados: mismo documento → error; mismo nombre completo + celular → advertencia con confirmación.
- `red_id` obligatorio cuando `es_lider = true`; `zona_id` opcional.
- El destino de `lider_id` debe estar dentro del ámbito del usuario que guarda (FR-018).

**Transiciones de estado**
`visitante → nuevo_creyente → miembro_activo → inactivo → (reactivar) miembro_activo`; cualquier estado puede pasar a `inactivo`; reactivar devuelve al estado anterior guardado en la bitácora o a `miembro_activo`.

**Índices**: (`apellidos`,`nombres`), `celular`, `estado`, `lider_id`, `zona_id`.

## Red (`redes`) — nueva

| Campo | Tipo | Notas |
|-------|------|-------|
| id | clave | |
| nombre | texto, único (sin distinguir mayúsculas ni tildes) | Ej. Jóvenes, Matrimonios |
| activa | booleano | Las inactivas no se ofrecen en selectores pero conservan sus líderes |
| timestamps | | |

Relación: una red tiene muchos líderes (`personas.red_id`). Acción "fusionar" mueve los líderes de una red a otra y elimina la origen.

## Zona (`zonas`) — nueva

| Campo | Tipo | Notas |
|-------|------|-------|
| id | clave | |
| nombre | texto, único | Sector geográfico |
| activa | booleano | |
| timestamps | | |

Relación: una zona tiene muchos líderes (`personas.zona_id`). No tiene supervisor.

## Célula (`celulas`) y pertenencia (`celula_persona`) — reglas nuevas, sin cambio de campos

- Un miembro solo puede ser una persona con `lider_id` = `celulas.lider_id` o `celulas.co_lider_id`.
- Al cambiar `lider_id` de una persona, se retira de las células del líder anterior (o se exige elegir otra del nuevo líder).
- Una célula no puede quedar con un líder que ya no es `es_lider` (bloquea quitar el rol; ver FR-013).

## Bitácora (`persona_cambios`) — nueva

| Campo | Tipo | Notas |
|-------|------|-------|
| id | clave | |
| persona_id | referencia a persona | Persona afectada |
| user_id | referencia a usuario, nullable | Quién hizo el cambio |
| accion | texto | `creada`, `editada`, `estado`, `lider`, `rol_lider`, `inactivada`, `reactivada`, `eliminada` |
| antes, despues | JSON, nullable | Solo campos modificados |
| created_at | fecha | |

Visible para pastor y administrador en la ficha de la persona (FR-023).

## Usuario (`users`) — sin cambio de esquema

Se retira el rol `lider_zona` (los usuarios pasan a `lider_celula`) y la etiqueta de `lider_celula` pasa a "Líder". `persona_id` sigue vinculando usuario y persona.

## Reglas de ámbito (resumen)

| Rol | Personas visibles |
|-----|-------------------|
| admin, pastor | Todas |
| lider_celula | Personas con `lider_id` = su persona; miembros de sus células; él mismo |
| user | Solo su propio registro |

## Migración de datos existentes

1. Crear tablas `redes`, `zonas`, `persona_cambios` y columnas nuevas.
2. `red` (texto) → `redes`, normalizando espacios, mayúsculas y tildes; asignar `red_id` a cada líder.
3. Los usuarios con rol `lider_zona` pasan a `lider_celula`.
4. `estado = miembro_activo` para todas las personas actuales.
5. Para miembros de célula sin `lider_id` o con `lider_id` distinto del líder de la célula: informar en un reporte de la migración (no se sobrescribe automáticamente si hay conflicto; quedan en "Sin líder asignado").
6. Migración posterior separada: eliminar la columna `red`.
