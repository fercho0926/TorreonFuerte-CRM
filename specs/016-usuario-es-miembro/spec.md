# Especificación de Funcionalidad: Todo usuario es un miembro y el pastor encabeza el liderazgo

**Rama de la funcionalidad**: `016-usuario-es-miembro`

**Creado**: 2026-10-23

**Estado**: Borrador

**Entrada**: Descripción del usuario: "un usuario del aplicativo TIENE que ser un miembro de la iaglesia, no es posible que personas que no son miembros, puedan ser uauarios por ende cuando quiera crear un usuario, al indicar su celular, debe buscar al miembro y asignarlo, si no esta creado en la creacion del usuario se toma la creacion del miembro, por ende debe ser UNA unica entidad, no puedo tener un nombre milton en usuaarios y un nombre milton en miembros, es la misma persona. para los lideres cuando se crea un lider, o se asigna un lider de inmediato se le pone como LIDER al pastor de la sede, el pastor es el unico que no tiene LIDER, puesto que es la cabeza,"

## Clarificaciones

### Sesión 2026-10-23

- P: Al designar como líder a alguien que ya tenía líder, ¿se reemplaza ese líder por el pastor o se conserva? → R: Siempre se reemplaza por el pastor; después se le puede mover a mano bajo otro líder.
- P: ¿Cómo aplica la regla «todo usuario es miembro» a los administradores, que hoy no tienen sede? → R: El administrador es miembro de una sede concreta (elegida al crear la cuenta) y sigue viendo todas las sedes.

## Contexto

Hoy un usuario (cuenta de acceso) puede existir sin estar vinculado a un miembro, y su nombre y celular se guardan aparte de los del miembro. Eso permite que "Milton" aparezca dos veces —una como usuario y otra como miembro— con datos que pueden diferir. Además, al designar a alguien como líder, nadie queda por encima de él, aunque en la iglesia toda la estructura de liderazgo depende del pastor de la sede.

Esta funcionalidad establece dos reglas de negocio:

1. **Una sola persona**: toda cuenta de usuario pertenece a un miembro de la iglesia. Los datos personales (nombre, celular) viven en el miembro; la cuenta solo agrega lo propio del acceso (rol, clave, estado de la cuenta).
2. **El pastor es la cabeza**: todo líder tiene un líder por encima; al crear o designar un líder, queda bajo el pastor de su sede. El pastor es el único sin líder.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Crear un usuario a partir de un miembro existente (Prioridad: P1)

Un administrador o pastor abre "Nuevo usuario" y escribe el celular de la persona. El sistema busca entre los miembros de la sede; si lo encuentra, muestra su nombre y lo deja seleccionado. El responsable solo elige el rol y guarda: la cuenta queda vinculada a ese miembro, sin volver a escribir nombre ni celular.

**Por qué esta prioridad**: es el corazón de la regla "una sola persona" y el caso más común: quien recibe acceso ya está registrado como miembro.

**Prueba independiente**: con un miembro "Milton Pérez" de celular 3001234567, crear un usuario indicando ese celular y verificar que la cuenta queda ligada a Milton, que en el listado de usuarios aparece "Milton Pérez" y que no se creó ningún miembro nuevo.

**Escenarios de Aceptación**:

1. **Dado** un miembro activo con celular 3001234567 en la sede, **Cuando** el responsable escribe ese celular en "Nuevo usuario", **Entonces** el sistema muestra el nombre del miembro encontrado y lo asigna a la cuenta.
2. **Dado** un miembro encontrado por celular, **Cuando** se guarda el usuario, **Entonces** la cuenta queda vinculada a ese miembro y no se crea otro miembro.
3. **Dado** un miembro que ya tiene una cuenta de usuario, **Cuando** se escribe su celular en "Nuevo usuario", **Entonces** el sistema avisa que esa persona ya tiene cuenta y no permite crear una segunda.
4. **Dado** un celular escrito con espacios, guiones o con el indicativo +57, **Cuando** se busca, **Entonces** el sistema encuentra al mismo miembro que con el número limpio.

---

### Historia de Usuario 2 - Crear el miembro al crear el usuario cuando no existe (Prioridad: P1)

Si el celular no corresponde a ningún miembro de la sede, el formulario pide los datos mínimos del miembro (nombres, apellidos y demás datos obligatorios de un miembro). Al guardar, se crean en una sola operación el miembro y la cuenta vinculada.

**Por qué esta prioridad**: sin este camino, el responsable tendría que salir del formulario, registrar al miembro y volver; además garantiza que nunca se cree una cuenta sin miembro.

