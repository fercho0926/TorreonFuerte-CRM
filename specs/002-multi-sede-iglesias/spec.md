# Especificación de la funcionalidad: Multi-sede (varias iglesias en un mismo CRM)

**Rama de la funcionalidad**: `002-multi-sede-iglesias`

**Creada**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "el crm lo van a usar multiples iglesias, vamos a comenzar con 3 iglesias por ahora pero son muchas mas. Quiero una especie de multitenant, es decir que cada iglesia tenga su pastor, que cada uno pueda ver sus miembros, que cada una pueda administrar sus miembros, liderazgo, eventos, células, ministerios y demás, que SOLO el admin pueda verlo TODO, de resto cada iglesia solo podrá ver lo de su iglesia; entonces necesitamos un módulo SOLO ADMIN para crear las sedes, que se cree la sede, el pastor, la pastora. Ayúdame a aclarar la idea y a ponerla en marcha."

## Aclaración de la idea *(resultado del análisis)*

Cómo se entiende la idea y qué cambia respecto a hoy:

1. **Sede = una iglesia con su propio mundo de datos.** Cada sede tiene sus propias personas, líderes, redes, zonas, células, encuentros, eventos (con sus pagos) y ministerios. Nada se comparte entre sedes.
2. **Dos niveles de acceso:**
   - **Administrador del sistema (admin):** el único que ve todas las sedes, crea sedes y consulta información global.
   - **Todos los demás (pastor, pastora, líderes, usuarios):** pertenecen a una sola sede y solo ven y gestionan lo de esa sede.
3. **Hoy no es así:** actualmente el rol "pastor" ve **todo** el sistema y no existe el concepto de sede. Con esta funcionalidad el pastor pasa a ver únicamente su sede; solo el admin conserva la visión total.
4. **Alta de una sede:** el admin crea la sede y, en el mismo flujo, registra al **pastor** y a la **pastora**, que quedan con su acceso para administrar esa sede. Con 3 sedes iniciales y la expectativa de crecer a muchas más, crear una sede nueva debe ser rápido y sin intervención técnica.
5. **Datos existentes:** lo que hoy hay en el sistema pertenece a una primera sede y no se pierde.

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿Un mismo usuario puede pertenecer a más de una sede? → A: No; cada usuario pertenece a una sola sede (solo el admin ve varias). Un pastor que atienda dos iglesias necesita una cuenta por sede.
- Q: Cuando el admin trabaja con una sede, ¿cómo elige la sede de los registros que crea o edita? → A: Con una "sede activa" elegida en la barra superior; lo que ve y crea queda en esa sede. Existe además "Todas las sedes" solo para consultar (sin crear ni editar).
- Q: ¿Qué usuarios puede ver y crear el pastor de una sede? → A: Ve y administra los usuarios de su sede y crea solo líderes y usuarios; las cuentas de pastor y pastora las crea o cambia únicamente el admin.
- Q: ¿Qué pasa con los cargos y relaciones de una persona al trasladarla a otra sede? → A: Se exige resolver antes (reasignar personas, células y ministerios que dirige); al trasladar, la persona queda sin líder, sin célula y sin ministerios en la sede nueva.
- Q: Al migrar los datos actuales, ¿qué pasa con los usuarios que hoy tienen rol de pastor? → A: Pasan a ser pastores de la primera sede y dejan de ver otras sedes; solo el admin conserva la visión total (el admin avisa antes de activar el cambio).

## Escenarios de usuario y pruebas *(obligatorio)*

### Historia de usuario 1 - El admin crea una sede con su pastor y su pastora (Prioridad: P1)

El administrador entra al módulo exclusivo de sedes, crea una iglesia nueva (nombre, ciudad, datos de contacto) y registra en el mismo flujo al pastor y a la pastora con sus datos de acceso. Al terminar, la sede queda lista y sus pastores pueden iniciar sesión y empezar a trabajar.

**Por qué esta prioridad**: Sin sedes y sin pastores por sede no existe el resto de la funcionalidad; es el punto de partida y el único camino para incorporar nuevas iglesias.

**Prueba independiente**: Un admin crea una sede de prueba con pastor y pastora; luego se inicia sesión como cada uno y se confirma que ven su sede (vacía) y que pueden registrar personas.

**Escenarios de aceptación**:

