# Especificación de Funcionalidad: Optimización de Personas, Líderes, Células y Ministerios

**Rama de la funcionalidad**: `006-optimizar-personas-celulas`

**Creado**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "sigamos optimizando: en el módulo personas quitar las cards de contacto y de estado y documento, y poner celular, correo, tipo y número de documento y estado en la card de datos personales; eliminar el módulo de Red y quitarlo de todos lados; en líderes, al designar un líder, el buscador debe autocompletar con filtro de personas activas; al ver un líder quiero ver a quién lidera en una pequeña grilla; en célula aún no me deja registrar a los integrantes (me deja crearla pero no ingresar ni crear integrantes); como son personas que aún no hacen parte de la iglesia pero queremos conocerlas, en la creación de persona debe haber un estado «célula»; ministerios debe estar en el grupo Congregación, y la selección de integrantes debe tener filtro por nombre con multiselección autocompletada, y esa misma selección se puede usar en las células"

## Contexto

Estas mejoras salen del uso real del sistema:

- El formulario de personas reparte los datos básicos en tres tarjetas (datos personales, contacto, estado y documento), lo que obliga a desplazarse y a repetir el contexto.
- El concepto **Red** ya no se usa y estorba en líderes, personas, filtros, tablero y menú.
- Designar un líder exige buscar a la persona escribiendo y pulsando «Buscar»; con muchas personas es lento.
- La ficha de un líder no muestra con claridad a quién lidera.
- En una **célula** no se puede registrar a los integrantes de forma práctica, ni crear a quien aún no existe como persona. Muchas de esas personas todavía no son parte de la iglesia, pero se quiere conocerlas y darles seguimiento.
- **Ministerios** está en el grupo «Actividades» del menú y su selección de integrantes es tediosa cuando hay muchas personas.

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿Una persona integrante de una célula puede tener como líder directo a otra persona (o a nadie), o su líder directo debe ser el líder de la célula? → A: Es independiente: cualquier persona activa de la sede puede ser integrante de una célula sin importar su líder directo (la célula es el lugar de reunión y el líder directo es el cuidado pastoral).

## Escenarios de usuario y pruebas *(obligatorio)*

### Historia de usuario 1 - Formulario de personas con una sola tarjeta de datos personales (Prioridad: P1)

Quien registra o edita una persona ve una sola tarjeta de «Datos personales» con todo lo básico: nombres, apellidos, fecha de nacimiento, **celular, correo, estado, tipo de documento y número de documento**. Las tarjetas «Contacto» y «Estado y documento» desaparecen.

**Por qué esta prioridad**: Es el formulario más usado y el cambio es pequeño y visible; reduce desplazamiento y tiempo de registro.

**Prueba independiente**: Abrir crear y editar persona y comprobar que existe una sola tarjeta con esos campos, que las otras dos ya no están y que guardar sigue funcionando con las mismas reglas.

**Escenarios de aceptación**:

1. **Dado** el formulario de crear persona, **Cuando** se abre, **Entonces** la primera tarjeta «Datos personales» contiene nombres, apellidos, fecha de nacimiento, celular, correo, estado, tipo de documento y número de documento, y no hay tarjetas «Contacto» ni «Estado y documento».
2. **Dado** el mismo formulario, **Cuando** se elige un estado que exige documento (por ejemplo miembro activo), **Entonces** el documento se marca como obligatorio, y para visitantes y para el estado «célula» es opcional.
3. **Dado** una persona existente, **Cuando** se abre su edición, **Entonces** los campos aparecen en la misma tarjeta con sus valores y las validaciones previas (celular o correo, duplicados) siguen aplicando.
4. **Dado** la ficha de una persona, **Cuando** se abre, **Entonces** los datos de contacto, estado y documento se muestran juntos en el bloque de datos personales.

---

### Historia de usuario 2 - Quitar el concepto «Red» de toda la aplicación (Prioridad: P1)

El módulo **Redes** y todo lo que depende de él desaparece: el menú, el catálogo, el campo en líderes y personas, los filtros, el tablero y la ayuda de términos.

**Por qué esta prioridad**: El concepto no se usa; mantenerlo confunde y alarga los formularios. Es una limpieza parecida a la de «Zona».

**Prueba independiente**: Recorrer menú, líderes, personas, tablero y ayudas y comprobar que no queda ninguna mención ni campo de red, y que designar un líder ya no la pide.

**Escenarios de aceptación**:

1. **Dado** cualquier usuario, **Cuando** abre el menú, **Entonces** no existe la opción «Redes» ni su ruta.
2. **Dado** el formulario de persona o la pantalla de designar líder, **Cuando** se abren, **Entonces** no hay campo de red; designar un líder solo exige elegir la persona (y su documento).
3. **Dado** los listados de personas y líderes y el tablero, **Cuando** se abren, **Entonces** no hay filtro, columna, caja ni bloque «Personas por red».
4. **Dado** una base con datos previos de redes, **Cuando** se aplica la actualización, **Entonces** no se pierde ninguna persona ni líder; solo desaparece el dato de red.

---

### Historia de usuario 3 - Designar líder con buscador autocompletado de personas activas (Prioridad: P1)

Al designar un líder, la persona se elige escribiendo en un buscador que **autocompleta** mientras se escribe (por nombre, apellido, documento o celular) y solo ofrece **personas activas** de la sede, sin pulsar «Buscar».

**Por qué esta prioridad**: Es una acción frecuente y hoy es lenta con muchas personas.

**Prueba independiente**: En «Designar líder», escribir parte de un nombre y comprobar que aparecen sugerencias de personas activas que aún no son líderes, elegir una y designarla.

**Escenarios de aceptación**:

1. **Dado** la pantalla de designar líder, **Cuando** se escriben 2 o más letras, **Entonces** aparecen en menos de 1 segundo sugerencias con nombre, documento y celular de personas activas que no son líderes.
2. **Dado** una persona inactiva o que ya es líder, **Cuando** se busca su nombre, **Entonces** no aparece entre las sugerencias.
3. **Dado** una persona sin documento, **Cuando** se elige, **Entonces** se avisa que debe completar el documento antes de designarla y se ofrece ir a editarla.
4. **Dado** un usuario de una sede, **Cuando** busca, **Entonces** solo ve personas de su ámbito.

---

### Historia de usuario 4 - Ver a quién lidera un líder (Prioridad: P2)

En la ficha de un líder aparece una **pequeña grilla «Personas a su cargo»** con nombre, estado, celular y célula de cada persona que lidera, con enlace a su ficha.

**Por qué esta prioridad**: Da el contexto de cuidado pastoral sin salir de la ficha.

**Prueba independiente**: Abrir un líder con personas a cargo y comprobar la grilla, su contador y el mensaje vacío cuando no tiene a nadie.

**Escenarios de aceptación**:

1. **Dado** un líder con personas a cargo, **Cuando** se abre su ficha, **Entonces** una grilla compacta muestra nombre (con enlace), estado, celular y célula de cada una, ordenadas por apellido.
2. **Dado** un líder sin personas a cargo, **Cuando** se abre su ficha, **Entonces** la grilla muestra «Aún no lidera a nadie».
3. **Dado** un líder con muchas personas a cargo, **Cuando** se abre su ficha, **Entonces** la grilla pagina o limita su altura sin alargar la página.
4. **Dado** un usuario con alcance limitado, **Cuando** abre la ficha de un líder, **Entonces** solo ve a las personas a cargo que puede ver.

---

### Historia de usuario 5 - Registrar integrantes de una célula, incluso creándolos al momento (Prioridad: P1)

En el formulario y la ficha de la célula se eligen los **integrantes** con un multiselect con búsqueda por nombre autocompletada, y se puede **crear una persona nueva** sin salir de la célula para añadirla de inmediato.

**Por qué esta prioridad**: Hoy una célula se crea pero no se pueden registrar sus integrantes, que es su razón de ser.

**Prueba independiente**: Crear una célula, añadir integrantes existentes buscando por nombre, crear una persona nueva desde la célula y comprobar que quedó como integrante.

**Escenarios de aceptación**:

1. **Dado** el formulario de crear o editar una célula, **Cuando** se escribe parte de un nombre en el campo de integrantes, **Entonces** se filtran y autocompletan las personas coincidentes y se pueden seleccionar varias, con un contador de seleccionadas.
2. **Dado** una célula con integrantes, **Cuando** se guarda, **Entonces** aparecen en su ficha y la lista de asistencia los incluye.
3. **Dado** que la persona buscada no existe, **Cuando** se elige «Crear persona nueva» desde la célula, **Entonces** se registra con nombres, apellidos y celular (como mínimo), queda en estado «célula» y se agrega a la célula.
4. **Dado** una persona ya integrante, **Cuando** se quita de la selección y se guarda, **Entonces** deja de ser integrante sin borrarse como persona.
5. **Dado** una persona de otra sede, **Cuando** se busca, **Entonces** no aparece.

---

### Historia de usuario 6 - Estado «Célula» para quienes aún no son parte de la iglesia (Prioridad: P1)

Al crear o editar una persona, el estado puede ser **«En célula»**: son personas que asisten a una célula y aún no son parte de la iglesia, pero se quiere conocerlas y darles seguimiento. Se pueden filtrar y distinguir en los listados.

