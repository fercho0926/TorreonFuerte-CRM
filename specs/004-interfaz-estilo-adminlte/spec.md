# Especificación de Funcionalidad: Interfaz de toda la aplicación al estilo AdminLTE

**Rama de la funcionalidad**: `004-interfaz-estilo-adminlte`

**Creado**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "quiero la UI de toda la aplicación más del tipo AdminLTE"

## Contexto

La aplicación ya usa la plantilla AdminLTE como base (menú lateral, barra superior, tarjetas y cajas de resumen del tablero), pero la apariencia real se aleja de lo que se espera de un panel de administración de ese estilo: encabezados de página sin migas de pan ni acciones, listados y fichas con aspecto desigual entre módulos, la barra superior y el menú sin información de la persona que inició sesión, estilos propios que compiten con los de la plantilla y un título de pestaña genérico ("AdminLTE Panel"). El usuario pide que **todas** las pantallas se vean y se comporten de forma consistente con el estilo AdminLTE (paneles de administración densos, limpios y reconocibles).

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿Mantener AdminLTE 3 y completarlo, o migrar a AdminLTE 4? → A: Migrar a AdminLTE 4 (rehaciendo también los componentes de formularios de 003 que dependan de la versión anterior).
- Q: ¿Se conservan la marca de agua y los colores propios de la iglesia o se adoptan los estándar de AdminLTE 4? → A: Se conservan la marca de agua y el nombre de sede; el resto usa los colores estándar de AdminLTE 4 (sin paleta propia adicional).
- Q: ¿Qué bloques informativos lleva el tablero además de las cajas de resumen? → A: Solo con datos existentes: próximos eventos, personas sin líder y personas nuevas recientes (sin gráficos).
- Q: ¿Cómo se comporta el menú lateral por defecto? → A: Abierto en pantallas grandes, cerrado en móvil, y se recuerda la última elección del usuario.

## Escenarios de usuario y pruebas *(obligatorio)*

### Historia de usuario 1 - Estructura común reconocible en todas las pantallas (Prioridad: P1)

Quien usa el CRM ve en cada pantalla la misma estructura de panel de administración: barra superior con la sede y su sesión, menú lateral agrupado por secciones con la opción activa resaltada, encabezado de página con título y migas de pan, contenido en tarjetas y pie de página.

**Por qué esta prioridad**: Es lo primero que se percibe como "estilo AdminLTE" y lo que da coherencia a toda la aplicación; las demás historias se apoyan en esta estructura.

**Prueba independiente**: Abrir una pantalla de cada módulo y comprobar que comparten el mismo marco: título, migas de pan, tarjeta de contenido, menú con opción activa y datos de sesión.

**Escenarios de aceptación**:

1. **Dado** un usuario con sesión iniciada, **Cuando** abre cualquier pantalla del sistema, **Entonces** ve el encabezado de página con el título y las migas de pan (Inicio › Módulo › Pantalla) y puede volver a cualquier nivel con un clic.
2. **Dado** un usuario en una pantalla de un módulo, **Cuando** mira el menú lateral, **Entonces** la opción de ese módulo aparece resaltada y los módulos están agrupados en secciones con encabezados claros (por ejemplo: Congregación, Actividades, Administración).
3. **Dado** un usuario con sesión iniciada, **Cuando** mira la barra superior y el menú lateral, **Entonces** ve su nombre, su rol y la sede en la que trabaja, y una opción visible para cerrar sesión.
4. **Dado** un usuario administrador, **Cuando** cambia de sede activa, **Entonces** el selector de sede y el aviso de "solo consulta" se muestran integrados en la barra superior sin romper el diseño.

---

### Historia de usuario 2 - Listados y fichas con el mismo aspecto en todos los módulos (Prioridad: P1)

Todos los listados (personas, líderes, células, eventos, encuentros, ministerios, redes, usuarios, sedes, sin líder) se presentan de la misma manera: una tarjeta de filtros, una tarjeta con la tabla, acciones por fila con iconos y botones, insignias de estado con colores consistentes, paginación y mensajes de lista vacía. Las fichas de detalle usan tarjetas con encabezado, bloques de datos y acciones agrupadas.

**Por qué esta prioridad**: Son las pantallas más usadas; la consistencia reduce el tiempo de aprendizaje y da la apariencia de producto terminado.

