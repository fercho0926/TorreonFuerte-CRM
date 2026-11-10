---

description: "Lista de tareas para la funcionalidad 018-auditoria-qa-crm"
---

# Tareas: Auditoría QA y mejoras de UX/UI del CRM

**Entrada**: Documentos de diseño de `/specs/018-auditoria-qa-crm/`

**Prerrequisitos**: `plan.md` (obligatorio), `spec.md` (obligatorio), `research.md`, `data-model.md`, `contracts/`, `quickstart.md`

**Pruebas**: El Principio IV de la constitución ("Pruebas primero") es obligatorio en este proyecto, así que cada historia incluye sus pruebas automáticas, escritas antes del arreglo. La única excepción documentada en `plan.md` es la verificación de RF-001/RF-002/RF-006 contra el `.env` real del servidor de producción, que no se puede fijar desde una prueba automática de este repositorio y se valida manualmente con `quickstart.md`.

**Organización**: Las tareas están agrupadas por historia de usuario (igual que en `spec.md`) para que cada una se pueda implementar y verificar por separado.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencia de una tarea sin terminar)
- **[Historia]**: a qué historia de usuario pertenece (US1–US5)
- Cada descripción incluye la ruta exacta del archivo

## Convenciones de ruta

Proyecto único (monolito Laravel): todas las rutas son relativas a `laravel_app/` salvo que se indique lo contrario (por ejemplo, el `.env` del servidor de producción, que vive fuera del repositorio).

---

## Fase 1: Preparación

**Propósito**: Confirmar una línea base verde antes de tocar nada.

- [X] T001 Confirmar que la rama `018-auditoria-qa-crm` está activa y ejecutar `php artisan test`, `npm test` y `npm run build` en `laravel_app/` para registrar la línea base (deben pasar en verde; si algo ya fallaba antes de esta funcionalidad, anotarlo para no confundirlo con una regresión propia) — **Línea base: 1112/1114 PHPUnit en verde (2 skipped), 114/114 Vitest en verde, `npm run build` exitoso.**

---

## Fase 2: Fundacional (prerrequisitos bloqueantes)

**Propósito**: Infraestructura compartida que deba existir antes de cualquier historia.

**Nota**: Ninguna de las 5 historias comparte infraestructura nueva ni bloquea a las demás — cada una toca archivos independientes (vistas de error, login, Flatpickr/validación, CSS de avisos/marca de agua y grilla, y `PersonaRequest`). No hay tareas fundacionales que agregar; se puede pasar directo a la Fase 3. Las historias **pueden** implementarse en cualquier orden, aunque se recomienda el orden de prioridad (US1/US2 → US3 → US4/US5) porque son las de mayor riesgo.

---

## Fase 3: Historia de Usuario 1 - Cerrar la exposición de información sensible en producción (Prioridad: P1) 🎯 MVP

**Objetivo**: Que ningún visitante anónimo vea trazas técnicas, rutas de servidor, versiones de software ni encabezados/cookies de su propia solicitud cuando ocurre un error no controlado, en ninguna ruta pública o autenticada.

**Prueba independiente**: Provocar una solicitud `GET` a una ruta registrada solo para `POST` (por ejemplo `/login`) y confirmar que la respuesta es una página de error genérica, sin ningún detalle técnico del servidor.

### Pruebas para la Historia 1

> **NOTA**: Escribir esta prueba primero y confirmar que falla antes de crear la vista 405.

- [X] T002 [P] [US1] Escribir una prueba de Feature que, forzando `config(['app.debug' => false])`, haga una petición `GET` a una ruta registrada solo para `POST` (reutilizar `/login`) y confirme que el cuerpo de la respuesta NO contiene "Exception trace" ni "vendor/laravel" ni ninguna cabecera de la propia solicitud, en `tests/Feature/Errores/Error405Test.php` — **La prueba pasó de inmediato (sin necesidad de T003) incluso antes de crear la vista 405: con `APP_DEBUG=false`, Laravel ya usa su resolución de respaldo sin trazas. Esto confirma que la causa raíz es 100% de configuración del servidor (T005), no del código.**

