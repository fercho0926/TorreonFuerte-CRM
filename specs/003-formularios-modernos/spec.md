# Especificación de la funcionalidad: Formularios modernos, organizados e interactivos

**Rama de la funcionalidad**: `003-formularios-modernos`

**Creada**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "no me gusta la forma en que están los formularios, me parecen muy básicos, los quiero más interactivos, más organizados, quiero como un estilo más moderno"

## Diagnóstico del estado actual *(resultado del análisis)*

Se revisaron los formularios de la aplicación (personas, designar líder, células, encuentros, eventos, ministerios, usuarios, sedes, redes, zonas, pagos, reuniones de célula e inicio de sesión). Qué se observa hoy:

1. **Aspecto básico y uniforme:** todos son una columna de campos con etiqueta encima, sin separación visual de temas; un formulario largo como el de persona mezcla datos personales, documento, dirección y liderazgo en una sola caja.
2. **Poca guía al usuario:** los campos no explican qué se espera (ejemplos, formatos), no indican con claridad cuáles son obligatorios y las ayudas aparecen como texto pequeño al final.
3. **Validación tardía:** los errores solo aparecen después de enviar y el usuario pierde de vista dónde estaba; no hay aviso inmediato de un campo mal escrito.
4. **Selectores difíciles:** las listas largas (líderes, personas, redes) son desplegables simples sin búsqueda; elegir una persona entre cientos es lento. Los formularios que asignan varias personas (eventos, encuentros, ministerios) usan casillas o listas largas sin filtro.
5. **Campos que no reaccionan:** campos que dependen de otros (por ejemplo, la red y la zona solo importan si la persona es líder; el documento es obligatorio solo para miembros y líderes) están siempre visibles o se descubren al fallar.
6. **Pocas ayudas de captura:** los celulares, documentos, fechas y montos se escriben a mano sin formato; las fechas usan el selector básico del navegador.
7. **Sin retroalimentación al guardar:** el botón no indica que se está enviando (se pueden hacer dobles envíos), no se avisa si se sale con cambios sin guardar y las confirmaciones usan el cuadro estándar del navegador.
8. **Comportamiento en teléfono mejorable:** los líderes usan el sistema desde el celular, pero los campos no aprovechan teclados adecuados (numérico, teléfono, correo) ni botones de acción cómodos.

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿Qué estilo visual prefieres para los campos de los formularios? → A: Tarjetas por sección con la etiqueta encima de cada campo, campos amplios con ícono a la izquierda y el error debajo del campo (sin etiqueta flotante ni estilo solo de línea inferior).
- Q: En los formularios largos, ¿quieres un índice de secciones o solo desplazarte? → A: Solo desplazamiento: las secciones se leen de arriba abajo, sin índice ni secciones plegables.
- Q: ¿Cómo se presentan en el teléfono los formularios con muchas personas o tablas de datos (asistencia, abonos)? → A: Como lista de tarjetas (nombre, dato de apoyo y un interruptor o botón grande por persona) en teléfono y tabla normal en computador.
- Q: ¿Los formularios deben guardar un borrador automático de lo escrito? → A: No; solo se avisa al intentar salir con cambios sin guardar, y no se guardan datos de personas en el navegador.
- Q: ¿En qué orden se renuevan los formularios? → A: Todos a la vez, en una sola entrega (sin entregas por grupos ni formulario piloto).

## Escenarios de usuario y pruebas *(obligatorio)*

### Historia de usuario 1 - Formularios organizados en secciones claras (Prioridad: P1)

Quien registra una persona, célula, evento u otro dato ve el formulario dividido en secciones con título, descripción breve e ícono (por ejemplo, en persona: "Datos personales", "Contacto", "Ubicación", "Liderazgo"), con los campos obligatorios claramente marcados y un diseño limpio de tarjetas con buen espaciado. Los botones de acción (guardar, cancelar) quedan siempre visibles.

