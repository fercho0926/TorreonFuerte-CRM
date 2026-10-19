# Modelo de datos: Personas, Líderes, Células y Ministerios

## Cambios en la base

### Se elimina: Red (`redes`) y `personas.red_id`

| Elemento | Acción |
|----------|--------|
| `personas.red_id` (clave foránea a `redes`, nula) | Se elimina |
| Tabla `redes` (con `sede_id`, nombre, clave, activa) y su índice `redes_sede_clave_unique` | Se elimina |
| `MigracionSedes::TABLAS` | Se quita `redes` |

Migración `2026_10_25_000001_drop_redes`: `up()` quita la clave foránea y la columna y luego la tabla; `down()` recrea la tabla y la columna vacías. Antes de aplicarla se verifica que no cambie el número de personas, líderes, células ni ministerios.

### Cambia: estados de persona (sin cambio de esquema)

| Valor | Etiqueta | Documento | Cuenta como activa | Aparece en «Sin líder asignado» |
|-------|----------|-----------|--------------------|-------------------------------|
| `visitante` | Visitante | Opcional | Sí | Sí |
| `nuevo_creyente` | Nuevo creyente | Opcional | Sí | Sí |
| `miembro_activo` | Miembro activo | Obligatorio | Sí | Sí |
| `celula` (nuevo) | En célula | Opcional | Sí | **No** |
| `inactivo` | Inactivo | Opcional | No | No |

Reglas: una persona `celula` puede cambiar a cualquier otro estado desde su edición sin perder historial ni pertenencia a su célula; para ser líder necesita documento como cualquier persona.

### Sin cambios de esquema pero con reglas nuevas

**Pertenencia a célula** (`celula_persona`, ya existente): ya no depende de `personas.lider_id`.

| Regla | Detalle |
|-------|---------|
| Quién puede ser integrante | Cualquier persona **no inactiva** de la sede visible para quien guarda |
| Efecto sobre el líder directo | Ninguno: añadir o quitar de la célula no modifica `lider_id` |
| Fecha de ingreso | `fecha_ingreso` = hoy al añadir; se conserva la existente al volver a guardar |
| Integrantes que pasan a inactivos | Se conservan hasta que se quiten explícitamente; no se ofrecen para nuevas selecciones |
| Duplicados | Una persona solo puede estar una vez por célula (clave única existente) |

**Ministerio** (`ministerio_persona`): la selección de integrantes usa el mismo buscador; el rol y la fecha de ingreso siguen editándose desde el detalle.

## Estructuras de presentación

### Resultado de búsqueda de personas

| Campo | Descripción |
|-------|-------------|
| `id` | Identificador de la persona |
| `texto` | Nombre completo |
| `apoyo` | Documento y celular para distinguir homónimos («CC 1234 · 310 555 9999»; «Sin documento» si falta) |
| `estado` | Valor de estado, para la insignia |
| `sinDocumento` | Verdadero si no tiene documento (la pantalla de designar líder avisa) |
| `mas` | Verdadero si hay otra página de resultados |

### Menú (`config/menu.php`)

Congregación: Personas, Líderes, Sin líder asignado, Células, **Ministerios**. Actividades: Eventos, Encuentros. Administración: Sedes, Usuarios, Solicitudes de clave. Sin «Redes».

## Relaciones tras el cambio

- Persona 1 — N Persona (líder directo → personas a su cargo).
- Célula N — N Persona (integrantes), independiente del líder directo.
- Ministerio N — N Persona (integrantes).
- Se elimina la relación Persona N — 1 Red.
