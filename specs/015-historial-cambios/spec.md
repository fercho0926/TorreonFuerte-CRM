# Especificación de Funcionalidad: Historial de cambios en toda la plataforma (pestaña «Histórico»)

**Rama de la funcionalidad**: `015-historial-cambios`

**Creado**: 2026-10-23

**Estado**: Borrador

**Entrada**: Descripción del usuario: "quiero que cada cambio que se haga en la plataforma lo registre y lo muestre asi como en miembros, quiero que cada que alguien registre un avance o haga un cambio lleve un historial de cambios, con fecha, hora, usuario, accion para poder saber quien modifico o realizo el cambio y que cambio. para toda mi plataforma y que cada modulo tenga su tab de control de cambios, es decir cuando ingrese al miembro pueda tener un tab que se llame historico, y pueda saber TODO lo que ha pasado con ese miembro, que dias ha ido a encuentro a que retiros, si alguien cambio el nombre, si le cambio la direccion TODO lo que pase que se vea alli, no solo para miembros, PARA TODA MI APP"

## Contexto

Hoy la plataforma guarda historiales **sueltos y parciales**: la ficha del miembro tiene una tabla «Historial de cambios» (alta, edición, estado, líder, niveles…), y hay historiales propios en Sedes, Roles y Niveles. Pero:

- **Muchos módulos no registran nada**: células (miembros que entran o salen, reuniones), ministerios (integrantes), eventos (inscripciones, abonos), encuentros (asistencia), usuarios (cambios de rol, claves), solicitudes de clave, QR de registro.
- En los que sí registran, **no siempre se ve qué campo cambió** y de qué valor a qué valor.
- En la ficha del miembro **no se ve su vida completa en la iglesia**: a qué encuentros fue y qué días, a qué retiros o eventos se inscribió y cuánto abonó, a qué célula o ministerio entró o salió, si lo trasladaron de sede.

Esta funcionalidad unifica todo en **un solo historial de cambios para toda la aplicación** y agrega una pestaña **«Histórico»** en la ficha de cada registro (miembro, líder, célula, ministerio, evento, encuentro, nivel, sede, usuario, rol), más un módulo **«Control de cambios»** para consultar todo lo ocurrido en la plataforma.

## Clarificaciones

### Sesión 2026-10-23

- Q: Además de los cambios a la información, ¿qué otras acciones debe registrar el historial? → A: Solo los cambios a la información (no se registran inicios de sesión, exportaciones ni consultas). Además, el historial NO se puede exportar (ningún rol, ni desde «Control de cambios» ni desde la pestaña «Histórico»).
- Q: ¿Quién debe poder abrir el módulo «Control de cambios»? → A: Por defecto solo el administrador; los demás roles solo si el administrador se lo concede desde Roles (siempre limitado a su sede y alcance).
- Q: Cuando se traslada a un miembro a otra sede, ¿la sede nueva debe ver el histórico que el miembro tenía en su sede anterior? → A: Sí, completo: la sede nueva ve todo el histórico anterior del miembro con todos sus detalles (usuarios, montos, nombres de células y eventos).
- Q: ¿Cómo debe ordenarse la pestaña «Histórico» del miembro? → A: En secciones separadas dentro de la pestaña (Cambios de datos; Encuentros y retiros; Células y ministerios; Liderazgo; Niveles; Sede), cada una con su propia lista de la más reciente a la más antigua.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Pestaña «Histórico» del miembro con todo lo que le ha pasado (Prioridad: P1)

Al abrir la ficha de un miembro, el pastor o líder ve una pestaña **«Histórico»**. Allí aparece **todo lo que ha pasado con ese miembro**, organizado en **secciones separadas** (Cambios de datos; Encuentros y retiros; Células y ministerios; Liderazgo; Niveles; Sede), cada una con su lista de la más reciente a la más antigua: cambios de sus datos (nombre, dirección, celular, estado…), mostrando el valor anterior y el nuevo; asistencias a encuentros con su fecha; inscripciones y abonos a eventos y retiros; entrada y salida de células y ministerios; cambio de líder; niveles completados o retirados; traslados de sede; inactivación y reactivación. Cada entrada indica **fecha, hora, usuario que lo hizo y acción**.

**Por qué esta prioridad**: Es el ejemplo central del pedido y donde más se consulta la información.

