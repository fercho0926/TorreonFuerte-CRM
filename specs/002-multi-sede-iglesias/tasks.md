---

description: "Lista de tareas para la funcionalidad multi-sede (varias iglesias en un mismo CRM)"
---

# Tareas: Multi-sede (varias iglesias en un mismo CRM)

**Entrada**: Documentos de diseño en `/specs/002-multi-sede-iglesias/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/rutas-ui.md, quickstart.md

**Pruebas**: Se incluyen pruebas automáticas (PHPUnit) porque el plan las exige como criterio de finalización (aislamiento por módulo, matriz de rutas, cobertura de modelos y migración). Hoy existen 54 pruebas de la funcionalidad 001 que deben seguir en verde.

**Organización**: Las tareas se agrupan por historia de usuario. Las historias P1 (US1, US2, US5) son el mínimo entregable; US3 y US4 son P2.

> ⚠️ **Seguridad de datos**: nunca ejecutar `php artisan migrate:fresh` contra la base del `.env` con datos reales; para pruebas manuales usar una base temporal (`DB_DATABASE=<ruta>\prueba.sqlite`). Hacer copia de seguridad antes de migrar en cualquier entorno con datos.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: Se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[Historia]**: Historia de usuario a la que pertenece (US1…US5)
- Todas las rutas son relativas a la raíz del repositorio (`C:\WorkSpace\TorreonFuerte-CRM`); el código vive en `laravel_app/`

## Convenciones de ruta

- Modelos: `laravel_app/app/Models/` · Servicios: `laravel_app/app/Services/` · Políticas: `laravel_app/app/Policies/`
- Controladores: `laravel_app/app/Http/Controllers/` · Middleware: `laravel_app/app/Http/Middleware/` · Requests: `laravel_app/app/Http/Requests/`
- Vistas: `laravel_app/resources/views/` · Migraciones: `laravel_app/database/migrations/` · Pruebas: `laravel_app/tests/`

---

## Fase 1: Preparación (infraestructura compartida)

**Propósito**: Línea base de pruebas y utilitarios de prueba para trabajar con varias sedes.

- [X] T001 Ejecutar `php artisan test` en `laravel_app/` y confirmar que las 54 pruebas actuales pasan (línea base antes de tocar nada)
- [X] T002 [P] Crear `laravel_app/database/factories/SedeFactory.php` (nombre único, ciudad, `activa` = true, con estados `inactiva()` y `conPastor()`)
- [X] T003 [P] Crear `laravel_app/tests/Support/ConSedes.php` (trait con `crearSede()`, `comoPastorDe(Sede)`, `comoLiderDe(Sede)`, `comoAdminEnSede(Sede)` que fija `sede_activa_id` en sesión y `comoAdminEnTodas()`), reutilizando `IniciaSesion`
- [X] T004 [P] Crear `laravel_app/tests/Support/EscenarioDosSedes.php` (trait que arma las sedes A y B, cada una con pastor, líder, personas, célula, evento con inscripción y pago, ministerio, red y zona, con nombres distintivos para detectar fugas)

---

## Fase 2: Fundamentos (prerrequisitos bloqueantes)

**Propósito**: Esquema, filtro global de sede, contexto y middleware que necesitan todas las historias.

**⚠️ CRÍTICO**: Ninguna historia puede empezar hasta terminar esta fase.

- [X] T005 Crear migración `laravel_app/database/migrations/2026_10_22_000001_create_sedes_tables.php`: tabla `sedes` (`nombre` texto único obligatorio máx. 255, `ciudad` texto obligatorio máx. 255, `direccion`/`telefono`/`email` texto nullable, `activa` booleano defecto `true`, `pastor_persona_id` y `pastora_persona_id` referencias nullable a `personas`, timestamps) y tabla `sede_cambios` (`sede_id`, `user_id` nullable, `accion` en `creada`/`editada`/`desactivada`/`reactivada`/`pastor_cambiado`/`pastora_cambiada`, `antes` y `despues` JSON nullable, `created_at`)
- [X] T006 Crear migración `laravel_app/database/migrations/2026_10_22_000002_add_sede_id_nullable_to_tables.php`: columna `sede_id` **nullable** con clave foránea a `sedes` e índice en `personas`, `redes`, `zonas`, `celulas`, `celula_reuniones`, `encuentros`, `eventos`, `evento_persona`, `evento_pagos`, `ministerios`, `persona_cambios`; en `users` columna `sede_id` nullable (solo el admin queda sin sede); en `personas` columnas `trasladada_a_sede_id` (referencia a `sedes`, nullable) y `trasladada_en` (fecha, nullable)
- [X] T007 [P] Crear modelos `laravel_app/app/Models/Sede.php` (sin filtro global; relaciones `pastor()` y `pastora()` hacia `Persona`, `usuarios()`, scope `activas()`) y `laravel_app/app/Models/SedeCambio.php` (con `antes`/`despues` como array)
- [X] T008 Crear `laravel_app/app/Models/Scopes/SedeScope.php` y `laravel_app/app/Models/Concerns/PerteneceASede.php` con las reglas de `research.md` D2: no admin con sede → `where sede_id = su sede`; no admin sin sede → `1 = 0`; admin con sede activa → `where sede_id = sede activa`; admin en "Todas las sedes" → sin filtro; sin usuario en la web → `1 = 0`; en consola → sin filtro. El evento `creating` asigna `sede_id` desde el contexto (los hijos heredan la sede del padre) y rechaza con 403 "Elige una sede activa para crear o editar" si no hay sede efectiva; incluye `scopeDeSede($id)` y `sede()` (relación)
- [X] T009 Crear `laravel_app/app/Services/SedeContext.php`: resuelve la sede efectiva por petición (no admin: `users.sede_id`; admin: `sede_activa_id` de la sesión o `null` = "Todas"), expone `sedeId()`, `esAdmin()`, `estaEnTodas()`, `sede()` y `puedeEscribir()`; se registra como singleton en `laravel_app/app/Providers/AppServiceProvider.php`
- [X] T010 Aplicar el trait `PerteneceASede` a `laravel_app/app/Models/Persona.php`, `Red.php`, `Zona.php`, `Celula.php`, `CelulaReunion.php`, `Encuentro.php`, `Evento.php`, `EventoPersona.php`, `EventoPago.php`, `Ministerio.php`, `PersonaCambio.php` y `User.php` (añadir `sede_id` a `#[Fillable]` y los campos `trasladada_*` a `Persona`)
- [X] T011 Actualizar `laravel_app/app/Models/User.php`: relación `sede()`, método `esAdmin()` (rol `admin`), mantener `tieneAccesoTotal()` como "ve todo dentro de su sede" y etiquetas de rol (`pastor` = "Pastor / Pastora"); el usuario autenticado se resuelve con `withoutGlobalScopes()` para evitar recursión
- [X] T012 Crear `laravel_app/app/Http/Middleware/ResuelveSede.php` y registrarlo en `laravel_app/bootstrap/app.php` para las rutas web: (1) cierra la sesión con mensaje claro si un no admin tiene sede inactiva o no tiene sede; (2) valida que la sede activa del admin exista; (3) bloquea POST/PUT/PATCH/DELETE con 403 "Elige una sede activa para crear o editar" (admin en "Todas") o "La sede está inactiva", con excepción del módulo Sedes, `sede-activa`, `login` y `logout`
- [X] T013 Actualizar `laravel_app/app/Http/Controllers/Controller.php` (`usuarioDeLaPeticion` con `User::withoutGlobalScopes()->with('persona')`) y `laravel_app/app/Http/Controllers/AuthController.php` (el login busca el usuario sin filtro global y rechaza con "Tu sede está inactiva; contacta al administrador" si es no admin con sede inactiva)
- [X] T014 [P] Actualizar `laravel_app/database/factories/PersonaFactory.php`, `CelulaFactory.php` y `UserFactory.php` para asignar `sede_id` (de la sede indicada o de una `SedeFactory` por defecto) y para que `lider()` cree red y zona en la misma sede
- [X] T015 Actualizar `laravel_app/tests/Support/IniciaSesion.php` para que `crearUsuario()` asigne sede a los no admin (sede por defecto compartida) y que `comoAdmin()` inicie con una sede activa por defecto; así las 54 pruebas actuales siguen pasando sin cambios de fondo
- [X] T016 [P] Prueba unitaria `laravel_app/tests/Unit/SedeScopeTest.php`: cada regla de filtro de T008 (no admin con sede, sin sede, admin con sede activa, admin en "Todas", sin usuario, consola) y la asignación automática y el rechazo de `sede_id` ausente al crear
- [X] T017 [P] Prueba `laravel_app/tests/Unit/ModelosConSedeTest.php`: recorre todas las tablas con columna `sede_id` (excepto `users`) y exige que su modelo use el trait `PerteneceASede`

