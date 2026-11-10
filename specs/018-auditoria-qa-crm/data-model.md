# Modelo de Datos: Auditoría QA y mejoras de UX/UI del CRM

Esta funcionalidad **no agrega entidades ni columnas nuevas**. Es una ronda de correcciones sobre entidades y campos ya existentes. Se documenta aquí qué entidades toca cada requisito y qué regla cambia, para que quede claro que no hay una migración de esquema de por medio.

## Entidades existentes afectadas

### Persona (`personas`)

- Campo relevante: `fecha_nacimiento` (nullable en base de datos; sin cambio de columna).
- **RF-005**: el campo sigue siendo `nullable` en la base de datos (una persona ya existente sin fecha de nacimiento no se corrompe ni se le inventa un valor). La obligatoriedad vive únicamente en la capa de validación del formulario público de confirmación por QR (`FormRequest` correspondiente), igual que el patrón ya usado por la funcionalidad 017 para `celular`/`numero_documento`: **obligatorio en el formulario, rellenado solo si está vacío** cuando la persona ya existe.
- **RF-009**: la regla "al menos un medio de contacto" (`celular` o `email`) pasa de dos mensajes independientes (uno por campo) a un único mensaje bajo una clave de validación compartida (p. ej. `contacto`). No cambia qué datos se guardan, solo cómo se reporta el error.

### RegistroQr / confirmación pública por QR

- Sin cambio de columnas. El paso 2 del formulario (`resources/views/registro/formulario.blade.php`) sigue usando `fecha_nacimiento` como hoy; lo único que cambia es la sincronización del estado de validación en el navegador (RF-004) y, si la investigación dirigida de RF-006 lo confirma, un ajuste del lado del cliente para que el envío siempre use `POST`.

## Elementos de interfaz (no son "entidades" de negocio, pero forman parte del diseño de esta funcionalidad)

- **Página de error 405**: nueva vista `errors/405.blade.php`, construida sobre el mismo parcial `errors._contenido` que ya usan 403/404/419/422/429/500. No introduce un modelo nuevo; reutiliza las mismas variables (`codigo`, `icono`, `tipo`, `mensaje`).
- **Notificación emergente (toast)**: sin cambio de datos; solo de posición (CSS) en `resources/js/forms/avisos.js`.
- **Objeto de idioma de la grilla (`ESPAÑOL`)**: diccionario estático de textos de DataTables en `resources/js/grilla/grilla.js`; se le agrega la clave `loadingRecords`.
- **Marca de agua de sede**: elemento puramente visual (CSS), sin datos propios más allá del nombre de la sede activa ya disponible en la vista.

## Resumen

No se requiere `data-model.md` con diagrama de entidades porque ninguna tabla cambia de forma. Esta sección existe para dejar explícito, de cara a `/speckit-tasks`, que ninguna tarea de esta funcionalidad debe incluir una migración.