**Prueba Independiente**: Con un miembro al que se le cambió la dirección, se le marcó asistencia a un encuentro y se le inscribió en un retiro, abrir su ficha y comprobar que la pestaña «Histórico» muestra las tres entradas con fecha, hora, usuario y detalle.

**Escenarios de Aceptación**:

1. **Dado** un miembro cuya dirección fue cambiada por «Ana Pérez» de «Calle 10 # 5-20» a «Carrera 8 # 3-15», **Cuando** se abre su pestaña «Histórico», **Entonces** aparece la entrada «Datos editados» con fecha, hora, «Ana Pérez» y el detalle «Dirección: Calle 10 # 5-20 → Carrera 8 # 3-15».
2. **Dado** una edición que cambió nombre y celular a la vez, **Cuando** se ve la entrada, **Entonces** se listan los dos campos con su valor anterior y su valor nuevo, y no se listan los campos que no cambiaron.
3. **Dado** un miembro marcado como asistente a un encuentro, **Cuando** se ve su histórico, **Entonces** aparece «Asistió al encuentro *Nombre del encuentro*» con la fecha del encuentro, quién marcó la asistencia y un enlace al encuentro.
4. **Dado** un miembro inscrito en un retiro (evento) con dos abonos, **Cuando** se ve su histórico, **Entonces** aparecen la inscripción y cada abono con su valor, fecha y quién lo registró.
5. **Dado** un miembro que entró a una célula, salió de ella y entró a un ministerio, **Cuando** se ve su histórico, **Entonces** aparecen las tres entradas con el nombre de la célula o ministerio.
6. **Dado** un miembro con cambios de datos, asistencias a encuentros e inscripciones a retiros, **Cuando** se abre su pestaña «Histórico», **Entonces** cada tipo aparece en su propia sección («Cambios de datos», «Encuentros y retiros», «Células y ministerios», «Liderazgo», «Niveles», «Sede»), cada sección con su contador, su lista ordenada de la más reciente a la más antigua y su propia paginación; las secciones sin entradas muestran «Sin movimientos».
7. **Dado** un histórico largo, **Cuando** se filtra por rango de fechas o por usuario, **Entonces** el filtro se aplica a todas las secciones a la vez.
8. **Dado** un celular de 360 px, **Cuando** se abre la pestaña, **Entonces** las secciones se apilan una debajo de otra y cada entrada se ve como tarjeta legible, sin desplazamiento horizontal.
9. **Dado** un usuario que ve la ficha pero no tiene acceso al módulo Eventos, **Cuando** abre el histórico, **Entonces** sigue viendo que el miembro se inscribió en el evento, pero sin enlace al evento ni montos de abonos.

---

### Historia de Usuario 2 - Todo cambio en la plataforma queda registrado (Prioridad: P1)

Cada vez que alguien **crea, edita, elimina, inactiva, reactiva, asigna, quita, marca asistencia, inscribe, abona, registra un avance o cambia un permiso** en cualquier módulo, el sistema guarda automáticamente una entrada de historial con fecha y hora, usuario, acción, registro afectado y el detalle de qué cambió (valor anterior → valor nuevo). Nadie tiene que hacer nada extra para que quede la constancia.

**Por qué esta prioridad**: Sin el registro completo, ninguna pestaña puede mostrar «TODO lo que pasa».

**Prueba Independiente**: Realizar una acción de escritura en cada módulo y verificar que cada una genera exactamente una entrada de historial con usuario, fecha, hora, acción y detalle.

**Escenarios de Aceptación**:

1. **Dado** cualquier acción de escritura en Miembros, Líderes, Sin líder, Células, Ministerios, Eventos, Encuentros, Niveles, Sedes, Usuarios, Roles, Solicitudes de clave o Registro por QR, **Cuando** se guarda con éxito, **Entonces** queda una entrada de historial con fecha, hora, usuario, acción, registro afectado y detalle.
2. **Dado** una acción que falla por validación o por falta de permiso, **Cuando** no se guarda nada, **Entonces** no se crea entrada de historial.
3. **Dado** una acción que afecta a varios registros a la vez (por ejemplo, guardar la asistencia de 30 miembros en un encuentro), **Cuando** se guarda, **Entonces** el encuentro muestra una entrada con el resumen (quiénes se agregaron y quiénes se quitaron) y cada miembro afectado muestra su propia entrada.
4. **Dado** un cambio hecho por el registro público por QR (sin usuario con sesión), **Cuando** se registra, **Entonces** el usuario aparece como «Registro público por QR» y se indica el QR de origen.
5. **Dado** un cambio que el sistema hace solo (por ejemplo, un nivel completado automáticamente por asistir a un encuentro), **Cuando** se registra, **Entonces** se indica «Automático» junto con el usuario cuya acción lo provocó.
6. **Dado** un cambio de clave de un usuario, **Cuando** se registra, **Entonces** se indica «Clave cambiada» o «Clave generada» sin mostrar nunca la clave ni ningún dato secreto.
7. **Dado** una edición en la que no cambió ningún valor, **Cuando** se guarda, **Entonces** no se crea una entrada vacía.