**Punto de control**: Las 54 pruebas previas siguen en verde y el filtro de sede existe; las historias pueden empezar.

---

## Fase 3: Historia de usuario 1 - El admin crea una sede con su pastor y su pastora (Prioridad: P1) 🎯 MVP

**Objetivo**: Módulo solo admin para crear sedes con pastor y pastora en una operación atómica y trazable.

**Prueba independiente**: Un admin crea una sede con pastor y pastora; se inicia sesión como cada uno y ven su sede vacía y pueden registrar personas.

### Pruebas de la historia 1

- [X] T018 [P] [US1] Prueba `laravel_app/tests/Feature/Sedes/SedesAdminTest.php`: solo el admin accede a `/sedes` (pastor, líder y usuario reciben 403 y el menú no muestra "Sedes"); el listado muestra pastor, pastora y conteos
- [X] T019 [P] [US1] Prueba `laravel_app/tests/Feature/Sedes/AltaSedeAtomicaTest.php`: alta con pastor y pastora crea sede, personas y usuarios (rol `pastor`, `sede_id` de la nueva sede) y la bitácora `creada`; alta solo con pastor funciona; correo repetido en la pastora revierte todo (sin sede, personas ni usuarios a medias); nombre de sede repetido da error por campo
- [X] T020 [P] [US1] Prueba `laravel_app/tests/Feature/Sedes/PastoresIniciaSesionTest.php`: el pastor y la pastora recién creados inician sesión, ven el nombre de su sede en la barra, ven la sede vacía y pueden registrar una persona que queda en su sede

