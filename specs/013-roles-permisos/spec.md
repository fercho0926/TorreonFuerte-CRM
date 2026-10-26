# Especificación de Funcionalidad: Roles y permisos por módulo

**Rama de la funcionalidad**: `013-roles-permisos`

**Creado**: 2026-10-23

**Estado**: Borrador

**Entrada**: Descripción del usuario: "debemos crear una pestaña dentro de usuarios, llamada ROLES, en la cual podremos crear los roles de la aplicacion y daremos permisos a los modulos, tendran permisos de lectura o escritura que se podran asignar."

## Aclaraciones

### Sesión 2026-10-23

- Q: ¿Qué pasa con los 4 roles actuales (Administrador, Pastor, Líder, Usuario) cuando exista la pestaña ROLES? → A: Se convierten en roles editables que aparecen en la pestaña con los permisos que hoy tienen; se pueden ajustar y se pueden crear roles nuevos. El rol Administrador queda protegido: siempre tiene todos los permisos y no se puede quitar ni dejar sin ellos.
- Q: ¿En un rol se elige también qué datos ve la persona? → A: Sí, con tres niveles: toda la sede, solo su rama (el líder y sus miembros) o solo su propio registro. Los permisos de lectura y escritura se aplican dentro de ese alcance de datos.
- Q: Con roles configurables, ¿quién crea y edita usuarios y qué roles puede asignar? → A: Cualquier rol con escritura en el módulo Usuarios puede crear y editar usuarios de su propia sede, y solo puede asignar roles marcados como «asignables» cuyos permisos y alcance de datos no superen los suyos; nunca modifica cuentas con un rol que no puede asignar ni su propia cuenta.
- Q: Con alcance «su rama» o «su registro», ¿qué se ve en módulos sin dueño como Eventos, Encuentros y Ministerios? → A: Se ven completos (de su sede) con permiso de lectura, sin importar el alcance; el alcance solo filtra las personas, líderes, células y los inscritos o asistentes que se muestran dentro de ellos.
- Q: Si cambia o se elimina el rol de un usuario con sesión abierta (o se desactiva su cuenta), ¿cuándo pierde el acceso? → A: En su siguiente acción: cada petición se evalúa con el rol vigente, sin cerrar sesiones ni esperar a un nuevo inicio de sesión.
- Q: ¿Qué muestra el Tablero a un rol sin acceso a algunos módulos? → A: Cada tarjeta, total y acceso rápido solo aparece si el rol tiene al menos lectura en el módulo al que pertenece; el Tablero se adapta a cada rol y los totales respetan su alcance de datos.
- Q: ¿Quién puede crear y editar roles y sus permisos? → A: Solo el administrador. El pastor sigue creando usuarios, pero únicamente con los roles que el administrador haya habilitado para él.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Ver los roles en la pestaña ROLES (Prioridad: P1)

El administrador abre el módulo Usuarios y encuentra dos pestañas: «Usuarios» (lo de siempre) y «Roles». En «Roles» ve la lista de roles con su nombre, descripción, alcance de datos, cantidad de usuarios que lo tienen y si es un rol del sistema. Al abrir un rol ve su matriz de permisos: una fila por módulo con las opciones «Sin acceso», «Lectura» y «Escritura».

**Por qué esta prioridad**: Es la puerta de entrada a todo lo demás y permite comprobar que los roles actuales se migraron con los permisos correctos.

**Prueba Independiente**: Abrir Usuarios › Roles y comprobar que aparecen los 4 roles actuales con los permisos que hoy tienen en el sistema.

**Escenarios de Aceptación**:

1. **Dado** el administrador en el módulo Usuarios, **Cuando** abre la pestaña «Roles», **Entonces** ve los roles Administrador, Pastor, Líder y Usuario, cada uno con su alcance de datos y su cantidad de usuarios.
2. **Dado** un rol de la lista, **Cuando** el administrador lo abre, **Entonces** ve cada módulo con su nivel actual: sin acceso, lectura o escritura.
3. **Dado** un usuario que no es administrador, **Cuando** intenta abrir la pestaña «Roles» o su dirección, **Entonces** no la ve en el módulo y recibe el rechazo de acceso habitual.

---

