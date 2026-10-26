# Especificación de Funcionalidad: Niveles de formación de los miembros (camino de retiros)

**Rama de la funcionalidad**: `014-niveles-miembros`

**Creado**: 2026-10-23

**Estado**: Borrador

**Entrada**: Descripción del usuario: "actualmente los miembros tienen un plan de retiros o encuentros en el orden: Libres para servir, Aliviando el fuego, Caminando en santidad, Viviendo en el espíritu. Quiero una gráfica tipo candy crush en los miembros que me permita ver en qué nivel están, también poder registrar los niveles en un módulo, poder saber quién está en qué nivel"

## Contexto

La iglesia acompaña el crecimiento de cada miembro con un **plan de retiros o encuentros que se vive en orden**:

1. **Libres para servir**
2. **Aliviando el fuego**
3. **Caminando en santidad**
4. **Viviendo en el espíritu**

Hoy el sistema registra encuentros sueltos y quién asistió, pero **no muestra en qué punto del camino está cada miembro** ni permite responder «¿quién está en qué nivel?». Esta funcionalidad agrega:

- Un **módulo «Niveles»** donde se registran y ordenan los niveles del plan (ya vienen cargados los cuatro de arriba).
- Un **camino gráfico tipo mapa de juego** (como el de *Candy Crush*): estaciones conectadas por un sendero; las superadas se ven completas, la siguiente resaltada y las demás pendientes. Se muestra en la ficha de cada miembro.
- El **avance de cada miembro**, que se registra a mano o se completa solo cuando el miembro asiste a un encuentro de ese nivel.
- Una **vista de niveles** para saber cuántos miembros hay en cada uno y quiénes son.

## Clarificaciones

### Sesión 2026-10-23

- Q: ¿Los niveles son un solo plan para toda la iglesia o cada sede tiene su propio plan? → A: Un solo plan para toda la iglesia: los niveles son comunes a todas las sedes y los edita quien tenga escritura en Niveles; el avance de cada miembro sigue siendo de su sede y se conserva al trasladarlo.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Ver el camino de niveles de un miembro (Prioridad: P1)

Al abrir la ficha de un miembro, el pastor o líder ve un **camino gráfico** con los niveles en orden: cada nivel es una estación (círculo grande con su número y nombre) unida a la siguiente por un sendero sinuoso. Los niveles superados se ven completos (marca o estrella y la fecha), el nivel que sigue está resaltado como «siguiente» y los demás aparecen pendientes. De un vistazo se entiende en qué nivel está.

**Por qué esta prioridad**: Es lo central del pedido: ver el nivel de cada miembro de forma clara y atractiva.

**Prueba Independiente**: Abrir la ficha de un miembro con dos niveles completados y comprobar que el camino muestra los dos primeros completos, el tercero como siguiente y el cuarto pendiente, también en un celular.

**Escenarios de Aceptación**:

1. **Dado** un miembro que completó «Libres para servir» y «Aliviando el fuego», **Cuando** se abre su ficha, **Entonces** el camino muestra esos dos niveles como completados (con su fecha), «Caminando en santidad» como siguiente y «Viviendo en el espíritu» como pendiente.
2. **Dado** un miembro sin ningún nivel, **Cuando** se abre su ficha, **Entonces** el primer nivel aparece como siguiente y los demás pendientes, con el texto «Aún no inicia su camino».
3. **Dado** un miembro que completó los cuatro niveles, **Cuando** se abre su ficha, **Entonces** todo el camino aparece completo con un mensaje de camino terminado.
4. **Dado** una persona que usa lector de pantalla o que no distingue colores, **Cuando** ve el camino, **Entonces** cada estación indica su estado con texto («Completado el 12 de marzo», «Siguiente», «Pendiente») y no solo con color.
5. **Dado** un celular de 360 px de ancho, **Cuando** se abre la ficha, **Entonces** el camino se ve completo sin desplazamiento horizontal y cada estación se puede tocar.
6. **Dado** el camino en un listado de miembros, **Cuando** se muestra una versión resumida, **Entonces** cada miembro tiene una fila de puntos de progreso (por ejemplo ●●○○) y el nombre de su nivel actual.

---

