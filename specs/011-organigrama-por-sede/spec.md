# Especificación de Funcionalidad: Organigrama interactivo por sede

**Rama de la funcionalidad**: `011-organigrama-por-sede`

**Creado**: 2026-10-22

**Estado**: Borrador

**Entrada**: Descripción del usuario: "quiero una grafica por sede, quiero tener un tipo organigrama, de en la cabeza los pastores, luego los lideres, y cada lider con sus miembros, que sea interactivo que sea full responsive, que yo pueda ir expandiendo para ver el organigrama de la iglesia"

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Ver el organigrama de una sede (Prioridad: P1)

Un usuario con acceso a una sede abre la pantalla «Organigrama» y ve una gráfica jerárquica de esa sede: en la cabeza los pastores de la sede, debajo los líderes y, bajo cada líder, sus miembros. Al abrir la pantalla se muestra el primer nivel (los pastores) y los niveles inferiores aparecen colapsados.

**Por qué esta prioridad**: Es el núcleo de la solicitud; sin la gráfica jerárquica de la sede no hay valor entregado.

**Prueba Independiente**: Con una sede que tenga al menos un pastor, dos líderes y varios miembros por líder, abrir el organigrama y comprobar que se ve la cabeza con los pastores y que cada nivel corresponde a la jerarquía real de datos.

**Escenarios de Aceptación**:

1. **Dado** una sede con pastores, líderes y miembros, **Cuando** el usuario abre el organigrama, **Entonces** ve a los pastores en la cabeza, y los líderes y miembros quedan disponibles al expandir.
2. **Dado** un líder con miembros asignados, **Cuando** se expande ese líder, **Entonces** aparecen exactamente sus miembros, cada uno con su nombre.
3. **Dado** una sede con varios pastores, **Cuando** se abre el organigrama, **Entonces** todos los pastores aparecen juntos en la cabeza.

---

### Historia de Usuario 2 - Expandir y colapsar para explorar (Prioridad: P1)

El usuario toca o hace clic en cualquier nodo con descendientes para expandirlo o colapsarlo, y así recorre la iglesia nivel por nivel. También dispone de acciones para expandir todo y colapsar todo, y cada nodo con hijos indica cuántos tiene.

**Por qué esta prioridad**: La interactividad es parte explícita de lo pedido («ir expandiendo para ver el organigrama»).

**Prueba Independiente**: Expandir y colapsar un pastor, un líder y usar «Expandir todo» / «Colapsar todo»; verificar que el contenido visible cambia en cada acción sin recargar la página.

**Escenarios de Aceptación**:

1. **Dado** un nodo colapsado con descendientes, **Cuando** el usuario lo toca, **Entonces** se muestran sus hijos directos y el nodo queda marcado como expandido.
2. **Dado** un nodo expandido, **Cuando** el usuario lo toca de nuevo, **Entonces** sus descendientes se ocultan.
3. **Dado** un organigrama parcialmente expandido, **Cuando** el usuario elige «Expandir todo», **Entonces** se ven todos los niveles; con «Colapsar todo» vuelve a verse solo la cabeza.
4. **Dado** un nodo con hijos, **Cuando** está colapsado, **Entonces** muestra la cantidad de hijos que contiene.

---

### Historia de Usuario 3 - Usarlo cómodamente desde el celular (Prioridad: P1)

El organigrama se adapta a cualquier tamaño de pantalla, de teléfono pequeño a monitor grande. En pantallas estrechas la jerarquía se presenta en forma de árbol vertical con sangría; en pantallas amplias se presenta como organigrama horizontal clásico. Todos los controles son fáciles de tocar y la página nunca se desplaza horizontalmente.

**Por qué esta prioridad**: La constitución exige diseño primero para celular y la mayor parte del uso es desde el teléfono.

**Prueba Independiente**: Abrir el organigrama a 320, 768 y 1920 px de ancho y comprobar que no hay desplazamiento horizontal de la página, que los controles miden al menos 44 × 44 px y que se puede expandir y colapsar en los tres tamaños.

**Escenarios de Aceptación**:

