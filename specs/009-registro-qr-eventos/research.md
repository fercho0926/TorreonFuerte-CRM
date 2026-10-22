# Investigación: Registro por código QR

## D1. Identificador del enlace
- **Decisión**: columna `registro_token` (cadena aleatoria de 32 caracteres, única) en `eventos`, `encuentros` y `celulas`; se genera al crear y se rellena con una migración para los existentes. URL: `/registro/{token}`.
- **Justificación**: no adivinable (FR-001), permite regenerar (FR-017) y una sola ruta pública atiende los tres tipos.
- **Alternativas**: usar el id (adivinable, rechazado); URL firmada de Laravel (caduca o no se puede regenerar por registro; más compleja).

## D2. Resolver el destino sin usuario y respetar el aislamiento
- **Decisión**: buscar el token en las tres tablas con `SedeContext::sinFiltro()` y ejecutar el resto con `SedeContext::conSede($destino->sede_id, ...)`.
- **Justificación**: el filtro global falla cerrado sin usuario (`SedeScope`). `conSede` ya existe para servicios y asegura que las consultas de Personas se limiten a la sede del destino (principio II).
- **Alternativas**: quitar el filtro en las consultas (riesgo de fuga, prohibido por la nota de `SedeScope`).

## D3. Persona de otra sede
- **Decisión**: dentro de `conSede` solo se ven personas de la sede del destino. Para detectar que existe en otra sede se hace una consulta de **existencia** sin filtro (`sinFiltro`, solo `exists`, sin leer datos). Si existe solo allá: no se precarga, no se crea persona, no se inscribe en las tablas de inscripción, y se guarda un registro en `registros_qr` con `de_otra_sede = true`, nombre y celular escritos.
- **Justificación**: cumple las aclaraciones 2 y 3 y SC-006: la sede del destino ve solo lo que la persona escribió, sin enlace a ficha.
- **Alternativas**: crear duplicado en la sede (viola SC-003); rechazar (descartado por el usuario).

## D4. Dónde guardar el origen QR y las notas
- **Decisión**: tabla `registros_qr` (polimórfica: `registrable_type/registrable_id`), una fila por intento aceptado, con `persona_id` nulo cuando es de otra sede, y campos escritos por la persona. La inscripción real sigue en `evento_persona`, `encuentro_persona` y `celula_persona`.
- **Justificación**: no cambia los módulos existentes ni sus consultas; da la traza (FR-011) y guarda correcciones como nota sin modificar la persona (aclaración 4).
- **Alternativas**: agregar columnas de origen a tres pivotes (cambia modelos y formularios existentes; además no sirve para las de otra sede).

## D5. Identidad: celular como llave, cédula como búsqueda
- **Decisión**: el celular se normaliza con `App\Support\Celular::limpio`. Orden de búsqueda: por cédula (si se escribe) y por celular, ambos en la sede del destino. Resultados: 0 → persona nueva; 1 → precarga; 2 distintas (cédula y celular de personas diferentes, o dos personas con el mismo celular) → rechazo con mensaje (FR-010).
- **Justificación**: el documento es opcional en Personas (migración `numero_documento` nullable) y el celular hoy no es único; hay que decidir en el servicio, no en la base.
- **Alternativas**: volver único el celular (rompe datos existentes).

## D6. Persona inactiva o eliminada lógicamente
- **Decisión**: si coincide con una persona inactiva o con borrado lógico, no se crea duplicado ni se inscribe; se guarda un `registros_qr` pendiente y se muestra «el administrador te contactará».
- **Justificación**: caso límite de la especificación; evita reactivar personas sin decisión humana.

## D7. Estado «En célula» y célula
- **Decisión**: al inscribir en una célula se agrega a `celula_persona` (con `fecha_ingreso` de hoy) y, si la persona era «Visitante», pasa a «En célula» con el servicio de estados existente (`Estados`).
- **Justificación**: FR-018.

## D8. Límite de frecuencia y abuso
- **Decisión**: `RateLimiter` por IP y por token en la precarga y en el envío (por ejemplo 30 consultas/min por IP y 10 envíos/10 min por IP+celular), como en `RecuperacionClaveController`. La precarga devuelve solo nombres, apellidos y fecha de nacimiento enmascarada; nunca documento completo, correo ni dirección.
- **Justificación**: FR-012, evitar recorrer la base de personas.
- **Pendiente de decisión en tareas**: el valor exacto de los límites.

## D9. Generación del QR
- **Decisión**: `bacon/bacon-qr-code` con salida SVG, generado en el servidor al mostrar la ficha; la descarga entrega el SVG y una página imprimible con el nombre del destino.
- **Justificación**: PHP puro, sin extensiones del hosting cPanel, se descarga e imprime sin pérdida.
- **Alternativas**: librería JS en el navegador (más peso en el paquete); servicio externo de QR (envía el enlace a terceros, rechazado).

## D10. Permisos
- **Decisión**: nueva habilidad `gestionarRegistroQr` con el mismo alcance que editar el destino (administrador, pastor de la sede; líder solo de su célula). Ver, abrir/cerrar y regenerar usan esa habilidad.
- **Justificación**: FR-013 y principio II.

## D11. Cierre automático
- **Decisión**: un destino está abierto si `registro_abierto` es verdadero **y** su estado no es cancelado/realizado (eventos y encuentros) ni inactiva (célula).
- **Justificación**: FR-003.

## D12. Mejora progresiva y móvil
- **Decisión**: el formulario es un flujo de dos pasos del servidor (paso 1 celular/cédula, paso 2 datos); `registro-qr.js` hace la precarga por `fetch` cuando hay JS. El service worker excluye `/registro/*` del caché.
- **Justificación**: principios V y VII.