**Por qué esta prioridad**: Es lo que más pide el usuario ("más organizados, estilo moderno") y mejora de inmediato cada formulario sin cambiar el funcionamiento.

**Prueba independiente**: Mostrar el formulario de persona a 3 usuarios y pedirles registrar una persona; deben encontrar cada campo sin ayuda y saber cuáles son obligatorios.

**Escenarios de aceptación**:

1. **Dado** el formulario de persona, **cuando** se abre, **entonces** se muestra en secciones con título e ícono, con los campos obligatorios marcados con un indicador visible y una nota que explica su significado.
2. **Dado** cualquier formulario largo, **cuando** el usuario se desplaza, **entonces** los botones Guardar y Cancelar permanecen accesibles sin tener que volver al final.
3. **Dado** un formulario abierto en un teléfono, **cuando** se visualiza, **entonces** las secciones se apilan en una sola columna sin desplazamiento horizontal y los botones son cómodos de tocar.
4. **Dado** un campo con una regla poco obvia (por ejemplo, el documento), **cuando** el usuario lo ve, **entonces** encuentra una ayuda breve junto al campo que explica cuándo es obligatorio.

---

### Historia de usuario 2 - Validación inmediata y mensajes claros (Prioridad: P1)

Mientras el usuario escribe o al salir de un campo, el sistema le indica enseguida si algo está mal (correo con formato incorrecto, celular incompleto, fecha futura, campo obligatorio vacío) con un mensaje amable junto al campo y un indicador visual de correcto o con error. Al enviar con errores, la pantalla lleva al primer campo con problema y resume los errores.

**Por qué esta prioridad**: Evita pérdida de tiempo y frustración: hoy el usuario descubre los errores al final y debe revisar todo.

**Prueba independiente**: Llenar el formulario de persona con un correo mal escrito y un celular incompleto; el sistema debe marcarlos antes de enviar y, al enviar, llevar al primer error.

**Escenarios de aceptación**:

1. **Dado** un campo de correo, **cuando** el usuario escribe un valor sin formato válido y sale del campo, **entonces** aparece un mensaje claro y el campo se marca con error; al corregirlo, el mensaje desaparece.
2. **Dado** un formulario con varios errores, **cuando** el usuario intenta guardar, **entonces** el sistema no envía, lleva al primer campo con error, lo enfoca y muestra un resumen de los problemas.
3. **Dado** un error que solo el servidor puede detectar (por ejemplo, documento ya registrado), **cuando** el servidor responde, **entonces** el formulario conserva todo lo escrito, marca el campo y lleva al usuario hasta él.
4. **Dado** un campo correcto, **cuando** el usuario lo completa, **entonces** recibe una señal discreta de que está bien.

---

### Historia de usuario 3 - Selectores con búsqueda y selección múltiple cómoda (Prioridad: P1)

Cuando hay que elegir un líder, una persona, una red o varias personas (para un evento, encuentro o ministerio), el usuario puede escribir para filtrar la lista, ve el nombre junto con un dato que ayuda a distinguirlo (documento o celular) y, en selecciones múltiples, ve las personas elegidas como etiquetas que puede quitar con un clic, con un contador de seleccionados.

**Por qué esta prioridad**: Con cientos de personas, los desplegables simples hacen lento y propenso a errores el trabajo diario; es la mayor mejora de usabilidad después de la organización.

**Prueba independiente**: En el formulario de evento, seleccionar 10 personas de una lista de 500 escribiendo parte de sus nombres, y comprobar que quedan como etiquetas y se guardan correctamente.

**Escenarios de aceptación**:

1. **Dado** un selector con más de 10 opciones, **cuando** el usuario escribe parte de un nombre, **entonces** la lista se filtra al instante (sin distinguir mayúsculas ni tildes) y muestra cada opción con su dato de apoyo.
2. **Dado** una selección múltiple, **cuando** el usuario elige personas, **entonces** aparecen como etiquetas con un botón para quitarlas y un contador del total seleccionado.
3. **Dado** una selección múltiple, **cuando** el usuario usa "Seleccionar todos los resultados" o "Limpiar", **entonces** el cambio se aplica solo a las opciones visibles tras el filtro (o a la selección completa, según el botón).
4. **Dado** que el usuario solo tiene permiso sobre ciertas personas, **cuando** abre un selector, **entonces** solo ve las que le corresponden, igual que hoy.

