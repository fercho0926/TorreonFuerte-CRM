---

description: "Lista de tareas para la revisión y mejora de los módulos de Personas y Líderes"
---

# Tareas: Revisión y mejora de los módulos de Personas y Líderes

**Entrada**: Documentos de diseño en `/specs/001-revision-personas-lideres/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/rutas-ui.md, quickstart.md

**Pruebas**: Se incluyen pruebas automatizadas (PHPUnit) porque el plan y el quickstart las usan como criterio de finalización (`php artisan test`).

**Organización**: Las tareas se agrupan por historia de usuario para poder implementarlas y probarlas de forma independiente.

**Cambio de decisión (2026-10-19)**: se eliminó el concepto de **líder de zona** (rol `lider_zona`, supervisor de zona y ámbito por zona). La zona queda como sector geográfico opcional. Las tareas T005, T010, T013, T015, T016, T021, T041, T042, T046, T048 y T052 se implementaron con ese cambio.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: Se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[Historia]**: Historia de usuario a la que pertenece (US1…US5)
- Todas las rutas son relativas a la raíz del repositorio (`C:\WorkSpace\TorreonFuerte-CRM`); el código vive en `laravel_app/`

## Convenciones de ruta

- Modelos: `laravel_app/app/Models/` · Servicios: `laravel_app/app/Services/` · Políticas: `laravel_app/app/Policies/`
- Controladores: `laravel_app/app/Http/Controllers/` · Requests: `laravel_app/app/Http/Requests/`
- Vistas: `laravel_app/resources/views/` · Migraciones: `laravel_app/database/migrations/` · Pruebas: `laravel_app/tests/`

---

## Fase 1: Preparación (infraestructura compartida)

**Propósito**: Dejar listo el entorno de pruebas y los utilitarios compartidos.

- [X] T001 Configurar en `laravel_app/phpunit.xml` la base SQLite en memoria (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) y verificar que `php artisan test` corre las pruebas de ejemplo
- [X] T002 [P] Crear `laravel_app/database/factories/PersonaFactory.php` (con estados `lider()`, `visitante()`, `inactiva()`) y `laravel_app/database/factories/CelulaFactory.php`
- [X] T003 [P] Crear `laravel_app/tests/Support/IniciaSesion.php` (trait que simula la sesión propia: `Session::put(['is_logged_in' => true, 'user_id' => $user->id])`, con helpers `comoAdmin()`, `comoPastor()`, `comoLiderZona()`, `comoLider()`)
- [X] T004 [P] Registrar la configuración regional en español (`APP_LOCALE=es`, `Carbon::setLocale('es')`) en `laravel_app/app/Providers/AppServiceProvider.php`

---

## Fase 2: Fundamentos (prerrequisitos bloqueantes)

**Propósito**: Esquema, modelos, ámbito por rol y migración de datos que necesitan todas las historias.

**⚠️ CRÍTICO**: Ninguna historia de usuario puede empezar hasta terminar esta fase.

- [X] T005 Crear migración `laravel_app/database/migrations/2026_10_20_000001_create_redes_y_zonas_tables.php`: tabla `redes` (`nombre` único sin distinguir mayúsculas ni tildes, `activa` booleano) y tabla `zonas` (`nombre` único, `activa` booleano; sin supervisor)
- [X] T006 Crear migración `laravel_app/database/migrations/2026_10_20_000002_add_estado_zona_red_to_personas_table.php`: `tipo_documento` nullable; `numero_documento` nullable y único; `estado` texto con valores `visitante`, `nuevo_creyente`, `miembro_activo`, `inactivo` (índice); `red_id` nullable → `redes`; `zona_id` nullable → `zonas`; `inactivado_en` fecha nullable; `deleted_at` (soft deletes); índices en (`apellidos`,`nombres`), `celular`, `estado`, `lider_id`, `zona_id`
- [X] T007 Crear migración `laravel_app/database/migrations/2026_10_20_000003_create_persona_cambios_table.php`: `persona_id`, `user_id` nullable, `accion` (`creada`, `editada`, `estado`, `lider`, `rol_lider`, `inactivada`, `reactivada`, `eliminada`), `antes` y `despues` JSON nullable, `created_at`
- [X] T008 [P] Crear modelos `laravel_app/app/Models/Red.php`, `laravel_app/app/Models/Zona.php` (con relación `supervisor`) y `laravel_app/app/Models/PersonaCambio.php`
- [X] T009 Actualizar `laravel_app/app/Models/Persona.php`: añadir `estado`, `red_id`, `zona_id`, `inactivado_en` a `#[Fillable]`; `SoftDeletes`; constante `ESTADOS`; relaciones `red()`, `zona()`, `cambios()`; accesor de edad y de fecha legible; scopes `buscar($q)` (texto parcial en nombres, apellidos, documento y celular), `activas()` y `sinLider()`
- [X] T010 Crear `laravel_app/app/Services/AlcanceService.php` con `personasVisibles(User): Builder` según las reglas de ámbito de `data-model.md` (admin/pastor: todas; líder: personas con `lider_id` = su persona, miembros de sus células y él mismo; `user`: solo su registro) y `lideresVisibles(User)`, `celulasVisibles(User)`
- [X] T011 Sustituir en `laravel_app/app/Http/Controllers/Controller.php` los helpers `personasVisiblesIds()` y `celulasVisiblesIds()` por llamadas a `AlcanceService`, y actualizar los usos en `laravel_app/app/Http/Controllers/CelulasController.php`, `laravel_app/app/Http/Controllers/EncuentrosController.php` y `laravel_app/app/Http/Controllers/EventosController.php` para que sigan funcionando
- [X] T012 [P] Crear `laravel_app/app/Services/LiderazgoService.php` con la estructura base y `registrarCambio(Persona, accion, antes, despues)` que escribe en `persona_cambios`
- [X] T013 Crear migración de datos `laravel_app/database/migrations/2026_10_20_000004_migrate_personas_redes_zonas_estado.php`: normaliza `red` (texto: espacios, mayúsculas y tildes) hacia `redes` y asigna `red_id`; convierte los usuarios con rol `lider_zona` en `lider_celula`; pone `estado = miembro_activo` a las personas existentes; los miembros de célula sin `lider_id` toman el líder de la célula y los conflictos solo se informan (no se modifican); escribe un reporte de la migración en el log; incluye `down()`
- [X] T014 Actualizar `laravel_app/database/seeders/DatabaseSeeder.php` para sembrar admin, pastor, dos líderes con sus usuarios, red y zona, personas con y sin líder y una célula por líder (escenario del quickstart)
- [X] T015 [P] Actualizar `User::ROLE_LABELS` en `laravel_app/app/Models/User.php`: `lider_celula` → "Líder", `user` → "Usuario"; se retira el rol `lider_zona` (sin renombrar los valores guardados de `lider_celula`)
- [X] T016 [P] Crear `laravel_app/app/Http/Requests/PersonaRequest.php` con reglas compartidas: `nombres` y `apellidos` obligatorios; celular o email obligatorio al crear; `tipo_documento`/`numero_documento` nullable, obligatorios si `estado = miembro_activo` o `es_lider`; `numero_documento` único; `lider_id` ≠ la propia persona, existente y con `es_lider`; `red_id` obligatorio si `es_lider` y `zona_id` opcional
- [X] T017 [P] Prueba de migración en `laravel_app/tests/Feature/MigracionDatosTest.php`: redes con distinta escritura quedan en una sola, se crean zonas desde los líderes de zona y no se pierde ninguna persona

