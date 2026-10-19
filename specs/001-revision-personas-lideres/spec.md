# Especificación de la funcionalidad: Revisión y mejora de los módulos de Personas y Líderes

**Rama de la funcionalidad**: `001-revision-personas-lideres`

**Creada**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "vamos a revisar el modulo de lideres y personas, que si este bien creado, que sea claro para la congregacion, que sea logico y usable, realiza un analisis, si vez estoy creando un CRM para administrar mi iglesia"

## Diagnóstico del estado actual *(resultado del análisis)*

Se revisaron los módulos de Personas y Líderes tal como existen hoy (listados, formularios, detalle, permisos por rol y su relación con Células). Resumen de hallazgos, ordenados por impacto:

### A. Claridad para la congregación (vocabulario y modelo mental)

1. **La palabra "líder" significa tres cosas distintas sin explicarlo.** Hay "líder de red" (módulo Líderes), "líder de célula" (campo en el formulario de Persona) y "líder" de una Célula (módulo Células). En el formulario de Persona, el campo "Líder de célula" lista a los *líderes de red*, lo que confunde: quien lo llena no sabe si está eligiendo a un líder de red o al líder de su célula.
2. **El mismo vínculo se guarda en dos lugares.** La pertenencia de una persona a un líder se registra tanto en la ficha de la persona como en la membresía de una Célula; ambos pueden contradecirse (una persona figura "sin líder" pero pertenece a una célula, o al revés).
3. **"Red" es un texto libre y obligatorio.** "Jóvenes", "jovenes" y "Jóvenes " crean redes distintas; el filtro por red de Personas se llena con esos textos y se fragmenta.
4. **Los roles de usuario (admin, pastor, líder de zona, líder de célula) no tienen un concepto equivalente visible en Líderes.** Decisión posterior: el rol "líder de zona" se elimina y el módulo trabaja con administrador, pastor y líder.

### B. Lógica del flujo

5. **Crear un líder siempre crea una persona nueva.** No se puede *promover* a una persona ya registrada; intentar hacerlo choca con "documento ya existe". Hoy la única vía es editar a la persona y no existe allí la opción de marcarla como líder.
6. **"Quitar rol" de líder no valida dependencias.** Un líder que aún dirige células o tiene personas a cargo puede perder el rol; sus personas quedan "sin líder" silenciosamente y las células quedan con un líder que ya no figura como tal.
7. **Eliminar una persona es definitivo** y no advierte si tiene historial (asistencias a encuentros, inscripciones y pagos de eventos, células, ministerios). Para una iglesia, lo normal es *inactivar* (se mudó, se retiró) y conservar el historial, no borrarlo.
8. **El documento de identidad es obligatorio y único para todos.** Esto bloquea registrar a visitantes, niños o personas que aún no entregan documento, que son justamente el público principal de un CRM de iglesia.
9. **No existe un estado de la persona** (visitante, nuevo creyente, miembro activo, inactivo), ni forma de distinguir a quién se está dando seguimiento.

### C. Usabilidad

10. **Listados sin búsqueda ni paginación**: solo filtros por red y líder; hay que recorrer la lista completa para encontrar a alguien por nombre, documento o celular.
11. **Columna "ID" interna visible** en listados: es un dato técnico sin valor para el usuario.
12. **Tabla ancha de una sola línea** (nombres, documento, celular, ciudad, red, líder, tres botones): se desborda en celular, que es el dispositivo más probable de un líder en campo.
13. **Botones que no corresponden al rol**: un líder de célula ve "Editar" y "Eliminar" en Personas y en el detalle de un líder; al usarlos es redirigido al tablero sin explicación.
14. **Formulario de Líder y de Persona duplicados** (mismos campos, dos pantallas): mantener ambos genera inconsistencias y el usuario no sabe cuál usar.
15. **Fechas en formato técnico** (año-mes-día) y sin edad visible.