---

### Historia de usuario 4 - Campos que reaccionan y ayudan a escribir (Prioridad: P2)

Los campos se adaptan a lo que el usuario va indicando: la sección de red y zona aparece solo al marcar que la persona es líder; el documento pasa a obligatorio cuando el estado es miembro o líder; el costo individual de un evento aparece solo si se desea uno distinto. Además, los campos ayudan a escribir bien: el celular y el documento se formatean mientras se escriben, las fechas usan un selector de calendario amigable con atajos (hoy, ayer), los montos se muestran con separador de miles y los campos del teléfono abren el teclado adecuado.

**Por qué esta prioridad**: Reduce errores y esfuerzo, pero el formulario ya es usable sin ello una vez cubiertas las historias P1.

**Prueba independiente**: En el formulario de persona, cambiar el estado a "miembro activo" y comprobar que el documento pasa a obligatorio de inmediato; marcar "es líder" y comprobar que aparecen red y zona.

**Escenarios de aceptación**:

1. **Dado** el formulario de persona, **cuando** el usuario marca "es líder", **entonces** la sección de red y zona aparece con una transición suave y la red pasa a obligatoria; al desmarcarlo, se oculta.
2. **Dado** el campo de estado, **cuando** el usuario elige "miembro activo", **entonces** el documento se marca como obligatorio al instante y la ayuda lo explica.
3. **Dado** un campo de celular, **cuando** el usuario escribe dígitos, **entonces** se formatean para facilitar la lectura y en un teléfono se abre el teclado numérico.
4. **Dado** un campo de fecha, **cuando** el usuario lo toca, **entonces** puede elegir desde un calendario y usar atajos como "Hoy"; no se permiten fechas imposibles (por ejemplo, un nacimiento futuro).
5. **Dado** un monto, **cuando** el usuario lo escribe, **entonces** se muestra con separador de miles y símbolo de moneda sin alterar el valor guardado.

---

### Historia de usuario 5 - Retroalimentación al guardar y protección de cambios (Prioridad: P2)

Al guardar, el botón muestra que está trabajando y se bloquea para evitar dobles envíos; si el usuario intenta salir con cambios sin guardar, el sistema le avisa; las confirmaciones de acciones delicadas (inactivar, eliminar, quitar rol, trasladar) usan un cuadro de diálogo propio de la aplicación, claro y con el detalle de lo que ocurrirá, en lugar del aviso básico del navegador. Tras guardar, aparece un mensaje de éxito discreto que desaparece solo.

**Por qué esta prioridad**: Da sensación de calidad y evita errores (duplicados por doble clic, pérdida de datos), pero depende de tener ya el formulario renovado.

**Prueba independiente**: Llenar un formulario, intentar cerrar la página y recibir el aviso; guardar haciendo doble clic rápido y comprobar que se crea un solo registro.

**Escenarios de aceptación**:

1. **Dado** un formulario con cambios sin guardar, **cuando** el usuario intenta navegar a otra página, **entonces** el sistema le pregunta si desea salir sin guardar; si no hay cambios, no pregunta.
2. **Dado** el botón Guardar, **cuando** el usuario lo pulsa, **entonces** muestra un indicador de progreso, se deshabilita y no permite un segundo envío hasta recibir respuesta.
3. **Dado** una acción delicada (por ejemplo, quitar el rol de líder), **cuando** el usuario la pide, **entonces** aparece un cuadro de confirmación con el texto de lo que sucederá y botones claros de continuar o cancelar.
4. **Dado** un guardado exitoso, **cuando** termina, **entonces** se muestra un mensaje de éxito que desaparece automáticamente en pocos segundos.