**Prueba independiente**: Comparar el listado y la ficha de tres módulos distintos y verificar que usan la misma disposición, los mismos colores de estado y los mismos botones.

**Escenarios de aceptación**:

1. **Dado** cualquier listado, **Cuando** se abre, **Entonces** muestra el título de la tarjeta, el contador de resultados, los filtros, la tabla con filas resaltadas al pasar el cursor y la paginación con el mismo aspecto.
2. **Dado** un listado vacío (sin datos o sin resultados de filtro), **Cuando** se abre, **Entonces** muestra un mensaje de vacío con icono y, si aplica, un botón para crear el primer registro.
3. **Dado** un registro con estado (activo/inactivo, miembro/visitante, planificado/realizado), **Cuando** aparece en un listado o ficha, **Entonces** el estado se muestra como insignia con un color que significa lo mismo en toda la aplicación.
4. **Dado** una ficha de detalle, **Cuando** se abre, **Entonces** las acciones principales (editar, eliminar, volver) aparecen siempre en el mismo lugar y con la misma jerarquía visual.

---

### Historia de usuario 3 - Tablero de inicio con cajas de resumen y bloques informativos (Prioridad: P2)

El tablero de inicio muestra cajas de resumen con cifra e icono, accesos rápidos a las tareas frecuentes y bloques de información con datos que ya existen (próximos eventos, personas sin líder y personas nuevas recientes), todo con el aspecto de un tablero AdminLTE.

**Por qué esta prioridad**: Es la primera pantalla tras iniciar sesión y refuerza la percepción del estilo, pero la aplicación es usable sin ella.

**Prueba independiente**: Iniciar sesión con distintos roles y verificar que el tablero muestra solo los bloques permitidos para ese rol, con cifras coherentes con los listados.

**Escenarios de aceptación**:

1. **Dado** un usuario con sesión, **Cuando** abre el tablero, **Entonces** ve cajas de resumen clicables que llevan al listado correspondiente y cuyas cifras coinciden con los datos de su ámbito.
2. **Dado** un líder con alcance limitado, **Cuando** abre el tablero, **Entonces** solo ve bloques y cifras de su propio ámbito.
3. **Dado** el nombre de la sede activa, **Cuando** se abre cualquier pantalla, **Entonces** la marca de agua y el nombre de sede se conservan sin tapar el contenido.

---

### Historia de usuario 4 - Uso cómodo en pantallas pequeñas y accesibilidad (Prioridad: P2)

Quien usa el CRM desde un teléfono o una tableta puede navegar el menú lateral plegable, leer los listados sin desplazamiento horizontal de toda la página y usar los botones con facilidad. El contraste y el foco del teclado cumplen un nivel básico de accesibilidad.

**Por qué esta prioridad**: Muchos líderes consultan datos desde el teléfono; un estilo de panel que no funciona en móvil no sirve.

**Prueba independiente**: Abrir las pantallas principales a anchos de 360, 768 y 1280 píxeles y verificar que no hay contenido cortado ni controles inaccesibles.

**Escenarios de aceptación**:

1. **Dado** una pantalla de 360 px de ancho, **Cuando** el usuario abre el menú, **Entonces** el menú se despliega sobre el contenido y se cierra al elegir una opción.
2. **Dado** un listado ancho en pantalla pequeña, **Cuando** se abre, **Entonces** la tabla se desplaza dentro de su tarjeta sin mover toda la página.
3. **Dado** un usuario que navega con teclado, **Cuando** recorre menú, botones y filtros, **Entonces** el elemento con foco se distingue claramente.

---

### Historia de usuario 5 - Inicio de sesión y páginas de error con el mismo estilo (Prioridad: P3)

La pantalla de inicio de sesión y las páginas de error (no encontrado, sin permiso, sesión expirada, error del servidor) usan el estilo de las páginas de acceso y de error de AdminLTE, con el nombre del sistema.

**Por qué esta prioridad**: Completa la coherencia, pero no bloquea el trabajo diario.

**Prueba independiente**: Cerrar sesión, abrir el inicio de sesión y provocar un 403 y un 404; comprobar que ambos mantienen el estilo.

**Escenarios de aceptación**:

1. **Dado** un visitante sin sesión, **Cuando** abre el inicio de sesión, **Entonces** ve una tarjeta centrada con el nombre del sistema, los campos y el botón, con el mismo estilo del resto.
2. **Dado** un usuario que abre una dirección inexistente o sin permiso, **Cuando** ve la página de error, **Entonces** el mensaje es claro, conserva el marco de la aplicación (si hay sesión) y ofrece volver al inicio.

---

### Casos límite

- Nombre de sede muy largo en la barra superior, el menú o la marca de agua: debe recortarse con elipsis sin romper el diseño.
- Usuario sin sede (administrador en "Todas las sedes"): la barra superior y el menú deben mostrar un texto adecuado en lugar del nombre de sede.
- Menú con pocas opciones (líder) frente a muchas (administrador): las secciones vacías no deben mostrarse.
- Listados con columnas numerosas, nombres largos o sin datos opcionales: se mantiene la legibilidad y se muestra un guion en los vacíos.
- Los formularios de la funcionalidad 003 (validación, selectores, calendario, diálogos) deben seguir funcionando y verse integrados con el nuevo estilo.
- Páginas con mensajes de éxito, error o aviso al mismo tiempo: los mensajes no deben desplazar ni tapar el encabezado de página.

## Requisitos *(obligatorio)*

### Requisitos funcionales

- **FR-001**: El sistema DEBE presentar todas las pantallas autenticadas con un marco común: barra superior, menú lateral, encabezado de página, área de contenido y pie de página.
- **FR-002**: Cada pantalla DEBE mostrar un encabezado de página con título y migas de pan que reflejen la ruta de navegación y permitan volver a cada nivel.
- **FR-003**: El menú lateral DEBE agrupar los módulos en secciones con encabezado, resaltar la opción activa (y su módulo cuando se está en una subpantalla) y ocultar las opciones y secciones que el rol del usuario no puede usar.
- **FR-004**: La barra superior y el menú lateral DEBEN mostrar nombre y rol de la persona con sesión iniciada, la sede en la que trabaja y la acción de cerrar sesión.
- **FR-005**: El selector de sede activa del administrador y el aviso de "solo consulta" DEBEN conservar su comportamiento actual y verse integrados en la barra superior.
- **FR-006**: Todos los listados DEBEN usar la misma disposición: tarjeta de filtros, tarjeta de tabla con título y contador, acciones por fila, paginación y estado vacío con mensaje y acción sugerida.
- **FR-007**: Todas las fichas de detalle DEBEN usar tarjetas con encabezado, datos agrupados y acciones principales en una posición fija.
- **FR-008**: Los estados de los registros DEBEN mostrarse como insignias con un esquema de colores único y documentado, usado igual en todos los módulos.
- **FR-009**: Los botones DEBEN seguir una jerarquía visual única (principal, secundaria, peligrosa) con iconos coherentes para las acciones comunes (crear, editar, ver, eliminar, volver, buscar).
- **FR-010**: El tablero de inicio DEBE mostrar cajas de resumen clicables, accesos rápidos a tareas frecuentes y tres bloques informativos —próximos eventos, personas sin líder y personas nuevas recientes—, limitados al ámbito y los permisos del usuario. NO DEBE requerir registrar datos nuevos ni incluir gráficos.
- **FR-011**: Los mensajes de éxito, error y aviso DEBEN mostrarse con un estilo uniforme y en un lugar fijo, sin desplazar el encabezado de página.
- **FR-012**: La pantalla de inicio de sesión y las páginas de error (403, 404, 419, 500) DEBEN usar el estilo de acceso y de error del mismo panel, con el nombre del sistema.
- **FR-013**: El título de la pestaña del navegador DEBE incluir el nombre de la pantalla, el nombre del sistema (no un texto genérico de plantilla) y, cuando aplique, el nombre de la sede.
- **FR-014**: La interfaz DEBE ser utilizable en anchos desde 360 px: menú plegable, tablas que se desplazan dentro de su tarjeta y controles con área de pulsación suficiente.
- **FR-015**: Los estilos propios de la aplicación NO DEBEN contradecir ni anular el aspecto de la plantilla; las únicas diferencias respecto de la plantilla DEBEN ser la marca de agua y el nombre de la sede; los colores son los estándar de la plantilla.
- **FR-016**: Esta funcionalidad NO DEBE cambiar reglas de negocio, permisos, rutas ni datos: solo la presentación. Los formularios modernos de la funcionalidad 003 DEBEN seguir operando igual.
- **FR-017**: La interfaz DEBE basarse en la versión 4 de la plantilla AdminLTE y NO DEBE conservar estilos, componentes ni comportamientos de la versión 3 que entren en conflicto con ella; todos los componentes interactivos de la funcionalidad 003 (validación inmediata, selectores con búsqueda, máscaras, calendario, diálogos de confirmación) DEBEN seguir funcionando y verse integrados tras la migración.
- **FR-018**: El foco de teclado DEBE ser visible y el contraste del texto con su fondo DEBE cumplir un nivel mínimo de legibilidad en todos los componentes.
- **FR-019**: El menú lateral DEBE aparecer abierto por defecto en pantallas grandes y cerrado en móvil (desplegándose sobre el contenido), y DEBE recordar la última elección de plegado del usuario en ese dispositivo.