**Prueba independiente**: crear un usuario con un celular inexistente, completar nombres y apellidos, y verificar que aparece un miembro nuevo en el listado de miembros de la sede y que la cuenta está vinculada a él.

**Escenarios de Aceptación**:

1. **Dado** un celular que no corresponde a ningún miembro de la sede, **Cuando** se escribe en "Nuevo usuario", **Entonces** el sistema indica que no existe y muestra los campos para registrar al miembro.
2. **Dado** el formulario completo, **Cuando** se guarda, **Entonces** se crean el miembro y la cuenta juntos; si algo falla, no queda ninguno de los dos.
3. **Dado** que falta un dato obligatorio del miembro, **Cuando** se intenta guardar, **Entonces** no se crea nada y se señala el campo faltante.

---

### Historia de Usuario 3 - Un solo nombre y un solo celular para la persona (Prioridad: P1)

Los datos personales de quien tiene cuenta se muestran y se editan en un solo lugar: el miembro. El listado de usuarios, el menú de la cuenta, el historial y cualquier otra pantalla muestran el nombre del miembro. Si se corrige el nombre o el celular del miembro, la cuenta refleja el cambio de inmediato; la cuenta ya no tiene un nombre ni un celular propios que puedan diferir.

**Por qué esta prioridad**: es lo que evita "Milton en usuarios y Milton en miembros" con datos distintos.

**Prueba independiente**: cambiar el nombre del miembro "Milton Pérez" a "Milton Andrés Pérez" y verificar que el listado de usuarios y el encabezado de su sesión muestran el nombre nuevo, sin editar la cuenta.

**Escenarios de Aceptación**:

1. **Dado** un usuario vinculado a un miembro, **Cuando** se corrige el nombre del miembro, **Entonces** el listado de usuarios muestra el nombre corregido.
2. **Dado** un usuario vinculado, **Cuando** se cambia el celular del miembro, **Entonces** ese celular es el que se usa para enviar claves por WhatsApp (el ingreso sigue siendo con el correo de la cuenta).
3. **Dado** el formulario de edición de un usuario, **Cuando** se abre, **Entonces** solo permite cambiar lo propio de la cuenta (rol, estado, sede si aplica) y ofrece ir a la ficha del miembro para los datos personales.
4. **Dado** que se intenta cambiar el celular de un miembro a uno que ya usa otro miembro con cuenta, **Cuando** se guarda, **Entonces** el sistema lo impide para que dos cuentas no compartan celular.

---

### Historia de Usuario 4 - Unificar las cuentas existentes con sus miembros (Prioridad: P2)

Las cuentas que hoy existen sin miembro vinculado se unifican: si el celular de la cuenta coincide con un miembro de su sede, se vinculan; si no hay coincidencia, se crea el miembro a partir de los datos de la cuenta. Al final ninguna cuenta queda sin miembro y el administrador recibe un resumen de lo hecho y de los casos que requieren revisión manual.

**Por qué esta prioridad**: sin esta unificación la regla no se cumple para los datos ya cargados, pero puede ejecutarse después de que las historias P1 estén en uso.

**Prueba independiente**: con una cuenta "Milton" sin vínculo y un miembro "Milton" del mismo celular en la misma sede, ejecutar la unificación y verificar que la cuenta queda vinculada a ese miembro y que no se duplicó.

**Escenarios de Aceptación**:

1. **Dado** una cuenta sin miembro cuyo celular coincide con un único miembro de su sede, **Cuando** se unifica, **Entonces** la cuenta queda vinculada a ese miembro.
2. **Dado** una cuenta sin miembro y sin coincidencia por celular, **Cuando** se unifica, **Entonces** se crea un miembro en su sede con el nombre y celular de la cuenta y se vinculan.
3. **Dado** una cuenta cuyo celular coincide con varios miembros, o cuyo nombre difiere claramente del miembro encontrado, **Cuando** se unifica, **Entonces** no se vincula automáticamente y queda en la lista de revisión manual.
4. **Dado** una cuenta ya vinculada a un miembro cuyo nombre o celular difieren, **Cuando** se unifica, **Entonces** prevalecen los datos del miembro y la diferencia queda registrada en el historial.

---

### Historia de Usuario 5 - Todo líder nuevo queda bajo el pastor de la sede (Prioridad: P1)

Cuando se registra un líder o se designa a un miembro como líder, el sistema le asigna de inmediato como líder al pastor de su sede. Después, un responsable puede moverlo bajo otro líder si la estructura lo requiere, pero nunca puede quedar sin líder.

**Por qué esta prioridad**: garantiza que el organigrama de la sede tenga una sola cabeza y que no aparezcan líderes "sueltos".