### D. Seguridad y privacidad de datos de la congregación

16. **Fugas de visibilidad entre ramas del liderazgo.** Al crear/editar una persona o filtrar el listado, el selector de líderes y la lista de redes muestran a *todos* los líderes, no solo los que el usuario puede ver; un líder de célula puede reasignar personas a cualquier líder de la iglesia.
17. **Cualquier usuario autenticado puede crear personas**, pero un líder de célula no debería poder crear registros fuera de su ámbito.
18. **Datos sensibles (documento, dirección, celular, fecha de nacimiento)** se muestran completos a quien pueda ver la persona. Decisión: esto es aceptable dentro del ámbito del líder; el problema real es el ámbito demasiado amplio (hallazgo 16), no los campos.

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿Cómo se organiza el liderazgo: líder de red y de célula son lo mismo o niveles distintos, y qué es una zona? → A: Un solo nivel de líder ("líder de red" y "líder de célula" son lo mismo); la zona es un sector geográfico que agrupa líderes.
- Q: ¿Qué datos personales puede ver un líder sobre las personas que tiene a cargo? → A: Todos, incluido el documento; la restricción es por ámbito (solo sus personas), no por campo.
- Q: ¿Cuál es la fuente de verdad del vínculo persona–líder frente a la pertenencia a una célula? → A: El líder directo manda: toda persona tiene un líder; la célula es un grupo de reunión opcional dentro de ese líder.
- Q: ¿Quién es responsable de una persona sin líder (visitante nuevo o migrada)? → A: Una vista "Sin líder asignado" para asignarlas a un líder (ver cambio de decisión: solo pastor y administrador).
- Cambio de decisión (2026-10-19, durante la implementación): se elimina el concepto de **líder de zona** (rol de usuario, supervisión y ámbito por zona). La **zona** queda como sector geográfico opcional sin supervisor. La vista "Sin líder asignado" es solo para pastor y administrador.
- Cambio de decisión (2026-10-19, posterior): se elimina por completo el concepto de **zona** (módulo, campo `personas.zona_id`, filtros y selectores). Una migración lo retira de las bases existentes. Las referencias a zonas en estos documentos son históricas.

## Escenarios de usuario y pruebas *(obligatorio)*

### Historia de usuario 1 - Un solo concepto claro de "persona" y de "líder" (Prioridad: P1)

Un secretario o pastor entiende de inmediato quién es cada persona en la iglesia y qué papel de liderazgo tiene (sin rol o líder) y, si aplica, su red y zona. Se usa un vocabulario único en todos los menús, listados y formularios, y existe una sola vista coherente para ver "a quién lidera quién".

**Por qué esta prioridad**: Si el vocabulario y el modelo no son claros, todo lo demás (permisos, reportes, células) se vuelve inconsistente y la congregación no confía en los datos.

**Prueba independiente**: Se puede validar mostrando los listados y formularios a 3 usuarios nuevos y pidiéndoles que expliquen en sus palabras qué es un líder, una red y una zona y a quién pertenece una persona dada; debe lograrse sin ayuda.

**Escenarios de aceptación**:

1. **Dado** una persona que es líder, **cuando** se consulta su ficha, **entonces** se muestra con una etiqueta de rol de liderazgo única y entendible (no términos ambiguos) y se ve claramente quién es su líder directo y a quiénes lidera.
2. **Dado** un formulario de persona, **cuando** el usuario elige a su líder, **entonces** el campo indica con una etiqueta y texto de ayuda a quién se está seleccionando y muestra solo las opciones válidas para esa persona.
3. **Dado** una persona que pertenece a una célula, **cuando** se consulta su ficha, **entonces** la célula es una de las células de su líder directo; el sistema no permite asignarla a una célula de otro líder.

---

### Historia de usuario 2 - Registrar, buscar y dar seguimiento a personas fácilmente (Prioridad: P1)

