# Especificación de Funcionalidad: Auditoría QA y mejoras de UX/UI del CRM

**Rama de la funcionalidad**: `018-auditoria-qa-crm`

**Creado**: 2026-11-10

**Estado**: Borrador

**Entrada**: Descripción del usuario: "actua como un QA e ingresa a mi aplicacion https://crm.torreonfuerte.org.co/ con el user admin@example.com pass = password revisa todos los scenarios prueba las rutas, la usabilidad, la funcionalidad, la claraidad, prueba toda mi aplicacion y al final saquems un resumen de posiblems mejoras, se ui ux de usabilidad, de claridad, revisa todo para luego crear task y solucionarlos"

## Contexto de la auditoría

Se realizó una sesión de pruebas manuales (QA) en vivo contra el ambiente de producción `https://crm.torreonfuerte.org.co/`, autenticado como `admin@example.com`. Se recorrieron las siguientes rutas: `/dashboard`, `/personas` (Miembros, listado/crear/editar/detalle/histórico), `/lideres`, `/sin-lider`, `/celulas`, `/niveles`, `/ministerios`, `/eventos` (incluyendo detalle, abonos y registro por QR), `/encuentros`, `/sedes`, `/users`, `/solicitudes-clave`, `/control-cambios`, el flujo público de autoservicio `/registro/{token}` y la pantalla de `/login`. Se probó el selector de sede activa ("Todas las sedes" vs. sede específica), la creación y eliminación de un miembro de prueba (limpiado al finalizar), el envío de formularios con datos inválidos, y casos de error intencionales (contraseña incorrecta, confirmación de registro QR).

Esta especificación documenta los hallazgos como mejoras a implementar, agrupadas por prioridad, para que puedan convertirse en tareas concretas.

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Cerrar la exposición de información sensible en producción (Prioridad: P1)

Como responsable del CRM, necesito que ningún visitante anónimo pueda ver trazas técnicas internas (rutas de archivos, versión de Laravel/PHP, encabezados HTTP y cookies de sesión) ni credenciales de acceso, para que la aplicación en producción no filtre información que facilite un ataque.

**Por qué esta prioridad**: Es un hallazgo de seguridad crítico y explotable por cualquier persona sin autenticar, ya sea un visitante del formulario público de registro por QR o alguien que simplemente cometa un error al iniciar sesión. Debe resolverse antes que cualquier mejora de usabilidad.

**Prueba independiente**: Se puede verificar provocando una solicitud con un método HTTP no soportado (por ejemplo, `GET` a una ruta que solo acepta `POST`, como `/login` o `/registro/{token}/buscar`) y confirmando que el sistema responde con una página de error genérica (p. ej. 404/405 estándar de la aplicación), sin trazas de pila, rutas de servidor ni encabezados de la solicitud.

**Escenarios de Aceptación**:

1. **Dado** que el modo de depuración está desactivado en producción, **Cuando** ocurre un error no controlado (por ejemplo, método HTTP no permitido) en cualquier ruta, pública o autenticada, **Entonces** el usuario ve una página de error genérica y amigable, sin información técnica del servidor.
2. **Dado** un visitante anónimo abriendo el enlace o código QR de registro a un evento, **Cuando** interactúa con el formulario (incluyendo el selector de fecha) y lo envía, **Entonces** en ningún caso se le muestra una traza de depuración, encabezados de la solicitud ni el valor de cookies de sesión.
3. **Dado** que la pantalla de inicio de sesión es pública, **Cuando** se produce cualquier error en el envío del formulario, **Entonces** el sistema nunca expone detalles internos del framework.

---

### Historia de Usuario 2 - No publicar credenciales de acceso en la pantalla de login (Prioridad: P1)

Como responsable del CRM, necesito que la pantalla pública de inicio de sesión no muestre en texto plano usuario y contraseña de una cuenta administradora, para evitar accesos no autorizados al panel de administración.

