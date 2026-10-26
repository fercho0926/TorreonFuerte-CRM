# Modelo de Datos: Roles y permisos por módulo

Una migración nueva (`2026_10_28_000001_create_roles_y_permisos.php`) crea tres tablas y siembra los 4 roles actuales. La tabla `users` **no cambia**: `users.role` sigue siendo una cadena y pasa a referenciar `roles.clave`.

## Tabla `roles`

| Campo | Tipo | Reglas |
|-------|------|--------|
| `id` | entero | |
| `clave` | cadena máx. 40, única | Identificador estable en minúsculas sin acentos ni espacios (`admin`, `pastor`, `lider_celula`, `user`, o generada: `secretaria`). No cambia al renombrar. Es el valor de `users.role`. |
| `nombre` | cadena máx. 60, único (sin distinguir mayúsculas ni acentos) | Obligatorio. Se limpian espacios sobrantes. |
| `descripcion` | cadena máx. 255, nula | Opcional |
| `alcance` | cadena máx. 10 | `sede`, `rama` o `registro`. Obligatorio. |
| `es_sistema` | booleano, por defecto falso | Verdadero en los 4 roles sembrados. Un rol de sistema no se elimina. |
| `asignable` | booleano, por defecto falso | Puede asignarlo quien tenga escritura en Usuarios (con el techo de D10). |
| `created_at`, `updated_at` | marcas de tiempo | |

Reglas del rol `admin`: `es_sistema` verdadero, `alcance` `sede`, todos los módulos en escritura; nombre, alcance y permisos no se pueden modificar y no se elimina; `asignable` solo lo cambia el administrador. Debe existir siempre al menos un usuario con `role = 'admin'`.

## Tabla `rol_permisos`

| Campo | Tipo | Reglas |
|-------|------|--------|
| `id` | entero | |
| `rol_id` | entero, FK a `roles` (borrado en cascada) | |
| `modulo` | cadena máx. 30 | Una de las claves de `App\Support\Modulos`: `tablero`, `miembros`, `lideres`, `sin_lider`, `celulas`, `ministerios`, `eventos`, `encuentros`, `organigrama`, `registro_miembros`, `usuarios`, `solicitudes_clave` |
| `nivel` | entero pequeño | 0 sin acceso, 1 lectura, 2 escritura (acumulativos) |

Índice único `(rol_id, modulo)`. Un módulo sin fila equivale a nivel 0. Al guardar un rol se escriben las 12 filas (o se borran las de nivel 0, según el servicio); el servicio garantiza que no haya módulos fuera del catálogo.

## Tabla `rol_cambios` (historial)

| Campo | Tipo | Reglas |
|-------|------|--------|
| `id` | entero | |
| `rol_id` | entero nulo, FK a `roles` (a nulo al borrar) | Se conserva el historial de roles eliminados |
| `rol_clave` | cadena máx. 40 | Copia de la clave para identificar el rol tras eliminarlo |
| `rol_nombre` | cadena máx. 60 | Copia del nombre en el momento del cambio |
| `user_id` | entero nulo, FK a `users` (a nulo al borrar) | Quién hizo el cambio |
| `accion` | cadena máx. 20 | `creado`, `editado`, `duplicado`, `eliminado` |
| `antes` | JSON nulo | Campos y permisos anteriores (solo lo que cambió) |
| `despues` | JSON nulo | Campos y permisos nuevos |
| `created_at` | marca de tiempo | |

## Relaciones

```text
Rol 1 ──── n RolPermiso            (12 módulos máximo por rol)
Rol 1 ──── n RolCambio             (historial; sobrevive al rol)
Rol 1 ──── n User                  (por users.role = roles.clave)
User n ──── 1 Sede                 (sin cambios)
```

## Catálogo fijo (no es tabla): `App\Support\Modulos`

Para cada módulo: clave, etiqueta, icono, grupo del menú, rutas que cubre y si admite escritura. No incluye Sedes ni Roles (reservados al administrador).

## Reglas y validaciones del formulario de rol

| Campo | Regla |
|-------|-------|
| `nombre` | Requerido, máx. 60, único sin distinguir mayúsculas ni acentos (ignorando el propio rol al editar) |
| `descripcion` | Opcional, máx. 255 |
| `alcance` | Requerido; `sede`, `rama` o `registro` |
| `asignable` | Casilla; ignorada para `admin` |
| `permisos[modulo]` | Cada módulo del catálogo con 0, 1 o 2; los módulos ausentes se toman como 0 |

## Resultado de comprobar un acceso

`AutorizacionRoles::nivel(User $usuario, string $modulo): int` → 0, 1 o 2. Si el usuario no tiene rol vigente, devuelve 0 en todo. Para el rol `admin` devuelve 2 siempre.

## Estados y transiciones

- **Rol**: creado → editado (permisos, alcance, nombre) → eliminado (solo sin usuarios y sin `es_sistema`). No hay borrado lógico; el historial conserva lo ocurrido.
- **Cambio de rol de un usuario**: se hace en el formulario de usuarios existente y se rige por RF-015 (techo de asignación) y por la protección del último administrador.
