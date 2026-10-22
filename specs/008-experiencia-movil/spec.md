# Especificación de Funcionalidad: Aplicación funcional y 100 % adaptada a celular

**Rama de la funcionalidad**: `008-experiencia-movil`

**Creado**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "realiza un análisis completo de mi aplicativo que sea funcional y 100% responsive, la mayoría del tiempo se usará desde celular"

## Contexto

La aplicación se usará **principalmente desde el celular** (líderes y pastores en campo, en reunión o de camino). Se hizo un análisis completo de las 38 pantallas navegables (ver [analisis.md](analisis.md)). Resultado resumido:

- **Lo que ya funciona**: ninguna pantalla se desborda de lado a 360 px, los formularios tienen acciones fijas y botones anchos en teléfono, los listados ya son grillas estándar y la lista de asistencia ya se ve como tarjetas.
- **Lo que falta para «100 % celular»**: controles táctiles demasiado pequeños en la barra superior y en los listados, campos con letra pequeña que en iPhone provocan acercamientos involuntarios, un tablero que abre con una tabla ancha, fichas con tablas que se desplazan de lado, grillas que en teléfono ocultan casi todo detrás de un signo «+», un formulario de búsqueda que ocupa media pantalla, una aplicación pesada para datos móviles y ningún acceso tipo «aplicación» en la pantalla de inicio.

Esta funcionalidad cierra esos hallazgos (H1 a H13 del análisis) y fija como regla que **toda pantalla se diseña primero para celular**.

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿Debe funcionar sin conexión (consultar datos ya vistos) o basta con que sea instalable y avise cuando no hay conexión? → A: Instalable en la pantalla de inicio con mensaje claro de «sin conexión»; no se guardan datos de personas en el teléfono.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Manejar la aplicación con el pulgar (Prioridad: P1)

Como líder o pastor con un celular de pantalla pequeña, quiero que el menú, el selector de sede, los botones, los campos y los enlaces sean fáciles de tocar y no hagan «saltar» la pantalla, para usar la aplicación con una mano y sin errores de pulsación.

**Por qué esta prioridad**: afecta a las 38 pantallas y a todos los usuarios; es la base de una experiencia móvil.

**Prueba Independiente**: abrir cada pantalla en un ancho de 360 px y comprobar que todo control tocable mide al menos 44 px de lado (o está separado de otro por el mismo espacio) y que ningún campo de texto tiene letra menor de 16 px.

**Escenarios de Aceptación**:

1. **Dado** cualquier pantalla en un teléfono, **cuando** toco el botón del menú, el selector de sede o el enlace de la marca, **entonces** aciertan a la primera porque miden al menos 44 px.
2. **Dado** un iPhone, **cuando** toco un campo de texto, un selector o un buscador, **entonces** la pantalla no se acerca ni se desplaza hacia los lados.
3. **Dado** un teléfono, **cuando** abro el menú lateral, **entonces** se abre sobre el contenido, se cierra al elegir una opción o tocar fuera, y no queda contenido tapado.
4. **Dado** un administrador, **cuando** veo la barra superior, **entonces** el selector de sede muestra un nombre legible y el aviso «solo consulta» no comprime ni desborda la barra.
5. **Dado** cualquier pantalla a 360 px, **cuando** la recorro, **entonces** no hay desplazamiento horizontal de la página.

---

### Historia de Usuario 2 - Listados cómodos en celular (Prioridad: P1)

Como líder, quiero buscar una persona, ver lo importante de cada fila sin abrir nada y llamarla o escribirle por WhatsApp con un toque, sin que la búsqueda y los filtros me quiten media pantalla.

**Por qué esta prioridad**: es la tarea más repetida del día; hoy en teléfono cada fila muestra solo el nombre y las acciones.

**Prueba Independiente**: en un teléfono, abrir personas, líderes, células y sin líder; ver al menos nombre, estado y un dato clave por fila, usar la búsqueda, filtrar y llamar, todo con la primera fila visible sin desplazarse.

**Escenarios de Aceptación**:

1. **Dado** un listado en un teléfono, **cuando** lo abro, **entonces** veo cada registro como una fila compacta con su nombre, su estado y su dato clave (por ejemplo celular o líder), y la primera fila queda a la vista sin tener que bajar.
2. **Dado** un listado en un teléfono, **cuando** quiero filtrar por columna, **entonces** un botón «Filtros» abre un panel con todos los filtros, y la cantidad de filtros activos se ve en el botón.
3. **Dado** un listado, **cuando** escribo en la búsqueda, **entonces** el resultado se actualiza al escribir y el campo, el interruptor «Incluir inactivos» y «Limpiar» ocupan una sola franja.
4. **Dado** una fila de persona con celular, **cuando** toco «Llamar» o «WhatsApp», **entonces** se abre la llamada o el chat; ambos botones miden al menos 44 px.
5. **Dado** un listado largo, **cuando** llego al final, **entonces** el paginador, las filas por página y la cantidad total se tocan con facilidad y no se desbordan.
6. **Dado** que la acción de asignar líder está en la fila (sin líder), **cuando** la uso en teléfono, **entonces** el selector y el botón caben en el ancho sin desplazarse.

---

### Historia de Usuario 3 - Formularios que ayudan a escribir en celular (Prioridad: P1)

Como usuario que registra personas desde el celular, quiero que cada campo abra el teclado adecuado y que el botón de guardar siempre esté al alcance, para registrar rápido y sin errores.

**Por qué esta prioridad**: registrar y editar personas, células y eventos es el segundo flujo más frecuente.

**Prueba Independiente**: abrir crear persona, crear usuario, cambiar clave y recuperar clave en un teléfono y comprobar teclado, etiquetas, errores y botón de guardar.

**Escenarios de Aceptación**:

1. **Dado** un campo de celular, correo, documento, fecha o costo, **cuando** lo toco, **entonces** el teléfono abre el teclado numérico, de correo o de fecha que corresponde.
2. **Dado** un formulario largo, **cuando** hago scroll, **entonces** el botón de guardar permanece visible al pie sin tapar el último campo.
3. **Dado** un error de validación, **cuando** envío el formulario, **entonces** el teléfono me lleva al primer campo con error y el mensaje se lee completo sin acercar la pantalla.
4. **Dado** un selector con muchas opciones (líder, persona), **cuando** lo abro, **entonces** el buscador interno cabe en la pantalla y no queda tapado por el teclado.
5. **Dado** que se pierde la señal al enviar, **cuando** falla el envío, **entonces** veo un aviso claro y mis datos escritos no se pierden.

---

### Historia de Usuario 4 - Tablero y fichas en formato de lista (Prioridad: P2)

Como pastor, quiero abrir la aplicación y ver primero lo que más uso (accesos rápidos y totales) y, al abrir una persona, líder, célula, evento o sede, leer su información como lista vertical y no como tablas anchas.

**Por qué esta prioridad**: son pantallas de consulta frecuentes, pero menos que los listados y formularios.

**Prueba Independiente**: abrir el tablero y una ficha de cada tipo en un teléfono sin desplazarse de lado.

**Escenarios de Aceptación**:

1. **Dado** el tablero en un teléfono, **cuando** lo abro, **entonces** los accesos rápidos aparecen primero, seguidos de los totales, y el resumen por sede se muestra como una lista de tarjetas (una por sede) en lugar de una tabla ancha.
2. **Dado** una ficha con historial, traslados, inscritos, abonos o personas a cargo, **cuando** la veo en un teléfono, **entonces** cada registro se ve como una tarjeta con sus datos en líneas, sin desplazamiento lateral.
3. **Dado** una tabla de datos en cualquier pantalla, **cuando** el ancho es menor a 768 px, **entonces** nunca queda sin un formato adaptado.
4. **Dado** una persona con celular en su ficha, **cuando** la abro, **entonces** «Llamar» y «WhatsApp» están arriba y a la vista.

---

### Historia de Usuario 5 - Rápida y como una aplicación instalada (Prioridad: P2)

Como usuario con datos móviles limitados, quiero que la aplicación cargue rápido y poder dejarla como un icono en la pantalla de inicio de mi celular, para abrirla en un toque.

**Por qué esta prioridad**: mejora el uso diario, pero las pantallas ya funcionan sin ello.

**Prueba Independiente**: medir la primera carga en una conexión móvil lenta y agregar la aplicación a la pantalla de inicio.

**Escenarios de Aceptación**:

1. **Dado** una conexión móvil lenta, **cuando** abro cualquier pantalla por primera vez, **entonces** el contenido útil aparece en menos de 4 segundos y las visitas siguientes en menos de 2.
2. **Dado** un teléfono, **cuando** elijo «Agregar a pantalla de inicio», **entonces** aparece un icono con el nombre corto del sistema y al abrirlo se ve sin la barra de direcciones del navegador y con el color de la marca.
3. **Dado** que la conexión se corta, **cuando** intento abrir una pantalla, **entonces** veo un mensaje claro de que no hay conexión y no una página de error del navegador.
4. **Dado** que estoy sin conexión, **cuando** reviso lo que el teléfono tiene guardado, **entonces** no hay datos de personas, líderes, células ni usuarios guardados para consulta sin conexión.
4. **Dado** una pantalla que no usa ciertas funciones (por ejemplo, una ficha sin selectores), **cuando** carga, **entonces** no descarga lo que no necesita.

