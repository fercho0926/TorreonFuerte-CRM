# Especificación de Funcionalidad: Selector de idioma en la interfaz (español/inglés)

**Rama de Funcionalidad**: `020-selector-idioma-interfaz`

**Creada**: 2026-11-13

**Estado**: Borrador

**Entrada**: Descripción del usuario: "quiero que el aplicativo tenga una flag interactiva para seleccionar el idioma entre ingles y español, si existe algun plugin o algo que me deje pasar de idiciomas, porque no quiero reescribir todo el codigo en ingles, como lo hariamos ? dame opciones antes de implementar"

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Cambiar el idioma de toda la interfaz (Prioridad: P1)

Como usuario del CRM, quiero poder elegir entre español e inglés desde un control visible (por ejemplo una bandera), para usar la aplicación en el idioma que prefiero.

**Por qué esta prioridad**: Es el valor central de la funcionalidad; sin esto no hay nada que entregar.

**Prueba independiente**: Se puede probar completamente abriendo cualquier pantalla del CRM, activando el control de idioma y verificando que todos los textos visibles (menús, botones, etiquetas, mensajes) cambian de idioma de inmediato.

**Escenarios de Aceptación**:

1. **Dado** que un usuario autenticado está viendo cualquier pantalla del CRM en español, **Cuando** selecciona "English" en el control de idioma, **Entonces** toda la interfaz visible (menú, botones, etiquetas, mensajes de validación) se muestra en inglés.
2. **Dado** que un usuario está viendo la interfaz en inglés, **Cuando** selecciona "Español", **Entonces** toda la interfaz vuelve a mostrarse en español, con el mismo texto que tenía antes de existir esta funcionalidad.

---

### Historia de Usuario 2 - Recordar la preferencia de idioma (Prioridad: P2)

Como usuario, quiero que la aplicación recuerde el idioma que elegí, para no tener que volver a seleccionarlo cada vez que inicio sesión.

**Por qué esta prioridad**: Mejora la experiencia de uso repetido, pero la funcionalidad ya entrega valor sin esto (Historia 1 por sí sola es usable).

**Prueba independiente**: Se puede probar eligiendo un idioma, cerrando sesión, volviendo a iniciar sesión y verificando que la interfaz aparece en el idioma elegido anteriormente.

**Escenarios de Aceptación**:

1. **Dado** que un usuario eligió inglés en una sesión anterior, **Cuando** vuelve a iniciar sesión, **Entonces** la interfaz se muestra en inglés sin que tenga que volver a seleccionarlo.
2. **Dado** que un usuario nunca ha elegido un idioma, **Cuando** usa la aplicación por primera vez, **Entonces** la interfaz se muestra en español de forma predeterminada.

---

### Historia de Usuario 3 - Cobertura completa y consistente de los dos idiomas (Prioridad: P3)

Como responsable del CRM, quiero que los textos de la interfaz estén completos y consistentes en ambos idiomas, para que ningún usuario vea una mezcla de español e inglés en una misma pantalla.

**Por qué esta prioridad**: Es una garantía de calidad sobre las Historias 1 y 2; sin ella la funcionalidad existe pero con una experiencia inconsistente.

**Prueba independiente**: Se puede probar recorriendo cada pantalla principal del CRM en ambos idiomas y verificando que ningún texto de interfaz queda en el idioma contrario al seleccionado.

**Escenarios de Aceptación**:

1. **Dado** que una pantalla no tiene aún una traducción cargada para el idioma elegido, **Cuando** el usuario la visita, **Entonces** el sistema muestra el texto en español como respaldo, en lugar de dejar el espacio vacío o mostrar una clave interna sin traducir.

---

### Casos Límite

