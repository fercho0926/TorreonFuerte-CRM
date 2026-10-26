# Modelo de Datos: Historial de cambios en toda la plataforma

**Funcionalidad**: `015-historial-cambios` | **Fecha**: 2026-10-23

## Tabla nueva `historial_cambios` (modelo `App\Models\CambioHistorial`)

Una fila es una **entrada de historial**: la constancia inalterable de una acción sobre un registro.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | bigint autoincremental | no | |
| `created_at` | datetime | no | Fecha y hora de la acción (zona horaria de la aplicación). No hay `updated_at`. |
| `sede_id` | bigint | sí | Sede donde ocurrió. Nulo en registros globales (roles, niveles del plan, sedes vistas como entidad, usuarios administradores). **Sin clave foránea.** |
| `actor` | string(12) | no | `usuario`, `qr`, `automatico` o `sistema`. |
| `user_id` | bigint | sí | Usuario que hizo la acción, o el que la provocó si `actor = automatico`. **Sin clave foránea**: borrar el usuario no toca el historial. |
| `actor_nombre` | string(120) | no | Nombre del usuario en ese momento, «Registro público por QR · *nombre del QR*», «Automático» o «Sistema». |
| `modulo` | string(30) | no | Clave del módulo (`Modulos::*`, más `sedes` y `roles`). |
| `accion` | string(40) | no | Clave de la acción en el catálogo `AccionesHistorial` (ver abajo). |
| `categoria` | string(24) | sí | Sección del histórico del miembro: `datos`, `encuentros_retiros`, `celulas_ministerios`, `liderazgo`, `niveles`, `sede`. Nula si el sujeto no es una persona. |
| `sujeto_tipo` | string(30) | no | Alias del registro afectado (`AccionesHistorial::CLASES`): `persona`, `celula`, `ministerio`, `evento`, `encuentro`, `nivel`, `sede`, `usuario`, `rol`, `solicitud_clave`. Las inscripciones, abonos, reuniones y avances se registran en el evento, la célula o el miembro al que pertenecen (con el otro registro como relacionado), no como sujetos propios. No se usa el *morph map* de Eloquent (lo usa `registros_qr`). |
| `sujeto_id` | bigint | no | Identificador del registro afectado (puede ya no existir). |
| `sujeto_nombre` | string(160) | no | Nombre legible del registro en ese momento («Juan Pérez», «Célula Los Olivos»). |
| `relacionado_tipo` | string(30) | sí | Segundo registro involucrado (por ejemplo, el encuentro en «Asistió al encuentro»). |
| `relacionado_id` | bigint | sí | |
| `relacionado_nombre` | string(160) | sí | Nombre del relacionado en ese momento. |
| `lote` | uuid | sí | Agrupa la entrada resumen de una acción masiva con las entradas individuales que generó. |
| `antes` | json | sí | Valores crudos anteriores (sin secretos), como en los historiales anteriores. |
| `despues` | json | sí | Valores crudos nuevos (sin secretos). |
| `detalle` | json | sí | Ver estructura abajo. |

**Índices**: `(sujeto_tipo, sujeto_id, categoria, created_at)`, `(sujeto_tipo, sujeto_id, created_at)`, `(sede_id, created_at)`, `(user_id, created_at)`, `(modulo, created_at)`, `(relacionado_tipo, relacionado_id)`, `(lote)`.

**Reglas**:
- Inalterable: el modelo prohíbe `update` y `delete` (lanza excepción en `updating` y `deleting`).
- Sin claves foráneas hacia sujetos, relacionados ni usuarios (RF-008).
- Lectura con el filtro global de sede (rasgo `PerteneceASede` en modo lectura; `exigeSede()` devuelve `false` para entradas globales), salvo la excepción del miembro trasladado (ver más abajo).

### Estructura de `detalle`

