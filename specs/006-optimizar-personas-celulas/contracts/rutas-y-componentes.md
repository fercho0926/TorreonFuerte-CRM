# Contrato de interfaz: rutas y componentes

Aplica a la aplicación web (sesión propia). Todas las rutas exigen sesión y respetan el aislamiento por sede y el alcance por rol (`AlcanceService`).

## Rutas nuevas

| Método y ruta | Nombre | Quién | Descripción |
|---------------|--------|-------|-------------|
| `GET /personas/buscar` | `personas.buscar` | Cualquier usuario con sesión | Búsqueda de personas con autocompletado, JSON paginado |
| `POST /personas/rapida` | `personas.rapida` | Quien puede crear personas (`crear`) | Alta rápida desde la célula; devuelve JSON |

### `GET /personas/buscar`

Parámetros (todos opcionales salvo que se indique):

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `q` | texto | Texto a buscar en nombres, apellidos, documento y celular; mínimo 2 caracteres (con menos devuelve vacío) |
| `activas` | `1` | Excluye personas inactivas (por defecto activo) |
| `sin_lideres` | `1` | Excluye a quienes ya son líderes (designar líder) |
| `excluir` | lista de ids | Ids a omitir (por ejemplo, ya seleccionados) |
| `pagina` | entero | Página (20 por página) |

Respuesta 200:

```json
{
  "resultados": [
    { "id": 12, "texto": "Ana Pérez Soto", "apoyo": "CC 1234 · 310 555 9999", "estado": "miembro_activo", "sinDocumento": false }
  ],
  "mas": false
}
```

Reglas: solo personas visibles para el usuario (sede y rol); ordenadas por apellido y nombre; sin datos de otras sedes; `Cache-Control: no-store`.

### `POST /personas/rapida`

Campos: `nombres`, `apellidos`, `celular` y/o `email` (al menos uno), `confirmar_duplicado` (opcional). El estado se fija en `celula`.

| Respuesta | Cuándo |
|-----------|--------|
| 201 `{ "id", "texto", "apoyo", "estado": "celula" }` | Persona creada |
| 409 `{ "duplicado": { "id", "nombre" } }` | Posible duplicado y no se confirmó |
| 422 `{ "errors": { … } }` | Validación |
| 403 | Sin permiso de crear |

Si quien crea es un líder, la persona queda a su cargo (como en el alta normal).

## Rutas modificadas

| Ruta | Cambio |
|------|--------|
| `POST /lideres/designar` | Ya no recibe `red_id`; recibe `persona_id` elegido con el autocompletado |
| `GET /lideres/designar` | Sin búsqueda por «Buscar»: campo con autocompletado y botón «Designar líder» |
| `POST /celulas` y `PUT /celulas/{celula}` | Reciben `integrantes[]` (ids) y los sincronizan |
| `POST /celulas/{celula}/miembros` | Acepta `personas[]`; ya no exige que el líder directo coincida |
| `POST/PUT /ministerios` | `personas[]` se elige con el buscador |
| `GET /redes`, `POST/PUT/DELETE /redes…` | **Eliminadas** |

## Componentes de formulario

| Componente | Propiedades nuevas | Comportamiento |
|------------|--------------------|----------------|
| `x-form.select` | `fuente` (URL), `filtros` | Selección única con autocompletado remoto; la opción actual se carga en el HTML |
| `x-form.multiselect` | `fuente`, `filtros`, `crear` (URL de alta rápida) | Selección múltiple con etiquetas, contador, «Quitar todos» y, si hay `crear`, botón «Crear persona nueva» |

Atributos generados: `data-fuente`, `data-filtros`, `data-crear`. Sin JavaScript conservan el `<select>` nativo con lo ya seleccionado.

## Pantallas

- **Persona (crear/editar)**: una tarjeta «Datos personales» con nombres, apellidos, fecha de nacimiento, celular, correo, estado, tipo y número de documento; luego «Ubicación» y «Liderazgo». Sin «Contacto» ni «Estado y documento» ni campo de red.
- **Ficha de persona**: contacto, estado y documento dentro del bloque de datos personales.
- **Designar líder**: autocompletado; si la persona elegida no tiene documento, aviso con enlace a editarla.
- **Ficha de líder**: tarjeta «Células» y tarjeta «Personas a su cargo» (grilla compacta: nombre, estado, celular, célula) con altura máxima y estado vacío «Aún no lidera a nadie».
- **Célula**: sección «Integrantes» con multiselect autocompletado y «Crear persona nueva».
- **Ministerio**: sección «Integrantes» con el mismo multiselect.
- **Menú**: sin «Redes»; «Ministerios» en Congregación.
