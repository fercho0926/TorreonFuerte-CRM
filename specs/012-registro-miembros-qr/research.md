# Investigación: Autorregistro y actualización de miembros por código QR

No quedaban marcadores `NEEDS CLARIFICATION`; estas son las decisiones técnicas tomadas, apoyadas en el código de la funcionalidad 009.

## D1. Dónde vive el QR: columnas en `sedes`

- **Decisión**: `sedes.registro_token` (32 caracteres, único, con relleno de las sedes existentes) y `sedes.registro_abierto` (booleano, por defecto verdadero). El enlace público es `/registro-sede/{token}`. Abierto efectivo = `registro_abierto` y sede `activa`.
- **Justificación**: es «un QR por sede, permanente» con el mismo patrón de token/apertura/regeneración de eventos, encuentros y células, sin tabla extra. `Sede` no lleva filtro global de sede (módulo del administrador), por lo que el token se resuelve sin `sinFiltro`.
- **Alternativas descartadas**: reutilizar `RegistroQrController` y `registro.show` con un cuarto tipo `sede` (mezcla dos formularios y servicios distintos en el mismo controlador); tabla `qr_sedes` aparte (una fila por sede no lo justifica).

## D2. Dónde se guardan los registros: tabla nueva `registros_miembros_qr`

- **Decisión**: una fila por envío, con `sede_id`, `persona_id` (nulo si no hay ficha), celular, nombres, apellidos, `resultado` (`nueva`, `actualizada`, `reactivada`, `sin_cambios`) y `cambios` (JSON con `campo → [antes, después]`).
- **Justificación**: `registros_qr` tiene una restricción única `(destino, celular)` pensada para «una inscripción por actividad»; aquí la misma persona puede volver a actualizar sus datos muchas veces, así que esa tabla no sirve. Mantener la bitácora de envíos separada de `persona_cambios` permite listar «sin cambios» y los intentos que no tocaron la ficha.
- **Alternativa descartada**: relajar la restricción única de `registros_qr` (pondría en riesgo la garantía de «no duplicar inscripción» de 009).

## D3. Historial del miembro y reactivación

- **Decisión**: se reutiliza `persona_cambios` con acciones nuevas `creada_qr`, `actualizada_qr` y `reactivada_qr` (`user_id` nulo; `antes`/`despues` con los campos cambiados). La reactivación llama a `LiderazgoService::reactivar` (devuelve el estado previo a la inactivación) y, si el miembro estaba eliminado, primero lo restaura (`restore()`).
- **Justificación**: cumple RF-009/RF-012 con la bitácora que el administrador ya consulta, sin código de reversión nuevo (la corrección es manual desde el historial).
- **Alternativa descartada**: guardar el origen solo en `registros_miembros_qr` (el historial del miembro no mostraría quién y cómo cambió).

## D4. Qué se actualiza y qué no

- **Decisión**: se aplican únicamente los campos enviados **no vacíos** y distintos del valor guardado (comparación sin mayúsculas ni espacios sobrantes): `nombres`, `apellidos`, `fecha_nacimiento`, `email`, `direccion`, `barrio`, `ciudad` y, solo si la ficha no tiene cédula, `tipo_documento` + `numero_documento`. No se tocan `celular` (llave), `estado` (salvo reactivación), `lider_id`, `es_lider`, células, ministerios ni `sede_id`.
- **Justificación**: es exactamente RF-006/007/008. La cédula nueva respeta la unicidad por sede; si ya pertenece a otro miembro, la búsqueda lo detecta como conflicto antes de escribir.
- **Riesgo aceptado (responsable)**: la actualización es inmediata y el formulario precarga todos los datos (incluida la cédula) a quien conozca un celular o una cédula. Mitigaciones: historial con valores anteriores, nunca borrar, límite de intentos, `no-store`. Alternativa para endurecer más adelante: mostrar solo el nombre o exigir cédula y celular juntos.

## D5. Límite de intentos

- **Decisión**: `RateLimiter` con clave `registro-miembros:{ip}` y máximo 5 por minuto, contando tanto consultas de búsqueda como envíos; al superarlo responde 429 con `Retry-After` (JSON si el cliente lo espera).
- **Justificación**: es lo definido en la aclaración (5 por minuto por dispositivo o conexión).
- **Riesgo conocido**: un registro normal consume 2 intentos (buscar + enviar), así que una misma conexión (por ejemplo, el wifi de la iglesia) completa unos 2 registros por minuto y varias personas escaneando a la vez recibirán «espera un minuto». Se mide en las pruebas y se deja el valor como constante única para ajustarlo sin tocar más código.
- **Alternativa descartada**: límites diferenciados búsqueda/envío (más permisivos); se pueden adoptar si el uso real lo pide.

## D6. Datos que devuelve la búsqueda JSON

- **Decisión**: el endpoint de búsqueda devuelve, para un miembro de la sede, todos los campos editables (`nombres`, `apellidos`, `fecha_nacimiento`, `celular`, `tipo_documento`, `numero_documento`, `email`, `direccion`, `barrio`, `ciudad`). Un miembro solo de otra sede se trata como nuevo sin revelar que existe (igual que 009). Respuestas con `Cache-Control: no-store, private`.
- **Justificación**: es la decisión del responsable (precarga completa); se mantiene la regla de 009 de no filtrar datos entre sedes.

## D7. Doble toque y repetición

- **Decisión**: el formulario deshabilita el botón al enviar (comportamiento ya existente en `forms/envio.js`) y el servicio trabaja en transacción: un segundo envío idéntico se resuelve como `sin_cambios` y no duplica la ficha ni el historial; los `sin_cambios` consecutivos con los mismos datos en menos de 60 s no crean otra fila de registro.
- **Justificación**: cumple RF-010/RF-015 sin tokens de formulario adicionales.

## D8. Gestión: administrador en el detalle de sede y pastor en pantalla propia

- **Decisión**: un solo parcial `panel-registro-miembros` (QR, enlace, descargar, abrir/cerrar, regenerar, lista de registros) se incluye en la pestaña «Registro de miembros» de `sedes.show` (administrador) y en `/registro-miembros` (pastor, su sede). Las acciones (`svg`, `abrir`, `regenerar`) viven bajo `/sedes/{sede}/registro-miembros/…` y las autoriza el gate `gestionarRegistroMiembros` (administrador: cualquier sede; pastor: solo `sede_id` propio). La opción de menú se muestra con el gate `verRegistroMiembros` (solo rol pastor).
- **Justificación**: cumple la aclaración 2 con un único panel y sin duplicar lógica.
- **Alternativa descartada**: dar al pastor acceso al módulo Sedes (rompe la regla de que Sedes es del administrador).

## D9. Reutilización del buscador de 009

- **Decisión**: extraer de `RegistroPorQr::buscar` un método `buscarEnSede(int $sedeId, ?string $celular, ?string $documento)` y hacer que `buscar($destino, …)` lo llame con `$destino->sede_id`. El comportamiento y las pruebas de 009 no cambian.
- **Justificación**: `Sede` no tiene `sede_id`, y copiar la lógica duplicaría reglas delicadas (normalización de celular, conflictos, otra sede).

## D10. Estado inicial elegido por la persona

- **Decisión**: el estado elegido por quien se registra es un campo `estado_inicial` con valores `visitante` o `miembro_activo` (obligatorio solo para miembros nuevos; se ignora en existentes).
- **Justificación**: aclaración 3; ambos valores existen en `Persona::ESTADOS`.
