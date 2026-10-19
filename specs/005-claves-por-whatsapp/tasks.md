---

description: "Lista de tareas: Claves de acceso generadas y entregadas por WhatsApp"
---

# Tareas: Claves de acceso generadas y entregadas por WhatsApp

**Entrada**: documentos de diseño en `specs/005-claves-por-whatsapp/`
**Requisitos previos**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/rutas-y-pantallas.md`, `quickstart.md`

**Pruebas**: se incluyen (plan D12): SC-003 a SC-008 son verificables automáticamente y la funcionalidad toca autenticación y secretos.

**Formato**: `[ID] [P?] [Historia] Descripción con ruta`. `[P]` = se puede hacer en paralelo (archivos distintos, sin dependencia pendiente). Las rutas son relativas a la raíz del repositorio; el código está en `laravel_app/`.

**Reglas de oro**:
- La clave temporal NUNCA se guarda en claro: ni en base de datos, sesión, logs, bitácora ni caché (research D2–D4). Solo se muestra una vez, en la respuesta del `POST` que la genera.
- No cambiar el aislamiento por sede ni los roles; reutilizar `UserPolicy`, `SedeContext` y el alcance por sede.
- Migraciones solo aditivas. Nunca ejecutar `migrate:fresh` sobre la base local; para probar usar una base temporal.
- Textos en español.

**Orden de las historias**: US1 (crear sin contraseña) → US4 (cambio obligatorio, necesario para que US1 sea usable de punta a punta) → US2 (recuperación y panel) → US3 (reenviar) → US5 (seguimiento). Los números de historia son los de `spec.md`.

## Fase 1: Preparación

- [X] T001 Crear `laravel_app/config/claves.php` con `vigencia_horas` = 24, `longitud` = 10, `solicitudes_por_cuenta_hora` = 3, `solicitudes_por_origen_hora` = 10, `login_fallos_por_minuto` = 5, `solicitud_vence_horas` = 48 y `contrasena_minimo` = 8, cada uno leído de una variable de entorno opcional (`CLAVES_VIGENCIA_HORAS`, etc.)
- [X] T002 [P] Crear `laravel_app/app/Support/Celular.php` extrayendo de `laravel_app/app/Models/Persona.php` (`getCelularLimpioAttribute` y `getCelularWhatsappAttribute`) la normalización: solo dígitos, y si son exactamente 10 dígitos anteponer `57`; añadir `Celular::formatoLegible()` («310 555 9999») y hacer que `Persona` delegue en la clase para no duplicar la regla
- [X] T003 [P] Prueba unitaria `laravel_app/tests/Unit/CelularTest.php`: limpieza de espacios, guiones y paréntesis; 10 dígitos → `57…`; ya con indicativo no se duplica; vacío → nulo; el comportamiento de `Persona::celular_whatsapp` no cambia

---

## Fase 2: Fundamentos (bloquea todas las historias)

**⚠️ CRÍTICO**: ninguna historia puede empezar hasta terminar esta fase.

- [X] T004 Migración `laravel_app/database/migrations/2026_10_24_000001_add_clave_columns_to_users_table.php`: añadir a `users` `celular` (texto de hasta 20, nulo), `debe_cambiar_clave` (booleano, por defecto falso) y `clave_vence_en` (fecha y hora, nula); `down()` las elimina
- [X] T005 Migración `laravel_app/database/migrations/2026_10_24_000002_create_solicitudes_clave_table.php` según `data-model.md`: `user_id` (obligatorio, referencia a `users`), `sede_id` (nulo, referencia a `sedes`), `estado` (texto: `pendiente`, `atendida`, `descartada`), `atendida_por` (nulo, referencia a `users`), `atendida_en` (nulo), `ultima_solicitud_en`, `origen_hash` (texto de hasta 64, nulo), marcas de tiempo; índices (`sede_id`, `estado`) y (`user_id`, `estado`)
- [X] T006 Migración `laravel_app/database/migrations/2026_10_24_000003_create_registros_clave_table.php` según `data-model.md`: `user_id` (obligatorio), `sede_id` (nulo), `solicitud_id` (nulo), `motivo` (`alta`, `recuperacion`, `reenvio`), `solicitada_por` (nulo), `preparada_por` (obligatorio), `estado` (`pendiente_envio`, `enviada`, `usada`, `reemplazada`, `vencida`), `vence_en`, `enviada_en` y `usada_en` (nulos), marcas de tiempo; **sin ninguna columna que contenga la clave o su hash**; índices (`user_id`, `created_at`) y (`sede_id`, `estado`)
- [X] T007 [P] Actualizar `laravel_app/app/Models/User.php`: añadir `celular` y `debe_cambiar_clave` y `clave_vence_en` a `#[Fillable]` (nunca `password` por asignación masiva desde formularios), casts (`debe_cambiar_clave` booleano, `clave_vence_en` fecha y hora), relaciones `registrosClave()` y `solicitudesClave()`, y `claveTemporalVencida()` (verdadero si `debe_cambiar_clave` y `clave_vence_en` ya pasó); mantener `'password' => 'hashed'`
- [X] T008 [P] Crear `laravel_app/app/Models/SolicitudClave.php` con el alcance por sede (usar `PerteneceASede` como los demás modelos; la `sede_id` nula pertenece solo al administrador del sistema), constantes de estado, relaciones `user()` y `atendidaPor()`, `scopePendientes()` y `estaVencida()` (pendiente con más de `config('claves.solicitud_vence_horas')` horas)
- [X] T009 [P] Crear `laravel_app/app/Models/RegistroClave.php` con el mismo alcance por sede, constantes de motivo y estado, relaciones `user()`, `preparadaPor()`, `solicitadaPor()` y `solicitud()`; `$hidden`/`$fillable` sin ningún campo de clave
- [X] T010 Actualizar `laravel_app/tests/Unit/ModelosConSedeTest.php` para incluir `solicitudes_clave` y `registros_clave` en la verificación de tablas con alcance por sede, y `laravel_app/app/Support/MigracionSedes.php` solo si el alcance de sede exige listarlas (la sede nula debe seguir permitida para estas dos tablas)
- [X] T011 Crear `laravel_app/app/Services/ClavesTemporales.php` con: `generar(User $objetivo, string $motivo, User $preparadaPor, ?SolicitudClave $solicitud = null, ?User $solicitadaPor = null): array` que (1) exige celular (si no, lanza `ValidationException` con el mensaje de FR-014), (2) crea la clave de `config('claves.longitud')` caracteres con `random_int` sobre el alfabeto sin `0 O 1 l I`, (3) en una transacción guarda su hash en `users.password`, pone `debe_cambiar_clave` = verdadero y `clave_vence_en` = ahora + `vigencia_horas`, marca el registro anterior pendiente/enviado como `reemplazada` y crea el `RegistroClave` en `pendiente_envio`, y (4) devuelve `['clave' => 'ABCDE-FGHJK', 'registro' => …]` sin persistirla; `mensaje(User, string $clave): string` con el texto de `contracts/rutas-y-pantallas.md` (nombre, sistema `config('app.sistema')`, sede, correo, clave, horas); `enlaceWhatsapp(User, string $mensaje): string` con `https://wa.me/{Celular}?text={rawurlencode}`; `normalizar(string $clave): string` que ignora guion, espacios y mayúsculas/minúsculas
- [X] T012 Prueba unitaria `laravel_app/tests/Feature/Claves/ClavesTemporalesServiceTest.php`: longitud y alfabeto sin caracteres ambiguos; dos generaciones consecutivas devuelven claves distintas; la clave valida con `Hash::check` contra `users.password` y la anterior deja de validar; `debe_cambiar_clave` y `clave_vence_en` (24 h) quedan fijados; el registro anterior pasa a `reemplazada`; sin celular lanza error y no cambia la contraseña; **la clave no aparece en ninguna columna de `registros_clave` ni de `users` salvo como hash**; el enlace usa `57` + 10 dígitos y el mensaje contiene la vigencia y no contiene `http`
- [X] T013 [P] Actualizar `laravel_app/app/Policies/UserPolicy.php`: `gestionarClaves(User $actor, User $objetivo)` igual a `gestionar` (admin sobre todos; pastor solo sobre usuarios de su sede con rol líder o usuario y no sobre sí mismo) y `verSolicitudesClave(User $actor)` igual a `verUsuarios`; prueba en `laravel_app/tests/Feature/Claves/PermisosClavesTest.php` (admin sí; pastor de la sede sí; pastor sobre otro pastor, sobre admin o sobre otra sede no; líder no; el actor no puede gestionarse a sí mismo)
- [X] T014 [P] Actualizar `laravel_app/database/factories/UserFactory.php` con `celular` de ejemplo, `debe_cambiar_clave` falso, y estados `conClaveTemporal()` (con `debe_cambiar_clave` y vigencia futura) y `conClaveVencida()`; actualizar `laravel_app/database/seeders/DatabaseSeeder.php` para dar `celular` a los usuarios de ejemplo
- [X] T015 Ejecutar `php artisan migrate` sobre una **copia** de la base local (o una base temporal) para validar las 3 migraciones y `php artisan test`; corregir regresiones (las pruebas de usuarios que envían `password` se ajustan en la Historia 1)