### Historia de Usuario 2 - Crear un rol con permisos por módulo (Prioridad: P1)

El administrador crea un rol nuevo: le pone nombre y descripción, elige su alcance de datos (toda la sede, su rama o solo su registro) y, módulo por módulo, marca «Sin acceso», «Lectura» o «Escritura». Al guardar, el rol queda disponible para asignarlo a usuarios.

**Por qué esta prioridad**: Es lo pedido: poder definir roles propios de la iglesia (por ejemplo «Secretaría» o «Tesorería»).

**Prueba Independiente**: Crear el rol «Secretaría» con lectura en Miembros y escritura en Eventos, asignarlo a un usuario y comprobar que ese usuario solo puede lo definido.

**Escenarios de Aceptación**:

1. **Dado** el formulario de rol, **Cuando** el administrador lo completa con nombre, alcance y permisos y guarda, **Entonces** el rol aparece en la lista con sus permisos.
2. **Dado** un nombre ya usado por otro rol, **Cuando** intenta guardar, **Entonces** ve el error junto al campo y no se crea.
3. **Dado** un rol sin ningún permiso marcado, **Cuando** guarda, **Entonces** se permite (acceso solo al inicio y a su cuenta) y la lista lo indica como «Sin módulos».
4. **Dado** un rol nuevo, **Cuando** se crea o edita un usuario, **Entonces** el rol aparece entre las opciones asignables.

---

### Historia de Usuario 3 - Que los permisos se cumplan en toda la aplicación (Prioridad: P1)

Lo definido en el rol se aplica de verdad: un usuario sin acceso a un módulo no lo ve en el menú ni puede abrirlo por la dirección web; con «Lectura» ve la información pero no ve botones ni puede ejecutar acciones de crear, editar, inactivar, eliminar, trasladar o exportar datos de acceso; con «Escritura» puede además hacerlo, siempre dentro del alcance de datos de su rol y de su sede.

**Por qué esta prioridad**: Sin esto los roles serían decorativos; es la parte de seguridad de la funcionalidad.

**Prueba Independiente**: Para cada módulo, probar con un usuario sin acceso, uno con lectura y uno con escritura: menú, pantalla, botones y envíos de formularios por dirección directa.

**Escenarios de Aceptación**:

1. **Dado** un rol sin acceso a «Eventos», **Cuando** su usuario inicia sesión, **Entonces** no ve «Eventos» en el menú y su dirección responde con el rechazo de acceso.
2. **Dado** un rol con lectura en «Miembros», **Cuando** su usuario abre un miembro, **Entonces** ve su ficha sin botones de editar, inactivar, trasladar ni eliminar; y si envía a mano una modificación, el sistema la rechaza.
3. **Dado** un rol con escritura en «Miembros» y alcance «su rama», **Cuando** edita miembros, **Entonces** solo puede hacerlo con los de su rama.
4. **Dado** un rol con escritura, **Cuando** su usuario intenta usar datos de otra sede, **Entonces** se aplica el mismo aislamiento por sede de siempre.
5. **Dado** que el administrador cambia los permisos de un rol, **Cuando** un usuario con ese rol sigue con su sesión abierta, **Entonces** el cambio se aplica en su siguiente acción, sin cerrar sesión.

---

### Historia de Usuario 4 - Editar, duplicar y eliminar roles con seguridad (Prioridad: P2)

El administrador puede cambiar los permisos, el nombre y el alcance de un rol, duplicar un rol para partir de uno existente y eliminar los roles que ya no use. El sistema impide dejar la aplicación sin administrador y avisa cuántos usuarios se verán afectados antes de guardar un cambio.

**Por qué esta prioridad**: Mantiene los roles útiles en el tiempo, pero depende de que crear y aplicar permisos ya funcione.

**Prueba Independiente**: Editar un rol con usuarios asignados, ver el aviso de cuántos se afectan; intentar eliminar un rol en uso y el rol Administrador.

**Escenarios de Aceptación**:

