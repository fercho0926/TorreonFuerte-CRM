# Guía Rápida de Validación: Auditoría QA y mejoras de UX/UI del CRM

Esta guía valida, de punta a punta, cada uno de los 10 requisitos funcionales de `spec.md`. No incluye código de implementación (eso va en `tasks.md`); solo los pasos para comprobar que cada corrección ya funciona.

## Prerrequisitos

- Acceso de administrador al hosting cPanel de producción (`torreonf`) para los pasos 1 y 1-bis.
- Una cuenta de prueba en el CRM (`admin@example.com` u otra con rol Administrador) para los pasos autenticados.
- Un evento activo con registro por QR abierto (puede crearse uno de prueba y cerrarlo al final) para los pasos 3–4.
- Navegador con herramientas de desarrollo (pestaña Red/Network) para inspeccionar encabezados y métodos HTTP.

## 1. RF-001 / RF-002 — Sin fuga de información técnica (corrección operativa)

1. Por SSH o el administrador de archivos de cPanel, abrir el `.env` real en `DEPLOYPATH` (el mismo que usa `.cpanel.yml`) y confirmar `APP_ENV=production` y `APP_DEBUG=false`. Corregir si no coincide con `laravel_app/.env.production`.
2. Ejecutar `php artisan config:clear && php artisan config:cache` en el servidor para que el cambio tome efecto de inmediato (no esperar al próximo despliegue).
3. Sin iniciar sesión, visitar `https://crm.torreonfuerte.org.co/login` y, con las herramientas de red del navegador, forzar una solicitud `GET` a una ruta solo-`POST` (por ejemplo reenviar la solicitud de login como `GET`, o usar `curl -I -X GET https://crm.torreonfuerte.org.co/login` solo para el encabezado si el equipo de red lo permite).
4. **Resultado esperado**: una página de error amigable (sin "Symfony\Component\...", sin "Exception trace", sin bloque "Headers"), código de respuesta `4xx`.
5. Repetir el paso 3 contra `/registro/{token}/buscar` de un evento de prueba.
6. **Resultado esperado**: igual que el paso 4.

## 2. RF-003 — Sin credenciales en el login

1. Sin iniciar sesión, visitar `https://crm.torreonfuerte.org.co/login`.
2. **Resultado esperado**: no aparece ningún texto con un correo y contraseña en la página (ni en el HTML renderizado, ni en el código fuente de la página).

## 3. RF-004 — El mensaje de "obligatorio" desaparece al elegir fecha con el calendario

1. Abrir el enlace de registro QR de un evento de prueba sin sesión iniciada.
2. Buscar por el celular de un miembro existente que no tenga fecha de nacimiento guardada (o crear uno de prueba para esta validación y borrarlo después).
3. En el paso 2, intentar enviar el formulario sin tocar la fecha. **Resultado esperado**: aparece "Este campo es obligatorio." bajo el campo.
4. Hacer clic en el campo de fecha y elegir cualquier día del calendario.
5. **Resultado esperado**: el mensaje de error desaparece de inmediato, sin necesidad de hacer clic en otro lado ni de intentar enviar de nuevo.
6. Enviar el formulario. **Resultado esperado**: la confirmación se guarda correctamente (pantalla de "registro listo"), sin página de error.

## 4. RF-005 — Criterio consistente de fecha de nacimiento

1. Repetir el paso 3 de la sección anterior con un miembro que ya tiene fecha de nacimiento guardada.
2. **Resultado esperado**: el campo llega precargado con esa fecha y el formulario se puede confirmar sin tener que volver a elegirla.
3. Para un miembro sin fecha de nacimiento guardada: confirmar que, tras completar el registro por QR con una fecha nueva, esa fecha queda guardada en su ficha (panel interno → Miembros → su ficha → "Datos personales").
4. Confirmar que el alta interna de un miembro nuevo (panel → Miembros → Crear miembro) **sigue sin exigir** fecha de nacimiento, y que esto no contradice ningún otro requisito ya acordado para la funcionalidad 017.

## 5. RF-006 — El envío del paso 2 siempre usa POST

1. Con las herramientas de red del navegador abiertas (pestaña Red/Network, con "Preserve log" activado), repetir el flujo de la sección 3 varias veces: abrir y cerrar el calendario sin elegir fecha, elegir una fecha y cambiarla varias veces, usar las flechas del teclado dentro del calendario, y finalmente enviar.
2. **Resultado esperado**: en el registro de red, la única solicitud de envío de página completa es un `POST` a la ruta de confirmación (`registro.store`); cualquier llamada a `.../buscar` debe aparecer como `POST` (es la precarga en segundo plano), nunca como una navegación `GET` de página completa.
3. Si en algún momento se reproduce una solicitud `GET` a una ruta solo-`POST`, anotar los pasos exactos (qué se clicó, en qué orden) para que quede documentado como reproducción confirmada antes de tocar código.

## 6. RF-007 — La notificación emergente no se superpone con el encabezado

1. Autenticado como administrador, crear un miembro de prueba y observar la notificación de éxito.
2. **Resultado esperado**: la notificación es completamente visible, no tapa el menú de usuario ni queda cortada por el borde de la ventana, en una resolución de escritorio de 1366×768 y en 1920×1080.
3. Repetir cambiando la sede activa en la barra superior y eliminando el miembro de prueba creado en el paso 1.

## 7. RF-008 — Sin fila "Loading..." remanente

1. Autenticado como administrador, abrir "Solicitudes de clave" en una sede sin solicitudes pendientes.
2. **Resultado esperado**: tras cargar, la tabla muestra únicamente el mensaje en español de "sin resultados", sin ninguna palabra en inglés ni fila de carga visible.

## 8. RF-009 — Un solo mensaje de "falta un medio de contacto"

1. Autenticado como administrador, abrir "Crear miembro", completar nombres y apellidos, dejar celular y correo vacíos, e intentar guardar.
2. **Resultado esperado**: el resumen de errores en la parte superior muestra la frase "Indica al menos un medio de contacto: celular o correo." **una sola vez**.

## 9. RF-010 — Marca de agua legible

1. Autenticado como administrador, seleccionar una sede específica (no "Todas las sedes") y abrir un listado con varias filas (por ejemplo Miembros).
2. **Resultado esperado**: el nombre de la sede de fondo no dificulta leer el contenido de las filas con más texto (nombre, celular, líder, nivel).

## Cierre

- Eliminar cualquier miembro o evento de prueba creado durante esta validación.
- Confirmar en "Control de cambios" que las acciones de prueba quedaron registradas y que no quedó ningún dato de prueba activo en los listados reales.