**Punto de control**: columnas, tablas, modelos, servicio y permisos listos y probados.

---

## Fase 3: Historia de usuario 1 - Crear usuario sin asignarle contraseña (Prioridad: P1) 🎯 MVP

**Objetivo**: crear la cuenta sin escribir contraseña, generar la clave temporal y mostrarla una sola vez con el botón de WhatsApp (FR-001, FR-002, FR-003, FR-014, FR-015).

**Prueba independiente**: como pastor, crear un usuario con celular y comprobar que no hay campos de contraseña, que aparece «Clave generada» con el botón de WhatsApp y que la cuenta puede validar la clave.

### Pruebas de la historia 1

- [X] T016 [P] [US1] Prueba `laravel_app/tests/Feature/Claves/CrearUsuarioSinClaveTest.php`: el formulario de crear no contiene `password` ni `password_confirmation` y sí `celular`; guardar con datos válidos responde 200 (sin redirección) con la clave, el enlace `wa.me`, el número y `Cache-Control: no-store`; la cuenta queda con `debe_cambiar_clave` y `clave_vence_en`; sin celular o con celular inválido se rechaza y no se crea la cuenta; el pastor solo crea en su sede y solo roles líder/usuario (como hoy); el administrador puede elegir sede; **ni la clave ni el enlace aparecen en `sessions`, `registros_clave` ni en el log de la prueba**; recargar el POST no muestra la clave guardada
- [X] T017 [P] [US1] Ajustar las pruebas existentes que envían `password` al crear o editar usuarios (`laravel_app/tests/Feature/Aislamiento/AislamientoUsuariosTest.php`, `laravel_app/tests/Support/RecorreFormularios.php` campos esperados de «usuario (crear)» y `laravel_app/tests/Feature/Formularios/*` que lo mencionen) para el nuevo formulario con `celular` y sin contraseña