---

### Historia de usuario 6 - Mismo estilo moderno en todos los formularios (Prioridad: P3)

Todos los formularios de la aplicación (incluido el inicio de sesión, las reuniones de célula, los pagos de eventos y los catálogos) comparten el mismo estilo visual y los mismos comportamientos, de modo que lo que el usuario aprende en uno le sirve en todos, y los formularios nuevos que se creen en el futuro hereden ese estilo sin esfuerzo.

**Por qué esta prioridad**: Da coherencia, pero se puede entregar después de renovar los formularios más usados.

**Prueba independiente**: Abrir todos los formularios de la lista de la sección de alcance y comprobar visualmente que comparten tarjetas, tipografía, colores, ayudas y botones.

**Escenarios de aceptación**:

1. **Dado** cualquier formulario de la aplicación, **cuando** se abre, **entonces** usa el mismo estilo de tarjetas, etiquetas, campos, mensajes de error y botones que los demás.
2. **Dado** un formulario nuevo creado en el futuro, **cuando** se construye con los componentes compartidos, **entonces** obtiene automáticamente el estilo y comportamientos modernos sin código visual adicional.
3. **Dado** el nombre de la sede en la marca de agua, **cuando** se muestra un formulario, **entonces** no interfiere con la lectura ni con los campos.

---

### Casos límite

- ¿Qué pasa si el usuario tiene el navegador con JavaScript deshabilitado o falla la carga de los recursos de estilo? El formulario debe seguir pudiéndose llenar y enviar con su validación en el servidor (la interactividad es una mejora, no un requisito para funcionar).
- ¿Qué pasa con listas muy grandes (más de 1.000 personas) en los selectores? Deben seguir respondiendo con fluidez, mostrando resultados parciales mientras se escribe.
- ¿Qué ocurre si el servidor rechaza el envío? El usuario no pierde nada de lo escrito, ve los errores junto a cada campo y el botón vuelve a habilitarse.
- ¿Qué pasa si el usuario usa solo el teclado (sin ratón) o un lector de pantalla? Todos los campos, selectores y diálogos deben poder manejarse con teclado, tener etiquetas asociadas y anunciar los errores.
- ¿Qué ocurre con pantallas muy pequeñas (teléfonos de 360 px) y muy grandes? El diseño se adapta sin cortar campos ni requerir desplazamiento horizontal.
- ¿Qué pasa con formularios que incluyen tablas de datos (asistencia de una reunión, abonos de un evento)? En teléfono se muestran como lista de tarjetas con un interruptor o botón grande por persona; en computador, como tabla.
- ¿Qué sucede si el usuario pega un valor con espacios o símbolos en un campo con formato (celular, documento)? Se limpia y formatea sin perder información.
- ¿Cómo se ven los formularios con el modo de nombre de sede (marca de agua) y el modo "Todas las sedes: solo consulta"? Deben seguir siendo legibles; en el modo de solo consulta se indica claramente que no se puede guardar.

## Requisitos *(obligatorio)*

### Requisitos funcionales

**Organización y estilo**

- **FR-001**: El sistema DEBE presentar cada formulario dividido en secciones (tarjetas) con título, descripción breve e ícono, con espaciado generoso y jerarquía visual clara; dentro de ellas, cada campo lleva su etiqueta encima, es amplio, lleva un ícono a la izquierda que indica su tipo y muestra su mensaje de error debajo.
- **FR-002**: El sistema DEBE marcar visiblemente los campos obligatorios e incluir una leyenda que explique la marca.
- **FR-003**: El sistema DEBE mantener visibles las acciones principales del formulario (guardar, cancelar) mientras el usuario se desplaza por formularios largos; las secciones se recorren solo desplazándose (sin índice de navegación ni secciones plegables).
- **FR-004**: El sistema DEBE adaptar los formularios a teléfono, tableta y computador, sin desplazamiento horizontal, con controles de tamaño cómodo para tocar; los formularios que contienen tablas de datos (asistencia de una reunión o encuentro, abonos de un evento) DEBEN mostrarse en teléfono como una lista de tarjetas (nombre, dato de apoyo y un interruptor o botón grande por persona) y como tabla en computador, con la misma información.
- **FR-005**: El sistema DEBE ofrecer ayudas breves junto a los campos con reglas no obvias y ejemplos de formato (placeholders) en campos de captura libre.

