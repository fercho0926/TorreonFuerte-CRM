# Especificación de Funcionalidad: Registro por código QR a eventos, encuentros y células

**Rama de la funcionalidad**: `modulos` (directorio `009-registro-qr-eventos`)

**Creada**: 2026-10-22

**Estado**: Borrador

**Entrada**: Descripción del usuario: "En eventos y encuentros, cada vez que genere uno, poder generar también un código QR para que las personas se registren por sí mismas. Cada persona llena sus datos y el administrador sabe quién se registró. La llave de identificación es el número de celular; el formulario permite digitar la cédula y buscarla en el módulo Personas: si existe, se cargan sus datos y se inscribe al evento o encuentro; si no existe, se crea la persona y se inscribe."

## Aclaraciones

### Sesión 2026-10-22

- Q: ¿Los registros por QR aplican solo a eventos y encuentros, o también a células? → A: También el QR de una célula: la persona queda asignada a esa célula como miembro.
- Q: ¿Qué pasa si la persona ya existe en Personas pero en otra sede? → A: Se inscribe sin precargar sus datos y sin cambiarle la sede; el administrador de la sede del evento solo ve el nombre y el celular que la persona escribió.
- Q: ¿Qué debe ver el administrador cuando una persona de otra sede se inscribe por QR? → A: Aparece en la lista de inscritos con el nombre y celular que escribió, marcada «de otra sede», sin enlace a su ficha.
- Q: ¿Se actualizan los datos de una persona existente con lo que corrija en el formulario? → A: No; solo se inscribe. Si corrigió algún dato, queda como nota para que el administrador decida.
- Q: ¿Qué datos pide el formulario a una persona nueva? → A: Obligatorios: nombres, apellidos, celular y fecha de nacimiento. La cédula (con su tipo) es opcional y sirve para buscar a la persona.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Generar y compartir el QR de un evento o encuentro (Prioridad: P1)

Un administrador (o pastor / líder con permiso sobre el evento o encuentro) abre la ficha de un evento o encuentro y obtiene un código QR propio (lo mismo aplica a las células, desde la ficha de la célula; ver FR-018). Puede verlo en pantalla, descargarlo como imagen y copiar el enlace para proyectarlo, imprimirlo o enviarlo por WhatsApp.

**Por qué esta prioridad**: sin el QR no existe el canal de autoinscripción; es la base de todo lo demás.

**Prueba independiente**: crear un evento, abrir su ficha, ver el QR, descargarlo y comprobar que al abrir su enlace se muestra el formulario de ese evento (y no el de otro).

**Escenarios de Aceptación**:

1. **Dado** un evento o encuentro recién creado, **cuando** el administrador abre su ficha, **entonces** ve un QR y un enlace de registro únicos para ese evento o encuentro.
2. **Dado** el QR visible, **cuando** el administrador lo descarga, **entonces** obtiene una imagen lista para imprimir con el nombre del evento o encuentro.
3. **Dado** que el administrador desactiva el registro, **cuando** alguien escanea el QR, **entonces** ve un mensaje de «registro cerrado» y no puede inscribirse.
4. **Dado** un usuario cuya sede o rol no alcanza el evento, **cuando** intenta ver o gestionar su QR, **entonces** se le niega el acceso.

---

### Historia de Usuario 2 - Registrarse escaneando el QR (persona nueva) (Prioridad: P1)

Una persona escanea el QR con su celular, sin iniciar sesión, e ingresa su número de celular. Como no existe en Personas, el formulario le pide sus datos (nombres, apellidos, fecha de nacimiento y, opcionalmente, cédula). Al enviar, se crea la persona en el módulo Personas, queda inscrita al evento o encuentro y ve una confirmación.

**Por qué esta prioridad**: es el flujo principal que ahorra el registro manual al administrador.

**Prueba independiente**: abrir el enlace del QR en un navegador sin sesión, registrar un celular nuevo con sus datos y verificar que aparece en Personas y en la lista de inscritos.

**Escenarios de Aceptación**:

1. **Dado** un celular que no existe en Personas, **cuando** la persona completa el formulario y lo envía, **entonces** se crea la persona (en la sede del evento), queda inscrita y ve un mensaje de confirmación.
2. **Dado** datos obligatorios faltantes o inválidos (celular mal formado, nombres vacíos, fecha de nacimiento ausente), **cuando** envía, **entonces** ve el error junto al campo y no se crea nada.
3. **Dado** una persona ya inscrita, **cuando** vuelve a registrarse con el mismo celular en el mismo evento o encuentro, **entonces** se le informa que ya está registrada y no se duplica la inscripción.

