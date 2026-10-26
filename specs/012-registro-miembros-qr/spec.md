# Especificación de Funcionalidad: Autorregistro y actualización de miembros por código QR

**Rama de la funcionalidad**: `012-registro-miembros-qr`

**Creado**: 2026-10-23

**Estado**: Borrador

**Entrada**: Descripción del usuario: "quiero que los miembros se puedan auto registrar con un QR que se les habilite un QR así como lo hicimos en eventos y encuentros, quiero que se habilite uno para miembros, que puedan registrar todos sus datos, si ya existe el miembro debe actualizarlo si tiene algún dato por actualizar. hazme preguntas para mejorar el proceso"

## Aclaraciones

### Sesión 2026-10-23

- Q: ¿Cómo se evita que alguien sobrescriba datos de otra persona al actualizar un miembro existente desde un formulario público? → A: Decisión del responsable: se aplica siempre al instante cuando se encuentra al miembro por celular o cédula. Para que el riesgo sea controlable, cada actualización queda en el historial del miembro con los valores anteriores y el origen «QR de miembros», y nunca se borra un dato guardado por dejar un campo vacío.
- Q: ¿Cómo es el QR de miembros? → A: Uno por sede, permanente, que el administrador o pastor puede cerrar, abrir y regenerar.
- Q: ¿Con qué estado queda un miembro nuevo? → A: Lo elige quien se registra: «Visitante» o «Miembro activo».
- Q: Cuando alguien inactivo o eliminado en la sede se registra por el QR, ¿qué pasa con su ficha? → A: Se reactiva y se actualizan sus datos al instante, igual que un miembro activo; vuelve al estado que tenía antes de inactivarse y queda en el historial.
- Q: ¿Dónde y quién gestiona el QR de miembros, si el módulo Sedes es exclusivo del administrador? → A: El administrador lo gestiona desde una pestaña en el detalle de cada sede; el pastor lo gestiona desde una pantalla propia de su sede, con una opción nueva en el menú lateral solo visible para él.
- Q: ¿Qué límite de intentos aplica al formulario público? → A: 5 envíos por minuto por dispositivo o conexión; al superarlo se pide esperar un minuto.
- Q: ¿Qué datos del miembro muestra el formulario público al encontrarlo por celular o cédula? → A: Todos sus datos completos precargados (nombres, apellidos, fecha de nacimiento, cédula, correo, dirección, barrio y ciudad), decisión del responsable; la exposición de datos personales a quien conozca un celular o cédula se acepta junto con el riesgo de actualización inmediata.
- Q: ¿Qué datos puede llenar la persona? → A: Los básicos del QR de eventos (nombres, apellidos, celular, fecha de nacimiento, cédula opcional) más contacto y ubicación: correo, dirección, barrio y ciudad.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Habilitar y compartir el QR de miembros de la sede (Prioridad: P1)

Un administrador (desde la pestaña «Registro de miembros» del detalle de cada sede) o un pastor (desde la pantalla «Registro de miembros» de su sede, en el menú lateral) encuentra el QR de registro de miembros: puede verlo en pantalla, descargarlo como imagen lista para imprimir o proyectar, copiar el enlace para enviarlo por WhatsApp, cerrarlo, volver a abrirlo y regenerarlo si se filtró.

**Por qué esta prioridad**: sin el QR no hay canal de autorregistro; es la base de todo lo demás.

**Prueba Independiente**: Abrir la sede, ver y descargar el QR, y comprobar que al abrir su enlace se muestra el formulario de miembros de esa sede y no el de otra.

**Escenarios de Aceptación**:

1. **Dado** una sede, **Cuando** el administrador abre la pestaña del QR de miembros en el detalle de la sede, o el pastor abre su pantalla «Registro de miembros», **Entonces** ve un QR y un enlace únicos de esa sede.
2. **Dado** el QR visible, **Cuando** lo descarga, **Entonces** obtiene una imagen con el nombre de la sede y la leyenda «Registro de miembros».
3. **Dado** que el registro está cerrado, **Cuando** alguien escanea el QR, **Entonces** ve el mensaje «registro cerrado» y no puede enviar datos.
4. **Dado** que el administrador regenera el QR, **Cuando** alguien usa el enlace o la imagen anteriores, **Entonces** ve que el enlace ya no es válido, y el nuevo funciona.
5. **Dado** un usuario cuyo rol o sede no alcanza esa sede, **Cuando** intenta ver o gestionar su QR, **Entonces** se le niega el acceso.

---

### Historia de Usuario 2 - Registrarse como miembro nuevo (Prioridad: P1)

