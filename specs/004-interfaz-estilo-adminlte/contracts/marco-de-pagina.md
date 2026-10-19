# Contrato de interfaz: marco de página

Aplica a toda vista que extiende `layouts.admin`. No cambia rutas ni permisos.

## Secciones de Blade que puede definir una vista

| Sección | Obligatoria | Uso |
|---------|-------------|-----|
| `title` | Sí | Nombre de la pantalla para la pestaña del navegador |
| `page-title` | Sí | Título (h1) del encabezado de página |
| `breadcrumbs` | No | Sustituye el último tramo de las migas por defecto |
| `page-actions` | No | Botones del encabezado (por ejemplo "Nuevo") alineados a la derecha |
| `content` | Sí | Contenido en tarjetas |

## Estructura que genera el layout

1. **Barra superior**: botón del menú (`data-lte-toggle="sidebar"`), enlace a Inicio, selector de sede activa (administrador) o nombre de sede (resto), aviso "Todas las sedes: solo consulta", menú de usuario con nombre, rol, sede y "Cerrar sesión".
2. **Menú lateral**: marca "CRM · {sede}", secciones con encabezado, opción activa resaltada (y sección abierta), pie con nombre y rol. Las opciones y secciones no permitidas no se dibujan.
3. **Encabezado de página**: título, migas de pan y `page-actions`.
4. **Contenido**: contenedor `[data-avisos]` (mensajes de éxito/error, comportamiento de 003) y `content`.
5. **Pie**: nombre del sistema y año.
6. **Marca de agua** de la sede (se conserva el comportamiento actual; el nombre largo se recorta con elipsis).

## Título de la pestaña

`{title} · {config('app.name')}` y, si hay sede, ` · {nombre de sede}`.

## Comportamiento del menú

- ≥ 992 px: abierto por defecto, plegable; la última elección se recuerda en el dispositivo.
- < 992 px: cerrado; al abrirlo se superpone al contenido y se cierra al elegir una opción o al tocar fuera.

## Garantías

- Ninguna vista usa clases propias de Bootstrap 4 (la prueba de guardia lo verifica).
- Los mensajes no desplazan el encabezado de página.
- Los elementos con foco son visibles.