---

### Historia de Usuario 3 - Pestaña «Histórico» en cada módulo (Prioridad: P2)

Las fichas de **Líder, Célula, Ministerio, Evento, Encuentro, Nivel, Sede, Usuario y Rol** tienen también su pestaña «Histórico», con el mismo diseño y filtros que la del miembro, mostrando lo ocurrido con ese registro: cambios de sus datos y de sus relaciones (miembros que entran o salen de una célula, reuniones registradas, inscripciones y abonos de un evento, asistencia de un encuentro, permisos de un rol, rol y sede de un usuario, pastores de una sede…).

**Por qué esta prioridad**: Extiende a toda la aplicación lo pedido; depende del registro de la historia 2.

**Prueba Independiente**: En una célula, agregar un miembro, registrar una reunión y cambiar el nombre de la célula; abrir su pestaña «Histórico» y ver las tres entradas.

**Escenarios de Aceptación**:

1. **Dado** una célula a la que se agregó un miembro, se registró una reunión y se cambió el líder, **Cuando** se abre su pestaña «Histórico», **Entonces** aparecen las tres entradas con fecha, hora, usuario, acción y detalle.
2. **Dado** un evento con inscripciones y abonos, **Cuando** se abre su histórico, **Entonces** se ve cada inscripción, retiro de inscripción, abono y abono eliminado, con el miembro y el valor.
3. **Dado** un rol al que se cambió el nivel de acceso de un módulo, **Cuando** se abre su histórico, **Entonces** se ve «Módulo Eventos: lectura → escritura».
4. **Dado** un usuario al que se cambió de rol y de sede, **Cuando** se abre su histórico, **Entonces** se ven ambos cambios con su valor anterior y nuevo.
5. **Dado** los historiales que ya existían antes de esta funcionalidad (miembros, sedes, roles, niveles), **Cuando** se abre la pestaña «Histórico», **Entonces** esas entradas antiguas siguen apareciendo junto con las nuevas, en orden cronológico.
6. **Dado** cualquier ficha con pestaña «Histórico», **Cuando** se entra por primera vez, **Entonces** la pestaña principal sigue siendo la de datos, y la de «Histórico» se abre al tocarla (y se puede abrir directamente con un enlace).

---

### Historia de Usuario 4 - Módulo «Control de cambios» para toda la plataforma (Prioridad: P2)

En el menú aparece **«Control de cambios»**: un listado de todo lo que ha pasado en la plataforma, con filtros por fecha y hora, usuario, módulo, acción y registro afectado. Sirve para responder preguntas como «¿qué hizo este usuario ayer?» o «¿quién eliminó esta célula?», incluso cuando el registro ya fue eliminado y no tiene ficha.

**Por qué esta prioridad**: Complementa las pestañas con una vista transversal, necesaria para registros eliminados y para auditar a un usuario.

**Prueba Independiente**: Hacer cambios en tres módulos con dos usuarios distintos; filtrar por un usuario y un rango de fechas y comprobar que solo aparecen sus cambios.

**Escenarios de Aceptación**:

1. **Dado** un administrador, **Cuando** abre «Control de cambios», **Entonces** ve todas las entradas de su alcance, de la más reciente a la más antigua, con columnas Fecha y hora, Usuario, Módulo, Acción, Registro y Detalle.
2. **Dado** el listado, **Cuando** filtra por usuario, módulo, acción y rango de fechas, **Entonces** solo ve las entradas que cumplen todos los filtros, con la grilla estándar (orden, filas por página, tarjetas en teléfono).
3. **Dado** una célula eliminada, **Cuando** se busca por su nombre, **Entonces** aparecen todas sus entradas, incluida la eliminación con quién y cuándo, y los datos que tenía al eliminarla.
4. **Dado** una entrada cuyo registro todavía existe, **Cuando** se pulsa el registro, **Entonces** se abre su ficha en la pestaña «Histórico».
5. **Dado** cualquier usuario, incluido el administrador, **Cuando** abre el listado, **Entonces** no hay botón de exportar y la dirección de exportación no existe o responde «no permitido».
6. **Dado** un usuario de alcance limitado con acceso al módulo, **Cuando** lo abre, **Entonces** solo ve entradas de su sede y de los registros que puede ver.