1. **Dado** un rol con usuarios asignados, **Cuando** el administrador cambia sus permisos y guarda, **Entonces** ve antes una confirmación con el número de usuarios afectados.
2. **Dado** un rol en uso, **Cuando** intenta eliminarlo, **Entonces** se rechaza con un mensaje que indica cuántos usuarios lo tienen y que debe reasignarlos primero.
3. **Dado** un rol sin usuarios, **Cuando** el administrador lo elimina con confirmación, **Entonces** desaparece de la lista y de las opciones asignables.
4. **Dado** el rol Administrador, **Cuando** el administrador intenta quitarle permisos, cambiar su alcance, renombrarlo o eliminarlo, **Entonces** el sistema lo impide y explica que es un rol protegido.
5. **Dado** un rol existente, **Cuando** el administrador elige «Duplicar», **Entonces** se abre un formulario con los mismos permisos y un nombre nuevo por completar.

---

### Historia de Usuario 5 - Quien tiene escritura en Usuarios crea cuentas solo con roles permitidos (Prioridad: P2)

El administrador marca qué roles son «asignables». Cualquier usuario con escritura en el módulo Usuarios (hoy, el pastor) crea y edita cuentas de su propia sede y solo ve como opciones los roles asignables cuyos permisos y alcance de datos no superen los suyos. No puede cambiar el rol de cuentas con un rol que no puede asignar, ni el de su propia cuenta.

**Por qué esta prioridad**: Conserva la delegación que el pastor tiene hoy, ahora basada en permisos y no en el nombre del rol, sin abrir una vía para escalar permisos.

**Prueba Independiente**: Con un usuario con escritura en Usuarios, crear una cuenta y comprobar que solo se ofrecen los roles permitidos; intentar enviar a mano otro rol o editar su propia cuenta y ver el rechazo.

**Escenarios de Aceptación**:

1. **Dado** un rol marcado como «asignable» cuyos permisos no superan los de quien crea, **Cuando** ese usuario (con escritura en Usuarios) crea una cuenta, **Entonces** puede elegirlo.
2. **Dado** un rol no asignable o con más permisos o mayor alcance que los de quien crea, **Cuando** intenta asignarlo, aun por dirección directa, **Entonces** se rechaza.
3. **Dado** una cuenta con un rol que quien edita no puede asignar, **Cuando** intenta editarla, **Entonces** no puede cambiar su rol, sus datos de acceso ni desactivarla.
4. **Dado** un usuario con escritura en Usuarios, **Cuando** intenta cambiar su propio rol, **Entonces** se rechaza.
5. **Dado** un usuario con solo lectura en Usuarios, **Cuando** abre el módulo, **Entonces** ve la lista pero no ve acciones de crear, editar ni generar claves.

---

### Historia de Usuario 6 - Ver quién tiene cada rol y qué cambió (Prioridad: P3)

Desde un rol, el administrador ve la lista de usuarios que lo tienen y el historial de cambios del rol (quién, cuándo, qué permiso cambió de qué nivel a cuál).

**Por qué esta prioridad**: Da trazabilidad y control; no es necesario para que los roles funcionen.

**Prueba Independiente**: Cambiar un permiso y comprobar que el historial muestra el cambio con fecha y autor.

**Escenarios de Aceptación**:

1. **Dado** un rol con usuarios, **Cuando** el administrador abre su detalle, **Entonces** ve nombre y sede de cada usuario con ese rol.
2. **Dado** un cambio de permisos, **Cuando** el administrador abre el historial del rol, **Entonces** ve la entrada con fecha, autor y los niveles anterior y nuevo.

---

### Casos Límite