**Por qué esta prioridad**: El texto "Demo: admin@example.com / password" es visible para cualquier persona que abra la URL pública de login, sin necesidad de autenticarse, y corresponde a una cuenta con rol de Administrador. Es un riesgo de seguridad crítico e inmediato.

**Prueba independiente**: Se puede verificar abriendo `/login` sin sesión iniciada y confirmando que no aparece ningún texto con credenciales de demostración ni de ningún otro usuario.

**Escenarios de Aceptación**:

1. **Dado** un visitante no autenticado, **Cuando** abre la pantalla de inicio de sesión, **Entonces** no se muestra ninguna credencial (usuario ni contraseña) en la página.
2. **Dado** que el entorno de pruebas/demo necesita credenciales de ejemplo, **Cuando** se requiera documentarlas, **Entonces** deben entregarse únicamente por un canal interno (documentación del equipo), nunca renderizadas en la interfaz pública.

---

### Historia de Usuario 3 - Corregir el flujo de confirmación de registro por QR (Prioridad: P2)

Como miembro o visitante que llega a un evento y escanea el código QR, necesito poder completar mi confirmación de asistencia sin errores de validación confusos ni callejones sin salida, para que mi inscripción quede registrada correctamente.

**Por qué esta prioridad**: El flujo de autoservicio por QR es la cara pública del sistema durante los eventos (uso no asistido, muchas veces desde el celular de la persona). Un error aquí se traduce en inscripciones fallidas o personas frustradas en la puerta del evento, y agrava el hallazgo P1 cuando el error expone la página de depuración.

**Prueba independiente**: Se puede probar end-to-end con el enlace de registro de un evento activo: buscar por celular un miembro existente sin fecha de nacimiento registrada, completar el formulario de confirmación usando el selector de calendario, y enviar.

**Escenarios de Aceptación**:

1. **Dado** que el visitante selecciona su fecha de nacimiento usando el calendario desplegable, **Cuando** el campo queda completado correctamente, **Entonces** el mensaje "Este campo es obligatorio." desaparece de inmediato (hoy permanece visible aunque el campo ya es válido).
2. **Dado** un miembro ya existente en la base de datos que nunca tuvo registrada su fecha de nacimiento (porque el formulario interno de "Crear miembro" la marca como opcional), **Cuando** intenta confirmar su asistencia por QR, **Entonces** el sistema debe tener un criterio consistente: o bien la fecha de nacimiento es opcional también aquí, o bien se explica claramente por qué ahora es obligatoria y se facilita completarla sin fricción.
3. **Dado** que el visitante envía el formulario de confirmación, **Cuando** la solicitud se procesa, **Entonces** siempre se usa el método HTTP correcto (`POST`) y nunca se produce una solicitud `GET` accidental hacia una ruta que no la soporta.

---

### Historia de Usuario 4 - Pulir retroalimentación visual y estados de carga (Prioridad: P3)

Como usuario administrativo (pastor, líder o administrador de sede), necesito que los mensajes de confirmación y los estados de carga de las listas sean claros y no se superpongan con otros elementos de la interfaz, para confiar en que mis acciones (crear, eliminar, cambiar de sede) se ejecutaron correctamente.

**Por qué esta prioridad**: Son defectos de pulido visual, reproducibles y recurrentes, que no bloquean el uso del sistema pero sí generan dudas ("¿sí se guardó?", "¿por qué dice cargando si ya no hay nada?").

**Prueba independiente**: Se puede verificar creando o eliminando un miembro, cambiando la sede activa en la barra superior, y abriendo el listado de "Solicitudes de clave" sin resultados pendientes.

**Escenarios de Aceptación**:

1. **Dado** que el sistema muestra una notificación emergente (crear miembro, eliminar miembro, cambiar de sede activa), **Cuando** la notificación aparece, **Entonces** se ubica en una posición que no se superpone con el menú de usuario ni queda cortada por el borde de la pantalla.
2. **Dado** el listado de "Solicitudes de clave" sin solicitudes pendientes, **Cuando** termina de cargar, **Entonces** se muestra únicamente el estado vacío ("Sin resultados"), sin una fila de "Loading..." remanente.
3. **Dado** un listado filtrado por una sede específica, **Cuando** la marca de agua con el nombre de la sede se dibuja detrás de la tabla, **Entonces** su opacidad y tamaño no deben dificultar la lectura de las filas con más contenido.