**Punto de control**: Esquema, ámbito y migración listos; las historias pueden empezar.

---

## Fase 3: Historia de usuario 1 - Un solo concepto claro de "persona" y de "líder" (Prioridad: P1) 🎯 MVP

**Objetivo**: Vocabulario único (Líder, Red, Zona), formulario único de persona con sección de liderazgo, ficha con rol y líder directo, y coherencia con células.

**Prueba independiente**: Mostrar listados y formularios a 3 usuarios nuevos; deben explicar qué es un líder, una red y una zona y a quién pertenece una persona dada, sin ayuda.

### Pruebas de la historia 1

- [X] T018 [P] [US1] Prueba en `laravel_app/tests/Feature/CoherenciaCelulaLiderTest.php`: un miembro solo puede asignarse a una célula de su líder directo; al cambiar el líder se retira de las células del líder anterior
- [X] T019 [P] [US1] Prueba en `laravel_app/tests/Feature/CatalogosTest.php`: crear la red "jovenes" existiendo "Jóvenes" es rechazado y sugiere la existente; no se elimina una red o zona con líderes

### Implementación de la historia 1

- [X] T020 [P] [US1] Crear `laravel_app/app/Http/Controllers/RedesController.php` y vistas `laravel_app/resources/views/redes/index.blade.php` y `laravel_app/resources/views/redes/_form.blade.php`: crear, renombrar, desactivar, fusionar (`POST /redes/{red}/fusionar`) y eliminar solo sin líderes (solo admin y pastor)
- [X] T021 [P] [US1] Crear `laravel_app/app/Http/Controllers/ZonasController.php` y vistas `laravel_app/resources/views/zonas/index.blade.php` y `laravel_app/resources/views/zonas/_form.blade.php`: crear, renombrar, desactivar y eliminar solo sin líderes (solo admin y pastor)
- [X] T022 [US1] Registrar las rutas de redes y zonas en `laravel_app/routes/web.php` y añadir las entradas "Redes" y "Zonas" al menú en `laravel_app/resources/views/layouts/admin.blade.php` (visibles solo para admin y pastor)
- [X] T023 [P] [US1] Crear `laravel_app/resources/views/partials/ayuda-terminos.blade.php` con las definiciones breves de Líder, Red y Zona y usarlo en los formularios y listados de personas y líderes
- [X] T024 [US1] Implementar en `laravel_app/app/Services/LiderazgoService.php` el cambio de líder (`cambiarLider(Persona, ?Persona)`): valida que no sea la propia persona ni cree un ciclo al recorrer la cadena de líderes, retira a la persona de las células del líder anterior y registra el cambio en la bitácora
- [X] T025 [US1] Actualizar `laravel_app/app/Http/Controllers/CelulasController.php` para que solo permita agregar miembros cuyo `lider_id` sea el líder o el co-líder de la célula, y elimine la sincronización inversa de `lider_id`
- [X] T026 [US1] Reescribir `laravel_app/resources/views/personas/_form.blade.php` como formulario único con sección "Liderazgo" (casilla es líder, red, zona, líder directo con texto de ayuda), reutilizado por Persona y por la designación de líder; retirar `laravel_app/resources/views/lideres/_form.blade.php`
- [X] T027 [US1] Actualizar `laravel_app/app/Http/Controllers/PersonasController.php` (`create`, `store`, `edit`, `update`) para usar `PersonaRequest`, guardar `red_id`/`zona_id` y delegar el cambio de líder a `LiderazgoService`
- [X] T028 [US1] Actualizar `laravel_app/resources/views/personas/show.blade.php` para mostrar el rol de liderazgo, la red y zona, el líder directo y sus personas y células con la etiqueta única "Líder"
- [X] T029 [US1] Reemplazar en `laravel_app/resources/views/layouts/admin.blade.php`, `laravel_app/resources/views/personas/` y `laravel_app/resources/views/lideres/` toda mención de "líder de red", "líder de célula" y "Líder de zona/red" por el vocabulario definido en FR-001