### Implementación de la historia 1

- [X] T018 [US1] Actualizar `laravel_app/app/Http/Controllers/UsersController.php`: `store` valida `name`, `email` (único), `role`, `sede_id`, `persona_id` y `celular` (obligatorio, `string`, `max:20`, y formato de celular con `App\Support\Celular`), ya no valida `password`; crea el usuario con un hash aleatorio provisional y llama a `ClavesTemporales::generar(…, 'alta', $actor)`; responde con `view('claves.mostrar', …)` y `Cache-Control: no-store, private` (sin `redirect`); `update` deja de aceptar `password` y permite editar `celular`
- [X] T019 [P] [US1] Crear `laravel_app/resources/views/claves/mostrar.blade.php` (pantalla «Clave generada»): nombre y rol, celular legible, la clave en grupos grandes y de fácil lectura, vigencia, botón «Enviar por WhatsApp» (`target="_blank" rel="noopener"`), botón «Copiar mensaje» (con `data-copiar`), botón «Ya la envié» (formulario `claves.enviada`, se completa en US5) y aviso «Esta clave se muestra una sola vez»; usar `x-ui.tarjeta`, `x-form.accion` y los estilos AdminLTE 4 existentes
- [X] T020 [P] [US1] Crear `laravel_app/resources/js/forms/copiar.js` (copia el texto de `data-copiar` al portapapeles con `navigator.clipboard` y un respaldo con `document.execCommand`, mostrando un aviso con SweetAlert2) y registrarlo en `laravel_app/resources/js/forms/index.js`; prueba en `laravel_app/resources/js/forms/copiar.test.js`
- [X] T021 [US1] Actualizar `laravel_app/resources/views/users/_form.blade.php`, `create.blade.php` y `edit.blade.php`: quitar los campos de contraseña, añadir `x-form.input name="celular"` con máscara de celular (`mascara="celular"`), ayuda «Aquí se enviará la clave por WhatsApp», prellenado desde la persona vinculada cuando exista (con un pequeño script o con el valor inicial) y botón «Crear y generar clave»
- [X] T022 [US1] Actualizar `laravel_app/routes/web.php` (sin rutas nuevas para `users.store`) y `laravel_app/app/Http/Middleware/ResuelveSede.php` (`EXENTAS` incluye ya `users.*`; añadir `solicitudes-clave.*`, `claves.*` para la fase siguiente)
- [X] T023 [US1] Ejecutar `php artisan test` y `npm test`; corregir hasta verde