1. **Dado** un teléfono de 320 px de ancho, **Cuando** el usuario abre y expande el organigrama, **Entonces** todo el contenido es legible y alcanzable sin desplazamiento horizontal de la página.
2. **Dado** un monitor de escritorio, **Cuando** el usuario abre el organigrama, **Entonces** aprovecha el ancho disponible mostrando la jerarquía de forma horizontal.
3. **Dado** una sede con muchos miembros, **Cuando** se expande un líder, **Entonces** la lista de sus miembros sigue siendo legible y navegable en cualquier tamaño.

---

### Historia de Usuario 4 - Elegir la sede y respetar el alcance por rol (Prioridad: P2)

Un SuperAdmin con acceso a varias sedes puede elegir de cuál sede quiere ver el organigrama; un Admin, un Pastor o un Líder solo ve lo que su rol y su sede le permiten. Nunca se muestra información de otra sede.

**Por qué esta prioridad**: Es necesario por la regla de aislamiento por sede, pero la gráfica en sí ya entrega valor con una sola sede.

**Prueba Independiente**: Con usuarios de distintas sedes y roles, comprobar qué organigrama ve cada uno y que ninguno obtiene datos de una sede ajena, ni siquiera cambiando la dirección web.

**Escenarios de Aceptación**:

1. **Dado** un SuperAdmin con varias sedes, **Cuando** abre el organigrama, **Entonces** puede escoger la sede y la gráfica cambia a la sede elegida.
2. **Dado** un Admin o Pastor de una sede, **Cuando** abre el organigrama, **Entonces** ve únicamente su sede.
3. **Dado** un Líder, **Cuando** abre el organigrama, **Entonces** ve únicamente su propia rama (él y sus miembros).
4. **Dado** un usuario que intenta abrir el organigrama de una sede que no le corresponde, **Cuando** modifica la dirección web, **Entonces** recibe el mismo rechazo de acceso que en el resto de la aplicación.

---

### Historia de Usuario 5 - Ir de un nodo a su ficha (Prioridad: P3)

Al tocar el nombre de un pastor, líder o miembro, el usuario puede abrir su ficha (si tiene permiso para verla) y volver al organigrama.

**Por qué esta prioridad**: Agrega comodidad, pero no es necesario para visualizar la estructura.

**Prueba Independiente**: Desde un nodo, abrir la ficha y regresar; comprobar que la ficha corresponde a la persona tocada.

**Escenarios de Aceptación**:

1. **Dado** un nodo de miembro, **Cuando** el usuario elige abrir su ficha, **Entonces** se muestra la ficha de ese miembro.
2. **Dado** un usuario sin permiso para ver una ficha, **Cuando** toca el nodo, **Entonces** no se ofrece el enlace a la ficha.

---

### Casos Límite

- Una sede sin pastores asignados: se muestra la sede como cabeza con un aviso, y los líderes quedan bajo ella.
- Un líder sin miembros: el nodo se muestra sin opción de expandir y con la indicación «0 miembros».
- Miembros sin líder asignado: se agrupan en un nodo «Sin líder asignado» para que no queden fuera del organigrama.
- Sede completamente vacía (sin pastores, líderes ni miembros): se muestra un mensaje de estado vacío claro.
- Miembros inactivos: no se incluyen en el organigrama.
- Nombres muy largos: se acortan visualmente sin romper el diseño y se leen completos al tocar o pasar el cursor sobre el nodo.
- Una sede con cientos de miembros: el organigrama sigue siendo utilizable porque los niveles inferiores permanecen colapsados hasta que el usuario los expande.
- Sin JavaScript: la jerarquía sigue siendo legible como lista anidada, sin la interactividad de expandir y colapsar.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El sistema DEBE ofrecer una pantalla «Organigrama» accesible desde el menú lateral que muestre la estructura jerárquica de una sede.
- **RF-002**: La jerarquía DEBE tener tres niveles: pastores de la sede en la cabeza, líderes debajo de ellos y miembros bajo el líder al que pertenecen.
- **RF-003**: Al abrirla, la pantalla DEBE mostrar solo la cabeza (pastores) con los niveles inferiores colapsados.
- **RF-004**: El usuario DEBE poder expandir y colapsar cualquier nodo que tenga descendientes, sin recargar la página.
- **RF-005**: El sistema DEBE ofrecer las acciones «Expandir todo» y «Colapsar todo».
- **RF-006**: Cada nodo con descendientes DEBE indicar la cantidad de hijos directos que contiene, también cuando está colapsado.
- **RF-007**: Cada nodo DEBE mostrar al menos el nombre de la persona y su rol (pastor, líder o miembro).
- **RF-008**: La pantalla DEBE adaptarse de 320 a 1920 px de ancho: presentación vertical con sangría en pantallas estrechas y horizontal tipo organigrama en pantallas amplias, sin desplazamiento horizontal de la página.
- **RF-009**: Todos los controles interactivos DEBEN medir al menos 44 × 44 px y ser operables con teclado (enfocar, expandir y colapsar) además de táctil y ratón.
- **RF-010**: El SuperAdmin DEBE poder elegir la sede cuyo organigrama desea ver; los demás roles ven solo la que les corresponde.
- **RF-011**: Un Líder DEBE ver únicamente su propia rama; un Pastor o Admin, el organigrama completo de su sede.
- **RF-012**: El sistema NO DEBE exponer datos de otra sede ni fuera del alcance del rol, ni en pantalla ni en las respuestas de datos que alimentan la gráfica; el alcance se aplica en la consulta.
- **RF-013**: Los miembros sin líder asignado DEBEN aparecer agrupados bajo «Sin líder asignado»; los miembros inactivos NO DEBEN aparecer.
- **RF-014**: Cuando el usuario tenga permiso, tocar un nodo DEBE permitir abrir la ficha de esa persona.
- **RF-015**: Cuando no existan datos que mostrar, el sistema DEBE presentar un estado vacío con un mensaje claro en lugar de una pantalla en blanco.
- **RF-016**: Sin JavaScript, la jerarquía DEBE seguir siendo visible como lista anidada legible.
- **RF-017**: La pantalla NO DEBE guardar datos de personas en el teléfono (cumplimiento del principio VII de la constitución).
- **RF-018**: Todos los textos de la pantalla DEBEN estar en español y usar el término «miembros» (conforme a la funcionalidad 010).