```json
{
  "cambios": [
    {
      "campo": "direccion",
      "etiqueta": "Dirección",
      "antes": "Calle 10 # 5-20",
      "despues": "Carrera 8 # 3-15",
      "antes_valor": "Calle 10 # 5-20",
      "despues_valor": "Carrera 8 # 3-15",
      "largo": false,
      "secreto": false,
      "modulo_sensible": null
    }
  ],
  "datos": { "Nombre": "Célula Los Olivos", "Líder": "Ana Pérez" },
  "agregados": [ { "id": 12, "nombre": "Juan Pérez" } ],
  "quitados":  [ { "id": 15, "nombre": "Luisa Gómez" } ],
  "fecha_evento": "2026-10-18",
  "origen_qr": "QR del evento Retiro Libres para servir",
  "legado": { "accion": "editada", "antes": { }, "despues": { } }
}
```

- `cambios`: una fila por campo que cambió (en ediciones). Si `secreto = true`, `antes` y `despues` se guardan vacíos y la vista dice «Clave cambiada». `largo = true` cuando algún valor pasa de 120 caracteres (la vista lo muestra con `<details>`). `modulo_sensible` (por ejemplo `eventos` en montos) oculta el valor a quien no tiene lectura en ese módulo.
- `datos`: foto legible del registro (en altas y eliminaciones).
- `agregados` / `quitados`: en la entrada resumen de una acción masiva.
- `fecha_evento`: fecha del encuentro, reunión o evento, si aplica.
- `legado`: solo en entradas migradas desde las tablas antiguas.

## Catálogo de acciones (`App\Support\AccionesHistorial`)

> **Ajuste de implementación**: el catálogo se organiza **por tipo de registro** (la clave de una acción es única dentro de su tipo) y **conserva las claves de los historiales anteriores** (`creada`, `editada`, `inactivada`, `lider`, `rol_lider` en miembros; `creada`, `editada`, `pastor_cambiado` en sedes; `creado`, `editado` en roles y niveles), así la migración de datos no traduce nada. La tabla de abajo es la selección del diseño; el catálogo vigente está en el código y lo vigilan `AccionesHistorialTest` y `CatalogoCompletoTest`.

Cada acción tiene clave, etiqueta en español, módulo y categoría (para personas). Selección principal:

| Módulo | Acciones (clave → etiqueta) | Categoría en el miembro |
|--------|-----------------------------|-------------------------|
| miembros | `creado` → Creado, `editado` → Datos editados, `estado` → Cambio de estado, `inactivado` → Inactivado, `reactivado` → Reactivado, `eliminado` → Eliminado, `restaurado` → Restaurado, `creado_qr` / `actualizado_qr` / `reactivado_qr` → … por QR de miembros | datos |
| lideres / sin_lider | `lider_cambiado` → Cambio de líder, `designado_lider` → Designado como líder, `quitado_lider` → Dejó de ser líder | liderazgo |
| encuentros | `encuentro_creado`, `encuentro_editado`, `encuentro_eliminado`, `inscrito_encuentro` → Inscrito al encuentro, `quitado_encuentro` → Retirado del encuentro, `asistio_encuentro` → Asistió al encuentro, `no_asistio_encuentro` → Asistencia retirada, `pago_encuentro` → Valor pagado, `asistencia_actualizada` (resumen) | encuentros_retiros |
| eventos | `evento_creado`, `evento_editado`, `evento_eliminado`, `inscrito_evento` → Inscrito al evento, `quitado_evento` → Retirado del evento, `abono_registrado` → Abono registrado, `abono_eliminado` → Abono eliminado | encuentros_retiros |
| celulas | `celula_creada`, `celula_editada`, `celula_eliminada`, `ingreso_celula` → Ingresó a la célula, `salida_celula` → Salió de la célula, `reunion_registrada`, `reunion_eliminada`, `asistio_reunion` → Asistió a reunión de célula | celulas_ministerios |
| ministerios | `ministerio_creado`, `ministerio_editado`, `ministerio_eliminado`, `ingreso_ministerio`, `salida_ministerio`, `integrantes_actualizados` (resumen) | celulas_ministerios |
| niveles | `nivel_creado`, `nivel_editado`, `nivel_reordenado`, `nivel_desactivado`, `nivel_reactivado`, `nivel_eliminado`, `nivel_registrado` → Nivel completado, `nivel_quitado` → Nivel retirado, `nivel_automatico` → Nivel completado por encuentro | niveles |
| sedes | `sede_creada`, `sede_editada`, `sede_desactivada`, `sede_reactivada`, `pastores_cambiados`, `trasladado_desde` → Llegó por traslado, `trasladado_a` → Trasladado a otra sede | sede (en el miembro) |
| usuarios | `usuario_creado`, `usuario_editado`, `usuario_eliminado`, `rol_cambiado`, `clave_generada`, `clave_cambiada`, `clave_enviada` | — |
| roles | `rol_creado`, `rol_editado` (con cambios por módulo «Eventos: lectura → escritura»), `rol_duplicado`, `rol_eliminado` | — |
| solicitudes_clave | `solicitud_creada`, `solicitud_atendida`, `solicitud_descartada` | — |
| registro_miembros / QR | `qr_abierto`, `qr_regenerado`, `qr_cerrado` | — |