Un líder o secretario registra a un visitante en menos de un minuto (con solo nombre y un dato de contacto), encuentra a cualquier persona escribiendo parte de su nombre, documento o celular, y distingue su estado (visitante, miembro activo, inactivo).

**Por qué esta prioridad**: Es la tarea diaria del módulo; si registrar y encontrar personas es lento, el CRM no se usa.

**Prueba independiente**: Se puede validar registrando un visitante sin documento y buscándolo luego por nombre parcial; entrega valor por sí sola como "libreta de contactos" de la iglesia.

**Escenarios de aceptación**:

1. **Dado** que llega un visitante sin documento, **cuando** se registra con nombres, apellidos y celular, **entonces** queda guardado con estado "visitante" y puede completarse después.
2. **Dado** un listado con muchas personas, **cuando** el usuario escribe una parte del nombre, apellido, documento o celular, **entonces** la lista se reduce a coincidencias en un tiempo imperceptible.
3. **Dado** un listado extenso, **cuando** se navega, **entonces** se muestra por páginas y el usuario puede combinar búsqueda con filtros por estado, red y líder.
4. **Dado** una persona que dejó de asistir, **cuando** se la marca como inactiva, **entonces** deja de aparecer en el listado por defecto pero conserva todo su historial y puede reactivarse.

---

### Historia de usuario 3 - Gestionar líderes y su responsabilidad sin perder datos (Prioridad: P2)

El pastor o administrador designa como líder a una persona **ya registrada**, le asigna su red de forma controlada, y puede quitarle el rol de manera segura: el sistema advierte qué personas y células quedarían sin líder y permite reasignarlas antes de confirmar.

**Por qué esta prioridad**: Corrige los errores de integridad más dañinos (personas huérfanas, células sin líder válido), pero depende de que el modelo de la historia 1 esté definido.

**Prueba independiente**: Se puede validar promoviendo a una persona existente a líder, asignándole personas y luego quitándole el rol, verificando que ninguna persona ni célula queda sin responsable sin que el usuario lo decida.

**Escenarios de aceptación**:

1. **Dado** una persona registrada que no es líder, **cuando** el pastor la designa líder y elige una red del catálogo de redes, **entonces** pasa a figurar en Líderes sin duplicar su registro.
2. **Dado** un líder con personas y/o células a cargo, **cuando** el usuario intenta quitarle el rol, **entonces** el sistema muestra cuántas personas y células quedarían sin líder y exige reasignarlas o confirmar explícitamente.
3. **Dado** que el usuario escribe un nombre de red parecido a uno existente, **cuando** guarda, **entonces** el sistema evita crear una red duplicada y sugiere la existente.
4. **Dado** el detalle de un líder, **cuando** se visualiza, **entonces** se ven sus personas, sus células y un resumen (cantidad de personas, células, última actividad) en una sola pantalla.

---

### Historia de usuario 4 - Cada usuario ve y hace solo lo que le corresponde (Prioridad: P2)

Un líder ve y gestiona únicamente a las personas de su célula; el pastor y el administrador, todas. Los botones y opciones que no puede usar no aparecen, y los selectores solo ofrecen opciones dentro de su ámbito.

**Por qué esta prioridad**: Protege datos personales de la congregación, pero la base funcional (historias 1 y 2) debe existir primero.

**Prueba independiente**: Se puede validar iniciando sesión como líder y verificando que no ve personas, líderes ni redes ajenas, y que no puede reasignar personas fuera de su ámbito.

**Escenarios de aceptación**:

