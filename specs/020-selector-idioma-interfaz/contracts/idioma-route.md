# Contrato: Cambio de idioma de la interfaz

## `POST /idioma` (nombre de ruta: `idioma.update`)

Interfaz interna que usa el control de idioma del header (`layouts/admin.blade.php`); no es una API pública.

**Autenticación**: Requiere un usuario autenticado. Sin sesión iniciada, redirige a `login` (mismo comportamiento que las demás rutas protegidas por el middleware `web`/`auth`).

**Petición**:

| Campo | Tipo | Obligatorio | Reglas |
|---|---|---|---|
| `idioma` | string | Sí | Debe ser `es` o `en` (`Rule::in(['es', 'en'])`); cualquier otro valor es un error de validación 422. |
| `_token` | string | Sí | Token CSRF estándar de Laravel (`@csrf` en el formulario). |

**Respuesta**:

- Éxito: `302` redirige de vuelta a la URL desde donde se envió el formulario (`redirect()->back()`), igual que el patrón ya usado por `sede-activa.update`. El idioma ya se aplica en la propia respuesta de redirección y en todas las peticiones siguientes de ese usuario.
- Error de validación: `422` (o redirección con errores si la petición no es JSON), sin cambiar el idioma actual.

**Efecto**:

1. Guarda `idioma` en el registro del usuario autenticado (persistente entre sesiones y dispositivos, RF-003).
2. A partir de esa petición (incluida la propia respuesta), el middleware `EstableceIdioma` aplica `App::setLocale($usuario->idioma)` en cada petición web siguiente, hasta que el usuario lo vuelva a cambiar.

**Funciona sin JavaScript**: Sí — es un `<form method="POST">` con un `<button type="submit">` por idioma; no depende de `onchange` ni de ninguna petición asíncrona (RF-007).

**Fuera de alcance de este contrato**: No afecta las pantallas públicas (registro QR, asistencia a eventos) ni los mensajes generados fuera de pantalla (WhatsApp, notificaciones, exportaciones), según RF-009 y RF-010.
