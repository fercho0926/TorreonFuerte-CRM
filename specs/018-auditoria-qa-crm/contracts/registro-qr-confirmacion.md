# Contrato: formulario público de confirmación de registro por QR

Aplica a `resources/views/registro/formulario.blade.php` (paso 2, acción `registro.store`) y al comportamiento del campo `fecha_nacimiento` gestionado por Flatpickr (`resources/js/forms/fechas.js`).

## Comportamiento esperado del campo de fecha

| Momento | Antes (observado) | Después (contrato) |
|---|---|---|
| El usuario intenta enviar el formulario con la fecha vacía | Se muestra "Este campo es obligatorio." bajo el campo | Igual (sin cambio: el campo sigue siendo obligatorio, ver `research.md` punto 4) |
| El usuario selecciona una fecha válida en el calendario desplegable (Flatpickr) | El mensaje de error permanece visible aunque el campo ya tiene un valor válido | El mensaje de error desaparece inmediatamente y el campo pasa a mostrar el estado "válido" |
| El usuario escribe la fecha a mano (sin usar el calendario) en el campo visible | No se reprodujo como defecto en la auditoría (comportamiento nativo del navegador) | Debe seguir funcionando igual; la corrección de la sincronización Flatpickr↔validación no puede empeorar este caso |
| JavaScript deshabilitado o Flatpickr no carga | El `<input type="date">` nativo sigue funcionando (mejora progresiva ya documentada en `fechas.js`) | Sin cambio: debe seguir funcionando igual |

## Comportamiento esperado del método de envío

| Acción | Contrato |
|---|---|
| Enviar el paso 1 (buscar por celular/cédula) | `POST` a `registro.buscar` |
| Enviar el paso 2 (confirmar datos) | `POST` a `registro.store`, nunca `GET` hacia `registro.buscar` ni hacia ninguna otra ruta solo-`POST` |
| Cualquier interacción intermedia (elegir fecha, cambiar un select) | No debe disparar una navegación de página completa; como mucho, una llamada `fetch` en segundo plano con `method: 'POST'` (la precarga ya existente en `registro-qr.js`) |

## Verificación

1. Abrir el enlace de registro de un evento activo sin sesión iniciada.
2. Buscar por el celular de un miembro existente sin fecha de nacimiento guardada.
3. En el paso 2, hacer clic en el campo de fecha, elegir un día del calendario y confirmar visualmente que el mensaje "Este campo es obligatorio." desaparece sin necesidad de otra acción.
4. Enviar el formulario y confirmar (con las herramientas de red del navegador) que la única solicitud de envío es un `POST` a la ruta de confirmación, y que la respuesta es la pantalla de "registro listo", no una página de error.
5. Repetir el paso 3–4 varias veces seguidas (incluyendo abrir y cerrar el calendario sin elegir fecha, y usar las teclas de flecha dentro del calendario) como parte de la reproducción dirigida de RF-006 descrita en `research.md`.