### Implementación de la historia 1

- [X] T021 [US1] Crear `laravel_app/app/Policies/SedePolicy.php` (`ver`, `crear`, `editar`, `desactivar`, `eliminar`, `trasladar`: solo `esAdmin()`) y registrar la habilidad en `laravel_app/app/Providers/AppServiceProvider.php` (directiva `@puede` ya existente)
- [X] T022 [US1] Crear `laravel_app/app/Http/Requests/SedeRequest.php`: `nombre` obligatorio, texto máx. 255, único en `sedes`; `ciudad` obligatorio máx. 255; `direccion`, `telefono`, `email` opcionales; bloques `pastor[...]` (nombres, apellidos, `email` obligatorio y único en `users`, `password` mínimo 8, `celular` opcional) y `pastora[...]` opcional con las mismas reglas (si se indica alguno de sus campos, se exigen todos los obligatorios)
- [X] T023 [US1] Implementar `laravel_app/app/Services/SedeService.php` con `crear(array $datos, User $por): Sede` en una transacción: crea la `Sede`; crea `Persona` (estado `miembro_activo`, en esa sede) y `User` (rol `pastor`, `sede_id`) del pastor y, si se indica, de la pastora; enlaza `pastor_persona_id`/`pastora_persona_id`; registra `SedeCambio` `creada`; si algo falla revierte todo
- [X] T024 [US1] Crear `laravel_app/app/Http/Controllers/SedesController.php` (`index` con conteos por sede sin filtro global, `create`, `store`, `show` básico) que autoriza con `SedePolicy`
- [X] T025 [P] [US1] Crear vistas `laravel_app/resources/views/sedes/index.blade.php`, `laravel_app/resources/views/sedes/create.blade.php` (datos de la sede + bloque Pastor + bloque Pastora opcional) y `laravel_app/resources/views/sedes/show.blade.php`
- [X] T026 [US1] Registrar las rutas de `sedes` (`index`, `create`, `store`, `show`) en `laravel_app/routes/web.php`, añadir la entrada de menú "Sedes" solo para admin en `laravel_app/resources/views/layouts/admin.blade.php` y mostrar el nombre de la sede del usuario en la barra superior (FR-023)

**Punto de control**: Se puede crear una sede completa y sus pastores inician sesión.

---

## Fase 4: Historia de usuario 2 - Cada sede ve y administra solo lo suyo (Prioridad: P1)

**Objetivo**: Aislamiento total entre sedes en todos los módulos: listados, selectores, búsquedas, enlaces directos, validaciones, usuarios y tablero.

**Prueba independiente**: Con dos sedes con datos distintos, un pastor de A no ve nada de B en ningún módulo, selector, búsqueda ni enlace directo; y viceversa.

### Pruebas de la historia 2