### Entidades clave

No se crean ni modifican datos. Conceptos de presentación que se definen y reutilizan en toda la interfaz:

- **Marco de página**: barra superior, menú lateral, encabezado con migas de pan, contenido y pie.
- **Sección del menú**: grupo con encabezado que contiene opciones visibles según el rol.
- **Insignia de estado**: etiqueta de color que representa el estado de un registro con significado único.
- **Tarjeta**: contenedor con encabezado, cuerpo y, opcionalmente, pie de acciones, usado en filtros, listados, fichas y formularios.

## Criterios de éxito *(obligatorio)*

### Resultados medibles

- **SC-001**: El 100 % de las pantallas autenticadas (listados, fichas, formularios, tablero) comparten el mismo marco, encabezado con migas de pan y disposición de tarjetas, verificado con un recorrido de todas las rutas.
- **SC-002**: Al menos 4 de 5 personas de la iglesia a las que se muestra una pantalla de cada módulo la describen como "un panel de administración" y reconocen la misma estructura en todas, sin explicaciones previas.
- **SC-003**: Una persona nueva encuentra en menos de 10 segundos el módulo que busca en el menú lateral agrupado, en al menos 90 % de los intentos de una prueba con 5 usuarios.
- **SC-004**: En anchos de 360, 768 y 1280 px no hay desplazamiento horizontal de la página completa ni controles cortados en ninguna pantalla principal.
- **SC-005**: Los mismos estados se muestran con el mismo color en el 100 % de los listados y fichas donde aparecen.
- **SC-006**: Ninguna prueba automatizada de comportamiento existente deja de pasar tras el cambio, y los tiempos de carga de las pantallas principales no aumentan más de un 10 %.
- **SC-007**: Tras la migración de versión, el 100 % de los escenarios de formularios de la funcionalidad 003 (validación, selectores, máscaras, calendario, diálogos) pasan sus pruebas existentes sin cambiar su comportamiento visible.
- **SC-008**: El 100 % de las páginas muestran un título de pestaña con el nombre de la pantalla y del sistema.

## Supuestos

- "Más del tipo AdminLTE" se interpreta como adoptar las convenciones visuales y de estructura de la plantilla AdminLTE (marco, migas de pan, tarjetas, cajas de resumen, insignias, botones, panel de usuario) y eliminar lo que se aparta de ellas.
- Se migra a la **versión 4** de la plantilla (decisión del usuario), reemplazando la versión 3 actual. Esto implica adaptar a la nueva versión todo lo que dependa de la anterior, incluidos los selectores con búsqueda, el calendario, los diálogos y los componentes de formulario de la funcionalidad 003.
- Se mantiene el tema claro por defecto; un modo oscuro no forma parte de esta funcionalidad.
- La marca de agua de sede y el nombre de sede en la barra y el menú se conservan como identidad propia; los colores son los estándar de la plantilla.
- Los idiomas y textos permanecen en español.
- El menú agrupará los módulos existentes (Inicio; Congregación: personas, líderes, sin líder, células, redes; Actividades: eventos, encuentros, ministerios; Administración: sedes, usuarios) sin añadir módulos nuevos.
- Las dependencias de estilo ya incluidas en el proyecto son suficientes; no se requiere contratar ni instalar servicios externos.
- No se rediseñan flujos ni se agregan funciones de negocio; si durante el rediseño aparece una mejora funcional, se anota para una funcionalidad posterior.
