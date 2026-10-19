# Especificación de Funcionalidad: Grillas uniformes con ordenamiento, filtros, paginación y exportación

**Rama de la funcionalidad**: `007-grillas-datatables`

**Creado**: 2026-10-19

**Estado**: Borrador

**Entrada**: Descripción del usuario: "aplica datatables así como en persona para todas las grillas, y de ahora en adelante las grillas deben ser así"

## Contexto

La grilla de personas ya se rediseñó (estilo PrimeNG con DataTables): ordenar por columna, filtros por columna, filas por página, paginador y exportación a CSV. El usuario quiere que **todas las demás grillas de listado** del sistema se comporten y se vean igual, y que esto quede como **regla permanente** para cualquier grilla futura.

Listados que hoy son tablas simples y deben migrarse: líderes, células, encuentros, eventos, ministerios, sedes, personas sin líder, usuarios y solicitudes de clave. Las tablas pequeñas dentro de una ficha (por ejemplo «Personas a su cargo» en un líder o las asistencias de una célula) no son listados principales y quedan fuera, salvo que se indique lo contrario.

## Clarificaciones

### Sesión 2026-10-19

- Q: ¿Los listados de usuarios y de solicitudes de clave también exportan CSV? → A: Solo si quien los ve es administrador; el resto de grillas exporta con el alcance que ve cada usuario.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Todos los listados principales se ven y se comportan igual (Prioridad: P1)

Como usuario, al entrar a cualquiera de los listados principales (líderes, células, encuentros, eventos, ministerios, sedes, sin líder, usuarios, solicitudes de clave) encuentro la misma grilla que en personas: cabecera gris, búsqueda general, filtros por columna, ordenamiento al pulsar el encabezado, selector de filas por página y paginador.

**Por qué esta prioridad**: es el pedido central; la uniformidad evita que cada módulo se sienta distinto.

**Prueba Independiente**: abrir cada listado y comprobar que ofrece ordenar, filtrar por columna, cambiar filas por página y paginar, mostrando solo lo que el usuario puede ver por su rol y sede.

**Escenarios de Aceptación**:

1. **Dado** que estoy en un listado con más filas que la página, **cuando** cambio las filas por página a 10, 25, 50 o 100, **entonces** la grilla muestra esa cantidad y el paginador se ajusta.
2. **Dado** un listado, **cuando** pulso el encabezado de una columna ordenable, **entonces** se ordena ascendente y al pulsarlo otra vez descendente, en todo el conjunto y no solo en la página visible.
3. **Dado** un listado, **cuando** escribo en el filtro de una columna o elijo un valor en un filtro de lista, **entonces** la grilla muestra solo las filas que coinciden y el contador de resultados se actualiza.
4. **Dado** un filtro sin coincidencias, **cuando** se aplica, **entonces** veo el mensaje de estado vacío del listado.
5. **Dado** un usuario con alcance limitado (por sede o por líder), **cuando** usa la grilla, **entonces** nunca ve filas fuera de su alcance, ni por filtros ni por exportación.

---

### Historia de Usuario 2 - Exportar a CSV desde cada grilla (Prioridad: P2)

Como usuario, en cada listado puedo exportar a un archivo que abre Excel lo que estoy viendo (con mis filtros y orden), no solo la página actual.

**Por qué esta prioridad**: complementa la uniformidad; es valioso pero no bloquea el uso.

**Prueba Independiente**: aplicar un filtro, exportar, y comprobar que el archivo contiene todas las filas filtradas y solo esas, con tildes correctas.

**Escenarios de Aceptación**:

1. **Dado** un listado filtrado, **cuando** pulso «Exportar CSV», **entonces** descargo un archivo con todas las filas que coinciden, en el orden actual.
2. **Dado** una celda cuyo texto empieza con `=`, `+`, `-` o `@`, **cuando** se exporta, **entonces** no se interpreta como fórmula en Excel.
3. **Dado** un usuario sin permiso sobre ciertos datos, **cuando** exporta, **entonces** el archivo respeta el mismo alcance que la grilla.
4. **Dado** el listado de usuarios o de solicitudes de clave, **cuando** lo ve un usuario que no es administrador, **entonces** no aparece el botón «Exportar CSV» y pedir la exportación directamente es rechazado.
5. **Dado** el mismo listado, **cuando** lo ve un administrador, **entonces** puede exportar con los filtros y el orden actuales.