- [X] T027 [P] [US2] Prueba `laravel_app/tests/Feature/Aislamiento/AislamientoPersonasTest.php`: listados, búsqueda, filtros, selectores de líder/red/zona, "Sin líder asignado", ficha y edición por enlace directo (404) y creación (queda en la sede propia; no se puede asignar a un líder, red o zona de otra sede); el mismo documento en dos sedes coexiste y ninguna ve a la otra
- [X] T028 [P] [US2] Prueba `laravel_app/tests/Feature/Aislamiento/AislamientoCelulasTest.php`: listado, detalle, reuniones, asistencia, miembros y selectores de líder/co-líder solo de la sede; no se puede añadir un miembro de otra sede
- [X] T029 [P] [US2] Prueba `laravel_app/tests/Feature/Aislamiento/AislamientoEventosTest.php`: eventos, inscripciones y pagos, y encuentros con sus asistentes; enlaces directos de otra sede dan 404; no se inscribe a una persona de otra sede
- [X] T030 [P] [US2] Prueba `laravel_app/tests/Feature/Aislamiento/AislamientoMinisteriosTest.php`: ministerios, integrantes y encargado solo de la sede; catálogos de redes y zonas independientes por sede (mismo nombre en dos sedes es válido)
- [X] T031 [P] [US2] Prueba `laravel_app/tests/Feature/Aislamiento/AislamientoUsuariosTest.php`: el pastor solo ve usuarios de su sede, solo crea `lider_celula` y `user` (crear `pastor` o `admin` → 403), no mueve usuarios de otra sede (404), no cambia su propio rol ni sede; el correo repetido en otra sede se rechaza (único global)
- [X] T032 [P] [US2] Prueba `laravel_app/tests/Feature/Aislamiento/MatrizRutasTest.php`: recorre todas las rutas registradas con parámetros de modelo; como pastor de A, cada GET con IDs de recursos de B responde 404 o 403 y cada POST/PUT/DELETE con IDs ajenos no modifica nada (falla si aparece una ruta sin cobertura)
- [X] T033 [P] [US2] Prueba `laravel_app/tests/Feature/Aislamiento/UsuarioSinSedeTest.php`: un usuario no admin sin sede no ve datos y recibe el mensaje claro; el dashboard de un pastor solo cuenta datos de su sede

### Implementación de la historia 2

- [X] T034 [US2] Reemplazar en `laravel_app/app/Http/Requests/PersonaRequest.php` todas las reglas `Rule::exists(...)` por versiones limitadas a la sede efectiva (`->where('sede_id', app(SedeContext::class)->sedeId())`, ya que `exists` no usa el filtro global) para `lider_id`, `red_id` y `zona_id`, y hacer que la unicidad de `numero_documento` sea `Rule::unique('personas')->where('sede_id', ...)` ignorando la persona actual
- [X] T035 [US2] Aplicar el mismo límite por sede a las reglas `exists` y `unique` en `laravel_app/app/Http/Controllers/PersonasController.php`, `LideresController.php`, `SinLiderController.php`, `RedesController.php` (`destino_id` de fusión), `ZonasController.php`, `CelulasController.php` (`persona_id`, `lider_id`, `co_lider_id`, `asistio.*`), `EncuentrosController.php`, `EventosController.php` y `MinisteriosController.php`, y cambiar sus listas de personas por consultas con el filtro global (ya no `Persona::orderBy(...)` sin comprobar sede)
- [X] T036 [US2] Añadir en `laravel_app/app/Services/LiderazgoService.php` y en los métodos que asocian pivotes (`celula_persona`, `encuentro_persona`, `ministerio_persona`, `celula_reunion_asistencia`) una validación explícita de que ambos extremos pertenecen a la misma sede, con el mensaje de error por campo correspondiente
- [X] T037 [US2] Revisar `laravel_app/app/Services/AlcanceService.php` para que sus consultas con subconsultas del *query builder* (`celula_persona`, `celulas`) también queden acotadas por sede y que `redesVisibles`/`zonasVisibles` solo devuelvan las de la sede efectiva
- [X] T038 [US2] Refactorizar `laravel_app/app/Http/Controllers/UsersController.php` y su acceso: pasar de `ensureIsAdmin()` a una nueva política `laravel_app/app/Policies/UserPolicy.php` (admin: todo; pastor: usuarios de su sede, crear solo `lider_celula` y `user`; otros: 403); la sede del usuario nuevo es la del pastor (el admin la elige y el rol `admin` no lleva sede); validar `persona_id` de la misma sede; rechazar cambios de rol a `pastor`/`admin` o de sede por un pastor
- [X] T039 [US2] Actualizar `laravel_app/resources/views/users/index.blade.php`, `create.blade.php` y `edit.blade.php` (rol y sede según quién edita, sin ofrecer `pastor`/`admin` al pastor) y la visibilidad del menú "Usuarios" en `laravel_app/resources/views/layouts/admin.blade.php` para admin y pastor
- [X] T040 [US2] Actualizar `laravel_app/app/Http/Controllers/AuthController.php` (`dashboard`) para que los conteos y los totales de eventos usen el filtro de sede (usuarios, personas, líderes, redes, eventos y recaudos solo de la sede efectiva) y mostrar el nombre de la sede en `laravel_app/resources/views/dashboard.blade.php`
- [X] T041 [US2] Mostrar el mensaje claro ("Tu cuenta aún no está asociada a una sede; contacta al administrador") a un usuario no admin sin sede, en `laravel_app/resources/views/errors/403.blade.php` y en el cierre de sesión de `ResuelveSede`
- [X] T042 [US2] Completar los huecos que revelen `laravel_app/tests/Feature/Aislamiento/MatrizRutasTest.php` y las demás pruebas de `laravel_app/tests/Feature/Aislamiento/` corrigiendo los controladores de `laravel_app/app/Http/Controllers/` o las vistas de `laravel_app/resources/views/` afectados hasta dejarlas en verde