1. **Dado** un líder autenticado, **cuando** abre el listado de Personas o los selectores de líder/red, **entonces** solo aparecen personas, líderes y redes dentro de su ámbito.
2. **Dado** un líder, **cuando** ve una ficha, **entonces** no ve acciones de editar o eliminar que no tiene permitidas, y las acciones permitidas funcionan sin redirecciones inesperadas.
3. **Dado** un líder que registra una persona nueva, **cuando** guarda, **entonces** la persona queda asignada dentro de su ámbito y no puede asignarse a un líder ajeno.
4. **Dado** un líder, **cuando** ve la ficha de una persona, **entonces** ve todos los datos de la persona (nombre, contacto, documento, dirección, fecha de nacimiento, estado, célula) y el documento, la dirección y la fecha de nacimiento de las personas de su ámbito, pero nunca los de personas fuera de él.

---

### Historia de usuario 5 - Usar el módulo cómodamente desde el celular (Prioridad: P3)

Un líder en campo consulta y registra personas desde su teléfono: listas en formato de tarjetas legibles, botones de llamar/escribir por WhatsApp desde la ficha, y formularios que se llenan sin desplazamiento horizontal.

**Por qué esta prioridad**: Mejora la adopción, pero no bloquea la corrección de datos y permisos.

**Prueba independiente**: Se puede validar abriendo el listado y la ficha en un teléfono y completando un registro nuevo sin hacer zoom ni desplazamiento lateral.

**Escenarios de aceptación**:

1. **Dado** un teléfono, **cuando** se abre el listado de personas, **entonces** cada persona se muestra de forma legible con su nombre, estado y acceso rápido a llamar o escribir.
2. **Dado** una ficha de persona, **cuando** el usuario toca el celular o WhatsApp, **entonces** se inicia la llamada o el chat.
3. **Dado** cualquier fecha mostrada, **cuando** se lee, **entonces** aparece en formato legible en español (ej. "19 de octubre de 2026") junto con la edad cuando aplique.

---

### Casos límite

- ¿Qué pasa si una persona quiere ser su propio líder o se forma un ciclo (A lidera a B y B a A)? El sistema DEBE impedirlo con un mensaje claro.
- ¿Qué pasa si se quita el rol a un líder que es el único líder de una célula? Debe exigirse reasignar la célula antes.
- ¿Qué ocurre si se registra una persona cuyo documento o celular ya existe (posible duplicado)? Debe advertirse con enlace a la ficha existente, sin impedir registrar casos legítimos (familiares que comparten celular).
- ¿Qué pasa con una persona con historial (pagos de eventos, asistencia) a la que se pide eliminar? Debe ofrecerse inactivarla; la eliminación definitiva queda restringida a administradores y solo sin historial.
- ¿Qué ve un usuario cuya cuenta no está vinculada a ninguna persona? Debe verse solo un mensaje claro de que su cuenta aún no está asociada, no una lista vacía sin explicación.
- ¿Qué pasa con una persona sin documento al buscarla por documento o al exportarla? Debe poder encontrarse por otros datos y no romper reportes.
- ¿Qué pasa con una persona sin líder? Es un estado válido y temporal: aparece en la vista "Sin líder asignado" para que pastor o administrador la asignen; ningún líder de célula la ve hasta entonces.
- ¿Qué pasa con las redes existentes con nombres escritos de formas distintas? Deben consolidarse en el catálogo sin perder el vínculo con sus líderes.

## Requisitos *(obligatorio)*

### Requisitos funcionales

**Claridad y modelo**

- **FR-001**: El sistema DEBE usar un vocabulario único y documentado en menús, listados, formularios y mensajes: **Líder** (un solo rol, antes "líder de red" y "líder"), **Red** (agrupación temática del líder) y **Zona** (sector geográfico que agrupa líderes), con una breve ayuda visible que explique cada término.
- **FR-002**: El sistema DEBE mostrar, en la ficha de toda persona, su rol de liderazgo (si lo tiene), su líder directo y las personas y células que lidera.
- **FR-003**: El sistema DEBE tratar el líder directo como fuente de verdad del vínculo persona–líder: la pertenencia a una célula es opcional y solo puede ser a una célula de ese líder; al cambiar el líder de una persona, el sistema DEBE retirarla de la célula anterior o pedir elegir otra célula del nuevo líder. Las personas sin líder son un estado válido y temporal (ver FR-024).
- **FR-004**: El sistema DEBE ofrecer catálogos administrables de redes y de zonas, de modo que un líder se asocie a una red (obligatoria) y, opcionalmente, a una zona existentes en lugar de escribir texto libre.

