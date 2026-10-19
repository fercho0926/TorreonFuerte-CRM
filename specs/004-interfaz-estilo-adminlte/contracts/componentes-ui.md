# Contrato de interfaz: componentes de presentación `x-ui.*`

Componentes Blade anónimos, sin lógica de negocio.

## `<x-ui.tarjeta>`

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| `titulo` | texto | Título del encabezado de la tarjeta |
| `icono` | texto, opcional | Icono de Font Awesome |
| `contador` | número, opcional | Muestra «N resultados» junto al título |
| `variante` | `primary`/`info`/`success`/`warning`/`danger`/ninguna | Aplica borde superior de color (`card-outline`) |
| slot `acciones` | opcional | Botones a la derecha del encabezado |
| slot `pie` | opcional | Pie (paginación o botones) |

El cuerpo es el contenido del componente. Para tablas se usa `tabla` = `true` (cuerpo sin relleno, `table-responsive` dentro de la tarjeta: la página no se desplaza horizontalmente).

## `<x-ui.estado>`

| Propiedad | Descripción |
|-----------|-------------|
| `valor` | Estado (clave de `Estados`) |
| `etiqueta` | Opcional; sustituye el texto por defecto |

Salida: insignia `badge text-bg-{color}`. Todo estado de la aplicación se dibuja únicamente con este componente.

## `<x-ui.vacio>`

| Propiedad | Descripción |
|-----------|-------------|
| `mensaje` | Texto («Aún no hay personas registradas», «Sin resultados para este filtro») |
| `icono` | Icono grande opcional |
| slot por defecto | Botón de acción sugerida (por ejemplo "Registrar persona"), solo si el usuario puede usarla |

## `<x-ui.acciones-fila>`

Agrupa las acciones de una fila con iconos fijos y títulos accesibles: ver (`fa-eye`, `btn-outline-secondary`), editar (`fa-edit`, `btn-outline-primary`), eliminar (`fa-trash`, `btn-outline-danger`, usa `x-form.confirm`). Cada botón recibe su ruta; si no se pasa, no se dibuja.

## `<x-ui.insignia>`

Insignia genérica para contadores y roles dentro de una ficha (no para estados). Propiedad `color` (`primary`, `secondary`, `success`, `danger`, `warning`, `info`, `light`, `dark`); el contenido es el texto. Se pueden añadir clases (por ejemplo `class="border"`). Ninguna vista usa clases `badge` directas (una prueba lo verifica).

## `<x-ui.caja>`

Caja de resumen de AdminLTE: `valor`, `texto`, `icono`, `color` y, opcionalmente, `enlace` (con `etiquetaEnlace`, por defecto «Ver más»).

## Propiedades añadidas en la implementación

- `<x-ui.acciones-fila>`: `puedeEditar` y `puedeEliminar` (booleanos, por defecto verdadero) ocultan el botón cuando el usuario no tiene permiso; el contenido del componente admite acciones extra (por ejemplo «Inactivar» o «Quitar rol»).
- Los listados y fichas usan las clases de tarjeta de AdminLTE directamente (`card`, `card-outline`) y reservan `<x-ui.tarjeta>` para bloques nuevos; el aspecto es el mismo.
- Botones de acción positiva (reactivar, trabajar en una sede) usan `btn-success`; el resto sigue el esquema siguiente.

## Esquema de botones

| Rol | Clase | Ejemplos |
|-----|-------|----------|
| Principal | `btn btn-primary` | Guardar, Nuevo |
| Secundaria | `btn btn-outline-secondary` | Cancelar, Volver, Limpiar |
| Peligrosa | `btn btn-outline-danger` (confirmada con diálogo) | Eliminar, Quitar |

## Compatibilidad con 003

Los componentes `x-form.*` conservan sus propiedades y atributos `data-*`; solo cambian sus clases a Bootstrap 5. Ninguna prueba de 003 cambia su comportamiento esperado.