1. **Dado** un admin autenticado, **cuando** completa el formulario de nueva sede con pastor y pastora, **entonces** la sede queda creada, el pastor y la pastora quedan registrados como personas y usuarios de esa sede, y la sede aparece en el listado de sedes.
2. **Dado** una sede sin pastora (la iglesia solo tiene pastor), **cuando** el admin la crea indicando solo al pastor, **entonces** la sede se crea correctamente; la pastora puede añadirse después.
3. **Dado** un usuario que no es admin (pastor, líder o usuario), **cuando** intenta abrir el módulo de sedes, **entonces** no puede ver ni acceder a él y recibe un mensaje claro de falta de permiso.
4. **Dado** un correo ya usado por otro usuario, **cuando** el admin intenta registrarlo como pastor, **entonces** el sistema lo rechaza con un mensaje claro y no deja la sede a medio crear.

---

### Historia de usuario 2 - Cada sede ve y administra solo lo suyo (Prioridad: P1)

El pastor, la pastora y los líderes de una sede ven y gestionan únicamente las personas, líderes, redes, zonas, células, encuentros, eventos y ministerios de su sede. Nunca ven, buscan ni reciben información de otra sede, ni siquiera por accidente (búsquedas, selectores, enlaces directos, listados o reportes).

**Por qué esta prioridad**: Es el requisito central de privacidad: los datos de los miembros de una iglesia no deben ser visibles para otra. Un solo fallo aquí rompe la confianza de todas las sedes.

**Prueba independiente**: Con dos sedes con datos distintos, iniciar sesión como pastor de la sede A y comprobar que no aparece nada de la sede B en ningún módulo, selector, búsqueda ni enlace directo; repetir con la sede B.

**Escenarios de aceptación**:

1. **Dado** dos sedes con personas y eventos propios, **cuando** el pastor de la sede A abre cualquier listado (personas, líderes, células, encuentros, eventos, ministerios, redes, zonas), **entonces** solo ve registros de la sede A.
2. **Dado** un pastor de la sede A, **cuando** escribe en el navegador el enlace directo a una persona, célula o evento de la sede B, **entonces** el sistema responde como si no existiera (sin revelar que existe).
3. **Dado** un pastor de la sede A, **cuando** crea o edita una persona, célula, evento o ministerio, **entonces** el registro queda asociado automáticamente a la sede A y no puede asignarse a líderes, redes, zonas o ministerios de otra sede.
4. **Dado** una persona con el mismo número de documento registrada en la sede A y en la sede B, **cuando** cada sede la consulta, **entonces** cada una ve solo su propio registro y el sistema no revela a una sede que esa persona existe en otra.
5. **Dado** un pastor, **cuando** gestiona usuarios, **entonces** solo ve y administra usuarios de su propia sede y solo puede crear cuentas de líder y de usuario; no puede crear ni modificar cuentas de pastor, pastora ni administrador, ni cuentas de otra sede.

---

### Historia de usuario 3 - El admin ve todo y puede cambiar de sede (Prioridad: P2)

El administrador ve la información de todas las sedes: un resumen por sede y, cuando lo necesita, entra al detalle de una sede concreta para ver o ayudar a corregir sus datos. En los listados puede filtrar por sede y siempre se identifica a qué sede pertenece cada registro.

**Por qué esta prioridad**: Da al admin la visibilidad y el soporte que necesita para operar muchas iglesias, pero depende de que primero existan las sedes y el aislamiento.

**Prueba independiente**: Con tres sedes con datos, el admin abre el resumen global, filtra por una sede, entra a su detalle y comprueba que ve exactamente lo que ve el pastor de esa sede más la identificación de la sede.

**Escenarios de aceptación**:

1. **Dado** tres sedes, **cuando** el admin abre el resumen global, **entonces** ve por cada sede el número de personas, líderes, células y eventos próximos.
2. **Dado** el admin en cualquier listado (personas, líderes, células, eventos, ministerios), **cuando** elige una sede activa en la barra superior, **entonces** se muestran solo los registros de esa sede; con "Todas las sedes" se muestran todas y cada registro indica su sede.
3. **Dado** el admin con "Todas las sedes" seleccionado, **cuando** intenta crear o editar un registro, **entonces** el sistema le pide primero elegir una sede activa; con una sede activa, todo lo que crea o edita queda en esa sede y ningún registro queda sin sede.

---

### Historia de usuario 4 - El admin administra las sedes existentes (Prioridad: P2)