- Un usuario cuyo rol fue eliminado o dejado sin módulos: se evita eliminando solo roles sin usuarios; un rol sin módulos solo da acceso al inicio y a «Mi cuenta».
- Un rol que da escritura en un módulo pero sin lectura: la escritura incluye siempre la lectura (los niveles son acumulativos: sin acceso < lectura < escritura).
- Dos administradores cambiando el mismo rol a la vez: gana el último guardado y el historial registra ambos cambios.
- Alcance «su rama» o «su registro» en un evento, encuentro o ministerio: se ve el evento completo de la sede, pero en su lista de inscritos o asistentes solo aparecen las personas dentro de su alcance.
- Alcance «solo su registro» con escritura en «Miembros»: solo puede editar su propia ficha.
- Un módulo que no tiene sentido para cierto alcance (por ejemplo, «Sedes» con alcance de rama): el módulo «Sedes» es exclusivo del administrador y no se puede asignar a otros roles.
- Usuario con sesión abierta cuando se elimina o cambia su rol: la siguiente acción aplica los permisos vigentes; si ya no tiene acceso a la pantalla, ve el rechazo habitual.
- Nombre de rol con espacios, mayúsculas o acentos: se limpia y la unicidad no distingue mayúsculas ni acentos.
- Exportaciones que contienen datos de acceso (usuarios, solicitudes de clave): siguen restringidas a quien tenga escritura en «Usuarios» o «Solicitudes de clave».
- Cuentas existentes: tras la migración cada usuario conserva exactamente el acceso que tenía antes.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El módulo Usuarios DEBE tener dos pestañas, «Usuarios» y «Roles»; la pestaña «Roles» solo la ve y la usa el administrador.
- **RF-002**: La pestaña «Roles» DEBE listar los roles con nombre, descripción, alcance de datos, cantidad de usuarios y marca de «rol del sistema», con acciones de ver, editar, duplicar y eliminar.
- **RF-003**: El administrador DEBE poder crear un rol con nombre (único, obligatorio, máx. 60 caracteres), descripción (opcional, máx. 255), alcance de datos y un nivel por módulo.
- **RF-004**: Cada módulo DEBE admitir tres niveles acumulativos: «Sin acceso», «Lectura» y «Escritura» (escritura incluye lectura).
- **RF-005**: El alcance de datos DEBE ser uno de: «Toda la sede», «Solo su rama» (el líder y sus miembros) o «Solo su registro». El alcance filtra personas, líderes, células y los inscritos o asistentes que se listan dentro de otros módulos; los módulos sin dueño (Eventos, Encuentros, Ministerios) se ven completos, dentro de la sede, con permiso de lectura, sea cual sea el alcance.
- **RF-006**: Los módulos configurables DEBEN ser: Tablero, Miembros, Líderes, Sin líder asignado, Células, Ministerios, Eventos, Encuentros, Organigrama, Registro de miembros por QR, Usuarios y Solicitudes de clave. «Sedes» y la gestión de roles son exclusivos del administrador y no se asignan a otros roles.
- **RF-007**: Los 4 roles actuales DEBEN existir como roles editables con exactamente los permisos y el alcance de datos que tienen hoy, y cada usuario existente DEBE conservar su acceso tras la migración.
- **RF-008**: El rol Administrador DEBE estar protegido: siempre con escritura en todos los módulos y alcance total, sin poder renombrarse, modificarse ni eliminarse; siempre debe existir al menos un usuario administrador activo.
- **RF-009**: Sin acceso a un módulo, el sistema NO DEBE mostrarlo en el menú ni permitir abrirlo, consultarlo ni modificarlo por dirección directa (pantallas, datos de grillas, exportaciones y búsquedas). El Tablero DEBE mostrar cada total, tarjeta y acceso rápido solo si el rol tiene al menos lectura en el módulo al que pertenece, con totales calculados dentro de su alcance de datos.
- **RF-010**: Con «Lectura», el sistema DEBE permitir ver y consultar pero NO DEBE mostrar ni aceptar acciones que cambian datos (crear, editar, inactivar, reactivar, eliminar, trasladar, asignar líder, abrir o cerrar QR, regenerar enlaces, atender solicitudes, generar claves).
- **RF-011**: Con «Escritura», el sistema DEBE permitir esas acciones dentro del alcance de datos del rol y de la sede del usuario; el aislamiento por sede NO DEBE depender del rol.
- **RF-012**: Cada petición DEBE evaluarse con el rol y los permisos vigentes del usuario (no con los de su inicio de sesión), de modo que un cambio, la eliminación del rol o la desactivación de la cuenta se aplican en su siguiente acción, sin cerrar sesiones; si ya no tiene acceso a la pantalla actual, ve el rechazo habitual.
- **RF-013**: El sistema DEBE pedir confirmación indicando cuántos usuarios se verán afectados antes de guardar cambios en un rol con usuarios.
- **RF-014**: El sistema NO DEBE permitir eliminar un rol con usuarios asignados; DEBE indicar cuántos y pedir reasignarlos.
- **RF-015**: Cada rol DEBE tener un indicador «asignable»; cualquier usuario con escritura en el módulo Usuarios DEBE poder crear y editar cuentas solo de su propia sede y asignar únicamente roles asignables cuyos permisos y alcance de datos no superen los suyos; NO DEBE poder cambiar el rol, los datos de acceso ni el estado de cuentas con un rol que no puede asignar, ni su propio rol.
- **RF-016**: Los selectores de rol de los formularios de usuarios DEBEN mostrar todos los roles vigentes al administrador y solo los asignables y permitidos a los demás usuarios con escritura en Usuarios.
- **RF-017**: El sistema DEBE guardar un historial de cambios de cada rol (creación, edición de permisos, alcance, nombre y eliminación) con autor, fecha y niveles anterior y nuevo.
- **RF-018**: Los usuarios de un rol DEBEN poder consultarse desde el detalle del rol.
- **RF-019**: Toda la gestión de roles DEBE cumplir las reglas de la constitución: aislamiento por sede, formularios con los componentes estándar, listado con la grilla estándar, diseño primero para celular y textos en español con el término «miembros».