### Historia de Usuario 2 - Registrar y ordenar los niveles en un módulo (Prioridad: P1)

En el menú aparece el módulo **Niveles**. Allí el administrador o quien tenga permiso de escritura ve la lista de niveles en orden, puede crear uno nuevo (nombre, descripción, color/icono opcional), editarlo, **cambiar su orden** y desactivarlo. Los cuatro niveles del plan ya vienen cargados en su orden.

**Por qué esta prioridad**: Sin los niveles registrados no hay camino que mostrar; además el plan puede crecer o cambiar.

**Prueba Independiente**: Abrir Niveles, comprobar los cuatro niveles en orden, crear un quinto, moverlo a la segunda posición y verificar que el camino de un miembro lo refleja.

**Escenarios de Aceptación**:

1. **Dado** el módulo Niveles recién estrenado, **Cuando** se abre, **Entonces** aparecen «Libres para servir», «Aliviando el fuego», «Caminando en santidad» y «Viviendo en el espíritu», numerados del 1 al 4.
2. **Dado** el formulario de nivel, **Cuando** se completa con un nombre nuevo y se guarda, **Entonces** el nivel aparece al final de la lista y en el camino de todos los miembros como pendiente.
3. **Dado** un nombre que ya usa otro nivel del plan, **Cuando** se intenta guardar, **Entonces** se muestra el error junto al campo y no se crea.
4. **Dado** un nivel de la lista, **Cuando** se sube o baja de posición, **Entonces** la numeración se actualiza y el camino de los miembros respeta el nuevo orden, conservando lo ya registrado.
5. **Dado** un nivel que ya tiene miembros registrados, **Cuando** se intenta eliminar, **Entonces** no se permite y se ofrece desactivarlo; un nivel sin registros sí se puede eliminar.
6. **Dado** un nivel desactivado, **Cuando** se ve el camino de un miembro, **Entonces** el nivel ya no se dibuja (los registros se conservan y vuelven a verse si se reactiva).
7. **Dado** un usuario con permiso solo de lectura en Niveles, **Cuando** abre el módulo, **Entonces** ve la lista pero no los botones de crear, editar, ordenar ni desactivar.

---

### Historia de Usuario 3 - Registrar el avance de un miembro (Prioridad: P1)

Un nivel se completa de dos maneras: **(a) a mano**, desde la ficha del miembro o desde el módulo Niveles, eligiendo el nivel y la fecha (útil para quienes ya hicieron el retiro antes de usar el sistema); y **(b) automáticamente**, cuando el miembro asiste a un encuentro que está vinculado a ese nivel. Cada encuentro puede asociarse a un nivel del plan.

**Por qué esta prioridad**: Sin registrar el avance, el camino no tiene información; y el vínculo con los encuentros evita doble trabajo.

**Prueba Independiente**: Vincular un encuentro al nivel 1, registrar la asistencia de un miembro y comprobar que su camino marca el nivel 1 completado con la fecha del encuentro; registrar a mano el nivel 2 de otro miembro.

**Escenarios de Aceptación**:

1. **Dado** un encuentro vinculado al nivel «Aliviando el fuego», **Cuando** se marca que un miembro asistió, **Entonces** el miembro queda con ese nivel completado, con la fecha del encuentro y el origen «Encuentro».
2. **Dado** esa asistencia, **Cuando** se desmarca (se corrige), **Entonces** el nivel vuelve a pendiente, salvo que también se hubiera registrado a mano.
3. **Dado** la ficha de un miembro, **Cuando** un usuario con permiso elige un nivel, una fecha y guarda, **Entonces** el nivel queda completado con el origen «Registro manual», la fecha elegida y quién lo registró.
4. **Dado** un nivel ya completado, **Cuando** se intenta registrarlo otra vez, **Entonces** se avisa que ya está completado y no se duplica.
5. **Dado** un nivel completado a mano, **Cuando** un usuario con permiso lo quita, **Entonces** vuelve a pendiente y queda constancia del cambio en el historial.
6. **Dado** el formulario de un encuentro, **Cuando** se elige el nivel al que pertenece (o ninguno), **Entonces** se guarda y se muestra en el detalle y en el listado de encuentros.
7. **Dado** un encuentro cancelado o aún no realizado, **Cuando** tiene asistentes marcados, **Entonces** no completa ningún nivel hasta que esté realizado.
8. **Dado** un miembro que asistió al nivel 3 sin haber completado el 2, **Cuando** se ve su camino, **Entonces** el nivel 3 aparece completado, el 2 como pendiente («saltado») y el siguiente a hacer es el 2.