**Punto de control**: crear usuarios sin contraseña y mostrar la clave una sola vez funciona (MVP parcial; la persona aún no puede elegir su contraseña hasta US4).

---

## Fase 4: Historia de usuario 4 - Cambio obligatorio de la clave temporal (Prioridad: P1)

**Objetivo**: quien entra con clave temporal solo puede elegir su contraseña; la temporal caduca; «Mi cuenta» (FR-003, FR-007, FR-008, FR-011).

**Prueba independiente**: ingresar con la clave temporal, comprobar la redirección obligatoria, elegir contraseña y verificar que la temporal ya no sirve.

### Pruebas de la historia 4

- [X] T024 [P] [US4] Prueba `laravel_app/tests/Feature/Claves/CambioObligatorioTest.php`: tras iniciar sesión con clave temporal cualquier ruta autenticada redirige a `cambiar-clave.edit` salvo `cambiar-clave.*` y `logout`; contraseña de 7 caracteres, igual a la temporal o con confirmación distinta se rechaza con mensajes claros; contraseña válida limpia `debe_cambiar_clave` y `clave_vence_en`, marca el registro como `usada` con `usada_en`, regenera la sesión y deja pasar al tablero; la clave temporal deja de validar tras el cambio; una clave temporal vencida rechaza el inicio de sesión con «Tu clave temporal venció; pide una nueva»; una cuenta antigua sin clave temporal entra sin interrupciones
- [X] T025 [P] [US4] Prueba `laravel_app/tests/Feature/Claves/MiCuentaTest.php`: con sesión, cambiar la contraseña con la actual correcta funciona; con la actual incorrecta se rechaza; la nueva exige mínimo 8 caracteres y confirmación; el enlace «Mi cuenta» aparece en el menú de usuario

### Implementación de la historia 4

