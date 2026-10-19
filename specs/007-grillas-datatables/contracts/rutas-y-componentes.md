# Contratos: rutas y componentes de la grilla estándar

## Rutas (por listado `{m}`)

| Método | Ruta | Nombre | Respuesta |
|--------|------|--------|-----------|
| GET | `/{m}` | `{m}.index` | HTML: primera página y tabla con `data-grilla` |
| GET | `/{m}/datos` | `{m}.datos` | JSON DataTables |
| GET | `/{m}/exportar` | `{m}.exportar` | CSV (`text/csv; charset=UTF-8`, BOM, separador `;`) |

Las rutas `datos` y `exportar` se declaran **antes** de `/{m}/{id}` para no chocar con el parámetro.

### `GET /{m}/datos`

Parámetros: `draw`, `start`, `length` (10/25/50/100; otro valor pasa a 25), `order[0][column]` y `order[0][dir]`, `q`, un parámetro por filtro de columna (`nombre`, `estado`, etc.) y los filtros propios del módulo.

Respuesta 200:

```json
{ "draw": 3, "recordsTotal": 120, "recordsFiltered": 8, "data": [["<celda HTML>", "..."]] }
```

Errores: `401` JSON sin sesión; `403` JSON sin permiso; nunca HTML (el manejador global solo responde JSON en `api/*`).

### `GET /{m}/exportar`

Mismos filtros y `orden`/`dir` (clave de columna). Respeta el alcance. En `usuarios` y `solicitudes-clave`: `403` si no es administrador.

## Componente Blade `<x-ui.grilla>`

Entradas: la definición de grilla, la página inicial (`paginate`), los filtros actuales y `puedeExportar`.

Salida: barra (contador, exportar, limpiar) y `<table data-grilla data-url data-exportar data-columnas data-filtros ...>` con fila de filtros por columna y paginador del servidor de respaldo.

La búsqueda general sigue usando `<x-form.layout modo="filtro">` (convención del proyecto: ningún `<form>` fuera de los componentes).

## Módulo JS `grilla/grilla.js`

`iniciarGrillas()` busca `table[data-grilla]` y activa DataTables (modo servidor, `deferLoading`, Responsive, idioma español y diseño inferior con filas por página, información y paginador). Exporta funciones puras comprobables: `parametrosFiltro`, `urlExportar` y `retardo`.