**Punto de control**: Las historias 1 y 2 funcionan; ningún módulo filtra datos entre sedes.

---

## Fase 5: Historia de usuario 5 - Los datos actuales pasan al nuevo modelo sin perderse (Prioridad: P1)

**Objetivo**: Migrar todos los datos existentes a una primera sede, endurecer el esquema y conservar credenciales.

**Prueba independiente**: Con una copia de los datos actuales, aplicar las migraciones y verificar que ningún registro se pierde, todos quedan en la primera sede y los usuarios existentes ven lo mismo que antes (el pastor actual ahora limitado a su sede).

### Pruebas de la historia 5

- [X] T043 [P] [US5] Prueba `laravel_app/tests/Feature/MigracionSedesTest.php` (sobre datos creados sin sede): todas las filas de las 11 tablas quedan en la primera sede con conteos idénticos antes y después, los admin no reciben sede, los pastores y demás usuarios quedan en la primera sede conservando credenciales y `sedes.pastor_persona_id` se llena con el primer pastor con persona vinculada
- [X] T044 [P] [US5] Prueba en `laravel_app/tests/Feature/MigracionSedesTest.php`: tras la migración no existe ninguna fila sin `sede_id` en las 11 tablas (SC-007), el documento puede repetirse entre sedes, y el nombre de red y de zona puede repetirse entre sedes

### Implementación de la historia 5

- [X] T045 [US5] Crear `laravel_app/app/Support/MigracionSedes.php` con `ejecutar()` idempotente: crea la primera sede (nombre desde `config('app.sede_inicial')`, por defecto "Sede principal"), asigna `sede_id` a todas las filas de las 11 tablas y a todos los usuarios que no sean `admin`, enlaza el pastor inicial y devuelve el reporte de conteos por tabla; y añadir `sede_inicial` (`env('SEDE_INICIAL', 'Sede principal')`) a `laravel_app/config/app.php` y `laravel_app/.env.example`
- [X] T046 [US5] Crear migración `laravel_app/database/migrations/2026_10_22_000003_migrate_data_to_first_sede.php` que llama a `MigracionSedes::ejecutar()`, escribe el reporte en el log e incluye `down()` que deja `sede_id` en `null` solo si aún no se endureció el esquema
- [X] T047 [US5] Crear migración `laravel_app/database/migrations/2026_10_22_000004_harden_sede_columns_and_uniques.php`: volver `sede_id` **no nulo** en las 11 tablas (no en `users`); eliminar los únicos globales de `personas.numero_documento`, `redes.clave` y `zonas.clave`; crear los únicos `(sede_id, numero_documento)` en `personas` y `(sede_id, clave)` en `redes` y en `zonas`; crear los índices `(sede_id, apellidos, nombres)` y `(sede_id, estado)` en `personas`, `(sede_id, nombre)` en `celulas` y `ministerios`, `(sede_id, fecha)` en `celula_reuniones` y `eventos`, `(sede_id, fecha_realizacion)` en `encuentros` y `(sede_id, persona_id)` en `persona_cambios`; compatible con SQLite y MySQL
- [X] T048 [US5] Verificar en una base temporal con datos antiguos (`DB_DATABASE=<ruta>\prueba.sqlite`, sin tocar `laravel_app/.env` ni `laravel_app/database/database.sqlite`): ejecutar `php artisan migrate` desde `laravel_app/`, comprobar conteos antes y después y que los pastores actuales solo ven la primera sede; anotar el resultado en `specs/002-multi-sede-iglesias/quickstart.md` (escenario 17)