**Registro y búsqueda de personas**

- **FR-005**: El sistema DEBE permitir registrar una persona con solo nombres, apellidos y un medio de contacto (celular o correo); el documento de identidad DEBE ser opcional para visitantes y obligatorio solo cuando la persona pase a ser miembro o líder.
- **FR-006**: El sistema DEBE asignar a cada persona un estado (visitante, nuevo creyente, miembro activo, inactivo) y permitir filtrar por él.
- **FR-007**: El sistema DEBE permitir buscar personas por texto parcial en nombres, apellidos, documento y celular, y combinar la búsqueda con filtros de estado, red y líder.
- **FR-008**: El sistema DEBE paginar los listados de personas y de líderes.
- **FR-009**: El sistema DEBE advertir de posibles duplicados (mismo documento, o mismo nombre y celular) al registrar o editar, mostrando enlace a la ficha existente.
- **FR-010**: El sistema DEBE permitir inactivar y reactivar personas conservando todo su historial; la eliminación definitiva DEBE estar limitada a administradores y a personas sin historial.

**Gestión de líderes**

- **FR-011**: El sistema DEBE permitir designar como líder a una persona ya registrada, sin crear un registro nuevo, desde Líderes y desde la ficha de la persona.
- **FR-012**: El sistema DEBE impedir que una persona sea su propio líder o se creen ciclos en la jerarquía de liderazgo.
- **FR-013**: Antes de quitar el rol de líder, el sistema DEBE informar la cantidad de personas y células afectadas y exigir reasignación o confirmación explícita.
- **FR-014**: El sistema DEBE mostrar en el detalle de un líder sus personas, sus células y un resumen de su responsabilidad en una sola pantalla.
- **FR-015**: El sistema DEBE reunir en una sola pantalla de formulario los datos de la persona y los de su rol de liderazgo, eliminando la duplicación entre los formularios de Persona y Líder.

**Permisos y privacidad**

- **FR-016**: El sistema DEBE limitar, según el rol del usuario, las personas, líderes, redes y células que aparecen en listados, filtros y selectores a su ámbito de responsabilidad (administrador y pastor: todo; líder: sus personas y su célula).
- **FR-017**: El sistema DEBE ocultar las acciones (editar, eliminar, designar, reasignar) que el rol del usuario no puede ejecutar y, cuando un usuario intente una acción no permitida, DEBE mostrar un mensaje claro en lugar de una redirección silenciosa.
- **FR-018**: El sistema DEBE impedir que un usuario registre o reasigne personas hacia líderes, células o redes fuera de su ámbito.
- **FR-019**: El sistema DEBE mostrar todos los datos de una persona (incluidos documento, dirección y fecha de nacimiento) a cualquier usuario que la tenga dentro de su ámbito, y a ningún usuario fuera de él; no se ocultan campos por rol dentro del ámbito.

**Usabilidad**

- **FR-020**: El sistema NO DEBE mostrar identificadores técnicos internos a los usuarios en listados ni fichas.
- **FR-021**: El sistema DEBE ofrecer listados y formularios usables en pantallas de teléfono, sin desplazamiento horizontal, y acciones rápidas de llamada y mensaje desde la ficha.
- **FR-022**: El sistema DEBE mostrar las fechas en formato legible en español y la edad de la persona cuando exista fecha de nacimiento.
- **FR-023**: El sistema DEBE dejar constancia de quién y cuándo creó, modificó, inactivó o cambió el líder de una persona, visible para pastor y administrador.
- **FR-024**: El sistema DEBE ofrecer una vista "Sin líder asignado" con las personas sin líder (visitantes nuevos y migradas), visible para pastor y administrador, desde la cual puedan asignarlas a un líder.

