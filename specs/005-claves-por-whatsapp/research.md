# Investigación (Fase 0): Claves por WhatsApp

No quedan incógnitas ni marcadores `NEEDS CLARIFICATION`.

## D1. Envío asistido con enlace `wa.me`

- **Decisión**: el sistema no envía nada por su cuenta. Construye `https://wa.me/{numero}?text={mensaje codificado}` y lo ofrece en un botón «Enviar por WhatsApp» (abre WhatsApp Web o la aplicación); además un botón «Copiar mensaje» como respaldo. El número se normaliza con la misma regla que ya usa `Persona::celular_whatsapp` (10 dígitos → antepone `57`), extraída a `App\Support\Celular`.
- **Justificación**: decisión de la clarificación (asistido); no requiere cuenta de empresa, proveedor ni costo, y el proyecto ya usa `wa.me` para contactar personas.
- **Alternativas**: WhatsApp Business Cloud API o Twilio (descartadas por el usuario por ahora; el servicio `ClavesTemporales` aísla la entrega para poder añadirlas luego); SMS o correo (fuera de alcance).

## D2. La clave temporal es la contraseña de la cuenta

- **Decisión**: al generar una clave se guarda su hash en `users.password` (ya tiene el cast `hashed`), se marca `debe_cambiar_clave = true` y se fija `clave_vence_en`. No hay tabla de claves con secretos. El inicio de sesión existente (`Hash::check`) funciona sin cambios; generar otra clave reemplaza el hash y así invalida la anterior (FR-007).
- **Justificación**: mínima superficie nueva de secretos; nada que filtrar ni limpiar. La vigencia se valida al iniciar sesión.
- **Alternativas**: tabla de claves temporales con huella aparte y login de dos caminos (descartada: más código y dos lugares donde validar).

## D3. Qué significa «de un solo uso»

- **Decisión**: la clave temporal deja de servir en cuanto (a) la persona elige su contraseña definitiva, (b) se genera otra o (c) vence. Con ella solo se puede iniciar sesión para llegar a «Elige tu contraseña» (el middleware bloquea todo lo demás). Si la persona cierra el navegador sin cambiarla, puede volver a entrar con la misma hasta que venza.
- **Justificación**: evita dejarla sin acceso por un cierre accidental y mantiene la ventana de exposición acotada (24 h por defecto).
- **Alternativas**: invalidar al primer inicio de sesión (descartada: castiga fallos de red o cierres accidentales).

## D4. Mostrar la clave una sola vez sin guardarla

- **Decisión**: la clave se genera en un `POST` y **esa misma respuesta** muestra la pantalla «Clave generada» (enlace de WhatsApp, mensaje, botón copiar y «Ya la envié»). No hay redirección, no se usa la sesión ni `flash` y la respuesta lleva `Cache-Control: no-store`. Recargar la página vuelve a pedir el envío del formulario, que generaría otra clave tras confirmar. El enlace `wa.me` se construye en ese momento y no se persiste.
- **Justificación**: la sesión usa el controlador `database` y se guardaría el valor en claro; esta forma cumple SC-005.
- **Alternativas**: redirigir con datos en sesión cifrada (descartada: sigue habiendo un secreto en la base durante la petición siguiente).

## D5. Recuperación sin revelar cuentas

- **Decisión**: `POST /olvide-clave` valida solo el formato del correo, aplica el límite de intentos y **siempre** responde con el mismo mensaje y el mismo código. Si la cuenta existe, no es una cuenta de sede inactiva y no tiene ya una solicitud pendiente, crea una `SolicitudClave`; si ya hay una pendiente, solo actualiza su fecha. El tiempo de respuesta se mantiene parecido haciendo siempre la misma consulta.
- **Justificación**: FR-005, FR-006 y SC-004. Agrupar solicitudes evita que un tercero llene el panel.
- **Alternativas**: enviar un enlace por correo (descartado: el usuario pidió WhatsApp y no hay correo transaccional configurado).

## D6. Límites de intentos

- **Decisión**: `RateLimiter` con tres claves: recuperación por cuenta (3/hora) y por origen (10/hora), e inicio de sesión por correo+origen (5 fallos/minuto, bloqueo de 1 minuto). Valores en `config/claves.php`. El límite del inicio de sesión se añade porque hoy no existe y una clave temporal de 10 caracteres sin límite sería atacable.
- **Justificación**: FR-006 y la seguridad de FR-003. El almacén de caché ya está configurado (`database`).
- **Alternativas**: sin límite en el login (descartada por riesgo).

## D7. Formato de la clave temporal