Una persona escanea el QR con su celular, sin iniciar sesión. Escribe su celular (y, si quiere, su cédula); si no existe en la sede, el formulario le pide el resto de datos: nombres, apellidos, fecha de nacimiento y, opcionalmente, correo, dirección, barrio y ciudad. También escoge si se registra como «Visitante» o como «Miembro activo». Al enviar queda creada en la sede y ve una confirmación.

**Por qué esta prioridad**: es el flujo principal que ahorra el registro manual a la iglesia.

**Prueba Independiente**: Abrir el enlace sin sesión, registrar un celular nuevo con todos los datos y verificar que aparece en el listado de miembros de la sede con el estado elegido.

**Escenarios de Aceptación**:

1. **Dado** un celular que no existe en la sede, **Cuando** la persona completa el formulario y elige «Visitante», **Entonces** se crea con estado visitante y ve una confirmación.
2. **Dado** el mismo caso eligiendo «Miembro activo», **Cuando** envía, **Entonces** se crea con estado miembro activo.
3. **Dado** datos obligatorios faltantes o inválidos (celular mal formado, nombres vacíos, fecha de nacimiento ausente, correo inválido, estado sin elegir), **Cuando** envía, **Entonces** ve el error junto al campo y no se crea nada.
4. **Dado** que los datos de contacto y ubicación son opcionales, **Cuando** los deja vacíos, **Entonces** el registro se crea igual.

---

### Historia de Usuario 3 - Actualizar los datos de un miembro que ya existe (Prioridad: P1)

Si el celular (o la cédula) digitado ya pertenece a un miembro de la sede, el formulario precarga sus datos actuales para que los revise. La persona corrige o completa lo que quiera y, al enviar, el miembro se actualiza al instante con los datos que cambió. Si no cambió nada, se le informa que sus datos ya estaban al día.

**Por qué esta prioridad**: evita duplicados y mantiene la información al día sin trabajo del administrador; es parte central del pedido.

**Prueba Independiente**: Con un miembro existente, abrir el QR, digitar su celular, cambiar la dirección y el correo, enviar, y comprobar que la ficha muestra los datos nuevos y el historial registra el cambio.

**Escenarios de Aceptación**:

1. **Dado** un celular que pertenece a un miembro de la sede, **Cuando** lo digita, **Entonces** el formulario precarga todos sus datos actuales (incluidos cédula, correo, dirección, barrio y ciudad).
2. **Dado** una cédula existente en la sede, **Cuando** la digita, **Entonces** se precargan sus datos, incluido su celular.
3. **Dado** un miembro precargado, **Cuando** cambia uno o más datos y envía, **Entonces** la ficha se actualiza solo en lo que cambió, no se crea otro miembro y ve una confirmación que lista lo actualizado.
4. **Dado** un miembro precargado, **Cuando** envía sin cambios, **Entonces** no se modifica nada y ve «tus datos ya estaban al día».
5. **Dado** un campo opcional que el miembro tenía guardado, **Cuando** lo deja vacío al enviar, **Entonces** el dato guardado se conserva (nunca se borra desde el formulario público).
6. **Dado** una actualización aplicada, **Cuando** el administrador abre el historial del miembro, **Entonces** ve la entrada «Actualizado por QR de miembros» con fecha y hora, y los valores anteriores y nuevos.
7. **Dado** un miembro existente, **Cuando** se registra por el QR, **Entonces** su estado actual, su líder, sus células y su rol de liderazgo no cambian.

---

### Historia de Usuario 4 - Casos especiales de identificación (Prioridad: P2)

El formulario resuelve sin romper los datos los casos dudosos: cédula y celular de personas distintas, miembro inactivo o eliminado (se reactiva), miembro que solo existe en otra sede, y celular repetido en más de una ficha.

**Por qué esta prioridad**: protege la integridad del módulo de miembros, pero depende de que el flujo básico exista.

**Prueba Independiente**: Probar cada caso con datos preparados y comprobar el mensaje y que no se modifica ninguna ficha ajena.

**Escenarios de Aceptación**:

1. **Dado** una cédula y un celular que pertenecen a miembros distintos, **Cuando** envía, **Entonces** se rechaza con un mensaje claro y no se crea ni modifica nada.
2. **Dado** un miembro inactivo o eliminado de la sede, **Cuando** se registra con su celular, **Entonces** su ficha se reactiva (o se restaura si estaba eliminada), recupera el estado que tenía antes de inactivarse, se actualizan sus datos como en HU3 y ve una confirmación normal.
3. **Dado** una reactivación por QR, **Cuando** el administrador abre el historial del miembro, **Entonces** ve la entrada de reactivación con origen «QR de miembros», además de la de actualización.
4. **Dado** un miembro que solo existe en otra sede, **Cuando** se registra, **Entonces** no se precargan ni se modifican sus datos; se crea un registro nuevo en la sede del QR y la persona ve una confirmación normal.
5. **Dado** un celular que está en más de una ficha de la sede, **Cuando** envía, **Entonces** ve un mensaje para contactar al administrador y no se modifica nada.

