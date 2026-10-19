# Contrato de interfaz: rutas y comportamiento de pantallas (Multi-sede)

La aplicación es web con vistas servidas por el servidor; el contrato son las rutas y su comportamiento visible. Todas requieren sesión iniciada. Reglas generales de esta funcionalidad:

- **Aislamiento**: un recurso de otra sede responde **404** (no revela que existe). Una acción sin permiso responde **403** con el mensaje "No tienes permiso para esta acción".
- **Escrituras bloqueadas** (POST/PUT/PATCH/DELETE) cuando el admin está en "Todas las sedes" o la sede activa está inactiva: responde **403** con el mensaje "Elige una sede activa para crear o editar" (o "La sede está inactiva"). Quedan exentas las rutas del módulo Sedes, el cambio de sede activa, el inicio y el cierre de sesión.
- **Sede inactiva**: el login de sus usuarios responde con error "Tu sede está inactiva; contacta al administrador" y una sesión abierta se cierra en la siguiente petición.
- **Barra superior**: muestra el nombre de la sede del usuario; el admin ve además el selector de sede activa.

## Sede activa (solo admin)

| Método y ruta | Quién | Comportamiento |
|---------------|-------|----------------|
| `POST /sede-activa` | admin | Parámetro `sede_id` (id de una sede o vacío para "Todas las sedes"). Guarda la elección en sesión y vuelve a la pantalla anterior. Otros roles → 403. |

## Módulo Sedes (solo admin)

| Método y ruta | Quién | Parámetros / comportamiento |
|---------------|-------|------------------------------|
| `GET /sedes` | admin | Listado de sedes con estado, pastor, pastora, nº de personas, líderes y células. Resto de roles → 403 (el menú no aparece). |
| `GET /sedes/create` | admin | Formulario de nueva sede: datos de la sede + bloque **Pastor** + bloque **Pastora** (opcional). |
| `POST /sedes` | admin | Campos de la sede: `nombre` (único), `ciudad`, `direccion`, `telefono`, `email`. Pastor: `pastor[nombres]`, `pastor[apellidos]`, `pastor[email]` (único global), `pastor[password]`, `pastor[celular]`. Pastora (opcional, mismos campos en `pastora[...]`). **Atómico**: si algo falla no se crea nada y se vuelve al formulario con los errores por campo. |
| `GET /sedes/{sede}` | admin | Detalle: datos, pastores, resumen (personas, líderes, células, eventos próximos), bitácora de la sede, botón "Trabajar en esta sede" (fija la sede activa). |
| `GET /sedes/{sede}/edit`, `PUT /sedes/{sede}` | admin | Edita los datos de la sede. |
| `GET /sedes/{sede}/pastores`, `PUT /sedes/{sede}/pastores` | admin | Cambia pastor y/o pastora: `rol` (`pastor` o `pastora`) y datos de la cuenta nueva (o `user_id` de una cuenta de la misma sede). La cuenta anterior pasa a líder y conserva su persona. |
| `POST /sedes/{sede}/desactivar`, `POST /sedes/{sede}/reactivar` | admin | Cambia `activa`; se registra en la bitácora. |
| `DELETE /sedes/{sede}` | admin | Si la sede tiene datos → **422** "Solo se pueden desactivar sedes con datos". |

## Usuarios (cambios)

| Método y ruta | Quién | Comportamiento |
|---------------|-------|----------------|
| `GET /users` | admin, pastor | Admin: todos los usuarios (con columna **Sede**, filtro por sede). Pastor: solo los de su sede. Otros roles → 403. |
| `GET /users/create`, `POST /users` | admin, pastor | Admin: elige rol y sede (el rol `admin` no lleva sede). Pastor: la sede es la suya y los roles permitidos son solo **líder** y **usuario**; intentar `pastor` o `admin` → 403. El correo es único en todo el sistema. |
| `GET /users/{user}/edit`, `PUT /users/{user}` | admin, pastor | Pastor: solo usuarios de su sede y de rol líder o usuario; no puede cambiar rol a `pastor`/`admin` ni mover la sede. Un usuario de otra sede → 404. |
| `DELETE /users/{user}` | admin, pastor | Mismas restricciones; no permite eliminarse a sí mismo. |

## Traslado de personas (solo admin)

| Método y ruta | Quién | Comportamiento |
|---------------|-------|----------------|
| `GET /personas/{persona}/trasladar` | admin (con sede activa) | Formulario con selector de sede destino (sedes activas distintas a la actual). Muestra lo que impide el traslado si la persona es líder con personas/células o encargada de ministerio. |
| `POST /personas/{persona}/trasladar` | admin | Parámetro `sede_destino_id`. Si hay personas, células o ministerios a cargo → **422** con el detalle ("reasigna primero"); si el documento ya existe en la sede destino → error por campo. Éxito: crea el registro en la sede destino (sin líder, célula ni ministerios) y deja el de origen inactivo con su historial; redirige a la ficha de origen con el mensaje de traslado. |

## Dashboard

| Método y ruta | Quién | Comportamiento |
|---------------|-------|----------------|
| `GET /dashboard` | todos | **Admin en "Todas las sedes"**: tabla por sede (personas, líderes, células, próximos eventos, recaudo) y total global rotulado. **Admin con sede activa** y **resto de roles**: el resumen de su sede (como hoy). |

## Resto de módulos (personas, líderes, redes, zonas, células, encuentros, eventos, ministerios, sin-lider)

Sin rutas nuevas. Comportamiento común:

| Aspecto | Regla |
|---------|-------|
| Listados, filtros, búsquedas y selectores | Solo registros de la sede efectiva; admin en "Todas las sedes" ve todo con columna **Sede** (solo lectura). |
| Detalle / edición / eliminación por enlace directo | Registro de otra sede → 404. |
| Creación | El registro queda en la sede efectiva; no se pueden elegir líderes, redes, zonas, células, ministerios o personas de otra sede (el selector no los ofrece y el servidor los rechaza). |

## Códigos y mensajes (resumen)

| Situación | Respuesta |
|-----------|-----------|
| Recurso de otra sede | 404 |
| Acción sin permiso (p. ej. no admin en Sedes) | 403, "No tienes permiso para esta acción" |
| Admin escribe sin sede activa | 403, "Elige una sede activa para crear o editar" |
| Sede inactiva (usuario) | Inicio de sesión rechazado / sesión cerrada con mensaje |
| Sede inactiva (admin escribe) | 403, "La sede está inactiva" |
| Alta de sede con error | Vuelve al formulario, nada creado |
| Traslado bloqueado | 422 con detalle de lo que hay que reasignar |
| Eliminar sede con datos | 422 |