- [X] T026 [US4] Crear `laravel_app/app/Http/Middleware/ExigeCambioDeClave.php`: si hay usuario en sesión con `debe_cambiar_clave` y la ruta no es `cambiar-clave.*` ni `logout`, redirigir a `cambiar-clave.edit`; si además `claveTemporalVencida()`, cerrar la sesión y volver al login con el mensaje de clave vencida; registrarlo en `laravel_app/bootstrap/app.php` en el grupo `web` después de `ResuelveSede`
- [X] T027 [US4] Actualizar `laravel_app/app/Http/Controllers/AuthController.php` `login`: rechazar con «Tu clave temporal venció; pide una nueva» si la cuenta tiene clave temporal vencida (sin revelar nada adicional), y después de validar la contraseña llamar a `Session::regenerate()`; mantener los mensajes y la validación de sede existentes
- [X] T028 [US4] Crear `laravel_app/app/Http/Controllers/CuentaController.php` con `editarClave`/`actualizarClave` (pantalla «Elige tu contraseña»: valida `password` con `min:config('claves.contrasena_minimo')`, `confirmed` y distinta de la contraseña actual con `Hash::check`; guarda, limpia las marcas, marca el `RegistroClave` vigente como `usada`, regenera la sesión y redirige al tablero con un aviso) y `mostrar`/`cambiarClave` para «Mi cuenta» (exige `password_actual` correcta)
- [X] T029 [P] [US4] Crear `laravel_app/resources/views/auth/cambiar-clave.blade.php` (layout de acceso o del panel sin menú; formulario con `x-form.*`, campos con mostrar/ocultar y reglas visibles) y `laravel_app/resources/views/cuenta/mi-cuenta.blade.php` (datos básicos y formulario de cambio de contraseña)
- [X] T030 [US4] Añadir las rutas `cambiar-clave.edit|update` y `cuenta.edit`, `cuenta.clave.update` en `laravel_app/routes/web.php`, el enlace «Mi cuenta» en el menú de usuario de `laravel_app/resources/views/layouts/admin.blade.php` y las exenciones necesarias en `ResuelveSede::EXENTAS` (`cambiar-clave.*`, `cuenta.*`)
- [X] T031 [US4] Ejecutar `php artisan test --filter=Claves` y el conjunto completo; corregir hasta verde

**Punto de control**: el circuito crear → entrar con la temporal → elegir contraseña funciona de punta a punta.

---

## Fase 5: Historia de usuario 2 - «No recuerdo mi contraseña» y panel de solicitudes (Prioridad: P1)

**Objetivo**: el visitante pide una nueva clave desde el login; un pastor o administrador la atiende desde un panel (FR-004, FR-005, FR-006, FR-009, FR-017).

**Prueba independiente**: pedir la recuperación con un correo existente, atenderla como pastor de la sede y entrar con la clave generada.

### Pruebas de la historia 2

- [X] T032 [P] [US2] Prueba `laravel_app/tests/Feature/Claves/RecuperacionTest.php`: correo existente y correo inexistente devuelven **el mismo código y el mismo texto**; una cuenta de sede inactiva no crea solicitud pero responde igual; una cuenta crea una solicitud `pendiente` y una segunda petición no crea otra (solo actualiza `ultima_solicitud_en`); el administrador del sistema también puede solicitar (solicitud con sede nula); el límite por cuenta (4.ª petición en una hora) y por origen (11.ª) responde con el mensaje de espera y no crea solicitudes; el límite de inicio de sesión (6 fallos en un minuto) bloquea con mensaje
- [X] T033 [P] [US2] Prueba `laravel_app/tests/Feature/Claves/PanelSolicitudesTest.php`: el pastor solo ve las solicitudes de su sede; el administrador ve todas (y respeta la sede activa); un líder recibe 403; atender genera la clave (motivo `recuperacion`, `solicitud_id` y `solicitada_por` nulo = «la propia persona»), invalida la anterior, marca la solicitud `atendida` con quién y cuándo y responde con la pantalla «Clave generada»; una solicitud de otra sede responde 404; descartar la marca `descartada`; una solicitud pendiente de más de 48 h aparece como «vencida» y aún se puede atender; la insignia del menú cuenta las pendientes del alcance del usuario