El catálogo es la única fuente de etiquetas; una prueba falla si se escribe una acción que no está en él.

## Cambios a tablas existentes

| Tabla | Cambio | Motivo |
|-------|--------|--------|
| `personas` | + `trasladada_desde_persona_id` (bigint, nulo, índice, sin clave foránea) | Une la persona nueva con la de origen tras un traslado (D8). Se rellena para traslados anteriores a partir de `persona_cambios.despues.persona_origen_id`. |
| `rol_permisos` | + fila `modulo = control_cambios` por rol: `admin` → 1 (lectura), demás → 0 | Módulo nuevo de solo lectura (RF-022). |
| `persona_cambios`, `sede_cambios`, `rol_cambios`, `nivel_cambios` | Se copian a `historial_cambios` y luego se eliminan | Historial unificado (D11, CE-007). |

## Definición de campos por modelo (rasgo `RegistraHistorial`)

Cada modelo con historial declara:

| Elemento | Ejemplo para `Persona` |
|----------|------------------------|
| `aliasHistorial()` | `persona` |
| `moduloHistorial()` | `miembros` |
| `nombreHistorial()` | `nombre_completo` |
| `camposHistorial()` | `nombres` → Nombres, `apellidos` → Apellidos, `celular` → Celular, `direccion` → Dirección, `barrio` → Barrio, `ciudad` → Ciudad, `email` → Correo, `estado` → Estado (traducido con `Estados`), `lider_id` → Líder (nombre del líder), `fecha_nacimiento` → Fecha de nacimiento (fecha local), … |
| `camposIgnorados()` | `updated_at`, `inactivado_en`, `trasladada_en`, columnas técnicas |
| `camposSecretos()` | (global) más los propios del modelo, por ejemplo `token` en `RegistroQr` |
| `sujetosHistorial()` | Para `EventoPago` y `EventoPersona`: escribe la entrada en el evento **y** en la persona (relacionado = evento). Para `AvanceNivel`: en la persona (relacionado = nivel). |

Los campos que no estén en `camposHistorial()` ni en los ignorados se registran con su nombre técnico y una prueba lo detecta para que se les ponga etiqueta.

## Reglas de visibilidad al leer

| Consulta | Regla |
|----------|-------|
| Pestaña «Histórico» de un registro | Se autoriza igual que su ficha. Filas: `sujeto_tipo` y `sujeto_id` del registro (y, en personas, de toda su cadena de traslados, sin el filtro de sede). |
| Enlace al relacionado | Solo si el usuario tiene lectura en el módulo del relacionado y puede verlo (misma sede o alcance). |
| Campo con `modulo_sensible` | Se muestra «—» si el usuario no tiene lectura en ese módulo. |
| «Control de cambios» | Requiere `modulo:control_cambios`. Filtro de sede del contexto. Sin acceso total: solo entradas cuyo sujeto sea una persona visible o una célula visible. |

## Transiciones

Las entradas no tienen estados: se crean una vez y no cambian. Lo que cambia es el registro que describen, cuyo ciclo (creado → editado → inactivado → reactivado → eliminado / restaurado) queda reflejado como una secuencia de entradas.