---

### Historia de Usuario 4 - Saber quién está en qué nivel (Prioridad: P2)

En el módulo Niveles hay una vista «Quién está en cada nivel»: una tarjeta por nivel con cuántos miembros lo tienen como nivel actual y la lista de quiénes son, con filtros y exportación. Además, el listado de miembros ofrece una columna y un filtro «Nivel».

**Por qué esta prioridad**: Responde a «poder saber quién está en qué nivel» y permite planear los próximos retiros.

**Prueba Independiente**: Con miembros repartidos en distintos niveles, abrir la vista y comprobar que los conteos y las listas coinciden con las fichas individuales.

**Escenarios de Aceptación**:

1. **Dado** miembros repartidos entre niveles, **Cuando** se abre «Quién está en cada nivel», **Entonces** cada tarjeta muestra el nombre del nivel y la cantidad de miembros cuyo nivel actual es ese, más una tarjeta «Sin iniciar».
2. **Dado** una tarjeta de nivel, **Cuando** se pulsa, **Entonces** se abre la lista de esos miembros (nombre, celular, fecha en que completó el nivel anterior) con las acciones de contacto («Llamar», «WhatsApp») y la misma grilla estándar con orden, filtros y exportación.
3. **Dado** el listado de miembros, **Cuando** se filtra por «Nivel», **Entonces** solo aparecen los miembros cuyo nivel actual es ese.
4. **Dado** un nivel, **Cuando** se pide «los que ya completaron este nivel», **Entonces** se ve también la lista de quienes lo completaron (aunque ya vayan más adelante) y la de quienes lo tienen pendiente como siguiente.
5. **Dado** un usuario de alcance limitado (líder o pastor de otra sede), **Cuando** abre la vista, **Entonces** solo cuenta y lista a los miembros que puede ver.

---

### Historia de Usuario 5 - Resumen de niveles en el tablero (Prioridad: P3)

El tablero muestra una tarjeta «Miembros por nivel» con una barra o fila de estaciones y el número de miembros en cada una, para ver la salud del camino de la iglesia sin entrar al módulo.

**Por qué esta prioridad**: Es un complemento de visibilidad; las historias anteriores ya cubren lo pedido.

**Prueba Independiente**: Abrir el tablero y comprobar que la tarjeta coincide con la vista de niveles y que respeta los permisos.

**Escenarios de Aceptación**:

1. **Dado** un usuario con lectura en Niveles, **Cuando** abre el tablero, **Entonces** ve la tarjeta con los miembros de cada nivel y puede entrar a la vista completa.
2. **Dado** un usuario sin acceso al módulo Niveles, **Cuando** abre el tablero, **Entonces** la tarjeta no aparece.

---

### Casos Límite