### Implementación de la historia 2

- [X] T034 [US2] Crear `laravel_app/app/Http/Controllers/RecuperacionClaveController.php`: `create` (formulario con correo) y `store` que valida solo el formato del correo, aplica `RateLimiter` (por cuenta y por origen, valores de `config/claves.php`), busca la cuenta con `User::withoutGlobalScopes()`, crea o actualiza la `SolicitudClave` dentro de `SedeContext::conSede()` de la cuenta (o sin filtro para administradores) cuando corresponde, guarda el origen solo como huella (`hash('sha256', ip.app_key)`) y **siempre** redirige al login con el mismo mensaje «Si la cuenta existe, avisaremos a quien administra tu sede para que te envíe una nueva clave por WhatsApp.»; cuando se supera el límite responde 429 con el mensaje de espera
- [X] T035 [US2] Añadir el límite de inicio de sesión en `laravel_app/app/Http/Controllers/AuthController.php` (`RateLimiter` por correo + origen, `login_fallos_por_minuto`, mensaje «Demasiados intentos; espera un minuto») y limpiarlo tras un ingreso correcto
- [X] T036 [US2] Crear `laravel_app/app/Http/Controllers/SolicitudesClaveController.php`: `index` (paginado, filtro por estado, solo lo visible según el alcance y `verSolicitudesClave`), `atender` (autoriza con `gestionarClaves` sobre la cuenta, llama a `ClavesTemporales::generar(..., 'recuperacion', $actor, $solicitud)`, marca la solicitud `atendida` y responde con `claves.mostrar` sin redirección y con `no-store`) y `descartar`
- [X] T037 [P] [US2] Crear `laravel_app/resources/views/auth/olvide-clave.blade.php` (layout de acceso, un campo de correo con `x-form.input`, botón «Pedir nueva clave» y enlace «Volver al inicio de sesión») y añadir el enlace «¿Olvidaste tu contraseña?» en `laravel_app/resources/views/auth/login.blade.php`
- [X] T038 [P] [US2] Crear `laravel_app/resources/views/claves/solicitudes.blade.php` con filtros por estado, tabla (persona/cuenta, sede en «Todas las sedes», fecha, estado con `x-ui.estado`, acciones «Generar y enviar por WhatsApp» con confirmación y «Descartar»), estado vacío con `x-ui.vacio`
- [X] T039 [US2] Añadir en `laravel_app/routes/web.php` las rutas `recuperacion.create|store`, `solicitudes-clave.index|atender|descartar` (las de recuperación y login fuera del grupo autenticado), la opción «Solicitudes de clave» (icono `fa-key`, permiso `verSolicitudesClave`, insignia `solicitudes`) en la sección Administración de `laravel_app/config/menu.php`, el contador `solicitudes` en `laravel_app/app/Providers/AppServiceProvider.php` (pendientes en el alcance del usuario) y los nuevos estados en `laravel_app/app/Support/Estados.php` (`pendiente_envio` → `warning`, `enviada` → `info`, `usada` → `success`, `reemplazada` y `descartada` → `secondary`, `vencida` → `danger`, `atendida` → `success`)
- [X] T040 [US2] Ejecutar `php artisan test` y `npm test`; corregir hasta verde

**Punto de control**: la persona pide ayuda sola y la sede la atiende desde un panel.

---

## Fase 6: Historia de usuario 3 - Reenviar una nueva clave desde Usuarios (Prioridad: P2)

**Objetivo**: «Enviar nueva clave» en el listado y la ficha de usuarios (FR-010, FR-013).

**Prueba independiente**: con un usuario existente, generar una clave nueva y comprobar que la anterior queda reemplazada y que quedó el registro.