---

### Historia de Usuario 3 - Regla permanente para grillas futuras (Prioridad: P2)

Como responsable del producto, quiero que la forma de construir una grilla quede definida y reutilizable, de modo que cualquier listado nuevo salga con este comportamiento sin rediseñarlo.

**Por qué esta prioridad**: asegura que «de ahora en adelante las grillas deben ser así».

**Prueba Independiente**: revisar que existe una guía documentada y una prueba automática que falla si un listado principal nuevo usa una tabla simple en lugar de la grilla estándar.

**Escenarios de Aceptación**:

1. **Dado** un desarrollador que crea un listado nuevo, **cuando** sigue la guía, **entonces** obtiene ordenar, filtrar, paginar y exportar con poco código específico del módulo.
2. **Dado** un listado principal que no usa la grilla estándar, **cuando** se corren las pruebas, **entonces** una prueba falla señalando el listado.
3. **Dado** la constitución del proyecto, **cuando** se consulta, **entonces** incluye la regla de grillas estándar.

---

### Historia de Usuario 4 - Funciona en teléfono y sin depender de que todo cargue (Prioridad: P3)

Como usuario en un celular, la grilla se adapta: las columnas menos importantes se pliegan y se pueden desplegar por fila; las acciones siguen disponibles. Si el script no carga, el listado sigue mostrando la primera página con su paginación.

**Por qué esta prioridad**: calidad y accesibilidad; ya existe como patrón en personas.

**Prueba Independiente**: abrir cada listado en ancho de teléfono y con scripts deshabilitados.

**Escenarios de Aceptación**:

1. **Dado** una pantalla estrecha, **cuando** abro un listado, **entonces** no hay desplazamiento horizontal de la página y puedo ver las columnas ocultas por fila.
2. **Dado** que los scripts no cargan, **cuando** abro un listado, **entonces** veo la primera página de filas y puedo avanzar con el paginador del servidor.

---

### Casos Límite

