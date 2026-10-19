# Contrato de interfaz: rutas y pantallas de claves

Aplica a la aplicación web (sesión propia). Todas las rutas autenticadas exigen sesión y respetan el aislamiento por sede. «Admin» = administrador del sistema; «Pastor» incluye a la pastora.

## Rutas

| Método y ruta | Nombre | Quién | Descripción |
|---------------|--------|-------|-------------|
| `GET /olvide-clave` | `recuperacion.create` | Visitante | Formulario con el correo |
| `POST /olvide-clave` | `recuperacion.store` | Visitante | Registra la solicitud; responde siempre igual (redirección con el mismo mensaje) |
| `GET /cambiar-clave` | `cambiar-clave.edit` | Con sesión y clave temporal | «Elige tu contraseña» |
| `PUT /cambiar-clave` | `cambiar-clave.update` | Con sesión y clave temporal | Guarda la contraseña nueva |
| `GET /mi-cuenta` | `cuenta.edit` | Cualquier usuario con sesión | Datos básicos y cambio de contraseña |
| `PUT /mi-cuenta/clave` | `cuenta.clave.update` | Cualquier usuario con sesión | Cambia la contraseña indicando la actual |
| `GET /solicitudes-clave` | `solicitudes-clave.index` | Admin, pastor | Panel de solicitudes (pendientes, atendidas, vencidas) |
| `POST /solicitudes-clave/{solicitud}/atender` | `solicitudes-clave.atender` | Admin, pastor de la sede | Genera la clave y responde con la pantalla «Clave generada» |
| `POST /solicitudes-clave/{solicitud}/descartar` | `solicitudes-clave.descartar` | Admin, pastor de la sede | Marca como descartada |
| `POST /users/{user}/clave` | `users.clave.store` | Quien puede gestionar al usuario | Genera una clave nueva (motivo reenvío) y responde con «Clave generada» |
| `POST /claves/{registro}/enviada` | `claves.enviada` | Quien la preparó, admin o pastor de la sede | Marca el registro como «enviada» |
| `POST /users` | `users.store` | Admin, pastor | Crea el usuario **sin contraseña**, genera la clave y responde con «Clave generada» |

`users.*` sigue exento del bloqueo de escritura sin sede activa, como hoy; `solicitudes-clave.*` y `users.clave.*` también.

## Campos

- **Crear usuario** (`users.store`): `name`, `email`, `role`, `sede_id` (solo admin), `persona_id` (opcional), `celular` (obligatorio, formato de celular del sistema; se propone el de la persona vinculada). **No existen** `password` ni `password_confirmation`.
- **Editar usuario** (`users.update`): los mismos sin contraseña; `celular` editable.
- **Recuperación**: `email` (formato de correo).
- **Elige tu contraseña**: `password`, `password_confirmation`; mínimo 8 caracteres, distinta de la temporal.
- **Mi cuenta / cambiar contraseña**: `password_actual`, `password`, `password_confirmation`.

## Pantalla «Clave generada» (respuesta del POST)

Contenido: nombre y rol de la persona, celular con formato legible (para verificarlo), la **clave temporal** (grupo `XXXXX-XXXXX`), vigencia, botón **Enviar por WhatsApp** (enlace `https://wa.me/{numero}?text={mensaje}`), botón **Copiar mensaje**, botón **Ya la envié** (`claves.enviada`) y aviso «Esta clave se muestra una sola vez».

Garantías: respuesta con `Cache-Control: no-store`; la clave no está en sesión, base de datos ni logs; si falta el celular se muestra el error de FR-014 en lugar de la pantalla.

## Mensaje de WhatsApp (español)

```text
Hola {nombre}, te damos acceso a {sistema} de la iglesia {sede}.
Correo: {email}
Clave temporal: {clave}
Vale por {horas} horas; al entrar te pediremos elegir tu propia contraseña.
Ingresa desde la dirección habitual del sistema.
```

Sin enlaces con la clave incrustada (FR-015).

## Respuestas y errores

| Situación | Respuesta |
|-----------|-----------|
| Recuperación con cualquier correo válido | Mensaje genérico, mismo código y mismo texto |
| Demasiadas solicitudes | Mensaje «Espera unos minutos antes de volver a intentarlo» (HTTP 429 en el límite) |
| Inicio de sesión con clave temporal vencida | «Tu clave temporal venció; pide una nueva» |
| Demasiados fallos de inicio de sesión | «Demasiados intentos; espera un minuto» |
| Pastor intenta gestionar a otra sede, a un pastor o a un admin | 403 con la página de «Sin permiso» |
| Usuario sin celular | Error de validación explicando cómo completarlo |

## Menú y listado de usuarios

- Menú (Administración): «Solicitudes de clave» con insignia de pendientes, visible para admin y pastor.
- Listado de usuarios: columna «Clave» con estado (`pendiente de envío`, `enviada`, `usada`, `vencida`, o `sin clave temporal`) y acción «Enviar nueva clave» (con confirmación).
- Menú de usuario (barra superior): enlace «Mi cuenta».