**Prueba independiente**: designar como líder a un miembro (con o sin líder previo) en una sede cuyo pastor es "Juan" y verificar que su líder queda en "Juan" y que el cambio aparece en su historial.

**Escenarios de Aceptación**:

1. **Dado** un miembro sin líder, **Cuando** se le designa como líder, **Entonces** su líder pasa a ser el pastor de la sede y queda registrado en el historial.
2. **Dado** un miembro que ya tenía un líder, **Cuando** se le designa como líder, **Entonces** su líder anterior se reemplaza por el pastor de la sede; si se requiere, un responsable puede moverlo después bajo otro líder.
3. **Dado** un líder, **Cuando** alguien intenta dejarlo sin líder, **Entonces** el sistema lo impide y explica que todo líder depende del pastor o de otro líder.
4. **Dado** el pastor de la sede, **Cuando** se consulta su ficha, **Entonces** aparece sin líder y no se le puede asignar uno.

---

### Historia de Usuario 6 - Al cambiar el pastor de la sede se mantiene la cabeza (Prioridad: P2)

Cuando el administrador cambia al pastor de una sede, el nuevo pastor queda sin líder y los líderes que dependían directamente del pastor anterior pasan a depender del nuevo pastor.

**Por qué esta prioridad**: los cambios de pastor son poco frecuentes, pero sin esta regla el organigrama quedaría con dos cabezas o con un pastor bajo otro líder.

**Prueba independiente**: con tres líderes bajo el pastor "Juan", cambiar el pastor de la sede a "Pedro" y verificar que los tres quedan bajo "Pedro" y que "Pedro" no tiene líder.

**Escenarios de Aceptación**:

1. **Dado** una sede con líderes bajo el pastor actual, **Cuando** se designa un pastor nuevo, **Entonces** esos líderes pasan a tener como líder al nuevo pastor.
2. **Dado** un pastor nuevo que tenía un líder, **Cuando** queda designado, **Entonces** su líder se retira porque ahora es la cabeza.
3. **Dado** el pastor anterior, **Cuando** deja de serlo, **Entonces** queda como miembro bajo el nuevo pastor.

---

### Casos Límite

- **Sede sin pastor asignado**: si se crea o designa un líder en una sede sin pastor, el sistema no puede cumplir la regla; se impide la operación con un mensaje que indica que primero se debe asignar el pastor de la sede.
- **Celular de otra sede**: la búsqueda por celular solo considera miembros de la sede de la cuenta; si el celular pertenece a un miembro de otra sede, se informa que la persona está registrada en otra sede y no se crea un duplicado (el traslado se hace por el módulo de traslados).
- **Miembro inactivo**: si el celular corresponde a un miembro inactivo, se avisa y se ofrece reactivarlo antes de crearle la cuenta; no se crea un miembro nuevo.
- **Celular repetido entre miembros**: si dos miembros de la sede comparten el celular buscado, el sistema muestra ambos y el responsable elige a cuál vincular.
- **Administrador del sistema**: las cuentas de administrador también son miembros, sin excepción. Su miembro pertenece a una sede concreta, que se elige al crear la cuenta, y el administrador sigue teniendo acceso a todas las sedes.
- **Pastora**: la pastora de la sede tampoco tiene líder, igual que el pastor (ver Supuestos).
- **Eliminar al miembro de una cuenta**: no se puede eliminar un miembro que tiene cuenta de usuario sin antes eliminar o desactivar la cuenta.
- **Quitar el rol de líder**: al quitar el rol, el miembro conserva al pastor (u otro líder) como su líder; las reglas actuales de reasignación de sus miembros se mantienen.
- **Ciclos**: no se permite poner al pastor bajo uno de sus líderes ni crear ciclos de liderazgo.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

**Usuario = miembro**

