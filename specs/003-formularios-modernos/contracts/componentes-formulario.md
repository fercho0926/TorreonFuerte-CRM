# Contrato de interfaz: componentes de formulario

Contrato entre las **vistas** (que declaran formularios), los **componentes Blade** (que generan el HTML) y el **módulo JavaScript** (que lo mejora). Los controladores y rutas no cambian.

## 1. Componentes Blade

Todos son anónimos (`resources/views/components/form/`). Los atributos no listados se propagan al control (`$attributes`).

### `<x-form.layout>`

Envuelve el `<form>`.

| Prop | Tipo | Descripción |
|------|------|-------------|
| `action` | texto | URL de envío |
| `method` | `POST` (defecto) | Si es `PUT`/`PATCH`/`DELETE` agrega `@method` |
| `guardar` | texto | Texto del botón principal (defecto "Guardar") |
| `cancelar` | URL, opcional | Enlace del botón secundario |
| `modo` | `completo` (defecto), `incrustado`, `filtro` | Aspecto |
| `sinCambios` | booleano | Si es `true`, no avisa al salir con cambios |

Genera: `@csrf`, marca `data-form="1"`, resumen de errores (`role="alert"`, solo con errores del servidor), contenedor de secciones y `<x-form.actions>`. Si el usuario está en modo "Todas las sedes: solo consulta", muestra el aviso y deshabilita el botón principal.

### `<x-form.section>`

| Prop | Descripción |
|------|-------------|
| `titulo` (req.), `descripcion`, `icono` | Cabecera de la tarjeta |
| `id` | Ancla para el resumen de errores |
| `visibleSi` | Regla `campo:valor` para mostrarla u ocultarla completa |

### Campos

Todos reciben `name` (igual al actual), `label`, `icono`, `ayuda`, `ejemplo` (placeholder), `value` (por defecto `old(name, $modelo->name)`), `required` y los atributos `data-*` de la sección 2. Cada uno renderiza: `<label for>`, el control, la ayuda y el mensaje de error (`@error`), con `aria-describedby` y `aria-invalid`.

| Componente | Control nativo | Mejora con JS |
|------------|----------------|---------------|
| `<x-form.input type="text|email|tel|number|password">` | `<input>` con `inputmode` y `autocomplete` adecuados | máscaras (`mascara="celular|documento"`), validación |
| `<x-form.date>` | `<input type="date">` (`max`, `min`) | Flatpickr `es`, atajos Hoy y Ayer, valor enviado `Y-m-d` |
| `<x-form.money>` | `<input inputmode="decimal">` | Separador de miles y símbolo; se envía el número plano |
| `<x-form.select :opciones="...">` | `<select>` | Select2 si hay más de 10 opciones (búsqueda sin tildes, dato de apoyo) |
| `<x-form.multiselect :opciones="...">` | `<select multiple>` | Etiquetas removibles, contador, "Seleccionar resultados" y "Limpiar" |
| `<x-form.switch>` | `<input type="checkbox" value="1">` | Aspecto de interruptor |
| `<x-form.textarea>` | `<textarea>` | Contador de caracteres opcional |

`opciones` es una colección de `['id' => ..., 'texto' => ..., 'apoyo' => ...]` (el `apoyo` es opcional y se imprime como `data-apoyo`).

### `<x-form.actions>`

Barra fija en la parte inferior con el botón principal y el enlace de cancelar; respeta el menú lateral y no se superpone a la marca de agua de la sede.

### `<x-form.lista-asistencia>`

| Prop | Descripción |
|------|-------------|
| `:filas` | Colección de personas con `id`, `nombre`, `apoyo` |
| `name` | Nombre del arreglo enviado (ej. `asistio`) |
| `:marcados` | IDs marcados al cargar |
| `columnas` | Columnas extra (ej. costo, saldo) |

Renderiza **una** estructura de datos con dos vistas: `<table>` desde `md` y tarjetas con interruptor grande hasta `sm`.

### `<x-form.confirm>`

Formulario de acción delicada (inactivar, eliminar, quitar rol, trasladar, desactivar sede).

