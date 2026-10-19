# Modelo de Datos: Grillas uniformes

No hay cambios de esquema ni de datos persistentes. Los conceptos son de presentación:

## Columna

| Campo | Descripción |
|-------|-------------|
| clave | identificador único dentro de la grilla (`nombre`, `estado`, `sede`, `acciones`) |
| titulo | texto del encabezado |
| ordenable | booleano; `acciones` nunca |
| filtro | `ninguno`, `texto` o `lista` (con opciones clave a etiqueta) |
| orden/filtro SQL | expresión o subconsulta propia de la definición (lista blanca) |
| celda | vista Blade o closure que devuelve el HTML de la celda |
| exportar | closure que devuelve texto plano para el CSV, o `null` si no se exporta |
| prioridad | prioridad de Responsive en teléfono (1 = nunca se pliega) |
| visible | closure opcional (por ejemplo «Sede» solo para quien ve todas las sedes) |

## Grilla

| Campo | Descripción |
|-------|-------------|
| id | prefijo de rutas (`personas`, `celulas`, etc.) |
| columnas | lista de `Columna` visibles para el usuario |
| consulta(usuario) | `Builder` o `Collection` ya limitada por alcance de rol y sede |
| busquedaGeneral | cómo se aplica `q` |
| exportaRol | quién puede exportar (todos con alcance, o solo administrador) |
| vacio | mensaje e icono del estado vacío |

## Estado de la Grilla (por petición)

`q`, filtros por columna, `orden` (clave de columna), `dir` (`asc` o `desc`), `start`, `length` (10/25/50/100), `inactivos` y demás filtros propios del módulo. Se usa igual para pintar, responder datos y exportar.