---

### Historia de Usuario 3 - Registrarse una persona que ya existe (búsqueda por cédula o celular) (Prioridad: P1)

Si el celular ya existe en Personas, o si la persona digita su cédula, el formulario encuentra el registro existente, muestra sus datos precargados (para confirmar o corregir) y la inscribe al evento o encuentro sin crear un duplicado.

**Por qué esta prioridad**: evita duplicados en el módulo Personas, que es la fuente de verdad.

**Prueba independiente**: con una persona ya creada, abrir el QR, digitar su cédula o celular y verificar que los datos se precargan y la inscripción queda asociada a esa misma persona.

**Escenarios de Aceptación**:

1. **Dado** un celular que ya pertenece a una persona, **cuando** lo digita, **entonces** el formulario precarga los datos de esa persona.
2. **Dado** una cédula existente en Personas, **cuando** la digita, **entonces** se precargan sus datos, incluido su celular.
3. **Dado** una cédula que no existe, **cuando** la digita, **entonces** el formulario continúa en modo «persona nueva» sin error.
4. **Dado** que la cédula y el celular digitados pertenecen a personas distintas, **cuando** envía, **entonces** se rechaza con un mensaje claro y no se crea ni modifica nada.
5. **Dado** una persona precargada, **cuando** envía, **entonces** queda inscrita al evento o encuentro, no se crea una persona nueva y sus datos en Personas no cambian (las correcciones quedan como nota para el administrador).

---

### Historia de Usuario 4 - Ver quién se registró por QR (Prioridad: P2)

El administrador ve, en la ficha del evento o encuentro, la lista de inscritos indicando cuáles llegaron por QR y cuándo, y distingue las personas creadas por el QR de las que ya existían.

**Por qué esta prioridad**: cierra el objetivo de «saber quién se registró», pero depende de que el registro funcione.

**Prueba independiente**: registrar dos personas por QR (una nueva y una existente) y comprobar que ambas aparecen en la lista de inscritos marcadas como «registro por QR» con su fecha y hora.

**Escenarios de Aceptación**:

1. **Dado** inscripciones hechas por QR y otras manuales, **cuando** el administrador abre la lista de inscritos, **entonces** distingue el origen de cada una.
2. **Dado** una persona creada por el QR, **cuando** el administrador la busca en Personas, **entonces** la encuentra en la sede del evento.

---

### Casos Límite