### Implementación para la Historia 1

- [X] T003 [US1] Crear `resources/views/errors/405.blade.php` reutilizando el parcial `errors._contenido` (mismo patrón que `resources/views/errors/404.blade.php` y `500.blade.php`), para que T002 pase — vista creada y la prueba se amplió para confirmar que es la que se usa (4 aserciones).
- [X] T004 [US1] Agregar en `.cpanel.yml`, después del paso `php artisan config:cache`, una verificación que falle el despliegue si `APP_DEBUG` quedó en `true` (por ejemplo con un comando `artisan` pequeño que lea `config('app.debug')` y termine con código distinto de cero si es `true`) — se agregó `php artisan about --json | grep -q '"debug_mode":false' || exit 1`. **No se pudo probar en vivo (es específico del runner de cPanel); revisar el diff antes de hacer push.**
- [ ] T005 [US1] **Operativo, fuera del repositorio**: en el `.env` real del servidor de producción (`DEPLOYPATH`, ver `.cpanel.yml`), corregir `APP_ENV=production` y `APP_DEBUG=false` para que coincida con `laravel_app/.env.production`, y ejecutar `php artisan config:clear && php artisan config:cache` para que el cambio tome efecto de inmediato (ver `quickstart.md` §1, pasos 1–2) — **PENDIENTE: requiere acceso al hosting de producción, fuera del alcance de este agente.**
- [ ] T006 [US1] Verificación manual: ejecutar `contracts/paginas-de-error.md` contra producción (`quickstart.md` §1, pasos 3–6), confirmando que ni `/login` ni `/registro/{token}/buscar` exponen trazas ni encabezados al recibir un `GET` — **PENDIENTE: depende de que T005 esté hecho en el servidor real.**

**Punto de control**: Ningún visitante anónimo puede ver información técnica del servidor, en producción, sin importar la ruta.

---

## Fase 4: Historia de Usuario 2 - No publicar credenciales de acceso en la pantalla de login (Prioridad: P1) 🎯 MVP

**Objetivo**: Que la pantalla pública de inicio de sesión no muestre ninguna credencial de acceso.

**Prueba independiente**: Abrir `/login` sin sesión iniciada y confirmar que no aparece ningún texto con credenciales.

### Pruebas para la Historia 2

- [X] T007 [P] [US2] Escribir una prueba de Feature que visite `GET /login` sin sesión y confirme que el contenido de la respuesta no incluye "Demo:" ni "admin@example.com", en `tests/Feature/Auth/LoginSinCredencialesTest.php` — confirmada en rojo antes del cambio.

### Implementación para la Historia 2

- [X] T008 [US2] Quitar la línea `<p class="mt-3 mb-1 text-center text-muted small">Demo: admin@example.com / password</p>` de `resources/views/auth/login.blade.php` (línea 23), para que T007 pase — prueba en verde.

**Punto de control**: El login ya no expone ninguna credencial, independientemente de la configuración de entorno del servidor.

---

## Fase 5: Historia de Usuario 3 - Corregir el flujo de confirmación de registro por QR (Prioridad: P2)

**Objetivo**: Que el formulario público de confirmación por QR no deje mensajes de error obsoletos, use siempre `POST` para enviarse, y trate la fecha de nacimiento con el mismo criterio de obligatoriedad que el resto de los datos de alta (coordinado con la funcionalidad 017).

**Prueba independiente**: En el enlace de registro de un evento activo, buscar por el celular de un miembro existente sin fecha de nacimiento, completar el formulario usando el selector de calendario, y enviarlo.

### Pruebas para la Historia 3

