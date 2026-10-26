# Contrato: Rutas, pestañas y permisos

**Funcionalidad**: `015-historial-cambios`

## Módulo nuevo del catálogo

| Clave | Etiqueta | Icono | Solo lectura | Descripción | Permiso por defecto |
|-------|----------|-------|--------------|-------------|---------------------|
| `control_cambios` | Control de cambios | `fa-history` | sí | Todo lo que ha cambiado en la plataforma: quién, cuándo y qué | `admin`: lectura. `pastor`, `lider_celula`, `user` y roles personalizados: sin acceso (se puede conceder desde Roles). |

Menú: opción «Control de cambios» en el grupo de administración, visible solo con lectura en el módulo.

## Rutas nuevas

| Método | URI | Nombre | Middleware | Respuesta |
|--------|-----|--------|------------|-----------|
| GET | `/control-cambios` | `control-cambios.index` | `modulo:control_cambios` | Página con la grilla estándar `control-cambios` (sin botón de exportar). |
| GET | `/control-cambios/datos` | `control-cambios.datos` | `modulo:control_cambios` | JSON de la grilla (mismo contrato que las demás grillas, spec 007). |

**No existen**: `control-cambios.exportar` ni ninguna ruta `PUT`, `PATCH` o `DELETE` sobre el historial. Pedir `/control-cambios/exportar` responde 404.

### Columnas y filtros de la grilla `control-cambios`

| Columna | Orden | Filtro | Notas |
|---------|-------|--------|-------|
| Fecha y hora | sí (inicial, descendente) | rango `desde` / `hasta` (fecha y hora) | |
| Usuario | sí | selector de usuarios + texto (para actores especiales) | Usa `actor_nombre`. |
| Sede | sí | selector | Solo en modo «Todas las sedes». |
| Módulo | sí | selector (catálogo) | |
| Acción | sí | selector (catálogo, dependiente del módulo) | |
| Registro | sí | texto (busca en `sujeto_nombre` y `relacionado_nombre`, incluye eliminados) | Enlace a la ficha en `?pestana=historico` si existe y se puede ver. |
| Detalle | no | — | Resumen de 2 líneas y botón «Ver detalle» (`<details>`). |

Búsqueda general: sí (registro, usuario y detalle). Filas por página: 10, 25, 50 y 100. En teléfono: tarjetas.

## Pestaña «Histórico» en las fichas

Todas usan `?pestana=historico` sobre la ruta de la ficha; sin el parámetro se abre la pestaña de datos.

| Ficha | URL de la pestaña | Autorización | Presentación |
|-------|-------------------|--------------|--------------|
| Miembro | `personas/{persona}?pestana=historico` | `AlcanceService::puedeVerPersona` | **Secciones** (ver abajo) |
| Líder | `lideres/{lider}?pestana=historico` | `lideresVisibles` | Secciones (es un miembro) |
| Célula | `celulas/{celula}?pestana=historico` | `celulasVisibles` | Lista única + filtro por acción |
| Ministerio | `ministerios/{ministerio}?pestana=historico` | `modulo:ministerios` | Lista única |
| Evento | `eventos/{evento}?pestana=historico` | `modulo:eventos` | Lista única |
| Encuentro | `encuentros/{encuentro}?pestana=historico` | `modulo:encuentros` | Lista única |
| Nivel | `niveles/{nivel}/edit?pestana=historico` | `modulo:niveles` | Lista única |
| Sede | `sedes/{sede}?pestana=historico` | administrador | Lista única (se suma a las pestañas Datos, Organigrama y Registro de miembros) |
| Usuario | `users/{user}/edit?pestana=historico` | `modulo:usuarios` | Lista única. Nunca muestra claves. |
| Rol | `roles/{rol}?pestana=historico` | administrador | Lista única (reemplaza la tarjeta «Historial del rol») |

### Parámetros de la pestaña

| Parámetro | Valores | Aplica a |
|-----------|---------|----------|
| `desde`, `hasta` | fecha `AAAA-MM-DD` | todas |
| `usuario` | id de usuario o `qr` / `automatico` / `sistema` | todas |
| `accion` | clave del catálogo | fichas con lista única |
| `pagina_<categoria>` | entero ≥ 1 (`pagina_datos`, `pagina_encuentros_retiros`, …) | miembro y líder |
| `pagina` | entero ≥ 1 | fichas con lista única |

Un valor no válido se ignora (no hay error 500). El formulario de filtros es un `GET` normal (funciona sin JavaScript).

### Secciones del miembro

En este orden, cada una con título, contador, lista (20 por página, de la más reciente a la más antigua) y paginador propio; si no tiene entradas, muestra «Sin movimientos».

1. Cambios de datos (`datos`)
2. Encuentros y retiros (`encuentros_retiros`)
3. Células y ministerios (`celulas_ministerios`)
4. Liderazgo (`liderazgo`)
5. Niveles (`niveles`)
6. Sede (`sede`)

### Cada entrada (componente `<x-historial.entrada>`)

- Fecha y hora («23 oct 2026, 3:45 p. m.»), y la fecha del evento cuando sea distinta («Encuentro del 18 oct 2026»).
- Acción (etiqueta del catálogo) con icono y texto, no solo color.
- Actor (`actor_nombre`; «Automático · por Ana Pérez» cuando aplica).
- Registro relacionado: enlace si se puede ver; si no, solo el nombre.
- Detalle: lista «Campo: antes → después» («(vacío)» para valores vacíos; «Clave cambiada» para secretos; «—» para valores de módulos sin acceso); textos largos dentro de `<details>`.
- Sede de origen, si es distinta de la sede activa (histórico de un miembro trasladado).
- Ninguna acción de editar ni borrar.

## Cambios de permisos existentes

- Se retira la habilidad `verBitacora` de `PersonaPolicy`: el historial del miembro lo ve quien ve su ficha (RF-021).
- La tarjeta «Historial de cambios» de `personas/show` y la tarjeta «Historial del rol» de `roles/show` se reemplazan por la pestaña.
