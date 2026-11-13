# Especificación de Funcionalidad: Pestaña Asistencia en Eventos

**Rama de Funcionalidad**: `019-tab-asistencia-eventos`

**Creado**: 2026-11-13

**Estado**: Borrador

**Entrada**: Descripción de usuario: "en eventos, cuando creo un nuevo evento puedo ver el QR y la información del evento y está muy bien, pero quiero separar en un tab que quede entre datos e histórico, llamado Asistencia, allí en asistencia ponemos la opción de inscribir los miembros y la card también de miembros inscritos para llevar el control de pagos de miembros y demás, para que no queden esas cards en Datos del evento tab, entonces quedamos con 3 tabs, Datos, Asistencia, Histórico"

## Clarifications

### Session 2026-11-13

- Q: ¿Dónde deben mostrarse los montos de «Total esperado» y «Total recaudado» del evento: en la pestaña «Datos» (como hoy) o junto al control de pagos en «Asistencia»? → A: Se mantienen en la pestaña «Datos», junto con el resto de la información general del evento; no se duplican en «Asistencia».
- Corrección técnica (planificación, 2026-11-13): el panel de código QR ya exige permiso de **escritura** sobre el módulo de eventos (regla `gestionarRegistroQr` existente antes de esta funcionalidad); por lo tanto un usuario de solo lectura nunca lo ve, ni hoy en «Datos» ni después en «Asistencia». Se ajustaron RF-004, la Historia 2 (escenario 4) y el primer caso límite para reflejar este comportamiento ya existente, sin cambiar ninguna regla de permisos.
- Corrección de alcance (implementación, 2026-11-13): el usuario pidió explícitamente que el panel «Registro por código QR» **se quede en «Datos»** y no se traslade a «Asistencia» («no los quiero en asistencia»). Esto revierte la decisión original de este documento (que lo movía a «Asistencia» junto con las tarjetas de inscripción). Se ajustaron RF-002, RF-003, RF-004, las Historias 1 y 2, los Casos Límite, las Entidades Clave y los Supuestos. «Asistencia» conserva el formulario de inscripción y el control de pagos; «Datos» conserva la información general del evento **y** el panel de código QR.
- Corrección de alcance (implementación, 2026-11-13): las tarjetas de «Miembros inscritos» se convirtieron en una grilla estándar (`<x-ui.grilla>` + `App\Grillas\InscripcionesEventoGrilla`, specs/007-grillas-datatables) por el volumen de miembros a gestionar; permite ordenar, filtrar por columna y buscar por nombre. El botón «Registrar abono» de cada fila abre un panel propio (un `<details>` nativo, sin necesidad de JavaScript) con el historial de abonos y el formulario para agregar uno nuevo (monto, fecha, nota). Se ajustó RF-003 y RF-008 en consecuencia.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Ver los datos del evento sin el ruido de inscripciones (Prioridad: P1)

Como usuario que consulta la ficha de un evento, quiero que la pestaña «Datos» muestre la información general del evento (nombre, tipo, fecha, lugar, costo, estado, descripción y totales) junto con el panel de registro por código QR, para poder revisar esa información rápidamente sin desplazarme entre las tarjetas de inscripción y el control de pagos.

**Por qué esta prioridad**: Es el problema central que reporta el usuario: hoy la pestaña «Datos» mezcla información general con las tarjetas de inscripción y pagos, lo que la hace larga y confusa. El panel de código QR no es parte de ese ruido: es información/gestión del evento igual que sus datos generales, así que se queda en «Datos».

**Prueba independiente**: Se puede probar abriendo la ficha de cualquier evento existente y verificando que la pestaña «Datos» ya no contiene el formulario de inscripción ni las tarjetas de miembros inscritos, pero sigue mostrando el panel de registro por código QR (con permiso de escritura).

**Escenarios de Aceptación**:

1. **Dado** un evento existente con miembros inscritos y pagos registrados, **Cuando** el usuario abre la ficha del evento, **Entonces** la pestaña «Datos» muestra la información general del evento y el panel de código QR, sin tarjetas de inscripción ni de pagos.
2. **Dado** un evento sin miembros inscritos, **Cuando** el usuario abre la pestaña «Datos», **Entonces** no aparece ningún mensaje ni formulario relacionado con inscripciones (el panel de código QR sí se muestra, si hay permiso de escritura).

---