**Por qué esta prioridad**: Permite registrar a los invitados de las células sin forzarlos a ser visitantes ni miembros.

**Prueba independiente**: Crear una persona con estado «En célula», verificar que no exige documento, que se filtra por ese estado y que puede ser integrante de una célula.

**Escenarios de aceptación**:

1. **Dado** el formulario de persona, **Cuando** se abre el campo estado, **Entonces** existe la opción «En célula» junto a las demás.
2. **Dado** una persona «En célula», **Cuando** se guarda con solo nombres, apellidos y un medio de contacto, **Entonces** se acepta sin documento.
3. **Dado** el listado de personas, **Cuando** se filtra por estado «En célula», **Entonces** solo aparecen esas personas, con una insignia propia.
4. **Dado** una persona «En célula», **Cuando** más adelante se vuelve miembro, **Entonces** se cambia su estado desde la edición sin perder su historial ni su pertenencia a la célula.

---

### Historia de usuario 7 - Ministerios dentro de Congregación con selección de integrantes autocompletada (Prioridad: P2)

«Ministerios» se muestra en el grupo **Congregación** del menú. Al elegir los integrantes de un ministerio se usa el mismo multiselect con búsqueda por nombre autocompletada (el mismo componente que en células).

**Por qué esta prioridad**: Ordena el menú y quita el trabajo tedioso de buscar uno a uno.

**Prueba independiente**: Ver el menú con Ministerios bajo Congregación y, en un ministerio con muchas personas disponibles, encontrar y seleccionar integrantes escribiendo parte del nombre.

**Escenarios de aceptación**:

1. **Dado** cualquier usuario, **Cuando** abre el menú, **Entonces** «Ministerios» aparece dentro de «Congregación» y ya no en «Actividades».
2. **Dado** el formulario de un ministerio, **Cuando** se escribe parte de un nombre, **Entonces** se filtran las personas por nombre y se pueden marcar varias con contador de seleccionadas.
3. **Dado** un ministerio con integrantes, **Cuando** se edita, **Entonces** los integrantes actuales aparecen ya seleccionados y se pueden quitar o añadir más.
4. **Dado** 500 personas disponibles, **Cuando** se busca por nombre, **Entonces** el filtro responde de inmediato y no hay que recorrer la lista completa.

---

### Casos límite

- Personas con el mismo nombre: las sugerencias muestran también documento o celular para distinguirlas.
- Una persona «En célula» no debe aparecer como «sin líder» pendiente de asignar por el solo hecho de no tener líder directo.
- Una persona integrante de una célula cuyo líder directo es otro: la grilla «Personas a su cargo» del líder de la célula NO la incluye (solo lista a quienes tienen a ese líder como líder directo); la célula sí la muestra entre sus integrantes.
- Eliminar el concepto Red cuando hay líderes con red asignada: los líderes siguen siendo líderes.
- Crear una persona desde la célula con un celular o correo ya registrado: se avisa de la posible duplicidad con la misma regla actual.
- Una persona «inactiva» que ya es integrante de una célula o ministerio: sigue apareciendo en su historial pero no se ofrece para nuevas selecciones.
- Un multiselect con cientos de personas seleccionadas: debe seguir siendo usable (contador, quitar todas, desplazamiento).
- Personas de otras sedes nunca aparecen en ninguna búsqueda ni selección.

## Requisitos *(obligatorio)*

### Requisitos funcionales

