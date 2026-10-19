# Modelo de datos (Fase 1): Formularios modernos

**No hay cambios de datos**: no se crean ni modifican tablas, columnas, migraciones ni reglas de negocio. Los formularios siguen enviando **los mismos campos con los mismos nombres** y el servidor aplica las mismas validaciones y permisos (FR-021, SC-009).

Lo que se modela aquí son los **conceptos de interfaz** (componentes y su estado), útiles para diseñar y probar.

## Sección de formulario

| Propiedad | Tipo | Notas |
|-----------|------|-------|
| titulo | texto | Obligatorio (ej. "Datos personales") |
| descripcion | texto, opcional | Una frase que explica la sección |
| icono | texto | Clase de Font Awesome (ej. `fa-user`) |
| id | texto | Identificador para enlazar el resumen de errores |

Una sección contiene uno o más campos y puede ocultarse completa por una regla de dependencia (ej. "Liderazgo → red y zona").

## Campo

| Propiedad | Tipo | Notas |
|-----------|------|-------|
| nombre | texto | **Igual al actual** (`nombres`, `lider_id`, `celular`, …) |
| etiqueta | texto | Obligatoria; va encima del campo |
| tipo | `texto`, `email`, `telefono`, `numero`, `clave`, `fecha`, `monto`, `area`, `seleccion`, `multiseleccion`, `interruptor` | Determina el control nativo y la mejora |
| icono | texto | Ícono a la izquierda |
| obligatorio | booleano | Marca visible + `required`/`aria-required` |
| ayuda | texto, opcional | Texto breve bajo el campo |
| ejemplo | texto, opcional | Placeholder de formato |
| valor | cualquiera | `old(nombre)` o el valor actual del modelo |
| error | texto, opcional | Mensaje del servidor (`$errors`) o del cliente |
| estado | `neutro`, `correcto`, `error` | Solo visual; no se envía |
| visible_si / requerido_si | `campo:valor`, opcional | Reglas de dependencia (ver contrato) |

**Restricciones por tipo** (se expresan como atributos nativos, sin cambiar lo que valida el servidor):

- `email`: `type="email"`, máx. 255.
- `telefono`: `type="tel"`, `inputmode="tel"`, máx. 20; máscara de presentación; se envía **sin máscara**.
- `fecha`: valor enviado `Y-m-d`; `max` configurable (ej. hoy en nacimiento); formato visible `d/m/Y` con JS.
- `monto`: `inputmode="decimal"`; separador de miles solo visual; se envía el número plano (`50000.00`).
- `seleccion` / `multiseleccion`: opciones con `value` = id actual y `data-apoyo` (documento/celular) opcional; búsqueda si hay más de 10 opciones.
- `interruptor`: casilla con valor `1` (como hoy); el servidor sigue recibiendo `es_lider`, `confirmar_duplicado`, `activa`, etc.

## Formulario

| Propiedad | Tipo | Notas |
|-----------|------|-------|
| accion y metodo | texto | Igual que hoy (incluye `@method('PUT'/'DELETE')` y `@csrf`) |
| secciones | lista | Una o más |
| acciones | guardar (texto del botón) y cancelar (enlace) | Barra fija |
| modo | `completo`, `incrustado`, `filtro`, `confirmacion` | Cambia el aspecto (tarjeta completa, bloque dentro de una pantalla, filtros en línea, acción delicada) |
| estado_envio | `reposo`, `enviando` | Bloquea el botón mientras se envía |
| huella | texto | Instantánea de valores al cargar, para detectar cambios sin guardar (en memoria, **nunca persistida**) |

## Confirmación

| Propiedad | Tipo | Notas |
|-----------|------|-------|
| titulo | texto | Ej. "¿Quitar el rol de líder?" |
| mensaje | texto | Detalle de las consecuencias (ej. "Sus 12 personas quedarán sin líder") |
| accion_confirmar / accion_cancelar | texto | Botones |
| peligro | booleano | Estilo rojo para eliminar o inactivar |

## Aviso (éxito / error)

| Propiedad | Tipo | Notas |
|-----------|------|-------|
| tipo | `exito`, `error`, `info` | Éxito se cierra solo en 5 s; error permanece |
| mensaje | texto | Proviene del mensaje flash de la sesión que ya existe |

## Inventario de formularios y su modo

| Pantalla / formulario | Archivo(s) | Modo |
|-----------------------|-----------|------|
| Persona (crear/editar) | `personas/_form`, `create`, `edit` | completo |
| Designar líder | `lideres/designar` | incrustado por persona |
| Quitar rol de líder | `lideres/quitar` | completo + confirmación |
| Célula (crear/editar) | `celulas/_form`, `create`, `edit` | completo |
| Agregar miembro, registrar reunión y asistencia | `celulas/show` | incrustado + lista de asistencia |
| Encuentro (crear/editar) y asistencia | `encuentros/_form`, `create`, `edit`, `show` | completo + lista de asistencia |
| Evento (crear/editar) | `eventos/_form`, `create`, `edit` | completo |
| Inscribir persona, registrar abono, quitar | `eventos/show` | incrustado + confirmación |
| Ministerio (crear/editar) e integrantes | `ministerios/_form`, `create`, `edit`, `show` | completo + lista |
| Usuario (crear/editar) | `users/create`, `edit` | completo |
| Sede (crear/editar) y cambio de pastor o pastora | `sedes/create`, `edit`, `pastores`, `_datos`, `_cuenta` | completo |
| Trasladar persona | `traslados/create` | completo |
| Redes y zonas (crear, editar en línea, fusionar) | `redes/index`, `zonas/index` | incrustado |
| Asignar líder en "Sin líder asignado" | `sin-lider/index` | incrustado por persona |
| Inicio de sesión | `auth/login` | completo (versión compacta) |
| Filtros de listados | `personas/index`, `lideres/index`, `sin-lider/index`, `users/index`, `celulas/index` (donde aplique) | filtro |
| Acciones delicadas (inactivar, reactivar, eliminar, quitar, trasladar, desactivar sede) | formularios `POST`/`DELETE` en listados y fichas | confirmación |
| Cerrar sesión y cambio de sede activa (barra superior) | `layouts/admin` | incrustado (sin cambios de estilo mayores) |

## Reglas que se conservan sin cambios

- Rutas, nombres de campos, validaciones del servidor, permisos por rol y filtro de sede.
- Mensajes flash (`success`, `error`) y errores de validación de Laravel; solo cambia cómo se presentan.
- Valores enviados: celulares sin máscara, montos numéricos planos, fechas `Y-m-d`.