---

### Historia de Usuario 5 - Reducir ruido en los mensajes de validación de formularios (Prioridad: P3)

Como usuario que registra un miembro nuevo, necesito ver cada mensaje de error una sola vez y en un lenguaje claro, para corregir rápido sin confundirme por mensajes duplicados.

**Por qué esta prioridad**: Es un defecto menor de claridad que no impide completar la tarea, pero reduce la confianza en la calidad del formulario.

**Prueba independiente**: Se puede verificar enviando el formulario "Crear miembro" sin celular ni correo y observando el resumen de errores en la parte superior.

**Escenarios de Aceptación**:

1. **Dado** que el formulario de creación de miembro se envía sin celular ni correo, **Cuando** se muestra el resumen de errores en la parte superior del formulario, **Entonces** el mensaje "Indica al menos un medio de contacto: celular o correo." aparece una sola vez, no duplicado.

---

### Edge Cases

- ¿Qué ocurre cuando un visitante anónimo provoca intencionalmente un error en una ruta pública (por ejemplo, reenviando un formulario ya enviado, usando el botón "atrás" del navegador, o accediendo por `GET` a una ruta que solo acepta `POST`)? Hoy se expone una página de depuración completa; debe mostrarse siempre una página de error genérica.
- ¿Qué ocurre cuando un miembro existente, creado desde el panel interno sin fecha de nacimiento, intenta autoconfirmarse en un evento por QR? Hoy queda bloqueado hasta ingresar una fecha de nacimiento que nunca se le pidió antes.
- ¿Qué ocurre cuando no hay solicitudes de clave pendientes? Hoy la tabla muestra simultáneamente "0 resultados" y una fila "Loading..." que nunca se retira.
- ¿Qué ocurre con la vista "Todas las sedes" (modo de solo consulta) respecto a los botones de creación/edición? Debe quedar claramente indicado en la propia interfaz (no solo en el banner superior) cuándo una acción requiere seleccionar primero una sede específica.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El sistema NO DEBE mostrar trazas de pila, rutas de archivos del servidor, versión del framework/lenguaje, ni encabezados/cookies de la solicitud en ninguna respuesta de error en el ambiente de producción; en su lugar DEBE mostrar una página de error genérica y amigable.
- **RF-002**: El sistema DEBE responder de forma controlada (por ejemplo, redirigiendo o mostrando un mensaje claro) cuando una ruta recibe un método HTTP distinto al soportado, tanto en rutas públicas (login, registro por QR) como autenticadas.
- **RF-003**: La pantalla de inicio de sesión NO DEBE mostrar ninguna credencial de acceso (usuario, contraseña ni pistas equivalentes) en el contenido visible de la página.
- **RF-004**: El formulario público de confirmación de registro por QR DEBE actualizar el estado de validación del campo "Fecha de nacimiento" (y de cualquier otro campo) inmediatamente después de seleccionar un valor con el selector de calendario, quitando el mensaje de error si el valor ya es válido.
- **RF-005**: El criterio de obligatoriedad del campo "Fecha de nacimiento" DEBE ser consistente entre el formulario interno de creación de miembros y el formulario público de confirmación por QR; si se mantiene como obligatorio en el flujo QR, el sistema DEBE guiar al usuario para completarlo sin callejones sin salida cuando el registro ya existía sin ese dato.
- **RF-006**: El envío del formulario de confirmación de registro por QR DEBE usar siempre el método HTTP `POST` hacia la ruta de búsqueda/confirmación, sin que una interacción válida (como seleccionar una fecha) derive en una solicitud `GET` hacia esa misma ruta.
- **RF-007**: Las notificaciones emergentes de confirmación (crear, eliminar, cambiar de sede activa, y equivalentes) DEBEN mostrarse en una posición fija que no se superponga con el menú de usuario ni quede cortada por el borde de la ventana, en cualquier resolución de escritorio soportada.
- **RF-008**: Los listados que usan el componente estándar de grilla (`App\Grillas`) DEBEN ocultar cualquier indicador de carga ("Loading...") en cuanto el resultado (incluyendo el estado vacío "sin resultados") esté disponible.
- **RF-009**: El resumen de errores de validación de un formulario NO DEBE repetir el mismo mensaje de error más de una vez cuando varios campos comparten la misma regla de validación (p. ej. "celular o correo").
- **RF-010**: La marca de agua con el nombre de la sede activa, dibujada detrás de las tablas de listado, DEBE tener una opacidad y tamaño que no comprometan la legibilidad del contenido de las filas.

