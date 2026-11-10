# Especificación de Funcionalidad: Corregir errores del registro por QR y exigir celular y documento

**Rama de la funcionalidad**: `017-registro-qr-datos-obligatorios`

**Creada**: 2026-11-10

**Estado**: Borrador

**Entrada**: Descripción del usuario: "quiero que revises porque cuando estoy haciendo la creacion del miembros por QR, registro a evntos y demas inscripciones por QR a veces muestra error, quiero que revises los side caases scenarios, tambien hagamos obligatorio el celular y numero de documento, porque son los keys para poder registrar a eventos ver informacion y demas"

## Aclaraciones

### Sesión 2026-11-10

- Q: ¿Dónde debe aplicar la obligatoriedad de celular y número de documento? → A: En todos los formularios de alta de un miembro: tanto en los QR públicos (eventos, encuentros, células y miembros) como en la creación manual desde el panel interno (administrador, pastor o líder con permiso).
- Q: ¿Qué pasa con un miembro que ya existe pero no tiene guardado su celular o su documento, cuando se le encuentra por un QR? → A: Se le exige completar el dato faltante ahí mismo, antes de poder continuar (inscribirse, actualizarse o guardarse), sea que lo encuentre el formulario público de QR o que lo abra el administrador o el pastor para editarlo.
- Q: Cuando dos envíos casi idénticos del mismo celular llegan casi al mismo tiempo (doble toque, reintento por conexión lenta, dos pestañas), ¿el segundo envío debe darse por registrado automáticamente o debe pedirle a la persona que reintente? → A: Se da por registrado automáticamente («ya quedaste registrado»), sin pedirle a la persona que reintente nada.
- Q: ¿El reintento del caso poco frecuente de RF-007 debe contar como un intento normal para los límites de frecuencia ya existentes, o debe quedar exento? → A: Cuenta como un intento normal, igual que cualquier otro envío, sin ninguna excepción.
- Q: ¿Los choques temporales (RF-007) y los casos en que se exige completar un dato faltante a un miembro existente (RF-004) deben quedar visibles para el administrador o el pastor en algún listado? → A: Sí; se agregan como resultados adicionales al listado de registros por QR que ya existe (junto a nueva/actualizada/reactivada/sin cambios/conflicto).

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Registrarse por QR sin errores inesperados (Prioridad: P1)

Una persona escanea cualquier QR de autorregistro (evento, encuentro, célula o miembros de una sede) y completa el formulario. Aunque lo envíe dos veces por un doble toque, su conexión sea lenta y reintente, o varias personas lo envíen al mismo tiempo desde el mismo celular del evento, el sistema siempre responde con una confirmación clara o con un mensaje de negocio entendible (nunca una pantalla de error técnico) y nunca deja una inscripción o un miembro duplicado ni a medio guardar.

**Por qué esta prioridad**: es la queja reportada («a veces muestra error»); sin esto, el canal de autorregistro no es confiable y genera trabajo manual de limpieza.

**Prueba Independiente**: enviar el mismo formulario dos veces seguidas muy rápido (doble toque) y también dos envíos simultáneos con el mismo celular desde dos pestañas, y comprobar que solo queda una inscripción o un miembro, sin pantallas de error técnico.

**Escenarios de Aceptación**:

1. **Dado** un formulario de QR de evento, encuentro, célula o miembros completo y válido, **Cuando** la persona lo envía dos veces seguidas por un doble toque, **Entonces** solo se guarda un registro (una inscripción o una actualización) y la segunda respuesta informa que ya quedó registrada, sin error técnico.
2. **Dado** el mismo celular enviando el mismo formulario casi al mismo instante desde dos pestañas o dos dispositivos, **Cuando** ambos envíos llegan al sistema, **Entonces** el primero se procesa normalmente y el segundo también se da por registrado («ya quedaste registrado»), sin pedirle reintentar nada ni mostrar una pantalla de error técnico.
3. **Dado** un caso poco frecuente en el que el sistema no logra resolver de inmediato si un envío es el mismo registro que otro que se está procesando en ese instante, **Cuando** ocurre, **Entonces** la persona ve un mensaje en español pidiendo reintentar en unos segundos y puede volver a enviar el formulario sin perder lo que escribió ni duplicar su registro si el primero ya había quedado guardado.
4. **Dado** un envío que sí tiene un conflicto de identidad real (cédula y celular de personas distintas, o celular repetido en más de una ficha), **Cuando** lo envía, **Entonces** sigue viendo el mismo mensaje de conflicto claro de hoy, y no se crea ni modifica nada.
5. **Dado** un choque temporal (RF-007) o un caso en el que se le exigió a un miembro completar un dato faltante (RF-004), **Cuando** el administrador o el pastor abre el listado de registros por QR de su sede, **Entonces** ve esos casos identificados con su propio resultado, junto a los demás (nueva, actualizada, reactivada, sin cambios, conflicto).