**Punto de control**: La historia 1 funciona y se puede validar sola.

---

## Fase 4: Historia de usuario 2 - Registrar, buscar y dar seguimiento a personas (Prioridad: P1)

**Objetivo**: Registro rápido de visitantes, búsqueda por texto parcial, paginación, estados, detección de duplicados e inactivación.

**Prueba independiente**: Registrar un visitante sin documento y encontrarlo por nombre parcial; inactivarlo y reactivarlo conservando su historial.

### Pruebas de la historia 2

- [X] T030 [P] [US2] Prueba en `laravel_app/tests/Feature/PersonasBusquedaTest.php`: búsqueda por parte de nombre, apellido, documento y celular; filtros combinados; paginación de 25; los inactivos no aparecen por defecto
- [X] T031 [P] [US2] Prueba en `laravel_app/tests/Feature/PersonasRegistroTest.php`: registrar visitante solo con nombres, apellidos y celular; exigir documento al pasar a `miembro_activo`; aviso de duplicado (mismo nombre y celular) y confirmación; documento duplicado rechazado
- [X] T032 [P] [US2] Prueba en `laravel_app/tests/Feature/PersonasInactivarTest.php`: inactivar y reactivar conserva el historial; eliminar con historial → 422 y solo admin puede eliminar sin historial

