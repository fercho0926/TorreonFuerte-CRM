# Investigación: Corregir errores del registro por QR y exigir celular y documento

No quedaban marcadores `NEEDS CLARIFICATION` tras `/speckit-clarify`; estas son las decisiones técnicas tomadas, apoyadas en el código real de las funcionalidades 009 y 012 y en tres fallas concretas encontradas al leerlo.

## D1. Obligatoriedad sin migración de esquema

- **Decisión**: celular, tipo de documento y número de documento se exigen en la **validación de los formularios** (`FormRequest` / `Validator::make`), no con una columna `NOT NULL` en `personas`. Las columnas siguen nulas a nivel de base de datos.
- **Justificación**: la especificación (Supuestos) acepta que las fichas antiguas incompletas no se migran en bloque; un `NOT NULL` exigiría backfillear primero todas las filas existentes, lo que contradice esa decisión del responsable. Validar en el formulario basta para RF-001/RF-003 y es reversible sin tocar datos.
- **Alternativa descartada**: `NOT NULL` + migración de backfill con un valor "pendiente" (inventa un dato falso en vez de pedírselo a la persona correcta).

## D2. RF-004 no necesita una "puerta" aparte

- **Decisión**: no se escribe ninguna comprobación nueva de tipo "¿le falta un dato a esta persona? bloquear". Al ser celular y documento siempre obligatorios en el formulario (D1/RF-001), **cualquier** envío —de alguien nuevo o de alguien ya encontrado— exige esos campos. Si la persona encontrada ya los tenía guardados, la precarga los llena y el envío no pide nada nuevo; si le faltaban, la precarga los deja vacíos y la validación obligatoria fuerza a escribirlos antes de poder enviar.
- **Justificación**: cumple RF-004 reutilizando exactamente la misma regla de RF-001, sin lógica de negocio adicional (principio VI). Es la simplificación más importante de este plan.
- **Alternativa descartada**: un paso o pantalla intermedia "completa tu información" antes de dejar continuar (duplica el formulario que ya existe).

## D3. Relleno de un dato vacío, nunca sobrescritura — ahora también el celular

- **Decisión**: en `RegistroMiembrosQr::cambios()` (funcionalidad 012) ya existe la regla "si la ficha no tiene documento y el envío trae uno, se escribe; si ya tiene uno, no se toca". Se amplía la misma regla para cubrir también `celular`: si `personas.celular` está vacío y el formulario trae uno, se guarda; si ya tiene un celular guardado, se mantiene como hasta ahora (celular sigue siendo la llave de búsqueda y no se cambia).
- **Justificación**: es la única forma de que RF-004 funcione para un miembro que hoy no tiene celular guardado y fue encontrado por su documento; sin esto, un dato recién escrito por la persona nunca llegaría a guardarse.
- **Alternativa descartada**: permitir cambiar el celular siempre que difiera (rompería la regla ya vigente de que el celular es la llave y no se edita por este medio).

## D4. La misma regla de relleno se extiende a eventos, encuentros y células

- **Decisión**: hoy `RegistroPorQr` (funcionalidad 009) **nunca** modifica una persona existente cuando la inscribe a un evento, encuentro o célula; cualquier diferencia queda solo como nota de corrección para el administrador. Se agrega una única excepción puntual: si a la persona encontrada le falta el celular o el documento, esos dos campos (y solo esos) se rellenan con lo escrito, igual que D3; el resto de la regla de "nunca modificar, solo anotar" sigue intacta.
- **Justificación**: RF-004 exige esto también para los QR de eventos/encuentros/células, no solo para el de miembros (la especificación habla de "un formulario público de QR" en general).
- **Alternativa descartada**: extender toda la actualización de 012 (nombres, apellidos, fecha, email, etc.) a 009 (cambiaría el comportamiento ya validado de 009 mucho más allá de lo que pide esta funcionalidad).

## D5. Bug encontrado: el caso «de otra sede» no crea ni inscribe a nadie