### Entidades Clave *(incluir si la funcionalidad maneja datos)*

- **Sede**: Congregación de la iglesia; es el ámbito de cada organigrama y la raíz conceptual de la gráfica.
- **Pastor**: Persona con rol pastoral asignada a una sede; ocupa la cabeza del organigrama. Una sede puede tener varios.
- **Líder**: Persona que dirige una o más células o grupos dentro de la sede; es el segundo nivel y agrupa a sus miembros.
- **Miembro**: Persona activa de la iglesia, asociada a un líder; es el tercer nivel y no tiene descendientes.
- **Nodo del organigrama**: Representación visual de un pastor, líder, miembro o del grupo «Sin líder asignado», con su cantidad de hijos y su estado expandido o colapsado.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: Un usuario encuentra a cualquier miembro de su sede recorriendo el organigrama (pastor → líder → miembro) en menos de 30 segundos.
- **CE-002**: El 100 % de los pastores, líderes y miembros activos del alcance del usuario aparece en el organigrama, y ninguno de fuera de su alcance.
- **CE-003**: La pantalla es utilizable sin desplazamiento horizontal de la página en todo ancho entre 320 y 1920 px.
- **CE-004**: La pantalla inicial del organigrama de una sede de hasta 500 miembros se muestra en menos de 3 segundos en una conexión móvil típica, y cada expansión responde en menos de 0,3 s.
- **CE-005**: El 90 % de los usuarios nuevos logra expandir un líder y ver sus miembros en el primer intento, sin instrucciones.
- **CE-006**: Cero fugas de información entre sedes o entre roles en las pruebas de aislamiento.

## Supuestos

- El organigrama usa los datos que la aplicación ya tiene (sede, rol pastoral, líder de cada miembro); no requiere capturar información nueva.
- «Pastores» se entiende como las personas con rol de Pastor asignadas a la sede; «líderes» como los líderes de célula de esa sede; «miembros» como los miembros activos asignados a cada líder.
- La gráfica es de solo lectura: no se reorganiza la jerarquía arrastrando nodos ni se editan datos desde ella.
- La exportación o impresión del organigrama (imagen, PDF) queda fuera de esta versión.
- La pantalla es un diagrama, no un listado principal, por lo que no aplica la grilla estándar del principio III.
- La terminología «Miembros» depende de la funcionalidad 010; esta funcionalidad usa ese vocabulario desde el inicio.
- Se respeta la estructura de roles existente (SuperAdmin, Admin, Pastor, Líder) y el aislamiento por sede ya implementado.