**Validación y retroalimentación**

- **FR-006**: El sistema DEBE validar los campos en el momento en que el usuario sale de ellos y mostrar un mensaje claro junto al campo y un indicador visual de correcto o con error.
- **FR-007**: Al intentar enviar con errores, el sistema DEBE detener el envío, llevar al primer campo con error, enfocarlo y mostrar un resumen de los problemas.
- **FR-008**: Cuando el servidor rechace un envío, el sistema DEBE conservar todo lo escrito, marcar los campos con error y llevar al usuario al primero.
- **FR-009**: Al guardar, el botón DEBE mostrar un indicador de progreso y deshabilitarse hasta recibir respuesta para evitar envíos duplicados.
- **FR-010**: El sistema DEBE avisar al usuario si intenta abandonar un formulario con cambios sin guardar, y no avisar si no hay cambios; NO DEBE guardar borradores ni datos de personas en el navegador del usuario.
- **FR-011**: El sistema DEBE mostrar mensajes de éxito discretos que desaparezcan solos, y diálogos de confirmación propios de la aplicación (con el detalle de las consecuencias) para acciones delicadas como inactivar, eliminar, quitar un rol o trasladar.

**Interactividad y captura**

- **FR-012**: Los selectores de una sola opción con más de 10 elementos DEBEN permitir buscar escribiendo, sin distinguir mayúsculas ni tildes, y mostrar un dato de apoyo (por ejemplo, documento o celular) junto al nombre.
- **FR-013**: Las selecciones múltiples (personas de un evento, encuentro o ministerio) DEBEN mostrar lo elegido como etiquetas removibles con un contador, y permitir seleccionar o limpiar los resultados filtrados.
- **FR-014**: Los campos dependientes DEBEN mostrarse, ocultarse u hacerse obligatorios al instante según otros campos (por ejemplo, red y zona al marcar "es líder"; documento obligatorio al elegir estado miembro activo), con transición suave.
- **FR-015**: Los campos de celular, documento, fecha y monto DEBEN ayudar a escribir: formato mientras se escribe, teclado adecuado en teléfono, selector de calendario con atajos (hoy, ayer) y montos con separador de miles, sin cambiar el valor que se guarda.
- **FR-016**: Las fechas DEBEN rechazar valores imposibles para su contexto (por ejemplo, nacimiento en el futuro) desde la propia captura.

**Consistencia, accesibilidad y robustez**

- **FR-017**: Todos los formularios de la aplicación (personas, designar líder, quitar rol de líder, células, reuniones y asistencia, encuentros, eventos, inscripciones y pagos, ministerios, usuarios, sedes y cambio de pastores, traslado de personas, redes, zonas, filtros de listados e inicio de sesión) DEBEN compartir el mismo estilo y comportamientos y entregarse renovados en una sola entrega, de modo que ninguno quede con el estilo anterior.
- **FR-018**: Los formularios nuevos DEBEN poder construirse reutilizando componentes compartidos, de modo que hereden estilo y comportamientos sin trabajo visual adicional.
- **FR-019**: Todos los campos, selectores y diálogos DEBEN poder usarse solo con teclado, tener etiquetas asociadas y anunciar los errores a tecnologías de apoyo; el contraste de texto y mensajes DEBE ser legible.
- **FR-020**: Los formularios DEBEN seguir pudiéndose completar y enviar, con validación en el servidor, aunque la interactividad no esté disponible.
- **FR-021**: La renovación NO DEBE cambiar las reglas de negocio, los permisos ni los datos que se guardan; los formularios deben seguir aceptando los mismos campos y aplicando las mismas validaciones del servidor.
- **FR-022**: Los formularios DEBEN convivir con el nombre de la sede (marca de agua y menú) sin que interfiera con la lectura, y en el modo "Todas las sedes: solo consulta" DEBEN indicar claramente que no se puede guardar.