**Punto de control**: Las historias 1, 2 y 5 funcionan; el esquema está endurecido y los datos existentes están a salvo.

---

## Fase 6: Historia de usuario 3 - El admin ve todo y puede cambiar de sede (Prioridad: P2)

**Objetivo**: Sede activa en la barra superior, modo "Todas las sedes" de solo consulta y resumen global por sede.

**Prueba independiente**: Con tres sedes con datos, el admin abre el resumen global, filtra por una sede, entra a su detalle y ve exactamente lo que ve el pastor de esa sede más la identificación de la sede.

### Pruebas de la historia 3

- [X] T049 [P] [US3] Prueba `laravel_app/tests/Feature/SedeActivaAdminTest.php`: el admin elige la sede A y solo ve datos de A; con "Todas las sedes" ve todo con columna **Sede**; en "Todas" cualquier POST/PUT/DELETE responde 403 "Elige una sede activa para crear o editar"; con sede activa lo que crea queda en esa sede; un no admin que envíe `sede-activa` recibe 403
- [X] T050 [P] [US3] Prueba `laravel_app/tests/Feature/DashboardGlobalAdminTest.php`: el admin en "Todas" ve la tabla por sede (personas, líderes, células, próximos eventos, recaudo) y el total global rotulado, y con sede activa ve el resumen de esa sede; los pastores nunca ven datos de otra sede

### Implementación de la historia 3

- [X] T051 [US3] Crear `laravel_app/app/Http/Controllers/SedeActivaController.php` (`POST /sede-activa` con `sede_id` de una sede existente o vacío = "Todas las sedes"; solo admin; guarda `sede_activa_id` en sesión) y registrar la ruta en `laravel_app/routes/web.php`
- [X] T052 [US3] Añadir en `laravel_app/resources/views/layouts/admin.blade.php` el selector "Sede activa" (con "Todas las sedes") solo para el admin y un aviso visible "Todas las sedes: solo consulta" cuando corresponda; el nombre de la sede de los demás roles sigue visible
- [X] T053 [US3] Añadir la columna **Sede** (visible solo con "Todas las sedes") a los listados `laravel_app/resources/views/personas/index.blade.php`, `lideres/index.blade.php`, `celulas/index.blade.php`, `encuentros/index.blade.php`, `eventos/index.blade.php`, `ministerios/index.blade.php`, `redes/index.blade.php` y `zonas/index.blade.php`, cargando la relación `sede`
- [X] T054 [US3] Implementar en `laravel_app/app/Http/Controllers/AuthController.php` el resumen global del admin en "Todas las sedes": consultas agrupadas por `sede_id` sin filtro global (personas, líderes, células, próximos eventos y recaudo por sede, más total global rotulado) y la vista en `laravel_app/resources/views/dashboard.blade.php`
- [X] T055 [US3] Añadir en `laravel_app/resources/views/sedes/show.blade.php` el resumen de la sede (personas, líderes, células, eventos próximos) y el botón "Trabajar en esta sede" que fija la sede activa

**Punto de control**: Las historias 1, 2, 3 y 5 funcionan.

---

## Fase 7: Historia de usuario 4 - El admin administra las sedes existentes (Prioridad: P2)

**Objetivo**: Editar sedes, cambiar pastor o pastora, desactivar y reactivar, impedir su eliminación con datos y trasladar personas entre sedes.

**Prueba independiente**: El admin cambia el pastor de una sede y desactiva otra; el nuevo pastor entra a su sede y los usuarios de la sede desactivada no pueden iniciar sesión, pero el admin sigue viendo sus datos.

### Pruebas de la historia 4

