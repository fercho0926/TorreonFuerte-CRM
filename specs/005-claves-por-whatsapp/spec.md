# Especificación de Funcionalidad: Claves de acceso generadas y entregadas por WhatsApp

**Rama de la funcionalidad**: `005-claves-por-whatsapp`

**Creado**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "con mi arquitectura actual cómo puedo generar una contraseña y enviarla al usuario, no quiero tener que asignarle una contraseña, quiero que se pueda gestionar; no sé si por WhatsApp puedo hacer la generación de contraseña y enviársela por WhatsApp para que ingrese a la herramienta; lo mismo un panel de reset de contraseña: que el usuario en el login diga que no recuerda la contraseña y pueda generarse una nueva y enviarse al WhatsApp"

## Contexto

Hoy, quien crea un usuario debe escribir una contraseña y comunicársela por su cuenta; si la persona la olvida, solo un administrador puede editar su cuenta y escribir otra. Esto obliga a conocer y manejar contraseñas ajenas, y no hay forma de que la propia persona recupere el acceso. El usuario quiere que el sistema **genere** las claves, las **entregue por WhatsApp** al celular de la persona, y que exista un flujo de «No recuerdo mi contraseña» en el inicio de sesión que haga lo mismo.

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿La clave se envía automáticamente por un servicio de WhatsApp o de forma asistida? → A: Asistida: el sistema genera la clave y deja el mensaje de WhatsApp listo; siempre lo envía una persona (administrador o pastor) pulsando un botón. El envío automático queda fuera de alcance por ahora.

## Escenarios de usuario y pruebas *(obligatorio)*

### Historia de usuario 1 - Crear un usuario sin asignarle contraseña (Prioridad: P1)

Quien administra crea la cuenta de una persona indicando nombre, correo, rol, sede y celular de WhatsApp, **sin escribir ninguna contraseña**. El sistema genera una clave temporal y, al guardar, muestra un botón «Enviar por WhatsApp» que abre la conversación con el mensaje ya redactado; quien administra solo lo envía. Quien administra ve la clave temporal únicamente en ese momento y nunca maneja la contraseña definitiva, que la persona elige después.

**Por qué esta prioridad**: Es el objetivo principal: dejar de asignar y comunicar contraseñas a mano.

**Prueba independiente**: Crear un usuario sin contraseña y comprobar que la cuenta existe, que se registró la entrega de la clave temporal al celular indicado y que la persona puede ingresar con ella.

**Escenarios de aceptación**:

1. **Dado** un administrador o pastor en el formulario de nuevo usuario, **Cuando** lo guarda con nombre, correo, rol, sede y celular de WhatsApp válidos (sin contraseña), **Entonces** la cuenta se crea, se genera una clave temporal y se muestra una pantalla con el botón «Enviar por WhatsApp» (abre el chat con el celular indicado y el mensaje listo) y el listado muestra el estado «pendiente de envío».
2. **Dado** un celular con formato inválido o faltante, **Cuando** se intenta guardar, **Entonces** el sistema lo rechaza con un mensaje claro y no crea la cuenta.
3. **Dado** una cuenta recién creada, **Cuando** la persona ingresa con la clave temporal, **Entonces** el sistema le exige elegir su propia contraseña antes de continuar (Historia 4).
4. **Dado** una cuenta cuya clave aún no se ha enviado, **Cuando** se mira el listado, **Entonces** aparece como «pendiente de envío» con la opción de preparar el mensaje de nuevo (esto genera una clave nueva).

---

### Historia de usuario 2 - «No recuerdo mi contraseña» en el inicio de sesión (Prioridad: P1)

En la pantalla de ingreso hay un enlace «¿Olvidaste tu contraseña?». La persona escribe su correo y el sistema registra una **solicitud de nueva clave**; le responde siempre lo mismo, exista o no la cuenta («Si la cuenta existe, avisaremos a quien administra tu sede para que te envíe una nueva clave por WhatsApp»). Quien administra la sede ve la solicitud en un panel de **Solicitudes de clave**, genera la clave y la envía por WhatsApp con un botón.

**Por qué esta prioridad**: Da a la persona un camino propio para pedir ayuda sin conocer a un administrador en particular y sin que nadie tenga que escribir contraseñas.

**Prueba independiente**: Desde el inicio de sesión, pedir la recuperación con el correo de una cuenta existente; comprobar que aparece una solicitud pendiente para su sede, que al atenderla se genera una clave temporal que permite ingresar y que la clave anterior deja de servir.

**Escenarios de aceptación**:

1. **Dado** un visitante en el inicio de sesión, **Cuando** elige «¿Olvidaste tu contraseña?» y escribe el correo de una cuenta activa, **Entonces** ve el mensaje genérico y se crea una solicitud pendiente visible para el administrador del sistema y los pastores de esa sede.
2. **Dado** un correo que no existe o una cuenta de una sede inactiva, **Cuando** se solicita la recuperación, **Entonces** el mensaje en pantalla es idéntico y no se crea ninguna solicitud visible.
3. **Dado** una solicitud pendiente, **Cuando** un pastor o administrador la atiende con «Generar y enviar por WhatsApp», **Entonces** se genera una clave temporal nueva, se invalida la anterior, se abre WhatsApp con el mensaje listo para el celular de la cuenta y la solicitud queda «atendida».
4. **Dado** muchas solicitudes seguidas para la misma cuenta o desde el mismo origen, **Cuando** se supera el límite permitido, **Entonces** el sistema pide esperar y no crea más solicitudes; varias solicitudes de la misma cuenta se muestran como una sola.
5. **Dado** una solicitud sin atender durante más del tiempo definido, **Cuando** pasa ese tiempo, **Entonces** aparece como «vencida» y se puede descartar o atender igualmente.

---

### Historia de usuario 3 - Reenviar una nueva clave desde el panel de usuarios (Prioridad: P2)

Quien administra puede, desde el listado o la ficha de un usuario, elegir «Enviar nueva clave»: el sistema genera otra clave temporal, invalida la anterior y la entrega al WhatsApp del usuario. El pastor solo puede hacerlo con usuarios de su sede; el administrador del sistema, con cualquiera.

**Por qué esta prioridad**: Cubre al usuario que no puede o no quiere usar el flujo de recuperación por sí mismo.

**Prueba independiente**: Con un usuario existente, pulsar «Enviar nueva clave» y comprobar que se invalidó la anterior, que se entregó la nueva y que quedó registro de quién lo hizo.

**Escenarios de aceptación**:

1. **Dado** un pastor viendo a un usuario de su sede, **Cuando** pulsa «Enviar nueva clave» y confirma, **Entonces** se genera y entrega una nueva clave temporal y queda registrado quién y cuándo lo hizo.
2. **Dado** un pastor, **Cuando** intenta hacerlo con un usuario de otra sede, **Entonces** el sistema lo impide.
3. **Dado** un administrador en la edición de un usuario, **Cuando** abre el formulario, **Entonces** ya no ve campos para escribir contraseñas.

---

### Historia de usuario 4 - Cambio obligatorio de la clave temporal (Prioridad: P1)

Quien ingresa con una clave temporal (recién creada o recuperada) debe elegir su propia contraseña antes de usar el sistema. La clave temporal caduca y solo sirve una vez.

**Por qué esta prioridad**: Sin este paso, la clave que viaja por WhatsApp seguiría siendo la contraseña definitiva y quedaría expuesta en el historial del chat.

**Prueba independiente**: Ingresar con una clave temporal y comprobar que solo se puede avanzar eligiendo una contraseña nueva que cumpla las reglas, y que la temporal ya no funciona después.

**Escenarios de aceptación**:

1. **Dado** una persona que ingresó con clave temporal, **Cuando** intenta abrir cualquier pantalla del sistema, **Entonces** se le lleva a «Elige tu contraseña» hasta que la cambie.
2. **Dado** una contraseña nueva que no cumple las reglas (longitud mínima, igual a la temporal), **Cuando** intenta guardarla, **Entonces** se rechaza con un mensaje que explica cómo corregirla.
3. **Dado** una clave temporal con más de su vigencia, **Cuando** se intenta usar, **Entonces** el ingreso se rechaza y se invita a pedir una nueva.
4. **Dado** una persona con sesión iniciada, **Cuando** abre «Mi cuenta», **Entonces** puede cambiar su contraseña por otra conociendo la actual.

---

### Historia de usuario 5 - Seguimiento y auditoría de las claves (Prioridad: P3)

Cada generación de clave deja un registro (quién la pidió o la preparó, para qué cuenta, cuándo y en qué estado está) sin guardar la clave. Como el envío es asistido, el sistema no puede confirmar que el mensaje llegó: quien lo envía lo marca como «enviada», y el estado «pendiente de envío» sigue visible hasta entonces.

**Por qué esta prioridad**: Da trazabilidad y evita claves olvidadas sin enviar, pero la función principal no depende de ello.

**Prueba independiente**: Generar claves en distintos casos y comprobar que el registro muestra el estado correcto, quién actuó y que la clave nunca aparece en él.

**Escenarios de aceptación**:

1. **Dado** una clave preparada, **Cuando** quien administra pulsa «Enviar por WhatsApp» y vuelve al sistema, **Entonces** puede marcarla como «enviada»; si no lo hace, sigue «pendiente de envío».
2. **Dado** cualquier generación de clave, **Cuando** se revisa el registro, **Entonces** aparece quién la solicitó (o «la propia persona»), quién la preparó, la cuenta afectada, la fecha y el estado, y en ningún caso la clave.
3. **Dado** una clave temporal vencida sin usar, **Cuando** pasa su vigencia, **Entonces** el estado cambia a «vencida» y se ofrece generar otra.

---

### Casos límite

- Usuario sin celular de WhatsApp registrado: no se puede preparar el mensaje; se le pide a quien administra completar el celular.
- Número de WhatsApp equivocado o sin WhatsApp: la clave llegaría a otra persona o no llegaría; por eso es temporal, de un solo uso, caduca rápido y obliga a cambiarla. Quien envía ve el número antes de abrir WhatsApp y puede corregirlo.
- Quien administra cierra la pantalla sin enviar la clave: la clave sigue vigente hasta que caduque o se genere otra; el estado «pendiente de envío» permite retomarlo.
- Nadie de la sede disponible para atender solicitudes: las solicitudes quedan pendientes y visibles para el administrador del sistema.
- Dos personas con el mismo celular (por ejemplo, familia): la recuperación se identifica por el correo de la cuenta, no por el celular.
- La persona pide varias claves seguidas: solo la última es válida.
- Usuario administrador del sistema (sin sede): también puede recuperar su clave por el mismo flujo.
- Usuario de una sede inactiva: no puede iniciar sesión ni recibir claves; el mensaje en pantalla no lo revela.
- Idioma y formato del mensaje: español, con el nombre del sistema y la sede, y sin enlaces que puedan confundirse con suplantación.
- Un administrador solo ve la clave temporal en el instante de enviarla; no puede leer una contraseña definitiva en ningún momento ni volver a ver una clave temporal ya enviada.

## Requisitos *(obligatorio)*

### Requisitos funcionales

- **FR-001**: El formulario de crear usuario NO DEBE pedir contraseña; DEBE pedir un celular de WhatsApp, que se valida con el mismo formato que usa el resto del sistema.
- **FR-002**: Al crear un usuario, el sistema DEBE generar una clave temporal aleatoria y mostrar a quien administra, en ese momento, un botón «Enviar por WhatsApp» que abre el chat con el celular de la cuenta y un mensaje ya redactado que incluye la clave.
- **FR-003**: La clave temporal DEBE ser de un solo uso, caducar en un tiempo corto definido (por defecto 24 horas) y NO DEBE guardarse en claro en el sistema; solo se muestra una vez a quien la genera.
- **FR-004**: El inicio de sesión DEBE ofrecer «¿Olvidaste tu contraseña?», que pide el correo de la cuenta y registra una solicitud de nueva clave si la cuenta existe y está habilitada.
- **FR-005**: La respuesta de la recuperación DEBE ser idéntica exista o no la cuenta, esté o no la sede activa, para no revelar qué correos tienen acceso.
- **FR-006**: La recuperación DEBE tener un límite de solicitudes por cuenta y por origen en una ventana de tiempo y, al superarlo, DEBE pedir esperar sin crear más solicitudes; las solicitudes repetidas de una misma cuenta se agrupan en una.
- **FR-007**: Generar una clave nueva DEBE invalidar de inmediato la contraseña o clave temporal anterior de esa cuenta.
- **FR-008**: Quien ingrese con una clave temporal DEBE ser obligado a elegir su propia contraseña antes de acceder a cualquier otra pantalla; la nueva contraseña DEBE cumplir las reglas mínimas (longitud mínima y distinta de la temporal).
- **FR-009**: DEBE existir un panel de «Solicitudes de clave» para administrador y pastor, con las solicitudes pendientes, atendidas y vencidas; el pastor solo ve y atiende las de su sede y el administrador del sistema las de todas.
- **FR-010**: Desde ese panel, y desde el listado y la ficha de un usuario, administrador y pastor DEBEN poder generar una clave nueva con confirmación y abrir WhatsApp con el mensaje listo; el pastor solo para usuarios de su sede.
- **FR-011**: El formulario de editar usuario NO DEBE permitir escribir contraseñas de otras personas, y una persona con sesión iniciada DEBE poder cambiar su propia contraseña desde «Mi cuenta» indicando la actual.
- **FR-012**: El sistema DEBE registrar cada generación de clave (cuenta, quién la solicitó o «la propia persona», quién la preparó, fecha, motivo —alta, recuperación o reenvío— y estado: pendiente de envío, enviada, vencida o usada) sin almacenar la clave.
- **FR-013**: Como el sistema no puede confirmar la entrega, quien envía DEBE poder marcar la clave como «enviada»; el listado de usuarios DEBE mostrar el estado de la última clave.
- **FR-014**: Si el usuario no tiene celular de WhatsApp registrado, el sistema DEBE impedir preparar el mensaje y explicar cómo resolverlo.
- **FR-015**: El mensaje DEBE estar en español, identificar el sistema y la sede, indicar la vigencia de la clave y NO DEBE incluir enlaces de acceso directo con la clave incrustada.
- **FR-016**: El envío automático por un servicio de WhatsApp queda fuera de alcance; la funcionalidad DEBE funcionar sin ninguna cuenta ni servicio externo de pago.
- **FR-017**: Esta funcionalidad NO DEBE cambiar el aislamiento por sede ni los roles existentes: quien no puede ver un usuario no puede generarle claves ni ver sus solicitudes.