- **Miembro sin ningún nivel**: «Sin iniciar»; el siguiente es el nivel 1.
- **Niveles saltados** (asistió al 3 sin el 2): el sistema lo permite y lo muestra; el nivel actual es el más alto completado y el siguiente es el primero pendiente.
- **Reordenar o desactivar niveles** con registros existentes: los registros se conservan y siguen asociados a su nivel, no a su posición.
- **Varios encuentros del mismo nivel**: cuenta el primero al que asistió; asistir a otro del mismo nivel no cambia la fecha ni duplica el registro.
- **Encuentro cambia de nivel o se le quita el nivel** después de haber asistentes: el avance automático se recalcula; lo registrado a mano no cambia.
- **Encuentro cancelado o eliminado**: deja de completar niveles de forma automática.
- **Miembro inactivo**: conserva su camino y no cuenta en los totales de «quién está en cada nivel» salvo que se incluyan inactivos.
- **Traslado de sede**: el miembro conserva su avance sin cambios, porque el plan de niveles es el mismo en todas las sedes.
- **Cuatro niveles o muchos más** (por ejemplo ocho): el camino se adapta (en teléfono baja en zigzag; en pantalla grande avanza en curvas) sin desbordar.
- **Persona con movimiento reducido en su sistema**: las animaciones del camino (brillo, pulso) se desactivan.
- **Sin permisos**: quien no tiene acceso a Niveles no ve el módulo ni el camino detallado; con solo lectura ve el camino sin botones de registro.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El sistema DEBE ofrecer un módulo «Niveles» en el menú (grupo Congregación) con la lista ordenada de niveles del plan de formación.
- **RF-002**: El sistema DEBE traer cargados, en este orden, los niveles «Libres para servir», «Aliviando el fuego», «Caminando en santidad» y «Viviendo en el espíritu», como **un único plan común a todas las sedes** (también para las sedes que se creen después).
- **RF-003**: Un nivel DEBE tener nombre (obligatorio y único en todo el plan), descripción opcional, posición en el orden y estado activo o inactivo. El plan es común a todas las sedes.
- **RF-004**: El sistema DEBE permitir crear, editar, reordenar, desactivar y reactivar niveles, y DEBE impedir eliminar un nivel con registros de avance (ofreciendo desactivarlo).
- **RF-005**: La ficha de cada miembro DEBE mostrar un **camino gráfico** con sus niveles activos en orden: estaciones conectadas por un sendero, cada una con su número, nombre y estado (completado con fecha, siguiente, pendiente).
- **RF-006**: El estado de cada estación DEBE comunicarse también con texto y forma, no solo con color, y el camino DEBE ser comprensible con lector de pantalla.
- **RF-007**: El camino DEBE verse completo y ser usable en teléfono (320 a 430 px) y en escritorio, sin desplazamiento horizontal de la página, con estaciones tocables de al menos 44 px (constitución, principio VII).
- **RF-008**: Las animaciones del camino DEBEN respetar la preferencia de movimiento reducido del usuario.
- **RF-009**: El sistema DEBE registrar el avance de un miembro en un nivel con su fecha, su origen (encuentro o registro manual) y quién lo registró.
- **RF-010**: Un usuario con permiso de escritura en Niveles DEBE poder registrar y quitar a mano el avance de un miembro desde su ficha y desde el módulo, sin duplicar un nivel ya completado.
- **RF-011**: Cada encuentro DEBE poder asociarse a un nivel (o a ninguno); la asociación DEBE verse en el formulario, el detalle y el listado de encuentros.
- **RF-012**: Cuando un miembro asiste a un encuentro **realizado** vinculado a un nivel, el sistema DEBE completar ese nivel con la fecha del encuentro y origen «Encuentro»; al corregir la asistencia o al cancelar o desvincular el encuentro, DEBE retirar ese avance automático sin tocar lo registrado a mano.
- **RF-013**: El **nivel actual** de un miembro DEBE ser el más alto que haya completado; su **siguiente** nivel DEBE ser el primer nivel activo pendiente; sin avances, el miembro está «Sin iniciar».
- **RF-014**: El sistema DEBE permitir niveles saltados y mostrarlos como pendientes dentro del camino.
- **RF-015**: El módulo DEBE ofrecer la vista «Quién está en cada nivel» con la cantidad de miembros por nivel actual (más «Sin iniciar»), la lista de miembros de cada nivel y la de quienes ya completaron cada nivel.
- **RF-016**: Las listas de miembros por nivel DEBEN usar la grilla estándar (orden, filtros por columna, filas por página, exportar a CSV, tarjetas en teléfono) y las acciones de contacto.
- **RF-017**: El listado de miembros DEBE incluir una columna «Nivel» (nivel actual con progreso resumido) y un filtro por nivel.
- **RF-018**: El tablero DEBE mostrar una tarjeta «Miembros por nivel» solo a quien tenga lectura en Niveles.
- **RF-019**: Todo lo anterior DEBE respetar el aislamiento por sede y el alcance de datos del rol: cada usuario solo ve y cuenta a los miembros que puede ver. Los niveles en sí (nombre, orden) son comunes y los ve cualquier rol con acceso al módulo; lo que se aísla son los avances de los miembros.
- **RF-020**: «Niveles» DEBE ser un módulo del catálogo de permisos (roles de la spec 013) con niveles de acceso «sin acceso», «lectura» y «escritura»; el administrador y el pastor conservan escritura por defecto, el líder lectura sobre su rama.
- **RF-021**: Todo cambio en niveles y en avances de miembros DEBE quedar en el historial de cambios con quién, cuándo y qué.
- **RF-022**: Al trasladar a un miembro de sede, el sistema DEBE conservar su avance completo (el plan de niveles es el mismo en todas las sedes).
- **RF-023**: La exportación a CSV de las listas por nivel DEBE incluir el nivel actual y la fecha del último nivel completado.