---

### Historia de Usuario 2 - Dar celular y documento al crear un miembro (Prioridad: P1)

Ya sea por un QR público o por el panel interno (administrador, pastor o líder con permiso), toda persona o miembro nuevo DEBE quedar con su celular y su número de documento (con su tipo) guardados; ya no se puede crear un miembro solo con nombre y correo, ni dejando el documento vacío.

**Por qué esta prioridad**: el celular y el documento son la llave para identificar a cada miembro, inscribirlo a eventos y mostrarle su información; sin ellos, el resto de los flujos por QR fallan o generan duplicados.

**Prueba Independiente**: intentar crear un miembro nuevo (por QR o desde el panel interno) sin celular o sin documento y comprobar que el sistema lo impide con un mensaje claro junto al campo; completarlos y comprobar que se crea correctamente.

**Escenarios de Aceptación**:

1. **Dado** un formulario público de QR (evento, encuentro, célula o miembros) para una persona nueva, **Cuando** falta el celular, el tipo de documento o el número de documento, **Entonces** el envío se rechaza con un mensaje junto a cada campo faltante y no se crea nada.
2. **Dado** el panel interno de creación manual de un miembro, **Cuando** el administrador, el pastor o el líder intenta guardar sin celular o sin documento, **Entonces** el sistema lo impide con el mismo tipo de mensaje claro, y ya no acepta «correo en vez de celular» como antes.
3. **Dado** un formulario completo con celular, tipo y número de documento, **Cuando** se envía (por QR o manualmente), **Entonces** el miembro o la persona queda creado con esos datos guardados.
4. **Dado** una persona que se registra sin tener cédula propia (por ejemplo, un menor con tarjeta de identidad, o un extranjero), **Cuando** elige el tipo de documento correspondiente y escribe su número, **Entonces** el registro se acepta igual.

---

### Historia de Usuario 3 - Completar el dato que falta a un miembro ya existente (Prioridad: P2)

Un miembro o persona que ya existía antes de este cambio puede no tener guardado su celular o su documento. Cuando se le encuentra (por celular o documento en un QR público, o al abrirlo para editarlo desde el panel interno), el sistema le exige completar el dato faltante antes de continuar, sin tocar el resto de su información ni crear un duplicado.

**Por qué esta prioridad**: cierra la brecha de datos históricos sin necesitar una migración masiva; depende de que la obligatoriedad (Historia 2) ya exista.

**Prueba Independiente**: con un miembro de prueba guardado sin documento, buscarlo por su celular en un QR y comprobar que el formulario le pide el documento antes de dejarlo continuar; repetir abriéndolo para editarlo desde el panel interno.

**Escenarios de Aceptación**:

1. **Dado** un miembro existente sin documento guardado, **Cuando** se le encuentra por su celular en un formulario público de QR, **Entonces** sus demás datos se precargan y el formulario le exige completar el documento (tipo y número) antes de poder enviarlo.
2. **Dado** un miembro existente sin celular guardado que se encuentra por su documento, **Cuando** continúa el formulario, **Entonces** se le exige completar su celular antes de poder enviarlo.
3. **Dado** un miembro existente con el dato faltante, **Cuando** el administrador o el pastor lo abre para editarlo desde el panel interno, **Entonces** no puede guardar los cambios hasta completar el celular o el documento que falte.
4. **Dado** que la persona completa el dato que faltaba, **Cuando** envía el formulario, **Entonces** el miembro queda actualizado solo con ese dato nuevo (y los demás cambios que haya hecho), sin crear un miembro duplicado y sin perder su historial.

---

### Casos Límite