El administrador puede editar los datos de una sede, cambiar su pastor o pastora, y desactivar una sede que ya no opera. Una sede desactivada conserva todos sus datos pero sus usuarios no pueden iniciar sesión.

**Por qué esta prioridad**: Cubre el ciclo de vida normal de una iglesia (cambio de pastor, cierre temporal), pero puede resolverse después de crear sedes y de garantizar el aislamiento.

**Prueba independiente**: El admin cambia el pastor de una sede y desactiva otra; el nuevo pastor entra a su sede y los usuarios de la sede desactivada no pueden iniciar sesión, pero el admin sigue viendo sus datos.

**Escenarios de aceptación**:

1. **Dado** una sede con pastor, **cuando** el admin designa a otro pastor, **entonces** el nuevo pastor obtiene acceso a la sede y el anterior pierde el rol de pastor (conserva su registro como persona).
2. **Dado** una sede activa, **cuando** el admin la desactiva, **entonces** ninguno de sus usuarios puede iniciar sesión y sus datos se conservan; el admin puede reactivarla.
3. **Dado** una sede con datos, **cuando** el admin intenta eliminarla definitivamente, **entonces** el sistema no lo permite (solo desactivar) para evitar pérdida de información.

---

### Historia de usuario 5 - Las sedes actuales y los datos existentes pasan al nuevo modelo sin perderse (Prioridad: P1)

Los datos que hoy existen en el sistema (personas, líderes, células, eventos, ministerios, usuarios) se asignan a una primera sede y siguen funcionando igual. Los usuarios existentes siguen entrando con las mismas credenciales, ahora dentro de su sede.

**Por qué esta prioridad**: Sin una migración segura se pierde o se mezcla información real de la congregación actual.

**Prueba independiente**: Con una copia de los datos actuales, aplicar el cambio y verificar que ningún registro se pierde, todos quedan en la primera sede y los usuarios existentes ven lo mismo que antes (el pastor actual ahora limitado a su sede).

**Escenarios de aceptación**:

1. **Dado** los datos actuales del sistema, **cuando** se activa el modelo multi-sede, **entonces** todos los registros quedan asignados a una primera sede y el conteo de registros por módulo es idéntico antes y después.
2. **Dado** los usuarios actuales, **cuando** inician sesión tras el cambio, **entonces** los admins conservan la visión total, los pastores actuales quedan como pastores de la primera sede y el resto de usuarios queda en esa sede con el mismo rol.

---

### Casos límite

- ¿Qué pasa si un usuario que no es admin no tiene sede asignada? No debe ver ningún dato y debe recibir un mensaje claro para contactar al administrador.
- ¿Qué pasa si una persona (por ejemplo, un líder) se muda de iglesia? El admin puede trasladarla, pero antes debe reasignar sus personas, células y ministerios a cargo; en la sede nueva llega sin líder, célula ni ministerios (aparece en "Sin líder asignado") y el historial permanece con la sede donde ocurrió.
- ¿Qué pasa con una sede desactivada que aún tiene eventos con saldos pendientes? Los datos y saldos se conservan para consulta del admin; no se pueden registrar pagos nuevos mientras la sede esté inactiva.
- ¿Qué ocurre si dos sedes quieren el mismo nombre de red, zona, ministerio o evento? Es válido: los catálogos y nombres son independientes por sede; la unicidad se evalúa dentro de cada sede.
- ¿Qué pasa si falla la creación a mitad del flujo (por ejemplo, el correo de la pastora ya existe)? No debe quedar una sede o un usuario a medias: o se crea todo o no se crea nada.
- ¿Qué pasa si un pastor atiende dos iglesias? Necesita una cuenta distinta por sede (con correos diferentes); el sistema no mezcla ambas cuentas.
- ¿Qué pasa si un pastor intenta darse a sí mismo el rol de admin o moverse a otra sede? El sistema lo impide; solo el admin define roles globales y sedes.
- ¿Qué ve el admin en reportes y totales (por ejemplo, el resumen de recaudos)? Totales por sede y un total global claramente rotulados, sin mezclar montos de distintas sedes sin indicarlo.
- ¿Qué pasa cuando se agrega la sede número 4, 10 o 50? El alta funciona igual, sin cambios en el sistema ni en las sedes existentes.

## Requisitos *(obligatorio)*

### Requisitos funcionales

**Sedes**