### Implementación de la historia 2

- [X] T033 [US2] Actualizar `laravel_app/app/Http/Controllers/PersonasController.php` `index`: filtros `q`, `estado`, `red_id`, `zona_id`, `lider_id`; base `AlcanceService::personasVisibles`; excluir `inactivo` salvo casilla "Incluir inactivos"; `paginate(25)` con `withQueryString()`; selectores de red/zona/líder solo con opciones del ámbito
- [X] T034 [US2] Reescribir `laravel_app/resources/views/personas/index.blade.php`: caja de búsqueda con envío diferido, filtros de estado/red/zona/líder, paginación, columna de estado con insignia, sin columna ID, mensaje claro de lista vacía
- [X] T035 [US2] Implementar en `laravel_app/app/Http/Controllers/PersonasController.php` `store`/`update` la detección de posibles duplicados (mismo nombre completo y celular): devolver al formulario con aviso y enlace a la ficha existente, y aceptar `confirmar_duplicado` para continuar
- [X] T036 [US2] Añadir al formulario `laravel_app/resources/views/personas/_form.blade.php` el selector de estado (por defecto `visitante`), el documento como opcional con texto de ayuda sobre cuándo se vuelve obligatorio y el aviso de duplicado con casilla de confirmación
- [X] T037 [US2] Implementar `inactivar` y `reactivar` en `laravel_app/app/Http/Controllers/PersonasController.php` y `laravel_app/app/Services/LiderazgoService.php` (fijan `estado` e `inactivado_en`, registran bitácora) y las rutas `POST /personas/{persona}/inactivar` y `POST /personas/{persona}/reactivar` en `laravel_app/routes/web.php`
- [X] T038 [US2] Cambiar `destroy` en `laravel_app/app/Http/Controllers/PersonasController.php`: solo admin; si hay historial (encuentros, eventos o pagos, células, ministerios) responder 422 con el mensaje "inactívala en su lugar"; sin historial, borrado lógico con bitácora
- [X] T039 [US2] Añadir en `laravel_app/resources/views/personas/show.blade.php` los botones Inactivar/Reactivar y reemplazar el botón "Eliminar" del listado por "Inactivar" en `laravel_app/resources/views/personas/index.blade.php`

**Punto de control**: Las historias 1 y 2 funcionan de forma independiente.

---

## Fase 5: Historia de usuario 3 - Gestionar líderes sin perder datos (Prioridad: P2)

**Objetivo**: Designar como líder a una persona existente, quitar el rol con reasignación guiada, resumen del líder y vista "Sin líder asignado".

**Prueba independiente**: Promover una persona existente a líder, asignarle personas y quitarle el rol; ninguna persona ni célula queda sin responsable sin decisión del usuario.

### Pruebas de la historia 3

- [X] T040 [P] [US3] Prueba en `laravel_app/tests/Feature/LiderazgoTest.php`: designar líder existente no duplica el registro; quitar el rol a un líder con personas o células sin `reasignar_a` ni `confirmar_sin_lider` → 422 con conteos; con reasignación las personas y células pasan al nuevo líder; no se permiten ciclos
- [X] T041 [P] [US3] Prueba en `laravel_app/tests/Feature/SinLiderTest.php`: `/sin-lider` lo ven admin y pastor; un líder recibe 403; asignar un líder dentro del ámbito saca a la persona de la vista

