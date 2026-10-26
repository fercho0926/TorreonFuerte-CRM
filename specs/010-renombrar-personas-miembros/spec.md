# Especificación de Funcionalidad: Renombrar "Personas" a "Miembros"

**Rama de la funcionalidad**: `010-renombrar-personas-miembros`

**Creado**: 2026-10-22

**Estado**: Borrador

**Entrada**: Descripción del usuario: "vamos a cambiar el nombre de personas por miembros, en nuestra app, quiero que todo tenga el nombre de miembros en vez de personas, puesto que miembros es más lenguaje de iglesia"

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Ver "Miembros" en toda la interfaz (Prioridad: P1)

Un usuario de la iglesia (SuperAdmin, Admin, Pastor o Líder) navega por la aplicación y en todas partes encuentra el término "Miembros" (y "Miembro" en singular) en lugar de "Personas"/"Persona": menú lateral, títulos de pantalla, migas de pan, encabezados de grillas, botones, formularios, etiquetas, mensajes de confirmación, errores de validación, estados vacíos y exportaciones.

**Por qué esta prioridad**: Es el núcleo de la solicitud: el lenguaje de la aplicación debe sentirse propio de una iglesia. Sin esto la funcionalidad no entrega valor.

**Prueba Independiente**: Recorrer cada pantalla de la aplicación (escritorio y celular) y buscar el texto "persona"/"personas" visible; no debe aparecer ninguna ocurrencia que se refiera a los miembros de la iglesia.

**Escenarios de Aceptación**:

1. **Dado** un usuario autenticado, **Cuando** abre el menú lateral, **Entonces** la opción antes llamada "Personas" se muestra como "Miembros".
2. **Dado** el listado de miembros, **Cuando** el usuario lo abre, **Entonces** el título, los botones ("Nuevo miembro", "Editar miembro", etc.), los encabezados de la grilla y el mensaje de "sin resultados" usan "miembro(s)" con concordancia de género y número correcta.
3. **Dado** un formulario de miembro con un error de validación, **Cuando** el usuario envía datos inválidos, **Entonces** los mensajes de error y los nombres de campo usan el vocabulario "miembro".
4. **Dado** que el usuario exporta el listado de miembros, **Cuando** abre el archivo descargado, **Entonces** el nombre del archivo y sus títulos usan "miembros".

---

### Historia de Usuario 2 - Direcciones web y enlaces con "miembros" sin romper los antiguos (Prioridad: P2)

Las direcciones web visibles (por ejemplo la del listado y la ficha de un miembro) pasan a usar "miembros" en lugar de "personas". Los enlaces antiguos que ya existen (marcadores del navegador, mensajes de WhatsApp enviados, accesos directos de la app instalada en el celular) siguen funcionando y llevan al lugar correcto.

**Por qué esta prioridad**: Da coherencia al lenguaje también en la barra de direcciones, pero no debe romper nada que los usuarios ya tengan guardado.

**Prueba Independiente**: Abrir una dirección antigua con "personas" y comprobar que redirige a la dirección equivalente con "miembros"; abrir la nueva y comprobar que carga la misma pantalla.

**Escenarios de Aceptación**:

1. **Dado** un marcador a la dirección antigua del listado, **Cuando** el usuario lo abre, **Entonces** llega al listado de miembros (dirección nueva) sin ver un error.
2. **Dado** la dirección antigua de la ficha de un miembro concreto, **Cuando** se abre, **Entonces** se muestra la ficha de ese mismo miembro.
3. **Dado** un usuario sin permiso sobre un miembro (otra sede), **Cuando** abre cualquiera de las dos direcciones, **Entonces** se aplican las mismas restricciones de acceso de siempre.

---

### Historia de Usuario 3 - Datos, permisos y funcionamiento intactos (Prioridad: P1)

Tras el cambio, toda la información existente (miembros, historial de cambios, traslados, células, ministerios, eventos, inscripciones, registros por QR, usuarios vinculados) permanece completa y las reglas de aislamiento por sede y de permisos por rol se comportan exactamente igual que antes.

**Por qué esta prioridad**: Un cambio de nombre no puede costar datos ni abrir brechas de acceso entre sedes.

**Prueba Independiente**: Comparar conteos y contenido de registros antes y después del cambio, y ejecutar los flujos principales (crear, editar, inactivar, trasladar, inscribir a un evento, registro por QR, inicio de sesión) con cada rol.

**Escenarios de Aceptación**:

1. **Dado** la base de datos con N miembros antes del cambio, **Cuando** se aplica el cambio, **Entonces** existen los mismos N miembros con idénticos datos y relaciones.
2. **Dado** un Líder de una sede, **Cuando** consulta miembros, **Entonces** solo ve los que le corresponden, igual que antes.
3. **Dado** el flujo de registro público por QR o de claves por WhatsApp, **Cuando** un visitante lo completa, **Entonces** funciona igual y los textos que recibe dicen "miembro".
4. **Dado** las pruebas automatizadas existentes, **Cuando** se ejecutan, **Entonces** pasan usando la nueva terminología.

---

### Casos Límite