---

### Historia de Usuario 6 - Que no se pierda en el futuro (Prioridad: P3)

Como responsable del producto, quiero que toda pantalla nueva nazca pensada para celular y que una prueba automática avise si alguien introduce un desborde o un control demasiado pequeño.

**Por qué esta prioridad**: protege la inversión de las historias anteriores.

**Prueba Independiente**: introducir a propósito una tabla ancha o un botón de 20 px y comprobar que la prueba falla.

**Escenarios de Aceptación**:

1. **Dado** el conjunto de pantallas navegables, **cuando** se corren las pruebas, **entonces** una prueba recorre todas a 360 px y falla si hay desplazamiento horizontal de la página.
2. **Dado** esa misma prueba, **cuando** hay un control tocable menor a 44 px o un campo con letra menor a 16 px fuera de una lista de excepciones justificadas, **entonces** falla señalando la pantalla y el elemento.
3. **Dado** la constitución del proyecto, **cuando** se consulta, **entonces** incluye la regla «diseño primero para celular».

---

### Casos Límite

- Teléfonos muy pequeños (320 px) y muy grandes (430 px), en vertical y horizontal.
- Teclado en pantalla abierto: no debe tapar el campo activo ni el botón de guardar.
- Texto largo (nombres compuestos, correos, nombres de sede largos): se recorta con puntos suspensivos o se ajusta sin desbordar.
- Notificaciones de barra superior o muesca del teléfono: el contenido no queda debajo.
- Conexión lenta o intermitente al paginar, filtrar o exportar: indicador de carga y aviso de error con reintento.
- Cambio de orientación con un formulario a medias: no se pierden los datos.
- Uso en computador de escritorio: no debe empeorar; los listados y tablas conservan su presentación ancha.
- Usuario con tamaño de letra del sistema aumentado: no se rompen los botones ni las filas.
- Descargas (CSV) desde el celular: se guardan y se pueden abrir desde el teléfono.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: Toda pantalla DEBE verse y funcionar sin desplazamiento horizontal de la página entre 320 y 1920 px de ancho.
- **RF-002**: Todo control tocable (botones, enlaces de acción, interruptores, casillas, paginadores, opciones del menú) DEBE medir al menos 44 × 44 px o estar separado del contiguo por ese mismo espacio en teléfono.
- **RF-003**: Todo campo de texto, selector y buscador DEBE usar una letra de al menos 16 px en teléfono para evitar acercamientos automáticos.
- **RF-004**: La barra superior DEBE mostrar, en teléfono, el botón del menú, la sede activa legible y el menú del usuario sin comprimirse ni desbordar, también para el administrador con el aviso «solo consulta».
- **RF-005**: Los listados DEBEN presentarse en teléfono como filas compactas con nombre, estado y un dato clave visibles sin pulsar, y con la primera fila a la vista sin desplazarse.
- **RF-006**: Los listados DEBEN reunir sus filtros por columna en un panel «Filtros» en teléfono, indicando cuántos filtros hay activos, y DEBEN mantener la búsqueda en una sola franja de alto reducido.
- **RF-007**: Las acciones de contacto (Llamar, WhatsApp) DEBEN estar disponibles con un toque en los listados y en la ficha de la persona y cumplir RF-002.
- **RF-008**: Los formularios DEBEN pedir el teclado adecuado por tipo de dato (numérico para celular, documento y valores; correo para correos; fecha para fechas), mantener el botón de guardar visible y conducir al primer error.
- **RF-009**: Los formularios DEBEN conservar lo escrito si falla el envío o hay pérdida de conexión y avisar con un mensaje claro.
- **RF-010**: El tablero DEBE mostrar primero los accesos rápidos y luego los totales, y el resumen por sede DEBE verse como tarjetas en teléfono.
- **RF-011**: Las fichas (persona, líder, célula, evento, sede, usuario) DEBEN mostrar sus tablas como listas de tarjetas en teléfono, sin desplazamiento lateral; ninguna tabla puede quedar sin formato adaptado.
- **RF-012**: La aplicación DEBE poder agregarse a la pantalla de inicio con nombre corto, icono y color de marca, y abrirse sin la barra de direcciones del navegador.
- **RF-013**: Sin conexión, la aplicación DEBE mostrar un mensaje claro de falta de conexión en vez de la página de error del navegador.
- **RF-013b**: La aplicación NO DEBE guardar en el teléfono datos de personas, líderes, células, eventos ni usuarios para uso sin conexión; solo se guardan los archivos de la propia interfaz y la pantalla de «sin conexión».
- **RF-014**: La primera carga de cualquier pantalla DEBE mostrar contenido útil en menos de 4 s en conexión móvil lenta y en menos de 2 s en las visitas siguientes; cada pantalla DEBE cargar solo lo que necesita.
- **RF-015**: El menú lateral DEBE abrirse sobre el contenido en teléfono y cerrarse al elegir una opción o tocar fuera.
- **RF-016**: En escritorio, los listados y las tablas DEBEN conservar su presentación ancha actual (sin regresiones).
- **RF-017**: Todas las pruebas funcionales existentes DEBEN seguir pasando tras los cambios (sin pérdida de funcionalidad).
- **RF-018**: Una prueba automática DEBE recorrer todas las pantallas navegables a 360 px y fallar ante desplazamiento horizontal, controles tocables menores a 44 px o campos con letra menor a 16 px, con una lista de excepciones vacía por defecto.
- **RF-019**: La constitución del proyecto DEBE incluir la regla «diseño primero para celular».