---

### Historia de Usuario 5 - El historial no se puede alterar (Prioridad: P3)

Nadie, ni siquiera el administrador, puede editar ni borrar entradas del historial desde la aplicación. Eliminar un registro (miembro, célula, evento…) no borra su historial.

**Por qué esta prioridad**: Da confianza en la información; la mayor parte del valor ya lo entregan las historias anteriores.

**Prueba Independiente**: Eliminar un evento con historial y comprobar que su historial sigue en «Control de cambios»; comprobar que no existe ninguna opción para editar o borrar entradas.

**Escenarios de Aceptación**:

1. **Dado** cualquier usuario, **Cuando** ve una entrada de historial, **Entonces** no tiene opción de editarla ni de borrarla.
2. **Dado** un registro eliminado, **Cuando** se consulta el historial, **Entonces** sus entradas siguen disponibles.
3. **Dado** un usuario eliminado o desactivado, **Cuando** se ven entradas que hizo, **Entonces** su nombre sigue apareciendo tal como era al momento del cambio.

---

### Casos Límite

- **Miembro trasladado de sede**: su histórico lo acompaña completo; las entradas muestran en qué sede ocurrieron. La sede nueva ve **todo** el histórico anterior del miembro con todos sus detalles (usuario que hizo cada cambio, montos, nombres de células, eventos y encuentros), pero los nombres de registros de la sede anterior se muestran sin enlace, porque esas fichas siguen aisladas.
- **Registro eliminado**: su pestaña desaparece con la ficha, pero su historial queda en «Control de cambios»; las entradas de otros registros que lo nombran (por ejemplo, «Asistió al encuentro X» en un miembro) conservan el nombre aunque ya no tengan enlace.
- **Usuario o nombre cambiado después**: cada entrada conserva el nombre del usuario y del registro tal como eran en el momento del cambio.
- **Acciones masivas** (asistencia de un encuentro, integrantes de un ministerio): una entrada resumen en el registro principal y una entrada por cada miembro afectado; no se duplica si se guarda dos veces sin cambios.
- **Campos sensibles** (claves, tokens de QR, códigos de recuperación): solo se registra que cambiaron, nunca su valor.
- **Campos muy largos** (observaciones, descripciones): el detalle muestra un resumen y permite ver el texto completo anterior y nuevo.
- **Valores vacíos**: se muestran como «(vacío)» en lugar de en blanco.
- **Cambios simultáneos** de dos usuarios sobre el mismo registro: cada uno queda en su propia entrada con su hora.
- **Historial muy grande** (miles de entradas por registro): la pestaña sigue cargando rápido gracias a la paginación y los filtros.
- **Usuario con lectura de la ficha pero sin acceso a otro módulo**: ve las entradas que mencionan ese módulo, sin enlaces ni datos que solo ese módulo puede mostrar (por ejemplo, montos de abonos).
- **«Todas las sedes»** del administrador: el histórico y el control de cambios muestran entradas de todas las sedes, con una columna de sede.
- **Historiales anteriores** sin detalle de campos: se muestran con la información que tenían, sin inventar valores.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

**Registro**