### Entidades clave *(incluir si la funcionalidad involucra datos)*

- **Persona**: Cualquier individuo vinculado a la iglesia (visitante, creyente, miembro, líder). Datos de identificación y contacto, estado, fecha de nacimiento, y su líder directo. Puede tener un rol de liderazgo, pertenecer a células y ministerios, y tener historial de encuentros y eventos.
- **Líder (rol de liderazgo)**: Responsabilidad única que una persona ejerce sobre otras y sobre sus células; pertenece a una red y, opcionalmente, a una zona. Se asigna y se retira sin eliminar a la persona.
- **Zona**: Sector geográfico administrable y opcional que agrupa líderes; sirve para filtrar y ubicar líderes (no tiene supervisor ni rol propio).
- **Red**: Agrupación temática administrable (ej. jóvenes, matrimonios), a la que se asocian líderes; evita nombres duplicados escritos de formas distintas.
- **Célula**: Grupo pequeño de reunión de un líder, con un co-líder opcional y sus miembros; los miembros son siempre personas cuyo líder directo es el líder de la célula (o su co-líder).
- **Historial de cambios**: Registro de quién y cuándo modificó datos clave de una persona o su liderazgo.

## Criterios de éxito *(obligatorio)*

### Resultados medibles

- **SC-001**: Un usuario nuevo registra a un visitante (nombres, apellidos y celular) en menos de 1 minuto sin ayuda.
- **SC-002**: El 90% de los usuarios de prueba encuentra a una persona específica por nombre parcial en menos de 15 segundos, incluso con más de 1.000 personas registradas.
- **SC-003**: El 90% de 5 usuarios de prueba explica correctamente, tras usar el módulo por primera vez, qué es un líder, una red y una zona.
- **SC-004**: Cero casos, en pruebas de aceptación por rol, en los que un líder ve o modifica personas, líderes, redes o células fuera de su ámbito.
- **SC-005**: Cero personas o células quedan sin líder como efecto colateral de quitar un rol de liderazgo sin que el usuario lo haya decidido explícitamente.
- **SC-006**: Se eliminan las redes duplicadas por variaciones de escritura: tras la consolidación, el número de redes distintas coincide con las redes reales definidas por la iglesia.
- **SC-007**: Las tareas principales (buscar, registrar, ver ficha, llamar a una persona) se completan en un teléfono sin desplazamiento horizontal ni zoom.
- **SC-008**: Al menos el 80% de los líderes de célula usa el módulo al menos una vez por semana durante el primer mes tras el lanzamiento.

## Supuestos

- La estructura de liderazgo es: pastor y administrador (acceso total) → líder (un solo nivel, con su red, zona opcional y sus células) → persona. El rol "líder de zona" no existe.
- Los usuarios principales son pastor, administrador, secretaría y líderes, con acceso desde computador y, sobre todo, teléfono.
- El alcance de esta funcionalidad se limita a los módulos de Personas y Líderes y a su relación con Células y Ministerios; Encuentros, Eventos y pagos no se rediseñan, solo se preserva su vínculo con la persona.
- Los datos existentes (personas, líderes, redes escritas a mano, relaciones con células) se migran conservando la información; ningún registro actual se pierde.
- La eliminación de personas con historial se sustituye por inactivación por defecto, siguiendo la práctica habitual de gestión de congregaciones.
- Se cumplen las expectativas habituales de protección de datos personales aplicables a la iglesia (consentimiento implícito de uso interno, acceso mínimo necesario por rol).
- Se mantiene la interfaz en español y el sistema de acceso con roles actualmente en uso.
- Fuera de alcance: comunicaciones masivas (SMS, WhatsApp, correo), reportes de asistencia avanzados y tableros de crecimiento; podrán abordarse en funcionalidades futuras.