- Celular con formato distinto (espacios, guiones, prefijo +57): se normaliza antes de comparar.
- Persona que ya es miembro de la célula: se informa que ya pertenece y no se duplica.
- Dos personas ya existentes con el mismo celular: se rechaza la autoinscripción con un mensaje que indica contactar al administrador.
- Persona existente en otra sede: no se precargan sus datos ni se le cambia la sede; se inscribe con el nombre y el celular que escriba, y la sede del evento solo ve eso.
- Persona inactiva o eliminada (borrado lógico) que se registra: no se crea duplicado; se informa que el administrador debe gestionarla.
- Evento o encuentro cancelado o ya realizado: el registro se cierra.
- Enlace de QR alterado o inexistente: se muestra «enlace no válido» sin revelar información.
- Intentos masivos o automatizados contra el formulario: se limita la frecuencia de intentos por dispositivo.
- Persona menor de edad o sin cédula (TI, CE, pasaporte): se permite elegir el tipo de documento.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **FR-001**: El sistema DEBE permitir obtener, para cada evento y cada encuentro, un código QR y un enlace de registro únicos y no adivinables.
- **FR-002**: El sistema DEBE permitir descargar el QR como imagen imprimible y copiar el enlace.
- **FR-003**: El sistema DEBE permitir activar y desactivar el registro por QR de cada evento, encuentro o célula, y cerrarlo automáticamente cuando esté cancelado o realizado.
- **FR-004**: El formulario público DEBE funcionar sin iniciar sesión y mostrar el nombre, fecha y lugar del evento o encuentro.
- **FR-005**: El sistema DEBE usar el número de celular (normalizado) como identificador principal de la persona en el registro.
- **FR-006**: El formulario DEBE permitir digitar la cédula y buscarla en el módulo Personas; si existe, DEBE precargar los datos de esa persona.
- **FR-007**: Si la persona existe, el sistema DEBE inscribirla sin crear un duplicado; si no existe, DEBE crearla en el módulo Personas (con la sede del evento o encuentro) e inscribirla. El sistema NO DEBE modificar los datos de una persona existente; las correcciones que ella escriba se guardan como nota visible para el administrador.
- **FR-008**: El sistema NO DEBE permitir inscribir dos veces a la misma persona al mismo evento o encuentro.
- **FR-009**: El formulario DEBE pedir como obligatorios nombres, apellidos, celular y fecha de nacimiento, y como opcional la cédula con su tipo de documento (obligatorio el tipo si se escribe la cédula); DEBE validar los datos con mensajes claros junto a cada campo.
- **FR-010**: El sistema DEBE rechazar y explicar los conflictos de identidad (cédula y celular de personas diferentes, celular duplicado en Personas).
- **FR-011**: El sistema DEBE registrar el origen (QR) y la fecha y hora de cada inscripción hecha por este medio, y mostrarlos en la lista de inscritos. Las inscripciones de personas de otra sede DEBEN mostrarse con el nombre y celular escritos, marcadas «de otra sede» y sin enlace a su ficha.
- **FR-012**: La búsqueda por cédula o celular en el formulario público DEBE precargar solo los datos mínimos del registro que coincide, nunca de otra sede, y DEBE limitar la frecuencia de consultas para impedir recorrer la base de personas. Si el celular o la cédula pertenecen a una persona de otra sede, el sistema inscribe sin precargar, sin cambiar la sede de esa persona y sin crear un duplicado de ella.
- **FR-013**: Solo usuarios con alcance sobre la sede y el rol adecuados PUEDEN ver, descargar, activar/desactivar o regenerar el QR (principio II de la constitución).
- **FR-014**: El formulario público DEBE estar diseñado primero para celular (controles de 44 px, letra de 16 px, teclado numérico para celular y cédula) y NO DEBE guardar datos de personas en el teléfono (principio VII).
- **FR-015**: El formulario DEBE seguir funcionando sin JavaScript; la precarga por cédula es una mejora progresiva (principio V).
- **FR-016**: Tras inscribirse, el sistema DEBE mostrar una confirmación clara con el nombre del evento o encuentro.
- **FR-017**: El administrador DEBE poder regenerar el enlace de registro; el enlace anterior deja de funcionar.
- **FR-018**: Cada célula DEBE tener su propio QR y enlace de registro con el mismo comportamiento (FR-001 a FR-017); al registrarse, la persona queda como miembro de esa célula (nueva o existente, sin duplicar) y su estado pasa a «En célula» si era «Visitante».

### Entidades Clave

- **Evento / Encuentro / Célula**: actividad o grupo existente; gana un enlace/QR de registro y un indicador de registro abierto o cerrado.
- **Persona**: registro existente, identificado por celular y cédula; puede crearse desde el QR.
- **Inscripción**: relación persona–evento, persona–encuentro o membresía persona–célula; gana el origen (manual o QR) y la fecha y hora del registro.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **SC-001**: Una persona nueva completa su registro desde el celular en menos de 60 segundos.
- **SC-002**: Una persona ya existente se registra en menos de 30 segundos gracias a la precarga de datos.
- **SC-003**: El 0 % de los registros por QR genera personas duplicadas (misma cédula o mismo celular) en el módulo Personas.
- **SC-004**: El administrador obtiene y descarga el QR de un evento o encuentro en máximo 3 clics desde su ficha.
- **SC-005**: El administrador identifica a todos los inscritos por QR (con fecha y hora) desde una sola pantalla, sin cruzar listas.
- **SC-006**: Ninguna consulta pública del formulario revela datos de personas de otra sede.

## Supuestos

- Las células se incluyen en el alcance (aclaración 2026-10-22); no se genera QR por reunión de célula ni se registra asistencia por QR.
- El registro público no requiere inicio de sesión ni verificación por código (SMS/WhatsApp); la protección es el enlace no adivinable más límites de frecuencia.
- Las personas creadas por QR a un evento o encuentro quedan en estado «Visitante» y sin líder asignado, para que el administrador las gestione después.
- La inscripción por QR no registra pagos; el costo de eventos se gestiona como hasta ahora.
- Se reutilizan los módulos, permisos, alcance por sede y componentes de formulario existentes.
- No se define cupo máximo de inscritos en esta funcionalidad.