- [X] T056 [P] [US4] Prueba `laravel_app/tests/Feature/Sedes/PastoresSedeTest.php`: cambiar pastor o pastora crea la cuenta nueva (o designa una de la misma sede), la anterior pasa a `lider_celula` conservando su persona, queda la bitácora `pastor_cambiado`/`pastora_cambiada` y el nuevo pastor solo ve su sede
- [X] T057 [P] [US4] Prueba `laravel_app/tests/Feature/Sedes/DesactivarSedeTest.php`: al desactivar, sus usuarios no inician sesión y una sesión abierta se cierra en la siguiente petición; el admin ve sus datos en solo lectura (las escrituras responden 403 "La sede está inactiva"); reactivar restablece el acceso; eliminar una sede con datos responde 422; edición de datos de la sede queda en la bitácora `editada`
- [X] T058 [P] [US4] Prueba `laravel_app/tests/Feature/TrasladoPersonaTest.php`: el traslado crea el registro en la sede destino sin líder, célula ni ministerios y deja el de origen `inactivo` con `trasladada_a_sede_id` y su historial intacto; es 422 si la persona es líder con personas o células a cargo o encargada de un ministerio; falla si el documento ya existe en la sede destino; solo el admin puede trasladar; ninguna fila queda sin sede (SC-007)

### Implementación de la historia 4

- [X] T059 [US4] Completar `laravel_app/app/Services/SedeService.php` con `editar()`, `desactivar()`, `reactivar()` y `cambiarPastor(Sede, 'pastor'|'pastora', array $datos|User $existente, User $por)`: la cuenta anterior pasa a `lider_celula` y conserva su persona; cada operación registra `SedeCambio` con `antes`/`despues`
- [X] T060 [US4] Completar `laravel_app/app/Http/Controllers/SedesController.php` con `edit`, `update`, `pastores` (formulario) y `updatePastores`, `desactivar`, `reactivar` y `destroy` (422 "Solo se pueden desactivar sedes con datos" si tiene datos), y registrar las rutas correspondientes en `laravel_app/routes/web.php`
- [X] T061 [P] [US4] Crear vistas `laravel_app/resources/views/sedes/edit.blade.php` y `laravel_app/resources/views/sedes/pastores.blade.php`, y mostrar en `sedes/show.blade.php` la bitácora de la sede y los botones Desactivar/Reactivar
- [X] T062 [US4] Asegurar en `laravel_app/app/Http/Middleware/ResuelveSede.php` y `laravel_app/app/Http/Controllers/AuthController.php` que las sedes inactivas bloquean el inicio de sesión y cierran la sesión abierta con el mensaje "Tu sede está inactiva; contacta al administrador", y que el admin puede seguir viendo sus datos pero no escribirlos
- [X] T063 [US4] Implementar `laravel_app/app/Services/TrasladoService.php` con `trasladar(Persona, Sede, User $por)` según `research.md` D11: valida que no sea líder con personas o células a cargo ni encargada de un ministerio (422 con detalle), que la sede destino esté activa y sea distinta, y que el documento no exista ya en el destino; crea el registro nuevo (mismos datos de contacto y estado, sin líder, célula ni ministerios), marca el origen `inactivo` con `trasladada_a_sede_id` y `trasladada_en`, y registra bitácora en ambos registros, todo en una transacción
- [X] T064 [US4] Crear `laravel_app/app/Http/Controllers/TrasladosController.php` (`create` y `store` para `/personas/{persona}/trasladar`, solo admin con sede activa) y la vista `laravel_app/resources/views/traslados/create.blade.php` con el selector de sede destino y el detalle de lo que impide el traslado; registrar las rutas y añadir el botón "Trasladar" en `laravel_app/resources/views/personas/show.blade.php` solo para admin

**Punto de control**: Las cinco historias funcionan de forma independiente.

---

## Fase 8: Pulido y asuntos transversales

**Propósito**: Datos de ejemplo, escala, documentación y validación final.