### Entidades Clave *(incluir si la funcionalidad maneja datos)*

- **Rol**: Conjunto con nombre de permisos. Datos: nombre único, descripción, alcance de datos, marca de rol del sistema (protegido o no), indicador «asignable».
- **Permiso de rol por módulo**: Nivel de un rol en un módulo: sin acceso, lectura o escritura.
- **Módulo**: Parte de la aplicación a la que se da acceso (Tablero, Miembros, Líderes, etc.); la lista es fija y definida por el sistema.
- **Alcance de datos**: Qué registros ve un rol dentro de su sede: toda la sede, su rama o su propio registro.
- **Usuario**: Cuenta con exactamente un rol, de la sede que corresponda.
- **Historial del rol**: Bitácora de cambios de un rol (autor, fecha, antes y después).

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: El administrador crea un rol nuevo con sus permisos en menos de 3 minutos.
- **CE-002**: El 100 % de los usuarios existentes conserva, tras la migración, el mismo acceso que tenía (verificado por una comparación módulo por módulo).
- **CE-003**: En las pruebas de todos los módulos y niveles, cero accesos indebidos: ningún usuario abre, consulta o modifica algo por encima de su nivel, ni siquiera por dirección directa.
- **CE-004**: Un cambio de permisos se refleja en los usuarios con sesión abierta en su siguiente acción (menos de 1 minuto sin cerrar sesión).
- **CE-005**: Cero usuarios quedan sin rol y la aplicación nunca queda sin administrador activo.
- **CE-006**: Cero cambios de permisos sin historial: el 100 % queda registrado con autor y fecha.
- **CE-007**: La pestaña «Roles» es utilizable de 320 a 1920 px sin desplazamiento horizontal de la página y con controles de al menos 44 × 44 px.

## Supuestos

- Cada usuario tiene exactamente un rol (como hoy).
- Los módulos configurables corresponden a las secciones actuales del menú y a las funciones de la aplicación; «Sedes» (incluida su pestaña de organigrama) y la propia gestión de roles quedan reservados al administrador.
- «Sedes» y «Registro de miembros» del pastor: el pastor de hoy mantiene su acceso a su propio QR y a su sede como parte del permiso del módulo «Registro de miembros por QR».
- Los permisos de los roles actuales se derivan de lo que hoy hace cada uno: Administrador (todo, todas las sedes), Pastor (toda la sede, con escritura salvo Sedes), Líder (su rama; Miembros, Células y lo que hoy usa), Usuario (solo su registro).
- Los permisos aplican siempre dentro de la sede del usuario; solo el administrador puede trabajar con todas las sedes.
- Los niveles de permiso son solo tres; permisos por acción individual (por ejemplo, «puede eliminar pero no editar») quedan fuera de esta versión.
- Se mantiene el inicio de sesión, la recuperación de clave y «Mi cuenta» disponibles para cualquier rol.
- La asignación del rol a un usuario se hace en los formularios de usuarios existentes, que pasan a leer la lista de roles dinámica.
- Esta funcionalidad se apoya en el aislamiento por sede y en el vocabulario «Miembros» de la funcionalidad 010.