### Entidades Clave *(incluir si la funcionalidad involucra datos)*

No se agregan ni cambian datos persistentes. Los conceptos son de presentación:

- **Pantalla navegable**: cada ruta de consulta o formulario que ve un usuario (38 en el análisis).
- **Regla táctil**: tamaño mínimo de control (44 px) y de letra (16 px) en teléfono.
- **Hallazgo**: problema detectado en el análisis (H1 a H13) con severidad y dónde ocurre.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: El 100 % de las pantallas navegables (38) no tiene desplazamiento horizontal de la página a 320, 360, 390 y 430 px.
- **CE-002**: El 100 % de los controles tocables en teléfono mide al menos 44 px (excepciones justificadas: cero).
- **CE-003**: Cero acercamientos automáticos del navegador al tocar campos en iPhone.
- **CE-004**: Un usuario encuentra a una persona por nombre y la llama o le escribe por WhatsApp en menos de 15 segundos desde el listado, en un celular.
- **CE-005**: Un usuario registra una persona nueva con sus datos básicos en menos de 90 segundos desde un celular.
- **CE-006**: La primera carga útil de cualquier pantalla ocurre en menos de 4 segundos en conexión móvil lenta y en menos de 2 segundos en visitas siguientes.
- **CE-007**: El 90 % de los usuarios de prueba completan sin ayuda sus cinco tareas diarias (buscar y llamar, registrar persona, pasar lista, asignar líder, consultar tablero) desde el celular.
- **CE-008b**: Tras usar la aplicación, el teléfono no conserva ningún dato de personas ni de usuarios para consulta sin conexión.
- **CE-008**: La aplicación se puede agregar a la pantalla de inicio y abrir como aplicación en Android e iPhone.
- **CE-009**: Las 496 pruebas del servidor y las 76 de JavaScript existentes siguen en verde, más las nuevas pruebas de adaptación a celular.
- **CE-010**: En escritorio no hay regresiones visibles en listados ni fichas (revisión comparativa).

## Supuestos

- «100 % responsive» se interpreta como: sin desplazamiento horizontal de la página, controles cómodos al tacto y contenido pensado para teléfono, de 320 px hasta pantalla de escritorio.
- Dispositivos objetivo: celulares Android (Chrome) e iPhone (Safari) de los últimos cuatro años; tabletas y escritorio deben seguir funcionando.
- Conexión objetivo: datos móviles 4G con calidad variable; no se usa la aplicación sin conexión (solo el mensaje de falta de conexión).
- Fuera de alcance: consulta o edición de datos sin conexión (decisión de clarificación). No se crea una aplicación nativa de tienda; «como una aplicación» se logra con el acceso en la pantalla de inicio.
- El diseño actual (menú lateral, colores, tarjetas) se conserva; los cambios son de adaptación, no de rediseño de marca.
- Las funciones, permisos y datos de cada módulo no cambian.
- Las tablas pequeñas dentro de una ficha SÍ se incluyen aquí (a diferencia de la spec 007), pero solo para adaptarlas a teléfono, no para convertirlas en grillas estándar.
- Dependencias: grillas estándar de la spec 007, componentes de formulario de la spec 003 y diseño de interfaz de la spec 004.