### Entidades Clave *(incluir si la funcionalidad involucra datos)*

- **Nivel**: etapa del plan de formación de la iglesia, común a todas las sedes. Tiene nombre, descripción, posición en el orden y estado.
- **Avance de nivel**: constancia de que un miembro completó un nivel. Relaciona al miembro con el nivel, con fecha, origen (encuentro o registro manual), encuentro de origen (si aplica) y quién lo registró. Un miembro tiene a lo sumo un avance por nivel.
- **Encuentro** (existente): se le agrega la relación opcional con un nivel.
- **Miembro** (existente): su camino, nivel actual y siguiente se derivan de sus avances.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: Un usuario identifica el nivel actual de un miembro con solo abrir su ficha, en menos de 3 segundos y sin leer texto largo.
- **CE-002**: Un usuario registra a mano el avance de un miembro en menos de 30 segundos desde un celular.
- **CE-003**: El 100 % de las asistencias a encuentros realizados vinculados a un nivel se reflejan en el camino del miembro sin intervención manual.
- **CE-004**: El pastor obtiene la lista completa de miembros de un nivel (con teléfonos) en menos de 15 segundos desde el módulo.
- **CE-005**: Los conteos por nivel coinciden al 100 % con las fichas individuales y con el filtro de nivel del listado de miembros.
- **CE-006**: El camino y las pantallas nuevas pasan la auditoría móvil del proyecto (sin desplazamiento horizontal, controles de al menos 44 px, letra de campos de 16 px) en 320, 360, 390 y 430 px.
- **CE-007**: El 90 % de las personas de prueba entiende, sin explicación, qué niveles tiene completados un miembro y cuál sigue.
- **CE-008**: Ningún usuario ve niveles ni avances de miembros fuera de su sede o de su alcance de datos en las pruebas de aislamiento.
- **CE-009**: Las pruebas existentes (PHPUnit, JavaScript y auditoría móvil) siguen en verde tras la funcionalidad.

## Supuestos

- Los **cuatro niveles iniciales** son los mencionados, en ese orden; el plan puede cambiar después desde el módulo Niveles (más niveles, otro orden, desactivar).
- **El plan de niveles es único para toda la iglesia** (decisión de clarificación): lo edita quien tenga escritura en Niveles, sin importar la sede activa; el aislamiento por sede se aplica a los avances de los miembros, no a la definición de los niveles. El administrador en «Todas las sedes» puede consultar los avances de todos, pero no registrar avances en nombre de una sede.
- El avance se registra **de las dos formas** (a mano y por asistencia a encuentros vinculados); se asume que el registro manual es necesario para quienes ya hicieron retiros antes de usar el sistema.
- Los niveles **no son bloqueantes**: no se impide registrar un nivel posterior sin el anterior (se muestra como saltado), porque la realidad de la iglesia puede no seguir estrictamente el orden.
- «Nivel actual» significa el **más alto completado**; el nivel «siguiente» es el primero activo que falta. «Sin iniciar» es quien no completó ninguno.
- La «gráfica tipo candy crush» se entiende como un **mapa de camino con estaciones conectadas** (circulares, numeradas, con estados completado/siguiente/pendiente y un toque lúdico: estrellas, brillo); no se pretende un juego ni puntos. Es una representación propia, sin usar marcas ni imágenes de terceros.
- El módulo se integra con los **roles y permisos por módulo** (spec 013) y la **grilla estándar** (spec 007); «Miembros» es el nombre vigente de las personas (spec 010).
- Dependencias: módulo Encuentros existente (asistencia por miembro), ficha de miembro, roles y permisos, traslados de sede y la auditoría móvil de la spec 008.
