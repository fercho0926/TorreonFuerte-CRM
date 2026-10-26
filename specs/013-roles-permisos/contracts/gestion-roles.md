# Contrato: Gestión de roles (pestaña «Roles»)

Todas las rutas requieren sesión y son **solo del administrador** (módulo reservado, no configurable). Cualquier otro rol recibe 403.

| Método | Ruta | Nombre | Descripción |
|--------|------|--------|-------------|
| GET | `/roles` | `roles.index` | Pestaña «Roles» con la grilla estándar (nombre, descripción, alcance, usuarios, tipo) |
| GET | `/roles/datos` | `roles.datos` | JSON de la grilla (ordenar, filtrar, paginar) |
| GET | `/roles/create` | `roles.create` | Formulario vacío; con `?duplicar={rol}` precarga permisos y alcance y pide un nombre nuevo |
| POST | `/roles` | `roles.store` | Crea el rol y registra el historial |
| GET | `/roles/{rol}` | `roles.show` | Detalle: matriz de solo lectura, usuarios con el rol (nombre y sede) e historial |
| GET | `/roles/{rol}/edit` | `roles.edit` | Formulario; muestra cuántos usuarios se verán afectados |
| PUT | `/roles/{rol}` | `roles.update` | Guarda y registra el historial (confirmación previa si hay usuarios) |
| DELETE | `/roles/{rol}` | `roles.destroy` | Elimina un rol sin usuarios y no de sistema (con confirmación) |

`{rol}` se resuelve por `clave`. Los roles no usan filtro de sede (son globales); solo el administrador accede.

## Pestañas en el módulo Usuarios

`usuarios.index` y `roles.index` comparten un encabezado con pestañas «Usuarios» y «Roles». La pestaña «Roles» solo se muestra al administrador.

## Formulario de rol

Campos: `nombre`, `descripcion`, `alcance` (3 opciones), `asignable` (casilla) y `permisos[modulo]` con radios «Sin acceso / Lectura / Escritura» por módulo. En celular la matriz se presenta como una tarjeta por módulo con los tres botones de ≥ 44 px. Atajos (con JavaScript): «Todo en lectura», «Todo sin acceso».

Validaciones: ver [data-model.md](../data-model.md). Errores junto al campo; el formulario conserva lo escrito.

## Reglas de servidor

1. `admin`: no se puede editar ni eliminar (403 con mensaje «Rol protegido»).
2. Eliminar un rol con usuarios: se rechaza con «N usuarios lo tienen; reasígnalos primero».
3. Eliminar un rol de sistema: se rechaza.
4. Guardar cambios de un rol con usuarios exige el campo de confirmación (`confirmo_afectados=1`) que la pantalla muestra con el conteo; sin él, vuelve al formulario con el aviso.
5. Cada crear, editar, duplicar y eliminar escribe una fila en `rol_cambios` con autor, fecha, `antes` y `despues`.
6. Un cambio de permisos se aplica a los usuarios con sesión abierta en su siguiente petición (no hay caché de permisos entre peticiones).

## Respuestas y errores

Pantallas HTML con las páginas de error existentes. `roles.datos` responde JSON con la misma estructura que las demás grillas (`/users/datos`) y errores en JSON. Las respuestas de la gestión de roles llevan `Cache-Control: no-store, private`.

## Asignación de roles en Usuarios (cambios a rutas existentes)

`users.create`, `users.store`, `users.edit`, `users.update` leen la lista de roles de la tabla:

- Administrador: todos los roles vigentes.
- Otro usuario con escritura en Usuarios: solo los roles que cumplan el techo de asignación (rol `asignable`, alcance ≤ el suyo y nivel por módulo ≤ el suyo); nunca su propio rol; nunca cuentas con un rol que no podría asignar.
- No se puede dejar el sistema sin un usuario administrador (cambiar el rol, eliminar o desactivar al último).