### Entidades clave

- **Usuario**: cuenta de acceso; se le añade el celular de WhatsApp y la marca de «debe cambiar su contraseña».
- **Clave temporal**: secreto de un solo uso asociado a una cuenta, con caducidad y estado (pendiente de envío, enviada, usada, vencida, reemplazada); solo se conserva una huella no reversible.
- **Solicitud de clave**: petición hecha desde el inicio de sesión para una cuenta, con estado (pendiente, atendida, vencida, descartada), sede y fecha.
- **Registro de claves**: evento de generación con cuenta, solicitante, quien la preparó, motivo, fecha y estado, sin la clave.

## Criterios de éxito *(obligatorio)*

### Resultados medibles

- **SC-001**: Quien administra crea un usuario y deja listo su acceso sin escribir ninguna contraseña, en menos de 1 minuto por usuario, incluido el paso de enviar el mensaje por WhatsApp.
- **SC-002**: Una solicitud de «olvidé mi contraseña» aparece para quien administra la sede en menos de 1 minuto y se atiende (clave generada y WhatsApp abierto con el mensaje listo) en 3 clics o menos.
- **SC-003**: El 100 % de las claves temporales dejan de ser válidas tras usarse, tras generarse una nueva o tras su vigencia.
- **SC-004**: En el 100 % de los casos, la pantalla de recuperación responde igual para correos existentes y no existentes, y no revela el estado de la cuenta.
- **SC-005**: Cero claves ni contraseñas aparecen en registros, listados, historial o pantallas posteriores; la clave temporal solo se ve una vez, al prepararla.
- **SC-006**: Tras superar el límite de solicitudes, el 100 % de las solicitudes adicionales en la ventana de tiempo se rechazan sin crear nuevas solicitudes.
- **SC-007**: Cualquier clave sin enviar o vencida es visible para quien administra en el listado de usuarios y en el panel de solicitudes en menos de 1 minuto.
- **SC-008**: Ninguna prueba automatizada de comportamiento existente deja de pasar, salvo las que asumían escribir contraseñas en el formulario de usuarios.

## Supuestos

- El celular de WhatsApp se guarda en la cuenta de usuario (se propone el de la persona vinculada, si existe) con el formato de celular que ya usa el sistema; las cuentas de administradores sin persona vinculada también lo requieren.
- La recuperación se identifica por el **correo** de la cuenta (único); no se usa el celular como identificador para evitar confusiones entre familiares.
- Vigencia por defecto de la clave temporal: 24 horas; límite por defecto: 3 solicitudes por hora por cuenta y 10 por hora por origen; una solicitud sin atender pasa a «vencida» a las 48 horas. Ambos son configurables.
- Las contraseñas definitivas deben tener al menos 8 caracteres y no pueden ser iguales a la temporal; no se imponen reglas de complejidad adicionales en esta funcionalidad.
- Las cuentas existentes conservan su contraseña actual; no se obliga a nadie a cambiarla hasta que solicite una recuperación o se le reenvíe una clave.
- El envío es **asistido** (decisión del usuario): el sistema abre WhatsApp con el mensaje listo y una persona lo envía. No se contrata ningún servicio externo. El envío automático (por ejemplo con WhatsApp Business) puede añadirse más adelante sin cambiar los flujos descritos.
- Una persona que olvidó su contraseña depende de que un administrador o pastor de su sede atienda la solicitud; no hay recuperación totalmente automática.
- El envío por correo electrónico como alternativa queda fuera del alcance (el usuario pidió WhatsApp).
- Los mensajes de WhatsApp no son un canal totalmente seguro; por eso la clave es temporal, de un solo uso y de cambio obligatorio. Al ser asistido, quien envía ve la clave temporal una vez.
- No se agrega autenticación en dos pasos ni verificación del número en esta funcionalidad.