| Prop | Descripción |
|------|-------------|
| `action`, `method` | Destino |
| `titulo`, `mensaje` | Texto del diálogo |
| `boton`, `peligro` | Texto y estilo del botón que dispara la acción |

Genera `data-confirm`, `data-confirm-titulo`, `data-confirm-mensaje`. Sin JavaScript el formulario se envía directamente (el servidor ya exige permisos y reglas).

## 2. Atributos `data-*` (contrato con el módulo JS)

| Atributo | Dónde | Efecto |
|----------|-------|--------|
| `data-form` | `<form>` | Activa validación, envío y aviso de cambios |
| `data-mascara="celular|documento"` | input | Máscara de presentación; valor enviado sin máscara |
| `data-monto` | input | Formato de miles |
| `data-fecha` (+ `data-max`, `data-min`, `data-atajos="hoy,ayer"`) | input date | Calendario Flatpickr |
| `data-buscar` | select | Fuerza el buscador aunque tenga pocas opciones |
| `data-apoyo` | option | Texto de apoyo junto al nombre |
| `data-visible-si="campo:valor"` | campo o sección | Muestra/oculta (y deshabilita lo oculto) |
| `data-requerido-si="campo:valor[,valor]"` | campo | Obligatorio condicional |
| `data-confirm`, `data-confirm-titulo`, `data-confirm-mensaje`, `data-confirm-peligro` | form o botón | Diálogo de confirmación |
| `data-sin-aviso` | form | No avisa al salir con cambios |

## 3. Comportamientos garantizados por el módulo JS

| # | Comportamiento | Requisito |
|---|----------------|-----------|
| 1 | Valida al salir del campo y muestra el mensaje debajo; marca correcto o error | FR-006 |
| 2 | Al enviar con errores: no envía, enfoca el primer campo con error y muestra el resumen | FR-007 |
| 3 | Al recargar con errores del servidor: marca campos, resumen, foco al primero, conserva valores | FR-008 |
| 4 | Botón principal con progreso y deshabilitado hasta la respuesta | FR-009 |
| 5 | Aviso al salir con cambios sin guardar (sin borradores) | FR-010 |
| 6 | Avisos de éxito que se cierran solos (5 s); diálogos propios para acciones delicadas | FR-011 |
| 7 | Selectores con búsqueda sin tildes/mayúsculas y dato de apoyo; múltiples con etiquetas y contador | FR-012, FR-013 |
| 8 | Campos dependientes con transición; ocultos = deshabilitados | FR-014 |
| 9 | Máscaras y calendario sin cambiar los valores enviados | FR-015, FR-016 |

Eventos públicos (`CustomEvent` en el `<form>`): `form:invalido` (detalle: lista de campos), `form:enviando`, `form:cambios` (detalle: `true/false`). Útiles para pruebas y futuras extensiones.

## 4. Contrato de accesibilidad

- Cada campo: `<label for>` con el `id` del control; `aria-required="true"` si es obligatorio; `aria-invalid="true"` si tiene error; `aria-describedby` con los ids de ayuda y error.
- Resumen de errores: `role="alert"`, con un enlace por error que enfoca el campo.
- Diálogos: foco atrapado, cierre con `Esc`, botón de cancelar enfocado por defecto en acciones de peligro.
- Todo operable con teclado; los controles tienen área táctil ≥ 44 px en teléfono.
- Los textos de ayuda y error cumplen contraste ≥ 4,5:1.

## 5. Contrato sin JavaScript

Con JS deshabilitado o no cargado: cada componente deja un control nativo funcional; el formulario se envía; el servidor valida y devuelve errores con `old()`; las confirmaciones se omiten (el servidor mantiene sus reglas y permisos); el CSS de tarjetas y campos sigue aplicándose.

## 6. Reglas de migración de vistas

1. No cambiar `name`, `action`, `method`, rutas, validaciones ni controladores.
2. Sustituir cada campo por su componente; cada grupo temático por `<x-form.section>`.
3. Reemplazar `onclick="return confirm(...)"` por `<x-form.confirm>` o `data-confirm`.
4. Reemplazar listas de casillas de personas por `<x-form.multiselect>` o `<x-form.lista-asistencia>`.
5. Una prueba de inventario falla si una vista tiene `<form method="POST">` sin `data-form`.
