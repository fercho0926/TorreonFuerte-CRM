# Contrato: rutas y permisos del módulo Niveles

Todas las rutas van detrás de la sesión y del middleware `modulo:niveles` (lectura para consultar, escritura para modificar), salvo `niveles.avances.*`, que además comprueba el alcance sobre el miembro. Las rutas fijas se declaran **antes** de `/niveles/{nivel}`.

## Rutas

| Método | Ruta | Nombre | Nivel | Respuesta |
|--------|------|--------|-------|-----------|
| GET | `/niveles` | `niveles.index` | lectura | HTML: tarjetas «Quién está en cada nivel» + lista del plan (con controles si hay escritura) + historial |
| GET | `/niveles/create` | `niveles.create` | escritura | formulario de nivel |
| POST | `/niveles` | `niveles.store` | escritura | crea al final; redirige a `niveles.index` |
| GET | `/niveles/miembros` | `niveles.miembros.index` | lectura | HTML: miembros de un nivel (grilla estándar) |
| GET | `/niveles/miembros/datos` | `niveles.miembros.datos` | lectura | JSON de DataTables (contrato de la spec 007) |
| GET | `/niveles/miembros/exportar` | `niveles.miembros.exportar` | lectura | CSV (incluye nivel actual y fecha del último nivel completado) |
| POST | `/niveles/avances` | `niveles.avances.store` | escritura + alcance | registra un avance manual |
| DELETE | `/niveles/avances/{avance}` | `niveles.avances.destroy` | escritura + alcance | quita un avance **manual** |
| GET | `/niveles/{nivel}/edit` | `niveles.edit` | escritura | formulario |
| PUT | `/niveles/{nivel}` | `niveles.update` | escritura | edita nombre, descripción |
| POST | `/niveles/{nivel}/subir` | `niveles.subir` | escritura | intercambia con el anterior |
| POST | `/niveles/{nivel}/bajar` | `niveles.bajar` | escritura | intercambia con el siguiente |
| POST | `/niveles/{nivel}/desactivar` | `niveles.desactivar` | escritura | oculta del camino |
| POST | `/niveles/{nivel}/reactivar` | `niveles.reactivar` | escritura | vuelve al camino |
| DELETE | `/niveles/{nivel}` | `niveles.destroy` | escritura | elimina si no tiene avances ni encuentros; si no, error con sugerencia de desactivar |

### Parámetros de `niveles.miembros.*`

`nivel` (id del nivel o `sin` para «Sin iniciar»), `vista` (`actual`, `completaron`, `siguiente`; por defecto `actual`), `inactivos`, y los filtros por columna de la grilla estándar. Orden por defecto: apellidos.

### `POST /niveles/avances`

Campos: `persona_id` (miembro visible para el usuario), `nivel_id` (nivel activo), `fecha` (hoy o anterior; por defecto hoy), `nota` (opcional, máx. 255).

Errores de validación (con redirección y mensaje junto al campo): nivel ya completado («Ya tiene este nivel completado»), fecha futura, miembro fuera de alcance (404), nivel inactivo.

### `DELETE /niveles/avances/{avance}`

Solo avances de origen `manual`. Un avance automático responde con error: «Este nivel viene de un encuentro; corrige la asistencia».

## Permisos

| Acción | Requiere |
|--------|----------|
| Ver el módulo, el plan, los conteos y las listas | lectura en `niveles` |
| Crear, editar, ordenar, desactivar, eliminar niveles | escritura en `niveles` |
| Registrar o quitar un avance | escritura en `niveles` y miembro dentro del alcance del rol |
| Ver el camino en la ficha de un miembro | lectura en `niveles` y acceso al miembro |
| Elegir el nivel de un encuentro | escritura en `encuentros` (y lectura de los niveles, que no requieren permiso adicional para el selector) |
| Tarjeta del tablero | lectura en `niveles` |

Valores por defecto en la migración: administrador y pastor escritura; líder y usuario lectura; roles personalizados sin acceso.

## Cambios en rutas existentes

- `encuentros.store` y `encuentros.update` aceptan `nivel_id` (opcional, nivel existente).
- `encuentros.asistencia` y `encuentros.destroy` disparan `AvancesNivelService`.
- `personas.index` (grilla) acepta el filtro `nivel` (id o `sin`).
- `traslados.store` copia los avances al nuevo registro.