- Doble envío del mismo formulario por doble toque en cualquiera de los QR (eventos, encuentros, células o miembros): el segundo envío se resuelve como el mismo registro exitoso del primero («ya quedaste registrado»), sin pedir reintentar nada; nunca produce dos personas, dos inscripciones o dos entradas de historial iguales, ni una pantalla de error técnico.
- Dos personas distintas enviando, casi al mismo tiempo, un formulario con el mismo celular o el mismo documento dentro de la misma sede: una se procesa y la otra recibe un mensaje claro para reintentar o para contactar al administrador, nunca un error técnico ni datos corruptos.
- Conexión lenta que hace que el navegador reintente el envío automáticamente: se trata igual que el doble toque.
- Persona nueva sin cédula propia (menor de edad con tarjeta de identidad, extranjero con cédula de extranjería, o pasaporte): el documento sigue siendo obligatorio, pero con el tipo correspondiente.
- Miembro existente al que le falta el celular o el documento y además tiene un conflicto de identidad (su documento coincide con el de otra ficha): primero se muestra el conflicto; no se le pide completar nada hasta resolverlo.
- Persona que existe solo en otra sede y se registra por un QR de esta sede: se sigue creando un registro nuevo en la sede del QR (sin leer ni modificar el de la otra sede) y ese registro nuevo también exige celular y documento, como cualquier persona nueva.
- Edición interna de un miembro que ya tenía celular y documento completos: no cambia nada; solo se bloquea el guardado cuando falta alguno de los dos.
- Mensaje de error técnico que hoy pueda aparecer por un choque de envíos simultáneos (por ejemplo, un envío que llega mientras otro igual se está guardando): deja de mostrarse tal cual y se reemplaza por el mensaje de reintento de la Historia 1.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El sistema DEBE exigir celular y documento (tipo y número) para crear un miembro o una persona nueva, sin importar si la creación ocurre desde un formulario público de QR (eventos, encuentros, células o miembros de una sede) o desde el panel interno (administrador, pastor o líder con permiso para crear miembros).
- **RF-002**: El tipo de documento DEBE ser obligatorio siempre que el número de documento lo sea, con las mismas opciones ya existentes (cédula, tarjeta de identidad, cédula de extranjería, pasaporte).
- **RF-003**: Al crear un miembro manualmente desde el panel interno, el sistema YA NO DEBE aceptar «celular o correo» como alternativa de contacto; el celular y el documento pasan a ser siempre obligatorios, y el correo sigue siendo opcional.
- **RF-004**: Cuando el sistema encuentra, por celular o por documento, a un miembro o persona que ya existe pero le falta el celular o el documento guardado, DEBE exigir que complete el dato faltante antes de continuar — tanto si lo encuentra un formulario público de QR como si lo abre el administrador o el pastor para editarlo desde el panel interno.
- **RF-005**: Completar el dato faltante de un miembro ya existente (RF-004) NO DEBE crear un miembro duplicado, NO DEBE modificar ningún otro dato que la persona o quien edita no haya cambiado, y DEBE quedar en el historial del miembro igual que las demás actualizaciones por QR o por edición interna.
- **RF-006**: El sistema NO DEBE permitir que un doble envío del mismo formulario (doble toque, reintento por conexión lenta, dos pestañas, o dos envíos idénticos con el mismo celular o el mismo documento llegando al mismo tiempo) cree dos miembros, dos personas o dos inscripciones para el mismo destino (evento, encuentro, célula o sede); el envío repetido DEBE resolverse como el mismo registro exitoso del primero («ya quedaste registrado»), sin pedirle a la persona que reintente.
- **RF-007**: Cuando el sistema no pueda resolver de inmediato si un envío es el mismo registro que otro que se está procesando en ese instante (caso poco frecuente), DEBE mostrar un mensaje claro en español pidiendo reintentar en unos segundos, sin mostrar una pantalla de error técnico, sin dejar datos a medio guardar y sin duplicar el registro si el envío original ya había quedado guardado; este reintento cuenta como un envío más dentro de los límites de frecuencia ya existentes, sin ninguna excepción.
- **RF-008**: El sistema DEBE seguir rechazando, con el mismo mensaje claro de hoy, los conflictos de identidad ya conocidos (cédula y celular de personas distintas, celular o documento repetido en más de una ficha de la sede), sin crear ni modificar nada.
- **RF-009**: Las reglas de identificación, de aislamiento por sede y de los flujos ya definidos en las funcionalidades de registro por QR de eventos/encuentros/células y de miembros se mantienen sin cambios; esta funcionalidad solo cambia que el celular y el documento pasan de opcionales a obligatorios, exige completarlos cuando falten, y corrige los envíos que hoy pueden fallar con un error técnico.
- **RF-010**: Todos los mensajes de validación nuevos o cambiados DEBEN estar en español y mostrarse junto al campo correspondiente.
- **RF-011**: Los campos nuevos u obligatorios DEBEN cumplir el diseño primero para celular (controles de al menos 44 × 44 px, letra de al menos 16 px, teclado adecuado por tipo de dato) ya exigido en los formularios existentes.
- **RF-012**: Los choques temporales resueltos con el mensaje de reintento (RF-007) y los casos en los que se exigió completar un dato faltante a un miembro existente (RF-004) DEBEN quedar identificados en el listado de registros por QR que ya existe (funcionalidades 009 y 012), con un resultado propio junto a los ya definidos (nueva, actualizada, reactivada, sin cambios, conflicto), visible solo para quien tiene alcance sobre esa sede.