- **Hallazgo**: en `RegistroPorQr::registrar()`, cuando la búsqueda resuelve `OTRA_SEDE` (celular o documento ya usados por alguien de otra sede), el código actual no entra en ninguna de las ramas `NUEVA` / `EXISTENTE` / `PENDIENTE`: no crea la `Persona`, no la inscribe, y el único rastro es una fila de `registros_qr` con resultado `de_otra_sede` y `persona_id` nulo. La pantalla de confirmación, sin embargo, muestra «¡Listo, quedaste registrado!» (su `@else` por defecto), así que la persona nunca se entera de que no quedó inscrita.
- **Decisión**: corregir para que este caso se trate igual que `NUEVA` a efectos de creación e inscripción (crea una `Persona` nueva en la sede del QR, con celular y documento obligatorios como cualquier persona nueva, y la inscribe), manteniendo intacto que **no** se lee ni se modifica la ficha de la otra sede. El resultado guardado en `registros_qr` sigue siendo `de_otra_sede` (para que el administrador distinga este caso en la lista), pero ahora sí va acompañado de una inscripción real.
- **Justificación**: es exactamente lo que la propia especificación 009 ya documenta (RF-007 y el caso límite «persona existente en otra sede: se inscribe con el nombre y el celular que escriba»); el código actual se desvía de su propia especificación. Corregirlo es necesario además porque, a partir de esta funcionalidad, toda persona nueva debe quedar con celular y documento guardados (RF-001) — algo que hoy ni siquiera ocurre en este caso porque no se crea ninguna ficha.

## D6. Bloqueo contra envíos simultáneos en eventos, encuentros y células

- **Decisión**: envolver `RegistroPorQr::registrar()` en el mismo `Cache::lock('registro-qr:'.$tipoDestino.':'.$idDestino.':'.$celular, 15)->block(10, …)` que `RegistroMiembrosQr::registrar()` ya usa para el QR de miembros desde la funcionalidad 012.
- **Justificación**: hoy `RegistroPorQr::registrar()` no tiene ningún bloqueo. Un doble toque o dos pestañas con el mismo celular pueden ejecutar la comprobación "¿ya está registrado?" al mismo tiempo, pasar ambas, y luego chocar contra la restricción única `registros_qr_destino_celular_unique` (la segunda escritura lanza una `QueryException` sin capturar → pantalla de error técnico) o, si ninguno de los dos tenía todavía ficha, crear dos personas duplicadas con el mismo celular (no hay restricción única sobre `personas.celular`). Esto coincide con la queja reportada («a veces muestra error») y con RF-006.
- **Alternativa descartada**: agregar un token anti-doble-envío en el formulario (no cubre dos pestañas ni dos dispositivos distintos con el mismo celular).

## D7. Envío repetido idéntico = éxito, no error

- **Decisión**: dentro del bloqueo (D6), cuando la comprobación de "ya registrado" da positivo (la persona ya quedó inscrita por un envío anterior con ese mismo celular y destino), el servicio deja de lanzar `ValidationException` y en su lugar devuelve un resultado nuevo, `ya_registrado`. El controlador público trata ese resultado igual que un envío exitoso: redirige a la pantalla de confirmación con el mensaje «ya quedaste registrado», sin pedirle a la persona que reintente nada y sin mostrarlo como un error de formulario.
- **Justificación**: es la aclaración resuelta en `/speckit-clarify` (Historia de Usuario 1, RF-006): un doble toque o dos pestañas con el mismo celular deben resolverse solos.
- **Alternativa descartada**: seguir lanzando el error de validación «ya estás registrado» (es la causa de que, hoy, un reintento legítimo se vea como un fallo en vez de una confirmación).

## D8. El choque temporal (RF-007) es el `LockTimeoutException` sin capturar