- [X] T009 [P] [US3] Ampliar `resources/js/forms/fechas.test.js` con un caso que simule elegir una fecha en el campo con `altInput` de Flatpickr y confirme que el campo real (`name="fecha_nacimiento"`) pierde la clase `is-invalid` y su mensaje de error asociado queda vacío — **creado desde cero (no existía). Un diagnóstico reveló que Flatpickr pone `type="hidden"` al campo real, así que la prueba terminó cubriendo ese supuesto explícitamente (`expect(mainInput.type).toBe('hidden')`).**
- [X] ~~T010~~ [P] [US3] ~~Ampliar `validar.test.js`~~ — **No aplica: se descubrió que el campo oculto por Flatpickr queda excluido de `esValidable()` (por ser `type="hidden"`), así que `validarCampo()` de `validar.js` nunca llega a tocarlo. El arreglo no pasa por ese módulo (ver T011 y `research.md` §3); `validar.test.js` no necesitó cambios.**

### Implementación para la Historia 3

- [X] T011 [US3] ~~Importar `validarCampo`~~ — **Implementado distinto de lo planeado**: en `resources/js/forms/fechas.js`, el callback `onChange` de Flatpickr limpia a mano (sin pasar por `validar.js`) las clases `is-invalid`/`is-valid`, `aria-invalid` y el texto del `<div>` de error, tanto en el campo real como en el `altInput` visible. Ver `research.md` §3 para la causa exacta (el campo real es `type="hidden"` para cuando `onChange` se dispara, así que `validarCampo()` no es una opción viable).
- [X] T012 [US3] Reproducción dirigida de RF-006 — **Reproducida y causa confirmada** en producción: el disparador no es la fecha en sí, sino *cualquier* regla que solo valide el servidor (se reprodujo con cédula sin tipo de documento: `tipo_documento.required_with:numero_documento`, sin equivalente en el cliente). El paso 2 se renderiza como respuesta directa del `POST` a `.../buscar` (sin URL propia por `GET`); al fallar la validación del paso siguiente, el `back()` por defecto de Laravel redirige al `Referer` —esa misma URL de solo-POST— y el navegador la pide por `GET` → 405. Detalle completo en `research.md` §5.
- [X] T013 [US3] Corregido: se creó `App\Http\Requests\Concerns\RedirigeAlFormularioPublico` (trait) y se aplicó a `RegistroQrRequest` **y, por tener la misma arquitectura y el mismo defecto, también a `RegistroMiembroQrRequest`** (QR de autoservicio de miembros, fuera del texto original de `spec.md` pero dentro de su intención); ambos ahora redirigen al paso 1 (`registro.show`/`registro-sede.show`, rutas `GET`) en vez de usar el `back()` basado en `Referer`. Cubierto por `tests/Feature/RegistroQr/RedireccionTrasErrorTest.php` y `tests/Feature/RegistroMiembrosQr/RedireccionTrasErrorTest.php` (confirmados en rojo antes del arreglo).
- [X] T014 [US3] ~~Agregar ayuda visible~~ — **Reconsiderado y no implementado como se planeó**: tras confirmar con T012/T013 que el verdadero problema era el método de redirección (no la obligatoriedad en sí) y que `fecha_nacimiento` ya tenía su propio mensaje de error claro ("Escribe tu fecha de nacimiento."), agregar un texto de ayuda adicional no resuelve ningún hallazgo nuevo; se deja esta decisión de copy a la funcionalidad 017 (que sí define el criterio de obligatoriedad de fondo) para no adelantarse a un archivo que esa rama ya va a tocar.
- [X] T015 [US3] Verificación manual ejecutada directamente en producción (no solo localmente): se reprodujo el flujo completo del paso 2 de un evento real, incluida la sincronización de fecha (T011) y el disparador exacto de RF-006 (T012/T013); el envío de prueba fue rechazado por validación (cédula sin tipo de documento) y **no llegó a crear ningún registro**, así que no quedó dato de prueba que limpiar. Pendiente repetir una vez desplegado el código de esta funcionalidad, para confirmar en producción (hoy sin desplegar) que ya no aparece el 405.