- Listado vacío: se muestra el estado vacío con icono y mensaje propio del módulo, no una tabla en blanco.
- Error de red al cargar datos: aviso claro y reintento posible, sin perder los filtros escritos.
- Columnas con acciones (ver, editar, inactivar, eliminar con confirmación): deben funcionar también en filas cargadas dinámicamente y en filas plegadas en teléfono.
- Columna «Sede» solo visible para quien ve todas las sedes.
- Un no administrador intenta exportar usuarios o solicitudes de clave por dirección directa: se rechaza con un mensaje de permiso.
- Listados con pocas filas (por ejemplo sedes): los controles siguen siendo coherentes sin estorbar.
- Cantidad de filas por página fuera de los valores permitidos: se usa el valor por defecto.
- Exportación grande: debe completarse sin agotar el tiempo ni la memoria de una petición normal.
- Distintos tipos de columna: texto, estado con color, fecha, número, conteos y enlaces deben ordenar y filtrar de forma coherente.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El sistema DEBE presentar los listados principales de líderes, células, encuentros, eventos, ministerios, sedes, personas sin líder, usuarios y solicitudes de clave con la misma grilla estándar que personas.
- **RF-002**: Cada grilla DEBE permitir ordenar por columna en todo el conjunto de datos, no solo la página visible.
- **RF-003**: Cada grilla DEBE ofrecer un filtro por cada columna filtrable (texto libre o lista de valores según el tipo) y una búsqueda general.
- **RF-004**: Cada grilla DEBE permitir elegir 10, 25, 50 o 100 filas por página, con 25 por defecto, y mostrar un paginador y un contador de resultados.
- **RF-005**: Cada grilla DEBE ofrecer «Exportar CSV» con todas las filas que coinciden con los filtros y el orden actuales, legible en Excel con tildes correctas y sin ejecutar fórmulas.
- **RF-005b**: La exportación de los listados de usuarios y de solicitudes de clave DEBE estar disponible solo para administradores, tanto en la interfaz como en la petición directa.
- **RF-006**: Cada grilla DEBE respetar el alcance del usuario (rol, sede, líder) en la visualización, el filtrado, el ordenamiento y la exportación.
- **RF-007**: Cada grilla DEBE conservar las acciones por fila existentes con sus permisos y confirmaciones, incluso en filas cargadas dinámicamente.
- **RF-008**: Cada grilla DEBE adaptarse a teléfono plegando columnas secundarias, sin desplazamiento horizontal de la página.
- **RF-009**: Cada listado DEBE seguir mostrando la primera página con su paginación del servidor si los scripts no cargan.
- **RF-010**: Cada grilla DEBE mostrar un estado vacío con mensaje propio del módulo y un aviso de error con reintento si falla la carga.
- **RF-011**: Los filtros y la búsqueda existentes de cada listado (por ejemplo por estado, tipo o sede) DEBEN seguir disponibles y sus enlaces con parámetros en la dirección DEBEN seguir funcionando.
- **RF-012**: La columna «Sede» DEBE aparecer solo para quien ve todas las sedes.
- **RF-013**: El sistema DEBE contar con una definición única y reutilizable de grilla estándar, y una guía breve para crear grillas nuevas.
- **RF-014**: Una prueba automática DEBE fallar si un listado principal usa una tabla simple en lugar de la grilla estándar.
- **RF-015**: La constitución del proyecto DEBE incluir la regla de que todo listado principal nuevo usa la grilla estándar.
- **RF-016**: Las tablas internas pequeñas dentro de una ficha (por ejemplo «Personas a su cargo») NO se migran; mantienen su presentación actual.
- **RF-017**: La grilla de personas existente DEBE pasar a usar la misma definición reutilizable sin cambiar su comportamiento visible.

### Entidades Clave *(incluir si la funcionalidad involucra datos)*

- **Grilla estándar**: definición de un listado: columnas (título, ordenable, filtro de texto o de lista, prioridad en teléfono), búsqueda general, filas por página, acciones por fila y columnas exportables.
- **Estado de la grilla**: filtros, orden, página y filas por página actuales; se usa tanto para mostrar como para exportar.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: El 100 % de los listados principales (9) ofrecen ordenar, filtrar por columna, filas por página, paginación y exportar.
- **CE-002**: Un usuario encuentra una fila concreta en un listado de 500 filas en menos de 10 segundos usando filtros.
- **CE-003**: Al cambiar un filtro u orden, el listado se actualiza en menos de 1 segundo percibido para listados de hasta 5 000 filas.
- **CE-004**: Cero filas fuera del alcance del usuario aparecen en la grilla o en la exportación en las pruebas de aislamiento por sede y por rol.
- **CE-005**: Agregar un listado nuevo con la grilla estándar requiere definir solo sus columnas y datos, sin reescribir el comportamiento de ordenar, filtrar, paginar y exportar.
- **CE-005b**: Ningún usuario que no sea administrador puede descargar el listado de usuarios ni el de solicitudes de clave en las pruebas de permisos.
- **CE-006**: Todas las pruebas automáticas existentes de cada módulo siguen pasando tras la migración.

## Supuestos

- «Todas las grillas» se interpreta como los listados principales (índices de cada módulo); las tablas pequeñas dentro de fichas quedan fuera.
- La grilla de personas actual es el patrón visual y funcional de referencia.
- Se reutilizan los mismos valores de filas por página (10, 25, 50, 100) y el mismo formato de exportación CSV de personas.
- Los listados con muy pocas filas (como sedes) también adoptan la grilla por uniformidad.
- Cada listado conserva sus permisos, filtros y acciones actuales; no se agregan ni quitan datos mostrados, salvo reordenar columnas para acomodar filtros.
- Dependencias: la grilla de personas ya entregada, la estructura de permisos y alcance por sede y rol, y el diseño de interfaz vigente.