- **FR-001**: El sistema DEBE impedir que exista una cuenta de usuario sin un miembro vinculado.
- **FR-002**: Cada miembro DEBE poder tener como máximo una cuenta de usuario.
- **FR-003**: Al crear un usuario, el sistema DEBE pedir primero el celular y buscar al miembro de la sede con ese celular, normalizando espacios, guiones y el indicativo de país.
- **FR-004**: Si se encuentra el miembro, el sistema DEBE mostrar su nombre y vincular la cuenta a él sin pedir de nuevo nombre ni celular.
- **FR-005**: Si no se encuentra, el sistema DEBE pedir los datos obligatorios del miembro y crear el miembro y la cuenta en una sola operación atómica.
- **FR-006**: El nombre y el celular de una cuenta DEBEN ser siempre los de su miembro; la cuenta no DEBE guardar copias editables de esos datos.
- **FR-007**: Todas las pantallas que muestran a un usuario (listado, encabezado de sesión, historial, solicitudes de clave) DEBEN mostrar el nombre de su miembro.
- **FR-008**: El envío de claves por WhatsApp DEBE usar el celular del miembro vinculado. El ingreso sigue siendo con el correo de la cuenta.
- **FR-009**: El sistema DEBE impedir que dos miembros con cuenta compartan el mismo celular.
- **FR-010**: El sistema DEBE impedir eliminar un miembro que tiene cuenta de usuario activa.
- **FR-011**: El sistema DEBE ofrecer una unificación de las cuentas existentes: vincular por celular cuando hay una coincidencia única en la sede, crear el miembro cuando no la hay, y listar para revisión manual los casos ambiguos.
- **FR-012**: La búsqueda por celular DEBE respetar el alcance del responsable: solo miembros de las sedes que puede ver.
- **FR-012a**: Las cuentas de administrador DEBEN estar vinculadas a un miembro de una sede concreta, elegida al crear la cuenta; su acceso a todas las sedes no cambia.

**Liderazgo con el pastor como cabeza**

- **FR-013**: Al registrar un líder nuevo o designar a un miembro como líder, el sistema DEBE asignarle como líder al pastor de su sede, reemplazando el líder que tuviera.
- **FR-014**: Todo líder, salvo el pastor (y la pastora), DEBE tener un líder; el sistema DEBE impedir dejarlo sin líder.
- **FR-015**: El pastor de la sede NO DEBE tener líder y no se le DEBE poder asignar uno.
- **FR-016**: Si la sede no tiene pastor asignado, el sistema DEBE impedir crear o designar líderes y explicar el motivo.
- **FR-017**: Al cambiar el pastor de una sede, el sistema DEBE quitar el líder del nuevo pastor y mover bajo él a quienes dependían directamente del pastor anterior.
- **FR-018**: Todo cambio de líder hecho automáticamente por estas reglas DEBE quedar registrado en el historial del miembro, igual que un cambio manual.
- **FR-019**: El sistema DEBE ofrecer una corrección de los datos existentes: asignar al pastor de la sede como líder de los líderes que hoy no tienen uno.

### Entidades Clave

- **Miembro**: la persona de la iglesia. Única fuente de nombre, apellidos, celular y demás datos personales. Pertenece a una sede, puede tener un líder y puede ser líder.
- **Usuario (cuenta de acceso)**: el acceso de un miembro al aplicativo. Siempre vinculado a exactamente un miembro; guarda solo rol, clave, estado de la cuenta y sede de trabajo.
- **Sede**: tiene un pastor (y opcionalmente una pastora), que son miembros de la sede y la cabeza de su estructura de liderazgo.
- **Relación de liderazgo**: vínculo miembro → líder. En cada sede forma un árbol cuya raíz es el pastor.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **SC-001**: Tras la unificación, el 100 % de las cuentas de usuario están vinculadas a un miembro, salvo las que queden en la lista de revisión manual.
- **SC-002**: No existe ninguna persona registrada dos veces (una como usuario y otra como miembro) en una misma sede.
- **SC-003**: Crear un usuario para un miembro existente toma menos de 1 minuto y no requiere escribir su nombre.
- **SC-004**: El 100 % de los líderes de cada sede tienen un líder, excepto el pastor y la pastora.
- **SC-005**: En el organigrama de cada sede hay una sola cabeza: el pastor.
- **SC-006**: Un cambio de nombre o celular hecho en el miembro se ve en la cuenta de inmediato, sin pasos adicionales.

## Supuestos

- Los datos obligatorios para crear un miembro desde "Nuevo usuario" son los mismos que exige hoy el formulario de miembros.
- El celular es el dato de búsqueda y de ingreso; su normalización es la que ya usa el aplicativo (10 dígitos locales, indicativo de Colombia opcional).
- La pastora, cuando existe, es cabeza junto con el pastor: no tiene líder. Los líderes nuevos se asignan al pastor, no a la pastora.
- Un responsable puede mover después a un líder bajo otro líder (no solo bajo el pastor), siempre que no quede sin líder ni se formen ciclos.
- Solo se exige líder a los líderes; los miembros que no son líderes conservan las reglas actuales (pueden estar temporalmente sin líder y aparecer en "Sin líder").
- Los permisos de quién puede crear usuarios y designar líderes no cambian; se aplican las reglas de roles y aislamiento por sede vigentes.
- La unificación de cuentas existentes y la corrección de líderes sin líder se ejecutan una vez durante la implementación y pueden repetirse sin duplicar datos.