**Punto de control**: El formulario público de registro por QR no deja errores obsoletos en pantalla y siempre se envía por `POST`.

---

## Fase 6: Historia de Usuario 4 - Pulir retroalimentación visual y estados de carga (Prioridad: P3)

**Objetivo**: Que las notificaciones emergentes, la marca de agua de sede y el estado de carga de las grillas no generen dudas sobre si una acción se ejecutó correctamente.

**Prueba independiente**: Crear/eliminar un miembro, cambiar la sede activa, y abrir "Solicitudes de clave" sin pendientes.

### Pruebas para la Historia 4

- [X] T016 [P] [US4] Escribir (o ampliar, si ya existe) una prueba Vitest que confirme que el objeto `ESPAÑOL` de `resources/js/grilla/grilla.js` incluye la clave `loadingRecords` con un texto en español, en `resources/js/grilla/grilla.test.js` — confirmada en rojo antes del cambio.

### Implementación para la Historia 4

- [X] T017 [US4] Agregar `loadingRecords: 'Cargando…'` al objeto `ESPAÑOL` en `resources/js/grilla/grilla.js`, para que T016 pase.
- [X] T018 [P] [US4] En `resources/css/forms.css`, agregar una regla para el contenedor de SweetAlert2 (`.swal2-container.swal2-top-end`) que desplace el toast hacia abajo según el alto real del navbar fijo de AdminLTE (usar la variable CSS que expone la plantilla si existe, o un valor fijo documentado en un comentario si no existe ninguna) — AdminLTE 4 no expone una variable propia para el alto del navbar (no se encontró ninguna en `node_modules/admin-lte`); se usó un valor fijo (`top: 4.5rem !important`) documentado en un comentario, sobre el selector real que usa SweetAlert2 para el modo toast (`body.swal2-toast-shown .swal2-container.swal2-top-end`, confirmado en su propio CSS fuente).
- [X] T019 [P] [US4] En `resources/css/marca.css`, reducir el límite superior del `clamp(2rem, 8vw, 7rem)` y/o bajar la opacidad (`rgba(0, 0, 0, .05)`) de `.marca-agua-sede span` para mejorar la legibilidad de filas con más contenido, sin quitar el elemento — reducido a `clamp(1.5rem, 5vw, 4.5rem)` y opacidad `.035`.
- [X] T020 [US4] Ejecutar `npm run test:movil` y confirmar que los ajustes de T018/T019 no introducen desplazamiento horizontal de página ni texto bajo 16 px en ninguna pantalla de `tests/movil/pantallas.mjs` — **467 pruebas en verde, 2 fallas preexistentes y no relacionadas** (`organigrama.spec.js`/`roles.spec.js` a 768 px; se confirmó revirtiendo temporalmente los CSS de esta funcionalidad y viendo que fallan igual sin ellos — no son una regresión de esta auditoría).
- [ ] T021 [US4] Verificación manual: ejecutar `quickstart.md` §6, §7 y §9 (notificación al crear/eliminar un miembro de prueba y al cambiar de sede; listado de "Solicitudes de clave" sin pendientes; legibilidad de la marca de agua en un listado con varias filas) — **PENDIENTE: requiere confirmación visual en el navegador real contra el entorno desplegado; no verificado en esta sesión más allá de la revisión de código/CSS.**

**Punto de control**: Ninguna notificación ni elemento decorativo genera dudas sobre si una acción se completó.

---

## Fase 7: Historia de Usuario 5 - Reducir ruido en los mensajes de validación de formularios (Prioridad: P3)

**Objetivo**: Que "falta un medio de contacto" se muestre una sola vez, no una vez por campo.

**Prueba independiente**: Enviar "Crear miembro" sin celular ni correo y revisar el resumen de errores.

### Pruebas para la Historia 5

- [X] T022 [P] [US5] Escribir una prueba de Feature que envíe el formulario de alta de miembro (`PersonaRequest`) sin `celular` ni `email` y confirme que la respuesta trae un único mensaje de error con el texto "Indica al menos un medio de contacto: celular o correo." (no uno por cada campo), en `tests/Feature/Personas/ValidacionContactoTest.php` — confirmada en rojo antes del cambio (2 ocurrencias en vez de 1).