- ¿Qué ocurre si el usuario cambia de idioma mientras tiene un formulario abierto sin guardar? El cambio de idioma recarga la pantalla; los datos no guardados de ese formulario se pueden perder (ver Supuestos).
- ¿Qué ocurre con el contenido que los propios usuarios ingresaron (nombres de personas, notas, comentarios)? No se traduce automáticamente; permanece en el idioma en que fue escrito.
- ¿Qué ocurre si una persona usa la aplicación sin haber iniciado sesión (por ejemplo un formulario público de registro)? Queda fuera del alcance: esas pantallas públicas siguen mostrándose solo en español.
- ¿Qué ocurre con los mensajes que el sistema envía fuera de la pantalla (WhatsApp con clave, notificaciones, archivos exportados)? Quedan fuera del alcance: siguen enviándose en español, sin importar el idioma elegido en la interfaz.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **RF-001**: El sistema DEBE ofrecer un control visible e interactivo (por ejemplo una bandera) para cambiar entre español e inglés, accesible desde cualquier pantalla del CRM.
- **RF-002**: El sistema DEBE aplicar el cambio de idioma de inmediato a todos los textos de la interfaz: menús, botones, etiquetas de formularios, encabezados de las grillas estándar y mensajes de validación.
- **RF-003**: El sistema DEBE recordar la preferencia de idioma de cada usuario entre sesiones guardándola en su perfil (base de datos), de modo que el idioma elegido sea el mismo sin importar desde qué dispositivo o navegador inicie sesión.
- **RF-004**: El sistema DEBE usar español como idioma predeterminado para los usuarios que todavía no han elegido explícitamente un idioma.
- **RF-005**: El sistema NO DEBE requerir que el texto en español existente se reescriba manualmente; el texto actual en español se conserva sin cambios como una de las dos versiones del idioma.
- **RF-006**: El sistema DEBE mostrar el texto en español como respaldo en cualquier pantalla cuya traducción al inglés todavía no exista, en lugar de dejar el texto vacío o mostrar una clave interna.
- **RF-007**: El control de idioma DEBE seguir siendo utilizable sin JavaScript habilitado, de acuerdo con el principio de mejora progresiva del proyecto.
- **RF-008**: El control de idioma DEBE cumplir los tamaños táctiles mínimos del proyecto (al menos 44×44 px) y debe verse y usarse correctamente en pantallas de celular.
- **RF-009**: El alcance de esta funcionalidad se limita a las pantallas internas del CRM (administrador, pastor, líder de célula, usuario). Las pantallas públicas que llenan los miembros sin iniciar sesión (registro por código QR, confirmación de asistencia a eventos) quedan fuera de alcance y se mantienen solo en español.
- **RF-010**: Los mensajes que el sistema genera fuera de la pantalla (por ejemplo el envío de clave por WhatsApp, notificaciones y archivos exportados) quedan fuera de alcance: se siguen generando en español, sin importar el idioma elegido en la interfaz.

### Entidades Clave

- **Preferencia de idioma**: el idioma (español o inglés) guardado en el perfil de un usuario del CRM, usado para decidir en qué idioma se muestra la interfaz interna cada vez que inicia sesión.
- **Texto de interfaz**: cada elemento de texto mostrado por el CRM (etiqueta, mensaje, botón) que cuenta con una versión en español y, cuando existe, una versión en inglés.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **CE-001**: Un usuario puede cambiar el idioma de toda la interfaz visible en un máximo de 2 toques/clics, desde cualquier pantalla del CRM.
- **CE-002**: El 100% de los textos de la interfaz administrativa (menús, botones, etiquetas, mensajes de validación) se muestran en el idioma elegido, sin mezcla de idiomas, en una revisión manual de las pantallas principales.
- **CE-003**: La preferencia de idioma elegida se mantiene en el 100% de las sesiones posteriores del mismo usuario, sin que tenga que volver a seleccionarla.
- **CE-004**: La introducción de esta funcionalidad no provoca ninguna regresión en las pruebas automáticas existentes del proyecto.
- **CE-005**: El control de idioma es utilizable con una sola mano en pantalla de celular en el 100% de los casos evaluados.

## Supuestos

- El español sigue siendo el idioma predeterminado para cuentas nuevas y para usuarios que no han elegido explícitamente un idioma, dado que la mayoría de los usuarios actuales opera en español.
- La versión en inglés se genera a partir del texto en español existente; el contenido que los propios usuarios ingresan (nombres de personas, notas, comentarios) no se traduce automáticamente y permanece en el idioma en que fue escrito.
- Cambiar de idioma puede implicar una recarga de la pantalla; un formulario que el usuario tenía abierto y sin guardar en ese momento puede perder los datos no guardados.
- El control de idioma se ubica en un lugar visible y constante del diseño (por ejemplo el encabezado o el menú principal), de acuerdo con el principio de diseño primero para celular del proyecto.
- No se requiere traducir retroactivamente contenido histórico o ya generado (por ejemplo exportaciones o documentos anteriores a esta funcionalidad).
