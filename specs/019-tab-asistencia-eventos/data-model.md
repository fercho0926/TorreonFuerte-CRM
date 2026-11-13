# Modelo de Datos: Pestaña Asistencia en Eventos

**No hay cambios de base de datos ni de modelos.** Esta funcionalidad solo reorganiza en qué pestaña de la vista se muestra cada pieza de información que ya existe. Las entidades, sus campos y sus relaciones no cambian.

> **Nota de alcance (implementación, 2026-11-13)**: a pedido explícito del usuario, el panel «Registro por código QR» se quedó en **Datos** (no se trasladó a Asistencia, a diferencia de lo que indican las tablas originales más abajo). Las «tarjetas» de miembros inscritos se convirtieron además en una grilla estándar. Ver spec.md § Clarifications.

## Entidades existentes (sin cambios)

| Entidad (tabla) | Campos relevantes | Papel en la ficha del evento |
|-----------------|--------------------|-------------------------------|
| `Evento` (`eventos`) | `nombre`, `tipo`, `fecha`, `lugar`, `costo`, `estado`, `descripcion`, `registro_abierto`; métodos `totalEsperado()`, `totalRecaudado()` | Contenido de la pestaña **Datos** |
| `EventoPersona` (`evento_persona`) | `evento_id`, `persona_id`, `costo_individual`; métodos `costoAplicable()`, `totalPagado()`, `saldoPendiente()`, `estaPagado()` | Cada inscripción es una tarjeta en la pestaña **Asistencia** |
| `EventoPago` (`evento_pagos`) | `evento_persona_id`, `monto`, `fecha_pago`, `notas` | Tabla de abonos dentro de la tarjeta de cada inscripción, en **Asistencia** |
| `RegistroQr` (vía `TieneRegistroQr` en `Evento`) | `registro_token`, `registro_abierto`, registros recibidos (`registrosQr()`) | Panel «Registro por código QR» y su lista de registrados, en **Asistencia** |

### Relaciones (sin cambios)

```text
Evento (1) ──< EventoPersona (inscripción) >── (1) Persona
                      │
                      └──< EventoPago (abono)

Evento (1) ──< RegistroQr (registrosQr())
```

## Variables de vista por pestaña (lo único que cambia)

`EventosController::show()` sigue devolviendo la misma vista `eventos.show`, pero las variables que arma dependen de la pestaña activa (`$pestana = pestanaActiva($request, ['asistencia'])`), igual que ya hace `SedesController::show()` con sus pestañas adicionales:

| Variable | Pestaña que la necesita | Antes de este cambio | Después de este cambio |
|----------|--------------------------|------------------------|---------------------------|
| `evento` (datos generales: nombre, tipo, fecha, lugar, costo, estado, descripción, totales) | Datos | Siempre calculada | Siempre calculada (sin cambio) |
| `evento->inscripciones` (con `persona` y `pagos` cargados, filtrada por `$visibles`) | Asistencia (tarjetas) y Datos (`totalEsperado()`/`totalRecaudado()` la leen de aquí) | Cargada siempre que la pestaña no fuera «histórico» | Sigue cargada siempre que la pestaña no sea «histórico» — sin cambio, para no perder el filtro de alcance en los totales de «Datos» (ver research.md, D3) |
| `personasSinInscribir` (para el combo del formulario de inscripción) | Asistencia | Calculada siempre que la pestaña no fuera «histórico» | Calculada solo cuando `$pestana === 'asistencia'` |
| `historico` | Histórico | Sin cambios | Sin cambios |

No se agrega ninguna variable nueva: el panel de registro por código QR sigue usando `$evento` directamente (`partials.panel-registro-qr` recibe `'destino' => $evento]`), y el contador «Miembros inscritos (N)» sigue siendo `$evento->inscripciones->count()`.

## Estados de la pestaña «Asistencia»

| Estado | Condición | Contenido visible |
|--------|-----------|---------------------|
| Con inscripciones, con permiso de escritura | `inscripciones` no vacío + `eventos` escritura | Formulario de inscripción, panel QR, tarjetas con control de pagos y acciones (quitar, abonar, eliminar abono) |
| Con inscripciones, solo lectura | `inscripciones` no vacío + `eventos` solo lectura | Tarjetas con control de pagos en modo lectura (sin botones de acción); sin formulario de inscripción; sin panel QR |
| Sin inscripciones, con permiso de escritura | `inscripciones` vacío + `eventos` escritura | Formulario de inscripción, panel QR, mensaje «Este evento aún no tiene miembros inscritos.» |
| Sin inscripciones, solo lectura | `inscripciones` vacío + `eventos` solo lectura | Solo el mensaje de estado vacío; sin panel QR ni formulario |

La visibilidad del panel QR depende de la regla ya existente `gestionarRegistroQr` (requiere escritura en Eventos); no se agrega ninguna condición nueva de permisos (ver research.md, D5).