- [X] T065 Actualizar `laravel_app/database/seeders/DatabaseSeeder.php` para sembrar un admin y **tres sedes** (por ejemplo, "Torreón Fuerte Centro", "Norte" y "Sur") cada una con pastor y pastora, líderes, personas, células, evento con inscripciones y pagos y ministerio distintos; sin tocar nunca bases fuera del entorno de desarrollo
- [X] T066 [P] Prueba de escala `laravel_app/tests/Feature/EscalaSedesTest.php`: crear 20 sedes consecutivas con `SedeService::crear` y comprobar que cada alta funciona igual, y que el listado de personas de una sede con 1.000 personas responde correctamente paginado (SC-003)
- [X] T067 [P] Revisar que ninguna vista ni controlador use consultas sin filtro global (`withoutGlobalScopes`) fuera de `SedesController`, `SedeService`, `TrasladoService`, `AuthController` (resumen global) y las pruebas; documentar la lista permitida en un comentario en `laravel_app/app/Models/Scopes/SedeScope.php`
- [X] T068 Ejecutar `php artisan test` en `laravel_app/` y corregir fallos hasta dejarlo en verde (las 54 pruebas previas más las nuevas)
- [ ] T069 (pendiente: recorrido visual manual en el navegador con una base temporal; los escenarios 1–11 y 13–18 están cubiertos por pruebas automáticas y las migraciones y el seeder se verificaron en bases temporales; falta confirmar a ojo la interfaz, p. ej. el selector de sede activa) Recorrer los 18 escenarios de `specs/002-multi-sede-iglesias/quickstart.md` en una base temporal (`DB_DATABASE=<ruta>\prueba.sqlite`, nunca la del `.env`), anotando cualquier desvío; confirmar la consulta de verificación (filas sin sede en las 11 tablas = 0)
- [X] T070 [P] Actualizar `README.md` con el modelo multi-sede (roles admin/pastor/líder/usuario, sede activa, módulo Sedes, traslados), la variable `SEDE_INICIAL`, el orden de migraciones y la advertencia de copia de seguridad antes de migrar

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias; empieza de inmediato.
- **Fundamentos (Fase 2)**: depende de la Fase 1 y **bloquea** todas las historias.
- **Historias**: dependen de la Fase 2; después pueden avanzar en paralelo o por prioridad (P1: US1, US2, US5 → P2: US3, US4).
- **Pulido (Fase 8)**: depende de las historias que se entreguen.

### Dependencias entre historias

- **US1 (P1)**: tras Fundamentos; no depende de otras historias.
- **US2 (P1)**: tras Fundamentos; reutiliza `SedeService` de US1 en las pruebas de escenario, por lo que conviene terminar T021–T024 antes de las pruebas T027–T033.
- **US5 (P1)**: tras Fundamentos; **debe terminar antes de desplegar en cualquier entorno con datos**, y T047 requiere T046 (los datos ya tienen sede antes de volver `sede_id` no nulo).
- **US3 (P2)**: tras US1 y US2 (modifica las mismas vistas y el layout).
- **US4 (P2)**: tras US1 (amplía `SedeService` y `SedesController`); el traslado (T063–T064) usa el filtro de US2.

### Dentro de cada historia

- Las pruebas se escriben primero y deben fallar antes de implementar.
- Modelos y servicios → controladores → vistas → rutas.

### Oportunidades de paralelismo

- Fase 1: T002, T003 y T004.
- Fase 2: T007, T014, T016 y T017 una vez creadas las migraciones (T005–T006) y el trait (T008).
- US1: T018, T019 y T020; T025 en paralelo con T024.
- US2: T027–T033 (siete archivos de prueba independientes).
- US5: T043 y T044.
- US3: T049 y T050.
- US4: T056, T057, T058 y T061.
- Pulido: T066, T067 y T070.

---

## Ejemplo de paralelismo: Historia de usuario 2

```text
# Pruebas de aislamiento en paralelo (archivos distintos):
T027 AislamientoPersonasTest | T028 AislamientoCelulasTest | T029 AislamientoEventosTest
T030 AislamientoMinisteriosTest | T031 AislamientoUsuariosTest | T032 MatrizRutasTest | T033 UsuarioSinSedeTest
```

---

## Estrategia de implementación

### Primero el MVP (historias P1)

1. Completar Fase 1 (Preparación) y Fase 2 (Fundamentos); confirmar que las 54 pruebas previas siguen en verde.
2. Completar US1 (crear sedes), US2 (aislamiento) y US5 (migración): son inseparables para poder desplegar con seguridad.
3. Validar con los escenarios 1–9 y 17 del quickstart en una base temporal.

### Entrega incremental

1. Preparación + Fundamentos → filtro de sede listo.
2. US1 → se pueden crear sedes con pastores.
3. US2 → aislamiento total verificado por la matriz de rutas.
4. US5 → datos actuales migrados y esquema endurecido (listo para producción).
5. US3 → experiencia del admin (sede activa, resumen global).
6. US4 → ciclo de vida de sedes y traslados.
7. Pulido → semillas de 3 sedes, escala, README y validación final.

### Notas

- **Riesgo principal**: una pantalla olvidada que filtre datos; mitigarlo con `ModelosConSedeTest`, `MatrizRutasTest` y las pruebas por módulo.
- Las reglas `exists`/`unique` de validación **no usan el filtro global**; cada una debe acotarse por sede (T034–T035).
- Hacer commit tras cada tarea o grupo lógico.
- No ejecutar `migrate:fresh` contra la base del `.env`; usar bases temporales para pruebas manuales.