- [X] T041 [P] [US3] Prueba `laravel_app/tests/Feature/Claves/ReenviarClaveTest.php`: el pastor reenvía a un usuario de su sede (motivo `reenvio`, `preparada_por` = pastor, registro anterior `reemplazada`); a un usuario de otra sede, a otro pastor o a un admin recibe 403; el administrador puede con cualquiera; sin celular se rechaza con el mensaje de FR-014 y el formulario de edición permite completarlo; la acción requiere confirmación (atributos `data-confirm*`); el formulario de editar usuario no contiene campos de contraseña
- [X] T042 [US3] Crear `laravel_app/app/Http/Controllers/ClavesUsuarioController.php` con `store(User $user)` (autoriza con `gestionarClaves`, genera con motivo `reenvio`, responde con `claves.mostrar` sin redirección y `no-store`)
- [X] T043 [US3] Actualizar `laravel_app/resources/views/users/index.blade.php` (columna «Clave» con `x-ui.estado` del último registro: pendiente de envío, enviada, usada, vencida o «Sin clave temporal»; acción «Enviar nueva clave» con `x-form.confirm` hacia `users.clave.store`, visible solo con permiso) y `laravel_app/resources/views/users/edit.blade.php` (mismo botón), consultando el último `RegistroClave` de cada usuario sin consultas N+1
- [X] T044 [US3] Añadir la ruta `users.clave.store` en `laravel_app/routes/web.php` y ejecutar `php artisan test --filter=Claves`

---

## Fase 7: Historia de usuario 5 - Seguimiento y auditoría (Prioridad: P3)

**Objetivo**: marcar «enviada», estados visibles y registro sin secretos (FR-012, FR-013, SC-005, SC-007).

**Prueba independiente**: generar claves en distintos casos y comprobar estados, responsable y ausencia de la clave en cualquier registro.

- [X] T045 [P] [US5] Prueba `laravel_app/tests/Feature/Claves/SeguimientoClavesTest.php`: «Ya la envié» pasa el registro de `pendiente_envio` a `enviada` con `enviada_en` y solo lo puede hacer quien tiene permiso sobre la cuenta; tras el cambio de contraseña el estado es `usada`; una clave vencida sin usar se muestra `vencida` y se ofrece generar otra; el registro guarda quién solicitó (nulo = la propia persona), quién preparó, motivo y fechas; **se recorren `users`, `registros_clave`, `solicitudes_clave`, `sessions`, `cache` y el log y no se encuentra ninguna clave emitida durante la prueba**
- [X] T046 [US5] Crear la acción `claves.enviada` en `laravel_app/app/Http/Controllers/ClavesUsuarioController.php` (`enviada(RegistroClave $registro)`: autoriza con `gestionarClaves` sobre `$registro->user`, pasa a `enviada` solo desde `pendiente_envio`, redirige al listado de usuarios o al panel con un aviso) y su ruta `claves.enviada`; el botón «Ya la envié» de `claves/mostrar.blade.php` envía a esta acción
- [X] T047 [US5] Calcular el estado «vencida» al consultar (registros `pendiente_envio` o `enviada` con `vence_en` pasado) en `laravel_app/app/Models/RegistroClave.php` (`estadoVigente()`), usarlo en el listado de usuarios y en el panel, y no requerir procesos programados
- [X] T048 [US5] Ejecutar `php artisan test --filter=Claves`

---

## Fase 8: Pulido y verificación final