### Historia de Usuario 2 - Gestionar inscripciones y pagos desde la pestaña Asistencia (Prioridad: P1)

Como usuario con permiso de escritura sobre el módulo de eventos, quiero una pestaña «Asistencia» ubicada entre «Datos» e «Histórico» donde pueda inscribir miembros y gestionar el control de pagos de cada miembro inscrito, para concentrar toda la gestión operativa de inscripción y pagos en un solo lugar.

**Por qué esta prioridad**: Es el valor principal que pide el usuario: trasladar la gestión operativa (inscribir, pagar) a su propio espacio, separado de los datos generales y del panel de código QR.

**Prueba independiente**: Se puede probar abriendo la pestaña «Asistencia» de un evento y verificando que desde allí se puede inscribir un miembro y registrar o eliminar abonos de pago, sin salir de la pestaña.

**Escenarios de Aceptación**:

1. **Dado** un evento con cupos disponibles, **Cuando** el usuario con permiso de escritura abre la pestaña «Asistencia», **Entonces** ve el formulario para inscribir un miembro y la grilla de miembros ya inscritos con su estado de pago.
2. **Dado** un miembro inscrito con pagos pendientes, **Cuando** el usuario registra un abono desde la pestaña «Asistencia», **Entonces** el sistema guarda el abono y el usuario permanece en la pestaña «Asistencia» viendo el saldo actualizado.
3. **Dado** un miembro inscrito sin pagos registrados, **Cuando** el usuario con permiso de escritura lo quita del evento desde la pestaña «Asistencia», **Entonces** el miembro deja de aparecer en la lista y el usuario permanece en la pestaña «Asistencia».
4. **Dado** un usuario sin permiso de escritura sobre eventos, **Cuando** abre la pestaña «Asistencia», **Entonces** puede ver la grilla de miembros inscritos con su estado de pago, pero no ve el formulario para inscribir miembros, ni los controles para registrar pagos o quitar miembros.

---

### Historia de Usuario 3 - Navegar entre las tres pestañas en el orden correcto (Prioridad: P2)

Como usuario que consulta un evento, quiero ver siempre las pestañas en el orden «Datos», «Asistencia», «Histórico», para ubicar rápidamente la información que busco sin confundirme con un orden distinto.

**Por qué esta prioridad**: Mantener el orden esperado evita que el usuario pierda tiempo buscando la pestaña correcta, pero no bloquea el valor principal de las historias 1 y 2.

**Prueba independiente**: Se puede probar abriendo la ficha de cualquier evento y verificando que las tres pestañas aparecen en ese orden, y que los enlaces con `?pestana=datos`, `?pestana=asistencia` y `?pestana=historico` llevan cada uno a la pestaña correspondiente.

**Escenarios de Aceptación**:

1. **Dado** la ficha de un evento, **Cuando** el usuario la abre, **Entonces** ve las pestañas en el orden Datos, Asistencia, Histórico.
2. **Dado** un enlace guardado previamente con `?pestana=datos` o `?pestana=historico`, **Cuando** el usuario lo abre, **Entonces** el sistema muestra la pestaña correspondiente igual que antes de este cambio.

---

### Casos Límite