---

### Historia de Usuario 5 - Saber quién se registró o actualizó por QR (Prioridad: P2)

El administrador o pastor ve en la sede una lista de lo ocurrido por el QR de miembros: quién se registró como nuevo, a quién se le actualizaron datos, qué cambió y cuándo; y el listado de miembros permite distinguir a los creados por QR.

**Por qué esta prioridad**: da control y permite revertir cambios si alguien actualizó datos que no debía.

**Prueba Independiente**: Registrar una persona nueva y actualizar una existente por QR y comprobar que ambas aparecen en la lista con fecha y detalle.

**Escenarios de Aceptación**:

1. **Dado** registros por QR, **Cuando** el administrador abre la lista, **Entonces** ve nombre, celular, resultado (nuevo, actualizado, reactivado, sin cambios) y fecha y hora.
2. **Dado** un registro de actualización, **Cuando** el administrador lo abre, **Entonces** ve los campos cambiados con valor anterior y valor nuevo.
3. **Dado** un usuario de otra sede, **Cuando** intenta ver la lista, **Entonces** no ve nada ajeno a su alcance.

---

### Casos Límite

- Celular con formato distinto (espacios, guiones, prefijo +57): se normaliza antes de buscar.
- Doble envío del formulario (doble toque): no crea dos miembros ni dos entradas de historial iguales.
- Fecha de nacimiento futura o de edad imposible: se rechaza con mensaje.
- Un mismo celular que intenta registrarse como miembro nuevo cuando ya existe en la sede: se trata como actualización, nunca como duplicado.
- Cédula escrita que no coincide con la que ya tiene el miembro del celular: se trata como conflicto (HU4).
- Cambio de celular de un miembro existente (la persona tiene un celular nuevo): fuera del flujo; el celular es la llave de identificación y se cambia desde el módulo interno.
- Abuso del formulario público (muchos envíos seguidos desde el mismo origen): se aplica el límite de 5 envíos por minuto por dispositivo o conexión y se muestra un mensaje de espera.
- QR cerrado o regenerado mientras la persona llena el formulario: al enviar ve el mensaje correspondiente y no se guarda nada.
- Datos con espacios sobrantes o mayúsculas irregulares: se limpian antes de guardar.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El sistema DEBE ofrecer a cada sede un QR y un enlace permanentes para el registro de miembros, gestionables por el administrador desde el detalle de la sede y por el pastor desde una pantalla propia de su sede, enlazada en el menú lateral solo para su rol; el líder de célula y el rol usuario no tienen acceso.
- **RF-002**: El administrador y el pastor (de su propia sede) DEBEN poder descargar el QR como imagen, copiar el enlace, cerrar el registro, volver a abrirlo y regenerar el QR (el anterior deja de funcionar).
- **RF-003**: El formulario DEBE ser público (sin iniciar sesión) y mostrar el nombre de la sede; con el registro cerrado o el enlace inválido solo muestra un mensaje.
- **RF-004**: El formulario DEBE pedir como llave de identificación el celular y permitir digitar la cédula (tipo y número), opcional, para buscar al miembro dentro de la sede del QR.
- **RF-005**: Para un miembro nuevo, el formulario DEBE exigir nombres, apellidos, celular, fecha de nacimiento y la elección entre «Visitante» y «Miembro activo»; y DEBE permitir, de forma opcional, correo, dirección, barrio y ciudad.
- **RF-006**: Si el celular o la cédula corresponden a un miembro activo de la sede, el sistema DEBE precargar sus datos actuales y, al enviar, actualizar de inmediato únicamente los campos que cambiaron, sin crear un duplicado.
- **RF-007**: El sistema NO DEBE borrar ni vaciar ningún dato guardado a partir de un campo dejado vacío en el formulario público.
- **RF-008**: Al actualizar un miembro existente, el sistema NO DEBE cambiar su estado (salvo la reactivación de RF-012), su líder, sus células, su rol de liderazgo, su sede ni su cédula ya registrada.
- **RF-009**: Cada actualización o creación hecha por este QR DEBE quedar en el historial del miembro con el origen «QR de miembros», fecha y hora, y los valores anteriores y nuevos.
- **RF-010**: Si no hay cambios, el sistema DEBE informar «tus datos ya estaban al día» y no escribir ninguna modificación.
- **RF-011**: El sistema DEBE rechazar con un mensaje claro la combinación de cédula y celular de miembros distintos, y el celular repetido en más de una ficha, sin crear ni modificar nada.
- **RF-012**: Si el celular o la cédula corresponden a un miembro inactivo o eliminado de la sede, el sistema DEBE reactivarlo (restaurarlo si estaba eliminado) devolviéndolo al estado que tenía antes de inactivarse, actualizar sus datos como en RF-006 y dejar la reactivación en su historial con origen «QR de miembros».
- **RF-013**: Un miembro que solo existe en otra sede NO DEBE ser leído, precargado ni modificado; el sistema crea el registro nuevo en la sede del QR.
- **RF-014**: El sistema DEBE normalizar celular y datos de texto antes de buscar y guardar, y validar correo y fecha de nacimiento.
- **RF-015**: El sistema DEBE evitar envíos duplicados por doble toque y limitar el formulario a 5 envíos (y consultas de celular o cédula) por minuto desde un mismo dispositivo o conexión, mostrando un mensaje que pide esperar un minuto al superarlo.
- **RF-016**: El administrador (en la pestaña de la sede) y el pastor (en su pantalla) DEBEN poder ver la lista de registros por QR de su sede (nuevos, actualizados, reactivados, sin cambios) con fecha y detalle de los cambios; ningún usuario ve registros de una sede fuera de su alcance.
- **RF-017**: Los miembros creados por este QR DEBEN poder identificarse como tales en su ficha o en el historial.
- **RF-018**: El formulario público DEBE cumplir el diseño primero para celular (sin desplazamiento horizontal de 320 a 1920 px, controles de al menos 44 × 44 px, letra de al menos 16 px, teclado adecuado por campo) y NO DEBE guardar datos de personas en el teléfono.
- **RF-019**: Todos los textos DEBEN estar en español y usar el término «miembros».

