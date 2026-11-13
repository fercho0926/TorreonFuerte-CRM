# Modelo de Datos: Selector de idioma en la interfaz (español/inglés)

## Usuario (`users`) — modificación

Entidad existente (`App\Models\User`). Se agrega un atributo nuevo, sin crear una tabla nueva.

| Campo | Tipo | Reglas | Descripción |
|---|---|---|---|
| `idioma` | string corto | Obligatorio; valores permitidos: `es`, `en`; por defecto `es` | Idioma preferido del usuario para las pantallas internas del CRM (RF-003, RF-004). |

**Reglas de validación**:
- Al guardar, `idioma` DEBE ser `es` o `en` (validación de aplicación, ej. `Rule::in(['es', 'en'])`); no se acepta ningún otro valor.
- Un usuario sin preferencia explícita (cuentas creadas antes de esta funcionalidad) tiene `idioma = 'es'` por defecto, sin requerir acción manual (RF-004).

**Relaciones**: Ninguna nueva; es un atributo propio del usuario, no relacionado con sede (`sede_id`) ni con rol (`role`). No introduce ningún cruce de datos entre sedes o roles (principio II).

**Historial de cambios**: Como el resto de los campos sensibles de `User` (ver `camposHistorial()`), un cambio de `idioma` queda registrado igual que `email` o `role`, para mantener consistencia con el principio de auditoría ya existente en el proyecto.

**Transiciones de estado**: No aplica una máquina de estados; es un valor que el propio usuario cambia libremente entre `es` y `en` en cualquier momento, sin restricciones de transición.

## Texto de interfaz (conceptual, no es una tabla)

No es una entidad de base de datos. Es el conjunto de claves de traducción que vive en archivos:

- `lang/es/interfaz.php`: clave → texto en español (idéntico al texto que ya existía en las vistas antes de esta funcionalidad).
- `lang/en/interfaz.php`: misma clave → texto en inglés.

**Regla de respaldo**: Si una clave existe en `lang/es/interfaz.php` pero no en `lang/en/interfaz.php`, Laravel usa `fallback_locale` (configurado en `es`) y muestra el texto en español en vez de dejar el espacio vacío o la clave cruda (RF-006).

Las claves de las grillas estándar (textos fijos de DataTables: buscar, filas por página, paginador) se resuelven aparte, con el arreglo de idioma propio de DataTables según el idioma activo de la sesión (ver `research.md`, punto 5), no como entradas de `interfaz.php`.