### Implementación para la Historia 5

- [X] ~~T023~~ [US5] ~~Reemplazar las reglas `required_without` por una clave `contacto`~~ — **Reconsiderado durante la implementación, no se tocó `PersonaRequest.php`**: `tests/Feature/Personas/AltaRapidaTest.php::test_acepta_solo_correo_y_rechaza_sin_ningun_medio_de_contacto` ya prueba, a propósito, que el endpoint JSON de alta rápida (`PersonasController::rapida()`, mismo mensaje duplicado por diseño) responda con `errors => [celular, email]` **separados** — eso alimenta que la UI de esa pantalla marque ambos campos como inválidos. Cambiar a una clave compartida habría roto ese contrato ya probado y quitado la marca visual individual de los dos campos en el formulario completo (`$errors->has('celular')`/`has('email')` dejarían de ser `true`). El defecto real que se observó en la auditoría (el texto repetido en el **resumen** de la parte superior) no está en la regla de validación sino en cómo el resumen lista los errores — ver T024.
- [X] T024 [US5] En `resources/views/components/form/layout.blade.php`, el bucle `@foreach($errors->keys() as $campoConError)` del resumen ahora omite un mensaje ya mostrado (controla duplicados por texto, no por campo), conservando el enlace al primer campo al que aplica. `celular` y `email` **siguen** marcándose individualmente como inválidos (`is-invalid`/`aria-invalid`) igual que antes; solo el resumen deja de repetir la frase. No se tocó `PersonasController::rapida()` (contrato JSON distinto, ver nota de T023).
- [X] ~~T025~~ [US5] ~~Mostrar el mensaje de la clave `contacto` junto a los campos~~ — **No aplica**: al no introducirse una clave `contacto` (ver T023), no hay un mensaje nuevo que mostrar junto a los campos; la sección ya trae la ayuda "Basta con el celular o el correo." (`personas/_form.blade.php`), y los dos campos siguen marcándose en rojo individualmente como antes.
- [X] T026 [US5] Verificación manual: ejecutar `quickstart.md` §8 (crear miembro sin celular ni correo) y confirmar que aparece un solo mensaje — verificado con la prueba automática (T022, en verde) más `AccesibilidadFormulariosTest`/`AltaRapidaTest` (sin regresión); **pendiente una confirmación visual rápida en el navegador real contra el entorno desplegado.**

**Punto de control**: El resumen de errores nunca repite la misma frase por dos campos relacionados.

---

## Fase Final: Pulido y verificación cruzada

**Propósito**: Confirmar que las 5 historias juntas dejan el sistema en verde y sin cabos sueltos.

- [X] T027 Ejecutar `php artisan test`, `npm test` y `npm run build` completos en `laravel_app/` y confirmar que todo pasa en verde (Principio IV de la constitución) — **1118/1120 PHPUnit en verde (2 skipped, igual que la línea base), 117/117 Vitest en verde, `npm run build` exitoso. `npm run test:movil`: 467 en verde, 2 fallas preexistentes no relacionadas (ver T020).**
- [~] T028 Ejecutar `quickstart.md` completo, de la sección 1 a la 9 más el cierre, y confirmar en "Control de cambios" que no quedó ningún dato de prueba (miembro o evento) activo en los listados reales — **Parcial**: se ejecutaron en vivo, contra producción, las secciones 3–5 (flujo de registro por QR, incluida la reproducción de RF-006) sin dejar datos de prueba (la validación rechazó el envío antes de crear nada). Las secciones 1, 2, 6, 7, 8 y 9 **requieren acceso al servidor de producción o una repetición manual ya con el código desplegado** — pendientes de quien tenga ese acceso.
- [X] T029 [P] Confirmar que ningún cambio de esta funcionalidad agregó una migración ni modificó una columna (coherente con `data-model.md`: sin cambios de esquema) — confirmado: `git diff` no toca `database/migrations/` ni ningún `Schema::` en esta funcionalidad.
- [X] T030 Revisar `specs/018-auditoria-qa-crm/checklists/requirements.md` y confirmar que sigue en verde tras la implementación — sigue en verde, sin cambios necesarios.

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias, se ejecuta primero
- **Fundacional (Fase 2)**: no aplica (ver nota en esa fase) — no bloquea ninguna historia
- **Historias de Usuario (Fase 3 a 7)**: cada una puede empezar inmediatamente después de la Fase 1; no dependen entre sí
- **Pulido (Fase Final)**: depende de que todas las historias que se vayan a entregar estén completas