- **RF-001**: El sistema DEBE registrar automáticamente una entrada de historial por cada acción de escritura exitosa en todos los módulos: Miembros, Líderes, Sin líder, Células (incluidos sus miembros y reuniones), Ministerios (incluidos sus integrantes), Eventos (incluidas inscripciones y abonos), Encuentros (incluida la asistencia), Niveles (incluidos los avances), Sedes (incluidos pastores y traslados), Usuarios, Roles y permisos, Solicitudes de clave y Registro por QR.
- **RF-002**: Cada entrada DEBE guardar: fecha y hora, usuario que hizo la acción (o «Registro público por QR» / «Automático»), sede, módulo, acción, registro afectado (tipo, identificador y nombre en ese momento) y detalle.
- **RF-003**: En las ediciones, el detalle DEBE indicar cada campo que cambió con su valor anterior y su valor nuevo, con nombres de campo y valores legibles (por ejemplo, el nombre del líder y no su número interno); los campos sin cambio NO DEBEN aparecer.
- **RF-004**: En las eliminaciones, el detalle DEBE conservar los datos principales que tenía el registro al eliminarlo.
- **RF-005**: Una acción que no guarda nada (error de validación, falta de permiso, edición sin cambios) NO DEBE generar entrada.
- **RF-006**: Una acción que afecta a varios registros DEBE generar una entrada en el registro principal (con el resumen de agregados y quitados) y una entrada en cada registro afectado que tenga histórico (por ejemplo, cada miembro).
- **RF-007**: Los valores secretos (claves, tokens, códigos) NUNCA DEBEN guardarse ni mostrarse en el historial; solo se registra que cambiaron.
- **RF-008**: Las entradas DEBEN ser inalterables: la aplicación NO DEBE ofrecer editar ni borrar entradas, y eliminar un registro o un usuario NO DEBE borrar su historial.
- **RF-009**: Cada entrada DEBE conservar el nombre del usuario y del registro tal como eran en el momento del cambio.
- **RF-010**: Los historiales que ya existen (miembros, sedes, roles, niveles) DEBEN seguir visibles dentro del nuevo historial unificado, sin pérdida de entradas.

**Pestaña «Histórico»**

- **RF-011**: Las fichas de Miembro, Líder, Célula, Ministerio, Evento, Encuentro, Nivel, Sede, Usuario y Rol DEBEN tener una pestaña llamada «Histórico».
- **RF-012**: La pestaña DEBE mostrar las entradas del registro de la más reciente a la más antigua, cada una con fecha, hora, usuario, acción y detalle, con paginación.
- **RF-013**: La pestaña DEBE permitir filtrar por rango de fechas y usuario, y por tipo de acción en las fichas que no se organizan en secciones (ver RF-014).
- **RF-014**: El histórico del miembro (y del líder, que es un miembro) DEBE organizarse en secciones separadas —«Cambios de datos» (datos personales, estado, inactivación, reactivación, registros por QR), «Encuentros y retiros» (asistencias a encuentros, inscripciones, retiros de inscripción y abonos de eventos), «Células y ministerios», «Liderazgo», «Niveles» y «Sede» (traslados)—, cada una con su lista de la más reciente a la más antigua, su contador y su propia paginación; los filtros de fecha y usuario se aplican a todas las secciones. Las demás fichas muestran una sola lista con filtro por tipo de acción. El histórico del miembro DEBE incluir, además de los cambios a sus datos: asistencias a encuentros (con fecha del encuentro), inscripciones y retiros de eventos y retiros, abonos, entrada y salida de células y ministerios, asistencia a reuniones de célula cuando se registre, cambios de líder y de rol de líder, avances de nivel, traslados de sede, inactivación, reactivación y registros por QR.
- **RF-015**: Las entradas que mencionan otro registro DEBEN enlazar a su ficha cuando exista y el usuario pueda verla; si no, DEBEN mostrar solo su nombre.
- **RF-016**: La pestaña DEBE poder abrirse directamente con un enlace y verse correctamente en teléfono (tarjetas apiladas, sin desplazamiento horizontal, controles de al menos 44 px; constitución, principio VII), y funcionar sin JavaScript (constitución, principio V).

**Módulo «Control de cambios»**

- **RF-017**: El sistema DEBE ofrecer el módulo «Control de cambios» con el listado de todas las entradas del alcance del usuario, usando la grilla estándar (orden, filtros por columna, filas por página, tarjetas en teléfono; constitución, principio III), **sin la opción de exportar** (excepción justificada en RF-020).
- **RF-018**: El listado DEBE permitir filtrar por rango de fecha y hora, usuario, sede, módulo, acción y nombre del registro afectado, incluidos registros ya eliminados.
- **RF-019**: Cada entrada del listado DEBE enlazar a la ficha del registro (en la pestaña «Histórico») cuando el registro exista.
- **RF-020**: El historial NO DEBE poder exportarse por ningún medio (CSV ni otro formato), para ningún rol, ni desde «Control de cambios» ni desde la pestaña «Histórico». Es una excepción declarada a la exportación que exige el principio III de la constitución, porque el historial reúne datos personales y de acceso de toda la plataforma.

**Permisos y aislamiento**