### Implementación de la historia 3

- [X] T042 [US3] Implementar en `laravel_app/app/Services/LiderazgoService.php` `designarLider(Persona, Red, Zona)` (exige documento, no crea registro nuevo, registra bitácora) y `quitarRol(Persona, ?Persona $reasignarA, bool $confirmarSinLider)` (cuenta personas y células afectadas, reasigna o exige confirmación, impide dejar una célula con un líder que ya no es líder)
- [X] T043 [US3] Reescribir `laravel_app/app/Http/Controllers/LideresController.php`: `index` con `q`, `red_id`, `zona_id` y paginación; `designar`/`storeDesignar` (buscar persona existente o ir a "Nueva persona como líder" con el formulario único); `quitar` (pantalla de impacto) y `destroy` con `reasignar_a` / `confirmar_sin_lider`; eliminar la creación de personas duplicadas
- [X] T044 [P] [US3] Crear vistas `laravel_app/resources/views/lideres/designar.blade.php` (buscador de personas existentes) y `laravel_app/resources/views/lideres/quitar.blade.php` (conteo de personas y células afectadas y selector de líder de destino)
- [X] T045 [US3] Actualizar `laravel_app/resources/views/lideres/index.blade.php` (sin columna ID; nombre, red, zona, nº de personas, nº de células, celular; búsqueda y paginación) y `laravel_app/resources/views/lideres/show.blade.php` (personas, células y resumen: nº de personas, nº de células y última reunión)
- [X] T046 [US3] Crear `laravel_app/app/Http/Controllers/SinLiderController.php` y `laravel_app/resources/views/sin-lider/index.blade.php`: lista paginada de personas sin líder visible para admin y pastor con selector para asignar líder; rutas `GET /sin-lider` y `POST /sin-lider/{persona}/asignar`
- [X] T047 [US3] Registrar las rutas de `lideres/designar`, `lideres/{lider}/quitar` y `sin-lider` en `laravel_app/routes/web.php` y añadir la entrada "Sin líder asignado" al menú en `laravel_app/resources/views/layouts/admin.blade.php` con contador de pendientes

**Punto de control**: Las historias 1 a 3 funcionan de forma independiente.

---

## Fase 6: Historia de usuario 4 - Cada usuario ve y hace solo lo que le corresponde (Prioridad: P2)

**Objetivo**: Ámbito por rol en listados, selectores y destinos de asignación; acciones ocultas según permiso; 403 con mensaje claro.

**Prueba independiente**: Iniciar sesión como líder y comprobar que no ve personas, líderes ni redes ajenas y que no puede reasignar personas fuera de su ámbito.

### Pruebas de la historia 4

- [X] T048 [P] [US4] Prueba en `laravel_app/tests/Feature/AlcancePorRolTest.php`: por cada rol (admin, pastor, líder, usuario) verifica listados, ficha (404 fuera de ámbito), selectores de líder/red y destino de asignación; un líder no puede asignar una persona a un líder ajeno (422/403)
- [X] T049 [P] [US4] Prueba en `laravel_app/tests/Feature/PermisosAccionesTest.php`: acciones no permitidas responden 403 con el mensaje "No tienes permiso para esta acción" y la vista no renderiza los botones correspondientes

### Implementación de la historia 4