- **Hallazgo**: tanto `RegistroPorQr::registrar()` (tras D6) como `RegistroMiembrosQr::registrar()` (ya desde la funcionalidad 012) usan `Cache::lock(...)->block(10, …)`. Si el bloqueo no se libera en esos 10 segundos, Laravel lanza `Illuminate\Contracts\Cache\LockTimeoutException`, que hoy **no se captura en ningún lado**: se propaga como un error no controlado y termina en la pantalla de error técnico genérica. Es el caso "poco frecuente" descrito en RF-007 y la causa más probable de que el QR de miembros (funcionalidad 012) también muestre errores ocasionales hoy, no solo el de eventos.
- **Decisión**: capturar `LockTimeoutException` en los dos controladores públicos (`RegistroPublicoController::store`, `RegistroMiembrosPublicoController::store`) y responder con el mismo tipo de mensaje ya usado para el límite de frecuencia (`demasiados()`): texto claro pidiendo reintentar en unos segundos, 429 con `Retry-After` para peticiones JSON. Este intento cuenta contra el límite de frecuencia existente (aclaración de la especificación), así que no se necesita ningún contador nuevo.
- **Alternativa descartada**: aumentar el tiempo de espera del bloqueo (no elimina el caso límite, solo lo hace menos frecuente; sigue sin dar una respuesta controlada).

## D9. Nuevos valores de `resultado`, sin migración de esquema

- **Decisión**: `registros_qr.resultado` y `registros_miembros_qr.resultado` ya son columnas `string` libres (sin `enum` a nivel de base de datos); se agregan los valores `ya_registrado` (solo eventos/encuentros/células, D7) y `dato_completado` (ambos flujos, cuando el único motivo de la actualización fue rellenar el celular o el documento que faltaba, D3/D4) a las constantes `RegistroQr::RESULTADOS` / `RegistroMiembroQr::RESULTADOS` y a sus etiquetas, sin tocar ninguna migración.
- **Justificación**: cumple RF-012/CE-006 (visibilidad en el listado ya existente) reutilizando la misma tabla y el mismo campo, tal como pidió el responsable del proyecto al resolver la aclaración correspondiente.
- **Regla para distinguir `dato_completado` de `actualizada`**: se marca `dato_completado` cuando el campo que faltaba (celular o documento) es justamente lo que se acaba de completar — sin importar si además cambió algún otro dato en la misma actualización —; si no faltaba ningún dato de identificación y solo cambiaron otros campos, sigue siendo `actualizada`, igual que hoy.

## D10. Formularios internos: `PersonaRequest`, alta rápida y «Nuevo usuario»

- **Decisión**: `PersonaRequest::rules()` deja de aceptar `celular` o `correo` como alternativa (`required_without`) y exige siempre `celular`; se agregan `tipo_documento` (`required_with:numero_documento`... pasa a `required`) y `numero_documento` (`required`) a `reglasDeAlta()`, que ya reutilizan tanto `PersonaRequest` como `UsersController` («Nuevo usuario», funcionalidad 016). El validador manual de `PersonasController::rapida()` (alta rápida desde una célula) se actualiza con las mismas reglas en vez de su propio `required_without`.
- **Justificación**: RF-001/RF-003 piden que la obligatoriedad aplique también a la creación manual, en los tres puntos de entrada que hoy crean una `Persona` desde el panel interno.
- **Campos del formulario**: `resources/views/personas/_form.blade.php` no tenía ningún campo de documento; se agregan `tipo_documento` (`x-form.select`) y `numero_documento` (`x-form.input`) junto a `celular`, los tres marcados obligatorios. `resources/views/celulas/_form.blade.php` (alta rápida) recibe los mismos campos.
- **Riesgo encontrado al agregar el campo**: `personas` ya tiene una restricción única `(sede_id, numero_documento)` a nivel de base de datos, pero `PersonaRequest` nunca valida `numero_documento` (el campo no existía en el formulario). Si se agrega como obligatorio sin una regla `unique` a nivel de formulario, un administrador que escriba por error el documento de otro miembro de su sede provocaría una `QueryException` sin capturar (pantalla de error técnico) en vez de un mensaje claro — exactamente la clase de falla que esta funcionalidad busca eliminar. Se agrega `Rule::unique('personas', 'numero_documento')->where('sede_id', …)->ignore($persona)` (análoga a la restricción de base de datos) a `PersonaRequest::rules()`, con su mensaje junto al campo.
- **Edición de un miembro con el dato faltante**: al abrir `personas.edit` para un miembro al que le falta celular o documento, los mismos campos obligatorios de `PersonaRequest` impiden guardar hasta completarlos (RF-004/Historia de Usuario 3, escenario 3), sin necesitar ninguna comprobación adicional — igual razonamiento que D2.