### Entidades clave *(incluir si la funcionalidad involucra datos)*

No se agregan ni modifican datos de negocio: la funcionalidad cambia la forma de capturarlos. Conceptos de interfaz involucrados:

- **Sección de formulario**: Agrupación temática de campos con título, descripción e ícono.
- **Campo**: Elemento de captura con etiqueta, ayuda, formato, estado de validación (neutro, correcto, error) y marca de obligatorio.
- **Selector con búsqueda**: Campo que filtra opciones escribiendo, para una opción o varias (etiquetas removibles).
- **Confirmación**: Diálogo propio de la aplicación que describe las consecuencias de una acción delicada.

## Criterios de éxito *(obligatorio)*

### Medidas verificables

- **SC-001**: El 90% de los usuarios de prueba completa el registro de una persona nueva sin ayuda en menos de 90 segundos con el formulario renovado (frente a la medición del formulario actual).
- **SC-002**: Al menos el 80% de 10 usuarios de prueba califica los formularios renovados como "modernos" y "organizados" (4 o 5 en una escala de 1 a 5).
- **SC-003**: El número de envíos rechazados por errores de validación de formato (correo, celular, fecha) disminuye al menos un 50%, porque el usuario los corrige antes de enviar.
- **SC-004**: Elegir una persona entre 500 opciones toma menos de 10 segundos, y elegir 10 personas para un evento, menos de 60 segundos.
- **SC-005**: Cero registros duplicados por doble clic en Guardar en las pruebas de aceptación.
- **SC-006**: El 100% de los formularios listados en FR-017 usa el estilo y los comportamientos compartidos (verificado por revisión de cada uno).
- **SC-007**: Todos los formularios se pueden completar en un teléfono de 360 px de ancho sin desplazamiento horizontal ni acercar la pantalla.
- **SC-008**: Todos los formularios se pueden completar usando solo el teclado, y las pruebas de accesibilidad básicas (etiquetas, contraste de texto, anuncio de errores) no reportan fallos críticos.
- **SC-009**: Las pruebas automáticas existentes de reglas de negocio y permisos siguen pasando sin cambios de comportamiento.

## Supuestos

- La renovación es **visual y de interacción**: no cambia reglas de negocio, permisos, datos guardados ni el flujo entre pantallas; los formularios siguen enviando los mismos campos.
- El alcance incluye **todos** los formularios de la aplicación existente (lista en FR-017), que se renuevan **a la vez en una sola entrega**; los listados y tablas no se rediseñan salvo sus filtros.
- El estilo "moderno" se interpreta como: tarjetas por sección, etiqueta encima de cada campo, campos amplios con ícono a la izquierda, mensajes de error debajo del campo, estados de validación visibles, transiciones suaves y buen uso del espacio en pantallas pequeñas. Se mantiene la paleta general del panel de administración actual (se descartan la etiqueta flotante y el estilo de solo línea inferior).
- Se usa una estructura por **secciones en una sola página** (no un asistente de pasos) porque los formularios de este CRM tienen pocos campos y los usuarios suelen corregir cualquier sección en cualquier momento; si más adelante se desea un asistente por pasos para formularios muy largos, se evaluará aparte.
- Los usuarios principales acceden desde navegadores actuales en computador y, sobre todo, teléfono; no se da soporte a navegadores muy antiguos.
- La interactividad es una mejora progresiva: la validación del servidor sigue siendo la fuente de verdad.
- Se reutiliza el idioma español y el sistema de permisos y sedes actuales.
- Fuera de alcance: rediseño de listados, tablero y menú, modo oscuro, personalización por sede, asistentes de varios pasos, índice de secciones, secciones plegables y borradores automáticos.