- **Decisión**: 10 caracteres de un alfabeto sin ambigüedades (sin `0 O 1 l I`), generados con `random_int`, mostrados en dos grupos (`ABCDE-FGHJK`) para leerlos y escribirlos mejor; el guion se ignora al validar. Vigencia por defecto 24 h.
- **Justificación**: ≈ 2^50 combinaciones, suficiente con el límite de intentos y la caducidad; legible por teléfono.
- **Alternativas**: contraseñas largas con palabras (descartada: más difíciles de dictar en un mensaje corto).

## D8. Cambio obligatorio mediante middleware

- **Decisión**: `ExigeCambioDeClave` (en el grupo `web`, después de `ResuelveSede`) redirige a `cambiar-clave` toda petición de un usuario con `debe_cambiar_clave` salvo `cambiar-clave.*` y `logout`. La nueva contraseña exige 8+ caracteres, distinta de la temporal y confirmación; al guardar se limpia `debe_cambiar_clave` y `clave_vence_en`, se marca el registro como «usada» y se regenera el identificador de sesión.
- **Justificación**: FR-008; reutiliza el patrón del middleware existente y no depende de cada controlador.
- **Alternativas**: comprobar la bandera en cada controlador (descartada: fácil de olvidar).

## D9. Solicitudes, panel y alcance por sede

- **Decisión**: `solicitudes_clave` y `registros_clave` llevan `sede_id` (de la cuenta; nula para administradores del sistema) y el alcance por sede ya existente: el pastor solo ve las de su sede; el administrador, todas (según «sede activa»). Las solicitudes de cuentas sin sede (admin) solo las ve el administrador. Las solicitudes pendientes de más de 48 h se muestran como «vencidas» al consultar (no hace falta un proceso programado). El menú muestra «Solicitudes de clave» con insignia del número de pendientes.
- **Justificación**: FR-009 y FR-017 sin reglas nuevas de aislamiento. Evita depender de tareas programadas en hosting compartido.
- **Alternativas**: tarea programada para vencer solicitudes (descartada por complejidad operativa).

## D10. Permisos

- **Decisión**: se añaden `gestionarClaves` (igual que `gestionar`: administrador sobre todos; pastor sobre usuarios de su sede con rol líder/usuario, no sobre sí mismo) y `verSolicitudesClave` (administrador y pastor). Un pastor no puede generar claves de otros pastores ni de administradores, igual que hoy no puede editarlos.
- **Justificación**: coherencia con `UserPolicy` actual; evita que un pastor tome el control de otra cuenta privilegiada.
- **Alternativas**: permitir al pastor cualquier cuenta de su sede (descartada: escalada de privilegios).

## D11. Cuentas existentes y el celular

- **Decisión**: la columna `celular` de `users` es nula para cuentas actuales; el formulario de crear la exige (con la validación de celular existente) y el de editar la permite completar. Generar clave sin celular se rechaza con el mensaje de FR-014. Al crear un usuario con persona vinculada, el celular se propone desde la persona. Las contraseñas actuales no cambian.
- **Justificación**: migración sin impacto; coincide con los supuestos de la especificación.
- **Alternativas**: usar siempre el celular de la persona (descartada: no todos los usuarios, como el administrador, tienen persona).

## D12. Estrategia de verificación

- **Decisión**: pruebas de PHPUnit en `tests/Feature/Claves/` para cada historia (generar sin contraseña, mensaje y enlace, recuperación genérica, límites, solicitudes y alcance por sede, cambio obligatorio, caducidad, no persistir la clave en BD/sesión/logs) más ajuste de las pruebas de usuarios que enviaban contraseñas. Recorrido manual con la guía de `quickstart.md`.
- **Justificación**: SC-003 a SC-008 son verificables automáticamente; SC-001 y SC-002 con recorrido.
- **Alternativas**: simular WhatsApp con un servicio de prueba (innecesario: solo se genera un enlace).

## Riesgos

| Riesgo | Mitigación |
|--------|-----------|
| Quien administra envía la clave al chat equivocado | Se muestra nombre y número antes del botón; la clave caduca y obliga a cambiarla |
| Nadie atiende solicitudes | Panel con insignia en el menú; el administrador del sistema ve las de todas las sedes |
| Un tercero llena el panel de solicitudes | Una solicitud pendiente por cuenta, límites por cuenta y origen |
| Filtración de la clave en logs o depuración | Nunca se registra; prueba automática que busca la clave en BD y logs |
| Regresión en pruebas de usuarios | Se ajustan las que enviaban contraseña y se mantiene el resto |