### Entidades Clave

- **Hallazgo de auditoría**: Un problema detectado durante la sesión de QA, con una prioridad (P1–P3), una categoría (seguridad, funcionalidad, usabilidad/claridad), la ruta o pantalla donde se observó, y el comportamiento esperado una vez corregido.
- **Miembro (Persona)**: Registro de una persona de la congregación; puede o no tener fecha de nacimiento, celular o correo; es la entidad afectada por los formularios de creación interna y de autoconfirmación por QR.
- **Evento / Registro por QR**: Un evento de la sede tiene un enlace y código QR de autoservicio que permite a cualquier persona con el enlace buscar su registro por celular y confirmar su asistencia sin iniciar sesión.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **SC-001**: Ninguna respuesta de error de la aplicación (pública o autenticada) expone trazas de pila, rutas de servidor, versión de framework/lenguaje o encabezados/cookies de la solicitud, verificado provocando errores de método HTTP no soportado en al menos 2 rutas públicas distintas.
- **SC-002**: La pantalla de inicio de sesión no contiene ninguna credencial de acceso visible, verificado por inspección visual y de código fuente de la página renderizada.
- **SC-003**: El 100% de los intentos de confirmación de registro por QR, usando el selector de calendario para la fecha de nacimiento, completan el envío sin mostrar un mensaje de error obsoleto ni una página de error técnica.
- **SC-004**: Las notificaciones emergentes de confirmación son completamente visibles (sin superposición ni recorte) en al menos las resoluciones de escritorio de 1366×768 y 1920×1080.
- **SC-005**: El listado de "Solicitudes de clave" sin pendientes muestra el estado vacío sin ningún indicador de carga remanente, verificado recargando la página con cero solicitudes pendientes.

## Supuestos

- La corrección de exposición de errores (RF-001, RF-002) implica desactivar el modo de depuración del framework en el ambiente de producción y configurar páginas de error genéricas; se asume que existe un ambiente de pruebas/staging separado donde sí se puede conservar el detalle técnico para depuración del equipo.
- Se asume que la credencial "admin@example.com / password" mostrada en el login es una cuenta real de administrador del ambiente de producción actual (no solo de un entorno de demostración aislado), por lo que, además de ocultar el texto, se recomienda rotar esa contraseña una vez corregido el hallazgo.
- Se asume que la auditoría de usabilidad/claridad adicional (por ejemplo, verificación exhaustiva en vista móvil real) queda fuera del alcance de esta sesión, ya que el entorno de pruebas usado no permitió emular de forma confiable un viewport móvil; el proyecto ya cuenta con un flujo dedicado (`npm run test:movil`, spec 008) para esa verificación y se recomienda ejecutarlo por separado.
- La Historia de Usuario 3 (RF-004, RF-005, RF-006) se relaciona directamente con la funcionalidad ya en construcción en `specs/017-registro-qr-datos-obligatorios`; esta especificación documenta los defectos observados para que se resuelvan como parte de esa funcionalidad o como corrección inmediata posterior, evitando duplicar el trabajo ya planeado allí.
- No se realizaron pruebas de penetración activas (inyección, fuerza bruta, bypass de autenticación); el hallazgo de seguridad reportado (RF-001/RF-002) se limita a lo observado pasivamente durante el uso normal de la aplicación.