- [X] T050 [P] [US4] Crear `laravel_app/app/Policies/PersonaPolicy.php` (`ver`, `crear`, `editar`, `designarLider`, `inactivar`, `eliminar`) apoyada en `AlcanceService`, y un helper `puede($habilidad, $modelo)` en `laravel_app/app/Http/Controllers/Controller.php` que usa `Gate::forUser($this->usuarioActual())`
- [X] T051 [US4] Reemplazar en `laravel_app/app/Http/Controllers/PersonasController.php` y `laravel_app/app/Http/Controllers/LideresController.php` las redirecciones silenciosas (`ensurePuedeVerPersona`, `ensurePuedeGestionarEstructura`) por `abort(403, 'No tienes permiso para esta acción')` y `abort(404)` para personas fuera de ámbito
- [X] T052 [US4] Validar en `laravel_app/app/Http/Requests/PersonaRequest.php` y `laravel_app/app/Services/LiderazgoService.php` que `lider_id`, `zona_id` y `red_id` de destino estén dentro del ámbito del usuario que guarda (FR-018) y que los selectores de `personas/_form.blade.php`, `lideres/designar.blade.php` y `sin-lider/index.blade.php` solo ofrezcan opciones del ámbito
- [X] T053 [US4] Ocultar en `laravel_app/resources/views/personas/`, `laravel_app/resources/views/lideres/` y `laravel_app/resources/views/layouts/admin.blade.php` los botones y entradas de menú que el rol no puede usar, según `PersonaPolicy`
- [X] T054 [US4] Mostrar a un usuario sin persona vinculada un mensaje claro ("Tu cuenta aún no está asociada a una persona") en `laravel_app/resources/views/personas/index.blade.php` en lugar de una lista vacía
- [X] T055 [US4] Mostrar la bitácora de cambios solo a admin y pastor en `laravel_app/resources/views/personas/show.blade.php`, usando `PersonaCambio` ordenada por fecha

**Punto de control**: Las historias 1 a 4 funcionan de forma independiente.

---

## Fase 7: Historia de usuario 5 - Usar el módulo cómodamente desde el celular (Prioridad: P3)

**Objetivo**: Tarjetas en pantallas pequeñas, llamadas y WhatsApp desde la ficha, fechas legibles con edad.

**Prueba independiente**: Abrir listado y ficha en 375 px de ancho y completar un registro sin zoom ni desplazamiento horizontal.

- [X] T056 [P] [US5] Crear `laravel_app/resources/views/partials/persona-tarjeta.blade.php` (nombre, estado, líder, acciones rápidas) y `laravel_app/resources/views/partials/acciones-contacto.blade.php` (enlaces `tel:` y `https://wa.me/` solo con celular)
- [X] T057 [US5] Mostrar en `laravel_app/resources/views/personas/index.blade.php` y `laravel_app/resources/views/lideres/index.blade.php` las tarjetas en pantallas pequeñas (`d-md-none`) y la tabla solo desde `md` (`d-none d-md-block`)
- [X] T058 [US5] Incluir `partials/acciones-contacto` en `laravel_app/resources/views/personas/show.blade.php` y `laravel_app/resources/views/lideres/show.blade.php`
- [X] T059 [P] [US5] Mostrar las fechas en formato legible en español (ej. "19 de octubre de 2026") y la edad cuando exista fecha de nacimiento en `laravel_app/resources/views/personas/show.blade.php`, `laravel_app/resources/views/lideres/show.blade.php` y los formularios
- [X] T060 [P] [US5] Prueba en `laravel_app/tests/Feature/PresentacionPersonasTest.php`: la ficha muestra enlaces `tel:` y `wa.me` con celular, no los muestra sin celular, y la fecha aparece en español con la edad

**Punto de control**: Todas las historias funcionan de forma independiente.

---

## Fase 8: Pulido y asuntos transversales

**Propósito**: Cierre de migración, limpieza y validación final.