- Contenido escrito por los usuarios (nombres, notas, observaciones) que contenga la palabra "persona": NO se altera.
- Concordancia gramatical: "Personas sin líder" → "Miembros sin líder"; "Persona inactiva" → "Miembro inactivo"; "una persona" → "un miembro"; "las personas" → "los miembros"; "Ninguna persona" → "Ningún miembro". No basta con un reemplazo literal de la palabra.
- Historial de cambios ya registrado que contenga la palabra "persona" en descripciones guardadas: se muestra con la terminología nueva sin perder el dato (ver Supuestos).
- Dirección antigua con parámetros de búsqueda o filtros (por ejemplo `?q=...`): los parámetros se conservan al redirigir.
- Aplicación instalada en el celular o contenido en caché del navegador con el nombre anterior: tras actualizar, se ve la nueva terminología sin intervención del usuario.
- Mensajes enviados (WhatsApp, correos) o documentos ya emitidos con el término antiguo: no se modifican retroactivamente.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **FR-001**: El sistema DEBE mostrar "Miembro(s)" en lugar de "Persona(s)" en todo texto visible para el usuario autenticado: menús, títulos, migas de pan, botones, enlaces, etiquetas, encabezados de grillas, filtros, placeholders, tooltips, estados vacíos y mensajes de éxito/error/confirmación.
- **FR-002**: El sistema DEBE aplicar la concordancia correcta de género y número en español en cada frase afectada (p. ej. "Miembro inactivo", "Ningún miembro", "Miembros sin líder").
- **FR-003**: El sistema DEBE usar "miembro(s)" en los mensajes de validación y en los nombres de campo mostrados al usuario.
- **FR-004**: El sistema DEBE usar "miembros" en las pantallas públicas (registro por QR, registro público, recuperación de claves) y en los mensajes enviados al usuario (WhatsApp, correo).
- **FR-005**: El sistema DEBE usar "miembros" en los nombres de archivos exportados y en los títulos y encabezados dentro de ellos.
- **FR-006**: Las direcciones web del módulo DEBEN usar "miembros" en lugar de "personas".
- **FR-007**: Las direcciones web antiguas con "personas" DEBEN seguir funcionando, redirigiendo de forma permanente a su equivalente con "miembros" y conservando identificadores y parámetros de consulta.
- **FR-008**: El sistema DEBE conservar íntegros todos los datos existentes y sus relaciones (células, ministerios, eventos, inscripciones, pagos, traslados, historial, usuarios vinculados, registros QR).
- **FR-009**: El sistema DEBE conservar sin cambios el aislamiento por sede y los permisos por rol (SuperAdmin, Admin, Pastor, Líder).
- **FR-010**: El sistema NO DEBE modificar el contenido que los usuarios hayan escrito (nombres, notas, observaciones, etc.).
- **FR-011**: El sistema DEBE mantener la apariencia y comportamiento en celular (reglas de diseño móvil, app instalable) sin degradación por la longitud de los nuevos textos.
- **FR-012**: La documentación del proyecto (specs previos, memoria del proyecto, guías de uso) y el código DEBEN reflejar la terminología "miembro" de forma consistente para evitar que coexistan dos vocabularios.
- **FR-013**: Tras el cambio, una búsqueda de la palabra "persona(s)" en los textos visibles de la aplicación NO DEBE devolver resultados que se refieran a miembros de la iglesia.

### Entidades Clave

- **Miembro** (antes "Persona"): integrante de la iglesia registrado en una sede; tiene datos personales, estado (activo/inactivo), pertenencia a células y ministerios, liderazgo, historial de cambios, traslados entre sedes e inscripciones a eventos. Solo cambia su nombre, no su significado ni sus relaciones.
- **Historial de cambios del miembro**, **Inscripción de miembro a evento**, **Registro por QR**, **Usuario vinculado a un miembro**: elementos relacionados cuyo nombre visible pasa a hacer referencia a "miembro".

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **SC-001**: El 100 % de las pantallas del producto (revisadas pantalla por pantalla en escritorio y celular) no muestran la palabra "persona(s)" referida a miembros.
- **SC-002**: El 100 % de las direcciones antiguas con "personas" probadas (listado, ficha, edición, exportación, traslados) llegan a la pantalla correcta sin error.
- **SC-003**: Cero pérdida de datos: los conteos de miembros y de todas sus relaciones coinciden antes y después del cambio.
- **SC-004**: Todas las pruebas automatizadas del proyecto (incluida la suite de pruebas móviles) pasan tras el cambio.
- **SC-005**: Ningún usuario necesita volver a iniciar sesión, reinstalar la app ni reaprender flujos; el cambio se percibe únicamente como un cambio de vocabulario.
- **SC-006**: Un revisor externo, sin conocer el cambio, no encuentra frases con error de concordancia (género/número) en una muestra de al menos 20 pantallas y mensajes.

## Supuestos

- "Todo" incluye interfaz, mensajes, exportaciones, direcciones web, documentación y nomenclatura interna del código y la base de datos; la decisión técnica de cómo migrar la nomenclatura interna (y si en una o varias etapas) se toma en el plan, siempre que se cumplan FR-008 y FR-009.
- El singular es "Miembro" y el plural "Miembros"; no se introduce un término distinto para hombres y mujeres.
- Los términos "Líder", "Pastor", "Célula", "Ministerio", "Sede", etc. no cambian.
- El historial de cambios ya guardado con texto "persona" puede mostrarse con la terminología nueva en pantalla sin reescribir el dato original si ello complica la migración.
- Los mensajes y documentos ya enviados o emitidos no se modifican retroactivamente.
- Se mantiene el español como único idioma de la interfaz.
- Dependencia: las funcionalidades 001 a 009 existentes (grillas estándar, diseño móvil, registro QR, multi-sede) deben seguir cumpliendo sus especificaciones con la nueva terminología.