- **FR-001**: El sistema DEBE permitir al administrador crear sedes con, al menos, nombre (único), ciudad y datos de contacto.
- **FR-002**: El sistema DEBE permitir, en el mismo flujo de alta de sede, registrar al pastor y, opcionalmente, a la pastora con sus datos personales y de acceso, de forma que la sede quede operativa al terminar.
- **FR-003**: El alta de sede DEBE ser atómica: si algún paso falla (por ejemplo, un correo ya usado), no queda ninguna sede ni usuario parcial.
- **FR-004**: El sistema DEBE permitir al administrador editar los datos de una sede, cambiar su pastor o pastora y desactivar o reactivar la sede; no DEBE permitir eliminar sedes que tengan datos.
- **FR-005**: Las sedes desactivadas DEBEN conservar todos sus datos, impedir el inicio de sesión de sus usuarios y no admitir nuevas operaciones de escritura.

**Aislamiento de datos**

- **FR-006**: Toda la información de la iglesia (personas, líderes, redes, zonas, células y sus reuniones, encuentros, eventos con inscripciones y pagos, ministerios, bitácora de cambios) DEBE pertenecer a una única sede.
- **FR-007**: Los usuarios que no son administradores DEBEN ver, buscar, crear, modificar e inactivar únicamente información de su propia sede en todos los módulos, listados, filtros, selectores, reportes y enlaces directos.
- **FR-008**: Un registro de otra sede consultado por enlace directo DEBE responder como inexistente, sin revelar que existe.
- **FR-009**: Los registros nuevos DEBEN asociarse automáticamente a la sede del usuario que los crea; ninguna relación (líder, célula, red, zona, ministerio, evento, persona) puede cruzar sedes.
- **FR-010**: Las reglas de unicidad (documento, nombre de red, zona, ministerio, etc.) DEBEN evaluarse dentro de cada sede, sin revelar la existencia de datos de otras sedes.
- **FR-011**: El alcance por rol existente (el líder ve a sus personas y células) DEBE seguir funcionando dentro de la sede.

**Roles y permisos**

- **FR-012**: El sistema DEBE tener un único rol con visión total (administrador del sistema); el rol de pastor DEBE quedar limitado a su sede, y cada usuario no administrador DEBE pertenecer a una sola sede (sin selector de sede ni cuentas multi-sede).
- **FR-013**: El pastor y la pastora de una sede DEBEN tener los mismos permisos de administración sobre su sede: gestionar personas, liderazgo, redes, zonas, células, encuentros, eventos, ministerios y usuarios de la sede.
- **FR-014**: Un pastor DEBE poder ver y administrar los usuarios de su sede y crear únicamente cuentas con rol de líder o de usuario; NO DEBE poder crear ni modificar cuentas de pastor, pastora o administrador, ni usuarios de otra sede, ni cambiar su propia sede o rol. Las cuentas de pastor y pastora solo las crea o cambia el administrador.
- **FR-015**: Solo el administrador DEBE acceder al módulo de sedes (crear, listar, editar, desactivar) y al resumen global.
- **FR-016**: Un usuario que no es admin y no tiene sede asignada NO DEBE ver datos y DEBE recibir un mensaje claro.

**Vista del administrador**

- **FR-017**: El administrador DEBE poder ver todas las sedes, con un resumen por sede (personas, líderes, células, eventos próximos) y totales globales rotulados por sede.
- **FR-018**: El administrador DEBE poder elegir una "sede activa" en la barra superior o la opción "Todas las sedes"; los listados y detalles DEBEN filtrarse según esa elección y, con "Todas las sedes", cada registro DEBE identificar su sede.
- **FR-019**: La opción "Todas las sedes" DEBE ser de solo consulta: para crear o editar registros el administrador DEBE tener una sede activa seleccionada, de modo que ningún registro quede sin sede ni en una sede equivocada. La sede activa DEBE ser visible en todo momento.
- **FR-020**: El administrador DEBE poder trasladar a una persona de una sede a otra. Si la persona es líder con personas o células a cargo, o encargada de un ministerio, el sistema DEBE exigir reasignarlas antes de trasladar; al trasladar, la persona queda sin líder, sin célula y sin ministerios en la sede nueva, y su historial (asistencias, pagos, células) permanece con la sede donde ocurrió.

**Datos existentes y trazabilidad**