### Entidades Clave *(incluir si la funcionalidad maneja datos)*

- **Sede**: Congregación a la que pertenece el QR; define dónde se crean y buscan los miembros.
- **QR de registro de miembros**: Enlace y código únicos de una sede, con estado abierto o cerrado y posibilidad de regeneración.
- **Miembro**: Persona de la sede; es creado o actualizado desde el formulario. Datos: nombres, apellidos, celular, fecha de nacimiento, cédula (opcional), correo, dirección, barrio, ciudad y estado.
- **Registro por QR**: Rastro de cada envío del formulario: lo que escribió la persona, el resultado (nuevo, actualizado, reactivado, sin cambios) y cuándo ocurrió.
- **Historial del miembro**: Bitácora de cambios del miembro, con el origen «QR de miembros» y los valores anteriores y nuevos.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: Una persona nueva completa su registro en menos de 2 minutos desde que escanea el QR.
- **CE-002**: Un miembro existente revisa y actualiza sus datos en menos de 1 minuto.
- **CE-003**: Cero miembros duplicados por celular o cédula dentro de una misma sede tras los registros por QR.
- **CE-004**: El 100 % de las creaciones y actualizaciones por QR queda en el historial con valores anteriores y nuevos.
- **CE-005**: Cero datos guardados borrados o vaciados por el formulario público.
- **CE-006**: Cero fugas de datos entre sedes: el formulario nunca muestra ni modifica miembros de otra sede.
- **CE-007**: El formulario es utilizable sin desplazamiento horizontal de 320 a 1920 px y en conexión móvil lenta carga su contenido útil en menos de 5 segundos.
- **CE-008**: El 90 % de las personas que escanean el QR logra enviar el formulario en el primer intento.

## Supuestos

- Se reutiliza la forma de trabajo del registro por QR de eventos, encuentros y células (spec 009): formulario público, búsqueda por celular y cédula dentro de la sede del QR, y manejo de casos especiales; la diferencia es que aquí sí se actualizan los datos del miembro existente.
- Por decisión del responsable, el formulario muestra precargados todos los datos del miembro encontrado y la actualización se aplica al instante cuando se encuentra al miembro por celular o cédula; el riesgo (que alguien que conozca un celular vea o cambie datos ajenos) se acepta y se mitiga con historial reversible, sin borrado de datos y límite de 5 envíos por minuto.
- El estado de un miembro nuevo lo elige la persona entre «Visitante» y «Miembro activo»; en miembros existentes el estado no se toca.
- El celular es la llave de identificación y no se cambia desde este formulario.
- La persona no elige su líder ni su célula; la asignación sigue haciéndose internamente (por ejemplo, desde «Sin líder asignado»).
- Revertir una actualización indebida es manual: el administrador usa el historial para corregir la ficha.
- El administrador y el pastor de la sede gestionan el QR; los líderes de célula no.
- La gestión del QR y su lista viven en dos lugares con el mismo contenido: la pestaña «Registro de miembros» del detalle de sede (administrador) y una pantalla propia del pastor con una opción de menú visible solo para él; el pastor nunca ve ni gestiona otra sede.
- Esta funcionalidad usa el vocabulario «Miembros» de la funcionalidad 010; los identificadores de código siguen el estado del repositorio al momento de implementar.
