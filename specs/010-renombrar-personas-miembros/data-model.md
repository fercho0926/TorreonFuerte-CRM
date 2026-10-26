# Modelo de Datos: Renombrar "Personas" a "Miembros"

No se agregan ni se quitan columnas ni relaciones: solo cambian nombres. La entidad **Miembro** conserva significado, atributos y relaciones de la antigua **Persona**.

## Renombrado de tablas

| Antes | Después |
|---|---|
| `personas` | `miembros` |
| `persona_cambios` | `miembro_cambios` |
| `evento_persona` | `evento_miembro` |
| `encuentro_persona` | `encuentro_miembro` |
| `celula_persona` | `celula_miembro` |
| `ministerio_persona` | `ministerio_miembro` |

## Renombrado de columnas

| Tabla | Antes | Después |
|---|---|---|
| `users` | `persona_id` | `miembro_id` |
| `persona_cambios` / `miembro_cambios` | `persona_id` | `miembro_id` |
| `evento_miembro`, `encuentro_miembro`, `celula_miembro`, `ministerio_miembro` | `persona_id` | `miembro_id` |
| `registros_qr` | `persona_id` | `miembro_id` |
| `evento_pagos` | `evento_persona_id` | `evento_miembro_id` |
| `sedes` | `pastor_persona_id`, `pastora_persona_id` | `pastor_miembro_id`, `pastora_miembro_id` |

(La lista definitiva se confirma contra el esquema real al implementar la migración; el criterio es toda columna cuyo nombre contenga `persona`.)

## Índices y claves

- Índices con nombre explícito (p. ej. `cambios_sede_persona_index`) se renombran a `…_miembro_…`.
- Las claves foráneas siguen a la tabla/columna renombrada; el nombre autogenerado antiguo puede permanecer (cosmético) si el motor lo permite.

## Modelos

| Antes | Después | Notas |
|---|---|---|
| `Persona` | `Miembro` | `Miembro::liderados()` reemplaza a `Persona::miembros()` (miembros a cargo de un líder) |
| `PersonaCambio` | `MiembroCambio` | |
| `EventoPersona` | `EventoMiembro` | |
| `Celula::miembros()` | `Celula::miembros()` | sin cambio de nombre; ahora apunta a `Miembro` |

## Valores almacenados que NO cambian

- Estado `miembro_activo` (etiqueta «Miembro activo»): es un estado, no la entidad.
- Tipos de destino de registro QR (`evento`, `encuentro`, `celula`).

## Reglas de validación y transiciones

Sin cambios. El alcance por sede (`SedeScope`) y los permisos por rol aplican igual a `Miembro`.

## Reversibilidad

`down()` de la migración restituye todos los nombres anteriores; se prueba que `up` + `down` + `up` deja el mismo esquema y los mismos conteos.
