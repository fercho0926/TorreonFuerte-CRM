# Modelo de datos y de presentación

## Datos persistentes

**Sin cambios.** No hay migraciones, columnas ni tablas nuevas (FR-016). La preferencia de plegado del menú se guarda en el navegador del usuario, no en la base de datos.

## Estructuras de presentación

### Menú (`config/menu.php`)

| Campo | Descripción | Reglas |
|-------|-------------|--------|
| `secciones[].clave` | Identificador de la sección (`inicio`, `congregacion`, `actividades`, `administracion`) | Único |
| `secciones[].titulo` | Encabezado visible | Texto en español |
| `secciones[].opciones[]` | Opciones de la sección | Una sección sin opciones visibles no se dibuja |
| `opcion.etiqueta` | Texto de la opción | Obligatorio |
| `opcion.icono` | Clase de icono de Font Awesome | Obligatorio |
| `opcion.ruta` | Nombre de ruta destino | Debe existir en `routes/web.php` |
| `opcion.activa_en` | Patrones de nombre de ruta que la marcan como activa (por ejemplo `personas.*`) | Por defecto `{ruta sin sufijo}.*` |
| `opcion.permiso` | Habilidad de `Gate` (y modelo, si aplica) requerida | Opcional; sin permiso, visible para toda sesión |

Opciones iniciales: Inicio (Dashboard); Congregación (Personas, Líderes, Sin líder asignado, Células, Redes); Actividades (Eventos, Encuentros, Ministerios); Administración (Sedes, Usuarios).

### Estados (`App\Support\Estados`)

| Estado | Etiqueta | Color |
|--------|----------|-------|
| `activo`, `activa`, `miembro_activo`, `realizado`, `pagado`, `si` | Activo / Miembro activo / Realizado / Pagado / Sí | `success` |
| `planificado`, `en_curso`, `nuevo_creyente` | Planificado / En curso / Nuevo creyente | `info` |
| `visitante`, `pendiente` | Visitante / Pendiente | `warning` |
| `inactivo`, `inactiva`, `no` | Inactivo / Inactiva / No | `secondary` |
| `cancelado` | Cancelado | `danger` |
| `lider` | Líder | `primary` |

Un estado desconocido se muestra con su texto y el color `secondary` (no falla).

### Migas de pan

Lista ordenada de (texto, ruta opcional). Por defecto: Inicio › Sección › Opción activa › título de la pantalla; el último tramo no es enlace.

## Transiciones de estado

Ninguna. La interfaz no cambia ciclos de vida de registros.