- [X] T049 [P] Documentar en `README.md` (sección «Usuarios y claves») cómo funciona el flujo: sin contraseñas, clave temporal, WhatsApp asistido, panel de solicitudes, límites y variables `CLAVES_*`; y en `DEPLOY_CPanel.md` que hay migraciones nuevas y que los usuarios existentes conservan su contraseña
- [X] T050 [P] Añadir a `laravel_app/.env.example` las variables opcionales `CLAVES_VIGENCIA_HORAS`, `CLAVES_LOGIN_FALLOS_POR_MINUTO`, etc., comentadas con sus valores por defecto
- [X] T051 Revisión de seguridad: buscar con `grep` que ninguna línea registre la clave (`Log::`, `dd(`, `dump(`, `logger(`), que ninguna vista imprima `password` y que las respuestas de claves lleven `Cache-Control: no-store`; anotar el resultado en `specs/005-claves-por-whatsapp/quickstart.md`
- [X] T052 Recorrer los 17 escenarios de `specs/005-claves-por-whatsapp/quickstart.md` con una base temporal (`.env` de prueba con `APP_KEY`, migraciones y sembrado) y las herramientas de Chrome, incluida la apertura real de `wa.me`; luego apagar el servidor y borrar `.env` de prueba y base temporales
- [X] T053 Ejecutar `php artisan test`, `npm test` y `npm run build` completos, verificar que la base local del usuario **no** se tocó y marcar todas las tareas como [X]

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias.
- **Fundamentos (Fase 2)**: depende de la Fase 1 y **bloquea** las historias. Orden: T004–T006 → T007–T010 → T011–T012; T013 y T014 en paralelo; T015 cierra.
- **US1**: tras Fundamentos; es el MVP parcial.
- **US4**: tras Fundamentos; necesaria para cerrar el circuito de US1 y de US2 (todas generan claves temporales).
- **US2**: tras US1 y US4 (reutiliza `claves.mostrar` y el servicio).
- **US3**: tras US1 (reutiliza la pantalla de clave y el listado de usuarios).
- **US5**: tras US1 (necesita el botón «Ya la envié» y los registros).
- **Pulido**: al final.

### Ejemplos de paralelismo

- Fase 1: T002 y T003 en paralelo.
- Fase 2: T007, T008, T009, T013 y T014 en paralelo una vez hechas las migraciones.
- US1: T016, T017, T019 y T020 en paralelo.
- US4: T024, T025 y T029 en paralelo.
- US2: T032, T033, T037 y T038 en paralelo.

## Estrategia de implementación

1. **MVP**: Fases 1 y 2, US1 y US4 → se puede crear un usuario sin contraseña, enviarle la clave por WhatsApp y que él elija la suya.
2. **Incremental**: US2 (recuperación y panel) → US3 (reenviar) → US5 (seguimiento), con `php artisan test` y `npm test` al cerrar cada fase.
3. **Control de riesgo**: la Fase 2 termina con T015 sobre una copia de la base; ninguna tarea modifica la base local. Si una prueba de 003/004 se rompe, se corrige en su fase y no se avanza.

## Notas

- Aunque el login ya no pide ninguna contraseña asignada por administradores, las cuentas existentes conservan su contraseña (D11); nadie queda obligado a cambiarla hasta que se le genere una clave.
- Un envío automático por WhatsApp Business podrá añadirse después sustituyendo solo `ClavesTemporales::enlaceWhatsapp()` por un cliente de la API, sin cambiar flujos ni pantallas.
- `/speckit-analyze` puede ejecutarse antes de `/speckit-implement`.

## Notas de implementación

- **Redes sin «Fusionar»** (pedido aparte del usuario durante la implementación): se eliminó la ruta `redes.fusionar`, el método del controlador, el formulario de la vista y las pruebas asociadas.
- El trait `App\Http\Controllers\Concerns\MuestraClave` concentra la respuesta «Clave generada» (sin redirección y con `Cache-Control: no-store, private`) para `users.store`, `users.clave.store` y `solicitudes-clave.atender`.
- La clave temporal acepta escribirse sin guion, con espacios y en minúsculas.
- Recorrido en navegador (base temporal): crear usuario → clave generada con botón `wa.me` (`/573105559999?text=…`, `target=_blank`) → ingreso con la clave → redirección obligatoria a «Elige tu contraseña» → panel de líder; recuperación desde el login → solicitud pendiente con insignia en el menú → confirmación → «Clave generada».
- Fuera de alcance, anotado en `quickstart.md`: el alta de sede aún pide la contraseña de pastor y pastora; el login conserva la línea de demostración con `admin@example.com`.