### Dependencias entre historias

- **US1 (P1)** y **US2 (P1)**: totalmente independientes entre sí; ambas son el MVP de seguridad y deberían hacerse primero
- **US3 (P2)**: independiente de US1/US2; comparte un archivo (`PersonaRequest.php` vía T023/T024) con el trabajo ya en curso de la funcionalidad 017 — coordinar antes de fusionar, no depende de que 017 termine primero
- **US4 (P3)**: independiente de las demás
- **US5 (P3)**: independiente de las demás; T023 toca el mismo archivo que T014 de US3 solo de forma tangencial (T014 es la vista, T023 es la clase de validación) — sin conflicto de archivo real

### Dentro de cada historia

- Las pruebas (cuando existen) se escriben y deben fallar antes de la implementación correspondiente
- La implementación de cada historia no depende de otras historias

### Oportunidades de paralelismo

- T002 (US1), T007 (US2), T009+T010 (US3), T016 (US4) y T022 (US5) se pueden escribir todas en paralelo (archivos de prueba distintos)
- T018 y T019 (ambas US4, archivos CSS distintos) se pueden hacer en paralelo
- Una vez terminada la Fase 1, distintas personas pueden tomar US1, US2, US3, US4 y US5 al mismo tiempo sin bloquearse

---

## Ejemplo de paralelismo: Historias 1 y 2 (el MVP)

```bash
# Lanzar juntas las pruebas de las dos historias P1:
Task: "Prueba de Feature: GET a ruta solo-POST no expone trazas, en tests/Feature/Errores/Error405Test.php"
Task: "Prueba de Feature: GET /login no incluye credenciales, en tests/Feature/Auth/LoginSinCredencialesTest.php"
```

---

## Estrategia de implementación

### MVP primero (Historias 1 y 2)

1. Completar la Fase 1 (Preparación)
2. Completar la Fase 3 (US1) y la Fase 4 (US2) — son las dos de prioridad P1 y, juntas, cierran el hallazgo de seguridad crítico de la auditoría
3. **Detenerse y validar**: ejecutar `contracts/paginas-de-error.md` contra producción antes de seguir con el resto
4. Desplegar este MVP de seguridad cuanto antes, sin esperar a las historias P2/P3

### Entrega incremental

1. Preparación → línea base verde
2. US1 + US2 (P1) → validar → desplegar (cierra el riesgo de seguridad)
3. US3 (P2) → validar → desplegar (corrige el flujo público de eventos)
4. US4 + US5 (P3) → validar → desplegar (pulido de usabilidad)
5. Fase Final → confirmación cruzada de toda la funcionalidad

---

## Notas

- `[P]` = archivos distintos, sin dependencias entre sí
- La etiqueta de historia (`[US#]`) permite rastrear cada tarea hasta su hallazgo de origen en `spec.md`
- T005 (corregir el `.env` del servidor) y las verificaciones manuales (T006, T015, T021, T026, T028) no son archivos del repositorio: quien las ejecute debe tener acceso al hosting de producción
- Confirmar que las pruebas fallan antes de implementar el arreglo correspondiente
- Detenerse en cualquier punto de control para validar una historia por separado antes de seguir con la siguiente
