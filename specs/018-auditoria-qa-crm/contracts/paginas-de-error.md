# Contrato: páginas de error en producción

Aplica a **toda** ruta de la aplicación (pública o autenticada) cuando `APP_ENV=production`.

## Comportamiento esperado

| Situación | Antes (observado en la auditoría) | Después (contrato) |
|---|---|---|
| Método HTTP no soportado por la ruta (p. ej. `GET` a una ruta solo-`POST`) | Página de depuración de Symfony/Laravel con traza completa, rutas de `vendor/`, versión de PHP/Laravel y los encabezados de la solicitud (incluida la cookie de sesión) | Página `errors/405.blade.php` (o la vista de error que corresponda al código), sin ningún detalle técnico del servidor, usando `errors._contenido` como las demás páginas de error |
| Cualquier otra excepción no controlada (404, 419, 422, 429, 500, y cualquier código sin vista propia) | Depende de `APP_DEBUG`; con `APP_DEBUG=true` siempre se ve la traza completa, sin importar que existan vistas personalizadas | Vista personalizada existente (`errors/403.blade.php`, `404`, `419`, `422`, `429`, `500`) o, si no hay vista para ese código, el *fallback* mínimo de Laravel (sin trazas) |
| Visitante anónimo en una ruta pública (`/login`, `/registro/{token}`, `/registro/{token}/buscar`, `/registro-sede/...`) | Igual que arriba: expone encabezados y cookies de la *solicitud del propio visitante* | Nunca se expone ningún encabezado, cookie, ruta de archivo ni versión de software, para ningún visitante, autenticado o no |

## Verificación

1. Con sesión cerrada, hacer una solicitud `GET` a una ruta que solo acepta `POST` (por ejemplo `/login` o `/registro/{token}/buscar` con un token válido de un evento activo).
2. La respuesta debe tener un código `4xx` coherente (`405` si el framework lo distingue, o el que Laravel use para "método no permitido") y un cuerpo HTML que sea una de las vistas de `resources/views/errors/`, sin la cabecera "Symfony\Component\...", sin bloque "Exception trace", sin bloque "Headers".
3. Repetir autenticado como administrador contra una ruta interna equivalente (por ejemplo, un `GET` a una ruta de acción que solo acepta `POST` dentro del panel) y confirmar el mismo resultado.

## No objetivo

Este contrato no cubre el comportamiento en el ambiente de desarrollo local (`APP_ENV=local`), donde `APP_DEBUG=true` sigue siendo deseable para el equipo.
