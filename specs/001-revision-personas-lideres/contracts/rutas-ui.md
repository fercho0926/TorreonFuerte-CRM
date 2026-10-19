# Contrato de interfaz: rutas y comportamiento de pantallas

La aplicación es web con vistas servidas por el servidor; el contrato son las rutas y sus parámetros visibles al usuario. Todas requieren sesión iniciada; sin sesión → inicio de sesión. Una acción no permitida responde **403 con mensaje claro** (no redirección silenciosa).

## Personas

| Método y ruta | Quién | Parámetros / comportamiento |
|---------------|-------|------------------------------|
| `GET /personas` | Todos (según ámbito) | Query: `q` (texto parcial en nombres, apellidos, documento, celular), `estado`, `red_id`, `zona_id`, `lider_id`, `page`. 25 por página. Por defecto excluye `inactivo` (casilla "Incluir inactivos"). Selectores de red/zona/líder solo con opciones del ámbito. |
| `GET /personas/create` | admin, pastor, líder | Formulario único; sección "Liderazgo" opcional (es líder, red, zona). El selector de líder solo ofrece líderes del ámbito. |
| `POST /personas` | Idem | Campos: nombres, apellidos, celular o email (uno obligatorio), documento (condicional), estado, lider_id, es_lider, red_id, zona_id, `confirmar_duplicado`. Si hay posible duplicado y falta `confirmar_duplicado`: vuelve al formulario con aviso y enlace a la ficha existente. |
| `GET /personas/{persona}` | Con la persona en su ámbito | Ficha completa; botones `tel:` y WhatsApp; bitácora solo para pastor/admin; acciones solo si hay permiso. |
| `GET /personas/{persona}/edit`, `PUT /personas/{persona}` | Con permiso de edición | Cambiar `lider_id` retira de células del líder anterior (o pide elegir otra). |
| `POST /personas/{persona}/inactivar`, `POST /personas/{persona}/reactivar` | Con permiso de edición | Conserva historial; registra bitácora. |
| `DELETE /personas/{persona}` | Solo admin | Solo si no tiene historial (encuentros, eventos/pagos, células, ministerios); si lo tiene → 422 con mensaje "inactívala en su lugar". |

## Líderes

| Método y ruta | Quién | Comportamiento |
|---------------|-------|----------------|
| `GET /lideres` | Todos (según ámbito) | Query: `q`, `red_id`, `zona_id`, `page`. Columnas: nombre, red, zona, personas, células, celular. Sin ID técnico. |
| `GET /lideres/designar` | admin, pastor | Buscar persona existente (por `q`) o ir a "Nueva persona como líder" (formulario único con liderazgo marcado). |
| `POST /lideres/designar` | Idem | `persona_id`, `red_id`, `zona_id` (opcional). Exige documento si falta. No crea registro nuevo. |
| `GET /lideres/{lider}` | Con el líder en su ámbito | Personas, células y resumen (nº personas, nº células, última reunión). |
| `GET /lideres/{lider}/quitar` | admin, pastor | Muestra personas y células afectadas y permite elegir nuevo líder de destino. |
| `DELETE /lideres/{lider}` | Idem | Parámetros: `reasignar_a` (persona líder) o `confirmar_sin_lider=1`. Si el líder tiene células y no se indica `reasignar_a` → 422. |

## Sin líder asignado

| Método y ruta | Quién | Comportamiento |
|---------------|-------|----------------|
| `GET /sin-lider` | admin, pastor | Lista paginada de personas sin líder, con selector para asignar líder. |
| `POST /sin-lider/{persona}/asignar` | Idem | `lider_id` dentro del ámbito; registra bitácora. |

## Catálogos

| Método y ruta | Quién | Comportamiento |
|---------------|-------|----------------|
| `GET/POST /redes`, `PUT/DELETE /redes/{red}`, `POST /redes/{red}/fusionar` | admin, pastor | Crear, renombrar, desactivar y fusionar. No permite nombres duplicados (sin tildes/mayúsculas); sugiere la existente. Eliminar solo si no tiene líderes. |
| `GET/POST /zonas`, `PUT/DELETE /zonas/{zona}` | admin, pastor | Crear, renombrar y desactivar. Eliminar solo si no tiene líderes. |

## Códigos y mensajes (resumen)

| Situación | Respuesta |
|-----------|-----------|
| Sin permiso | 403, mensaje "No tienes permiso para esta acción" |
| Persona fuera de ámbito | 404 (no revela su existencia) |
| Validación | Vuelve al formulario con errores por campo en español |
| Posible duplicado | Aviso con enlace + casilla de confirmación |
| Quitar líder con dependientes sin decisión | 422 con conteo de personas y células afectadas |