- **FR-001**: El formulario de crear y editar persona DEBE tener una única tarjeta «Datos personales» con nombres, apellidos, fecha de nacimiento, celular, correo, estado, tipo de documento y número de documento, y NO DEBE tener las tarjetas «Contacto» ni «Estado y documento».
- **FR-002**: Las reglas existentes de personas (celular o correo obligatorio al crear, documento obligatorio según el estado, aviso de duplicados) DEBEN mantenerse sin cambios, salvo el estado nuevo de FR-006.
- **FR-003**: La ficha de la persona DEBE mostrar contacto, estado y documento dentro del bloque de datos personales.
- **FR-004**: El sistema DEBE eliminar por completo el concepto **Red**: menú, rutas, catálogo, campos y selectores en personas y líderes, filtros, columnas, cajas y bloques del tablero, ayudas de términos y documentación; los datos previos de redes DEBEN retirarse sin perder personas ni líderes.
- **FR-005**: La pantalla de designar líder DEBE ofrecer un buscador con **autocompletado** (a partir de 2 letras) por nombre, apellido, documento o celular, que ofrezca solo **personas activas** (no inactivas) de la sede que aún no sean líderes, sin necesidad de pulsar «Buscar».
- **FR-006**: El estado de persona DEBE incluir **«En célula»**: persona que asiste a una célula y aún no es parte de la iglesia; no exige documento, se puede filtrar y tiene insignia propia.
- **FR-007**: La ficha de un líder DEBE mostrar una grilla compacta «Personas a su cargo» con nombre (enlace), estado, celular y célula, ordenada por apellido, con estado vacío y límite de altura o paginación, respetando el alcance del usuario.
- **FR-008**: El formulario y la ficha de la célula DEBEN permitir registrar y quitar integrantes con un multiselect con búsqueda por nombre autocompletada y contador de seleccionados.
- **FR-009**: Desde la célula DEBE poder **crearse una persona nueva** (nombres, apellidos y un medio de contacto como mínimo) que quede en estado «En célula» y como integrante de esa célula, aplicando el aviso de duplicados existente.
- **FR-010**: Ser integrante de una célula DEBE ser independiente del líder directo: cualquier persona activa de la sede puede añadirse a una célula, tenga como líder directo a otra persona o a nadie; añadirla a la célula NO DEBE cambiar su líder directo, y la regla anterior que exigía que coincidiera con el líder de la célula se elimina.
- **FR-011**: «Ministerios» DEBE aparecer en el grupo «Congregación» del menú y no en «Actividades».
- **FR-012**: La selección de integrantes de ministerios y de células DEBE usar el mismo componente de multiselección con búsqueda por nombre autocompletada, que ofrezca solo personas activas de la sede y mantenga ya seleccionados a los integrantes actuales.
- **FR-013**: Las búsquedas y selecciones DEBEN respetar el aislamiento por sede y los permisos existentes; ninguna persona de otra sede puede aparecer ni asignarse.
- **FR-014**: Estos cambios NO DEBEN eliminar personas, líderes, células ni ministerios existentes; las personas ya integrantes conservan su pertenencia.

### Entidades clave

- **Persona**: se amplía el conjunto de estados con «En célula»; deja de tener red.
- **Célula**: grupo con líder, co-líder, reunión e **integrantes** (personas), que ahora se pueden registrar y crear desde la propia célula; la pertenencia no depende del líder directo de cada persona.
- **Ministerio**: grupo con encargado e integrantes, ahora en el menú de Congregación.
- **Red**: concepto que se elimina.

## Criterios de éxito *(obligatorio)*

### Resultados medibles

- **SC-001**: Registrar una persona con celular, correo, estado y documento requiere abrir una sola tarjeta y reduce el desplazamiento de página en al menos 40 % frente al formulario actual.
- **SC-002**: No queda ninguna mención, campo, filtro, columna, caja ni ruta de «Red» en la aplicación (verificado recorriendo todas las pantallas y rutas).
- **SC-003**: Designar un líder entre 500 personas toma menos de 15 segundos y las sugerencias aparecen en menos de 1 segundo tras escribir.
- **SC-004**: En el 100 % de las fichas de líder aparece la grilla «Personas a su cargo» con los mismos datos que el listado de personas.
- **SC-005**: Registrar 10 integrantes en una célula (incluyendo 2 nuevos creados al momento) toma menos de 3 minutos.
- **SC-006**: En el 100 % de los multiselect de integrantes, escribir 3 letras reduce la lista a las coincidencias de nombre en menos de 1 segundo, con 500 personas disponibles.
- **SC-007**: Ninguna persona, líder, célula o ministerio existente se pierde tras la actualización (conteos antes y después iguales).
- **SC-008**: Ninguna prueba automatizada existente deja de pasar, salvo las que dependían de redes o de las tarjetas eliminadas.

## Supuestos

- «Personas activas» significa personas cuyo estado **no es inactivo** (incluye visitante, nuevo creyente, miembro activo y «En célula»).
- El estado nuevo se llama «En célula»; cuenta como activo, no exige documento y no obliga a tener líder directo.
- Las personas «En célula» no se listan en «Sin líder asignado» solo por no tener líder directo, porque su vínculo es la célula; el resto de estados sin líder directo siguen apareciendo ahí como hoy.
- La eliminación de «Red» sigue el mismo criterio que la de «Zona»: se retira del sistema y de los datos, sin conservar el dato.
- Al crear una persona desde la célula bastan nombres, apellidos y un medio de contacto; el resto se puede completar después desde su edición.
- Los componentes de formulario modernos y el estilo AdminLTE ya existentes se reutilizan; no se cambian las reglas de permisos ni el aislamiento por sede.
- Los cambios de estructura de la información (quitar red, nuevo estado) se aplican con una actualización de la base que conserva todas las personas y no pierde datos distintos de la red.