- ¿Qué sucede si un evento no tiene ningún miembro inscrito? La pestaña «Asistencia» debe mostrar el formulario de inscripción (solo con permiso de escritura) junto con el mensaje de que aún no hay miembros inscritos, sin que ese contenido aparezca en «Datos».
- ¿Qué sucede si el usuario intenta acceder a una pestaña con un valor de `pestana` no reconocido? El sistema debe mostrar la pestaña «Datos» por defecto, igual que el comportamiento actual.
- ¿Qué sucede con las acciones de inscribir, quitar, pagar o eliminar un abono una vez movidas a «Asistencia»? Todas deben seguir funcionando igual que hoy y devolver al usuario a la pestaña «Asistencia» después de la acción, no a «Datos».
- ¿Qué sucede con los registros ya recibidos por código QR antes de este cambio? Siguen asociados al evento sin pérdida de datos; el panel de registro QR no cambia de pestaña (se queda en «Datos», como ya estaba).

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: La ficha de un evento DEBE mostrar exactamente tres pestañas, en este orden: «Datos», «Asistencia», «Histórico».
- **RF-002**: La pestaña «Datos» DEBE mostrar la información general del evento (nombre, tipo, fecha, lugar, costo por miembro, estado, descripción, total esperado y total recaudado) y el panel de registro por código QR, sin el formulario de inscripción ni las tarjetas o grilla de miembros inscritos. Los montos de total esperado y total recaudado permanecen solo en «Datos» y no se duplican en «Asistencia».
- **RF-003**: La pestaña «Asistencia» DEBE incluir el formulario para inscribir un nuevo miembro (cuando el usuario tiene permiso de escritura sobre el módulo de eventos) y una grilla (con orden, filtro por columna y búsqueda por nombre) de los miembros inscritos con su control de pagos (costo aplicable, total pagado, saldo pendiente); cada fila incluye un botón «Registrar abono» que abre el historial de abonos de ese miembro y el formulario para agregar uno nuevo. El panel de registro por código QR NO vive en esta pestaña.
- **RF-004**: Un usuario sin permiso de escritura sobre el módulo de eventos DEBE poder ver la pestaña «Asistencia» en modo de solo lectura (grilla de miembros inscritos con su estado de pago), sin ver el formulario de inscripción ni los controles para registrar pagos, eliminar abonos o quitar miembros. El panel de código QR, en «Datos», ya exige permiso de escritura sobre eventos desde antes de este cambio.
- **RF-005**: Toda acción realizada dentro de la pestaña «Asistencia» (inscribir miembro, quitar miembro, registrar abono, eliminar abono) DEBE devolver al usuario a la pestaña «Asistencia» después de completarse, no a la pestaña «Datos».
- **RF-006**: La pestaña «Histórico» DEBE mantener su posición (siempre al final) y su contenido sin cambios respecto al comportamiento actual.
- **RF-007**: Los enlaces existentes con `?pestana=datos` o `?pestana=historico` DEBEN seguir funcionando igual que antes de este cambio; el nuevo enlace para la pestaña de asistencia DEBE usar `?pestana=asistencia`.
- **RF-008**: El número de miembros inscritos DEBE ser visible junto a la grilla, dentro de la pestaña «Asistencia» (lo entrega el contador de resultados de la grilla estándar).

### Entidades Clave

- **Evento**: Representa la actividad programada (nombre, tipo, fecha, lugar, costo, estado, descripción, totales esperado y recaudado). Es el contenido de la pestaña «Datos».
- **Inscripción**: Relación entre un miembro y un evento; incluye el costo aplicable, el total pagado y el saldo pendiente. Es una fila de la grilla de la pestaña «Asistencia».
- **Pago (abono)**: Monto, fecha y notas de un pago parcial o total hecho por un miembro inscrito. Se gestiona desde el panel de la fila del miembro, en «Asistencia».
- **Registro por código QR**: Mecanismo existente de autoinscripción o marcación de asistencia mediante un código QR asociado al evento. Se queda en la pestaña «Datos» (no se traslada).

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **SC-001**: El 100 % de los eventos existentes muestran la información general y el panel de código QR en la pestaña «Datos» sin ninguna tarjeta de inscripción o pago visible en ella.
- **SC-002**: Un usuario puede inscribir a un miembro y registrar un abono sin salir de la pestaña «Asistencia» en ningún momento del proceso.
- **SC-003**: Todas las acciones relacionadas con inscripción y pagos que existían antes de este cambio (inscribir, quitar, registrar abono, eliminar abono) siguen funcionando correctamente después de la reorganización, verificado mediante pruebas automáticas.
- **SC-004**: Las tres pestañas (Datos, Asistencia, Histórico) se muestran en el orden correcto y son utilizables en pantallas desde 320 px de ancho en adelante.

## Supuestos

- «QA» en la solicitud original del usuario es una referencia al panel de registro por código QR («QR») que ya existe en la ficha del evento; este panel se queda en la pestaña «Datos» (decisión explícita del usuario durante la implementación), separado de la pestaña «Asistencia».
- No se introduce ninguna regla de negocio nueva sobre inscripciones, pagos o códigos QR: este cambio es una reorganización de la interfaz existente, no una funcionalidad nueva.
- La visibilidad de los formularios de inscripción y de los controles de pago dentro de «Asistencia» sigue el mismo criterio de permisos que ya aplica hoy en la pestaña «Datos» (permiso de escritura sobre el módulo de eventos), sin cambios en el modelo de permisos.
- La pestaña «Asistencia» es visible para cualquier usuario que pueda ver la ficha del evento, con o sin permiso de escritura; solo cambian las acciones disponibles según el permiso.