- **FR-021**: Al activar el modelo multi-sede, todos los datos existentes DEBEN asignarse a una primera sede sin pérdida de registros; los usuarios existentes DEBEN conservar sus credenciales: los administradores mantienen la visión total y los usuarios con rol pastor pasan a ser pastores de la primera sede (sin acceso a otras sedes). Antes de activar el cambio, el administrador DEBE poder revisar qué usuarios quedarán en la primera sede.
- **FR-022**: El sistema DEBE registrar quién creó, modificó o desactivó una sede y quién cambió al pastor o pastora, visible solo para el administrador.
- **FR-023**: El sistema DEBE mostrar al usuario el nombre de su sede en la interfaz, para que siempre sepa en qué iglesia está trabajando.

### Entidades clave *(incluir si la funcionalidad involucra datos)*

- **Sede (iglesia)**: Una iglesia con su propio conjunto de datos. Tiene nombre, ciudad, datos de contacto, estado (activa o inactiva), pastor y, opcionalmente, pastora.
- **Pastor / Pastora**: Personas con usuario de acceso, responsables de una sede; ambos con los mismos permisos de administración sobre ella.
- **Usuario**: Cuenta de acceso con rol; pertenece a una sede, salvo el administrador del sistema que no pertenece a ninguna y las ve todas.
- **Datos de la sede**: Personas, líderes, redes, zonas, células, reuniones, encuentros, eventos con inscripciones y pagos, ministerios y bitácora; cada registro pertenece a una sola sede.
- **Traslado de persona**: Movimiento de una persona de una sede a otra, hecho por el administrador tras reasignar lo que dirigía; llega sin líder, célula ni ministerios y conserva el historial en la sede de origen.

## Criterios de éxito *(obligatorio)*

### Resultados medibles

- **SC-001**: Un administrador crea una sede completa con su pastor y pastora en menos de 3 minutos, sin ayuda técnica.
- **SC-002**: Cero casos, en pruebas de aceptación con al menos 3 sedes y datos cruzados, en los que un usuario no administrador ve, encuentra o modifica información de otra sede (incluyendo búsquedas, selectores, enlaces directos y reportes).
- **SC-003**: Se pueden dar de alta 20 sedes consecutivas con la misma facilidad que la primera, sin degradar la velocidad de los listados (siguen respondiendo en menos de 2 segundos con 1.000 personas por sede).
- **SC-004**: Tras la migración de datos existentes, el 100% de los registros conserva su información y queda asignado a una sede, y el conteo por módulo coincide antes y después.
- **SC-005**: El 100% de las rutas y pantallas de la aplicación quedan cubiertas por pruebas de aislamiento entre sedes (ningún módulo queda fuera).
- **SC-006**: El 90% de los pastores nuevos completa su primera tarea (registrar una persona en su sede) sin ayuda en menos de 5 minutos tras su primer inicio de sesión.
- **SC-007**: Cero registros sin sede en la base de datos tras cualquier operación de creación o traslado.

## Supuestos

- Una persona pertenece a **una sola sede** a la vez; los traslados los hace el administrador. El mismo individuo puede existir como registros independientes en dos sedes (cada sede es dueña de su registro).
- El pastor y la pastora tienen **exactamente los mismos permisos** sobre su sede; la sede puede tener solo pastor (la pastora es opcional) y no se modelan más cargos de este nivel en esta funcionalidad.
- Los catálogos (redes, zonas, ministerios, tipos de evento) son **propios de cada sede**; no hay catálogos globales compartidos.
- El rol "administrador del sistema" es el actual `admin`: único con visión total y con acceso al módulo de sedes; el rol de pastor actual deja de ver todo el sistema.
- Los datos que hoy existen pertenecen a **una primera sede** que el administrador nombra al activar la funcionalidad; si hay usuarios con rol pastor actuales, quedan como pastores de esa sede.
- La funcionalidad se entrega con 3 sedes iniciales y está pensada para decenas de sedes; no se limita el número de sedes.
- Se mantiene el mecanismo actual de inicio de sesión; cada usuario ve el nombre de su sede en la interfaz.
- Fuera de alcance: comunicaciones entre sedes, reportes comparativos avanzados, facturación por sede, personalización visual por sede (logos, colores) y dominios o subdominios propios por sede; podrán abordarse en funcionalidades futuras.
- Esta funcionalidad se apoya en los módulos de Personas y Líderes ya implementados (especificación `001-revision-personas-lideres`) y los extiende con el concepto de sede.