### Entidades Clave *(incluir si la funcionalidad maneja datos)*

- **Miembro / Persona**: registro de una persona de la sede; de ahora en adelante, toda alta nueva (por QR o manual) exige celular y documento (tipo y número), y uno ya existente con alguno de esos datos vacío debe completarlo la próxima vez que se le encuentre o se edite.
- **Registro por QR**: rastro de cada envío de un formulario público (evento, encuentro, célula o miembros), con su resultado; no debe duplicarse por reintentos o envíos simultáneos, y ahora también identifica los choques temporales (RF-007) y los casos de dato faltante completado (RF-004) como resultados propios.
- **Conflicto de identidad**: situación ya existente (cédula y celular de personas distintas, o dato repetido en más de una ficha) que se sigue rechazando igual que hoy y no se confunde con un error técnico ni con un simple reintento.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: Cero pantallas de error técnico mostradas a quien se registra por cualquier QR o a quien crea o edita un miembro desde el panel interno, durante un mes de uso normal.
- **CE-002**: Cero miembros, personas o inscripciones duplicados (mismo celular o mismo documento en la misma sede y actividad) generados por dobles envíos o envíos simultáneos.
- **CE-003**: El 100 % de los miembros y personas creados de ahora en adelante, por QR o manualmente, queda con celular y documento guardados.
- **CE-004**: El 100 % de los miembros ya existentes a los que se les encuentra con un dato faltante (por QR o por edición interna) lo completa en ese mismo momento, sin que se le borre o cambie ningún otro dato.
- **CE-005**: Una persona nueva completa su registro por cualquier QR, con los campos ahora obligatorios, en menos de 2 minutos.
- **CE-006**: El administrador o el pastor puede confirmar, desde el listado de registros por QR de su sede, cuántos choques temporales y cuántos casos de dato completado hubo en el último mes, sin cruzar otras listas.

## Supuestos

- Esta funcionalidad reutiliza la infraestructura ya construida en las funcionalidades 009 (registro por QR a eventos, encuentros y células) y 012 (registro de miembros por QR de sede); no cambia su diseño general, solo endurece qué datos son obligatorios, exige completar los que falten en fichas antiguas, y corrige los envíos que hoy pueden terminar en un error técnico o en un duplicado.
- Una persona menor de edad o sin celular propio puede registrarse con el celular de un acudiente o familiar; el sistema no valida de quién es el número.
- No se hace una migración masiva de los miembros que ya existen sin celular o sin documento: su dato faltante se completa la próxima vez que alguien los encuentre por un QR o los edite desde el panel interno, no de forma retroactiva en bloque.
- El correo sigue siendo opcional en todos los casos; deja de aceptarse como sustituto del celular al crear un miembro manualmente.
- El mensaje de «reintenta en unos segundos» (RF-007) solo aplica a choques temporales de envíos; los conflictos de identidad reales conservan su propio mensaje específico (RF-008) y no deben confundirse con un error temporal.