- **RF-021**: Quien puede ver una ficha DEBE poder ver su pestaña «Histórico»; quien no puede ver la ficha NO DEBE ver su historial por ningún medio.
- **RF-022**: «Control de cambios» DEBE ser un módulo del catálogo de permisos (spec 013) de solo lectura; por defecto solo el administrador tiene acceso, y los demás roles lo obtienen si se les concede.
- **RF-023**: Toda consulta de historial DEBE respetar el aislamiento por sede y el alcance de datos del rol (constitución, principio II); dentro de una entrada, los datos de módulos a los que el usuario no tiene acceso se muestran sin enlace ni montos. **Excepción declarada**: el histórico de un miembro trasladado se muestra completo a su sede actual, incluidas las entradas ocurridas en sedes anteriores; lo que sigue aislado son las fichas de la otra sede (sin enlace).

### Entidades Clave *(incluir si la funcionalidad involucra datos)*

- **Entrada de historial**: constancia inalterable de una acción. Tiene fecha y hora, usuario (con su nombre en ese momento) o actor especial («Registro público por QR», «Automático»), sede, módulo, acción, registro afectado (tipo, identificador, nombre en ese momento), detalle de campos (valor anterior → valor nuevo) y, cuando aplica, registros relacionados (por ejemplo, el miembro y el encuentro en una asistencia).
- **Registro afectado**: cualquier elemento con ficha en la plataforma (miembro, líder, célula, ministerio, evento, encuentro, nivel, sede, usuario, rol) o sin ficha (solicitud de clave, QR de registro, abono, reunión de célula).
- **Historiales existentes** (cambios de miembro, sede, rol y nivel): se integran como entradas del historial unificado.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: El 100 % de las acciones de escritura de todos los módulos generan su entrada de historial (verificado con una prueba por cada acción).
- **CE-002**: Un usuario responde «¿quién cambió este dato y cuándo?» sobre un miembro en menos de 30 segundos desde su ficha, también desde un celular.
- **CE-003**: La pestaña «Histórico» de un miembro muestra sus asistencias a encuentros, inscripciones a eventos y retiros, y cambios de datos sin consultar ninguna otra pantalla.
- **CE-004**: La pestaña «Histórico» carga su primera página en menos de 2 segundos aunque el registro tenga más de 5 000 entradas.
- **CE-005**: Ninguna entrada del historial contiene claves, tokens ni códigos secretos.
- **CE-006**: Ningún usuario ve entradas de historial fuera de su sede o de su alcance de datos en las pruebas de aislamiento, salvo el histórico anterior de un miembro trasladado a su sede (RF-023).
- **CE-007**: El 100 % de las entradas de los historiales anteriores siguen visibles después del cambio.
- **CE-008**: Las pantallas nuevas pasan la auditoría móvil del proyecto en 320, 360, 390 y 430 px, y las pruebas existentes siguen en verde.

## Supuestos

- **Qué se registra** (decisión de clarificación): solo las acciones que **cambian** información. Las consultas, búsquedas, inicios de sesión y exportaciones no se registran.
- **Retención**: el historial se conserva indefinidamente; no hay depuración automática.
- **«Retiros»** del pedido se entiende como los **eventos** (con inscripciones y abonos) y los **encuentros** (con asistencia) que ya existen en la plataforma.
- **Líder**: su pestaña «Histórico» es la de su ficha de miembro más los cambios propios del liderazgo (designación, quitar, miembros asignados).
- **Acceso al módulo «Control de cambios»** (decisión de clarificación): por defecto solo administrador; el pastor y los demás roles consultan el historial desde la pestaña «Histórico» de cada ficha que pueden ver, y el administrador puede concederles el módulo desde Roles.
- **Historiales existentes**: se integran en el historial unificado; las entradas antiguas sin detalle por campo se muestran tal como se guardaron.
- **Actores especiales**: el registro público por QR y las acciones automáticas tienen nombres de actor fijos y legibles.
- **Las tablas de historial dentro de una ficha** no son listados principales, pero se presentan con el mismo estilo y adaptación a teléfono; el listado «Control de cambios» sí es un listado principal y usa la grilla estándar.
- Dependencias: roles y permisos por módulo (spec 013), aislamiento por sede (spec 002), grilla estándar (spec 007), diseño móvil (spec 008), niveles (spec 014) y los historiales actuales de miembros, sedes, roles y niveles.