- [X] T061 Crear migración `laravel_app/database/migrations/2026_10_21_000001_drop_red_text_from_personas_table.php` que elimina la columna de texto `red` (con `down()`), y retirar `red` de `#[Fillable]` en `laravel_app/app/Models/Persona.php` y de cualquier consulta restante
- [X] T062 [P] Eliminar de las vistas y controladores el uso de `$persona->red` y de `Persona::lideres()->pluck('red')`, y revisar `laravel_app/resources/views/dashboard.blade.php` por usos del vocabulario antiguo
- [X] T063 [P] Verificar que ningún listado ni ficha de personas, líderes o catálogos muestre el identificador técnico (FR-020) revisando `laravel_app/resources/views/personas/`, `laravel_app/resources/views/lideres/`, `laravel_app/resources/views/redes/` y `laravel_app/resources/views/zonas/`
- [X] T064 Ejecutar `php artisan test` en `laravel_app/` y corregir fallos hasta dejarlo en verde
- [ ] T065 (pendiente: recorrido visual manual; los escenarios 1–11 y 13 están cubiertos por pruebas automáticas, falta confirmar a ojo el escenario 12 en móvil) Recorrer los 13 escenarios de `specs/001-revision-personas-lideres/quickstart.md` con `php artisan migrate:fresh --seed` y `php artisan serve`, anotando cualquier desvío
- [X] T066 [P] Actualizar `README.md` con los roles y el vocabulario (Líder, Red, Zona), el flujo de migración de datos y los comandos de prueba

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias; empieza de inmediato.
- **Fundamentos (Fase 2)**: depende de la Fase 1 y **bloquea** todas las historias.
- **Historias (Fases 3–7)**: dependen de la Fase 2; después pueden avanzar en paralelo o por prioridad (P1 → P2 → P3).
- **Pulido (Fase 8)**: depende de las historias que se vayan a entregar; T061 requiere que todas las vistas ya no usen `red` (US1–US3).

### Dependencias entre historias

- **US1 (P1)**: tras Fundamentos; no depende de otras historias.
- **US2 (P1)**: tras Fundamentos; reutiliza el formulario de US1 (T026) para T036, por lo que conviene terminar T026–T027 antes.
- **US3 (P2)**: tras Fundamentos; usa `LiderazgoService` (T024) y el formulario único (T026) de US1.
- **US4 (P2)**: tras Fundamentos; endurece lo creado en US1–US3 (T051–T053 tocan sus controladores y vistas).
- **US5 (P3)**: tras US2 y US3 (modifica sus listados y fichas).

### Dentro de cada historia

- Las pruebas se escriben primero y deben fallar antes de implementar.
- Modelos y servicios → controladores → vistas → rutas.

### Oportunidades de paralelismo

- Fase 1: T002, T003 y T004.
- Fase 2: T008, T012, T015, T016 y T017 en paralelo una vez creadas las migraciones (T005–T007).
- US1: T018 y T019; T020, T021 y T023.
- US2: T030, T031 y T032.
- US3: T040, T041 y T044.
- US4: T048, T049 y T050.
- US5: T056, T059 y T060.

---

## Ejemplo de paralelismo: Historia de usuario 1

```text
# Pruebas en paralelo:
T018 CoherenciaCelulaLiderTest  |  T019 CatalogosTest

# Catálogos y ayuda en paralelo:
T020 RedesController + vistas  |  T021 ZonasController + vistas  |  T023 partial ayuda-terminos
```

---

## Estrategia de implementación

### Primero el MVP (solo la historia de usuario 1)

1. Completar Fase 1 (Preparación) y Fase 2 (Fundamentos).
2. Completar la Fase 3 (US1) y validar con el escenario 9 y las pruebas de coherencia.
3. Demostrar el vocabulario y el formulario único.

### Entrega incremental

1. Preparación + Fundamentos → base lista.
2. US1 → vocabulario y modelo claros (MVP).
3. US2 → registro y búsqueda (valor diario inmediato).
4. US3 → gestión segura de líderes y "Sin líder asignado".
5. US4 → privacidad por ámbito reforzada.
6. US5 → experiencia móvil.
7. Pulido → eliminar columna `red`, README, validación final.

### Notas

- **Riesgo de datos**: ejecutar T013 y T061 primero en una copia de la base de producción; T061 solo después de verificar la migración.
- El valor del rol guardado en base de datos (`lider_celula`, `lider_zona`) no cambia; solo cambian las etiquetas.
- Hacer commit tras cada tarea o grupo lógico.
