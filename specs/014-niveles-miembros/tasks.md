---

description: "Lista de tareas de la funcionalidad 014: niveles de formación de los miembros"
---

# Tareas: Niveles de formación de los miembros (camino de retiros)

**Entrada**: documentos de diseño en `/specs/014-niveles-miembros/`

**Requisitos previos**: plan.md, spec.md, research.md, data-model.md, contracts/ (rutas-y-permisos, camino-y-componentes), quickstart.md

**Pruebas**: se incluyen (constitución, principio IV; RF-019, CE-008, CE-009). Cada prueba se escribe **antes** de la implementación que verifica.

**Organización**: por historia de usuario. Todas las rutas son relativas a `laravel_app/`. Los miembros siguen siendo el modelo `Persona` (tabla `personas`); el modelo de niveles se llama `NivelFormacion` para no chocar con `App\Support\Niveles` (niveles de permiso).

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[HU1..HU5]**: historia de usuario a la que pertenece (spec.md)

---

## Fase 1: Preparación

- [X] T001 Confirmar la línea base antes de empezar: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` en verde; anotar los números actuales en `specs/014-niveles-miembros/quickstart.md` (sección «Línea base»)
- [X] T002 [P] Crear los directorios `tests/Unit/Niveles/`, `tests/Feature/Niveles/` y `resources/views/niveles/`, `resources/views/components/niveles/`

---

## Fase 2: Fundamentos (bloquea todas las historias)

**Propósito**: tablas, modelos, módulo de permisos, servicio del camino y sincronía de avances.

### Pruebas

- [X] T003 [P] Prueba de migraciones: crean `niveles_formacion`, `avances_nivel` (con `unique(persona_id, nivel_id)`), `nivel_cambios` y `encuentros.nivel_id`; cargan los 4 niveles en orden y activos (idempotente); dan el permiso `niveles` (administrador 2, pastor 2, líder 1, usuario 1, roles personalizados 0) en `tests/Feature/Niveles/MigracionNivelesTest.php`
- [X] T004 [P] Prueba unitaria de `CaminoService::camino` (sin avances: sin iniciar y siguiente = nivel 1; avances 1 y 2: actual = 2, siguiente = 3; todos completados: terminado; nivel 3 sin el 2: el 2 queda pendiente `saltado` y es el siguiente; niveles inactivos no se dibujan; sin niveles: estructura vacía) en `tests/Unit/Niveles/CaminoServiceTest.php`
- [X] T005 [P] Prueba unitaria de `CaminoService::geometria` (n = 0, 1, 4, 12; puntos con `x` entre 0 y 100, `y` creciente, patrón en zigzag izquierda–centro–derecha–centro, trazado `sendero` comienza con `M` y tiene n−1 curvas) en `tests/Unit/Niveles/GeometriaCaminoTest.php`
- [X] T006 [P] Prueba de `AvancesNivelService::recalcular` (crea el automático desde el primer encuentro **realizado** al que asistió; no cuenta encuentros cancelados o planificados; retira el automático al desmarcar la asistencia, al cancelar, al desvincular o al cambiar de nivel; no toca el manual; no duplica; si queda otro encuentro del mismo nivel, la fila pasa a ese) en `tests/Feature/Niveles/SincroniaEncuentrosTest.php`
- [X] T007 [P] Prueba de los alcances `Persona::enNivel($id)` y `Persona::sinNivel()` (nivel actual = mayor posición completada entre niveles activos; los inactivos no cuentan; coinciden con `CaminoService`) en `tests/Feature/Niveles/AlcanceNivelTest.php`

### Implementación

- [X] T008 Crear las migraciones en `database/migrations/` (fechas posteriores a `2026_10_28_000001`): `niveles_formacion` (`nombre` máx. 80 único sin distinguir mayúsculas, `descripcion` máx. 500 opcional, `posicion` entero único, `activo` por defecto verdadero), `avances_nivel` (`sede_id`, `persona_id` cascada, `nivel_id`, `fecha` obligatoria, `origen` `encuentro`|`manual`, `encuentro_id` opcional, `registrado_por` opcional, `nota` máx. 255; `unique(persona_id, nivel_id)`; índices `(sede_id, nivel_id)` y `(persona_id)`), `nivel_cambios` (`nivel_id` opcional, `user_id` opcional, `accion`, `nombre`, `antes`, `despues` JSON), columna `encuentros.nivel_id` opcional, y carga de los 4 niveles iniciales
- [X] T009 Crear la migración de permisos del módulo `niveles` para los roles existentes (matriz administrador 2, pastor 2, líder 1, usuario 1; roles personalizados 0; idempotente) en `database/migrations/` siguiendo el patrón de `2026_10_28_000001_create_roles_y_permisos.php`
- [X] T010 [P] Crear los modelos `NivelFormacion` (alcances `activos()` y `ordenados()`; relaciones `avances`, `encuentros`), `AvanceNivel` (usa `PerteneceASede`; relaciones `persona`, `nivel`, `encuentro`, `registradoPor`; etiquetas de origen) y `NivelCambio` en `app/Models/`, con fábricas `NivelFormacionFactory` y `AvanceNivelFactory` en `database/factories/`
- [X] T011 [P] Agregar a `app/Models/Persona.php` la relación `avances()` y los alcances `enNivel($nivelId)` y `sinNivel()` (subconsulta agregada del máximo de posición entre avances de niveles activos; compatible con SQLite, MySQL y SQL Server) y a `app/Models/Encuentro.php` la relación `nivel()` y `nivel_id` en `#[Fillable]`
- [X] T012 Registrar el módulo `niveles` en `app/Support/Modulos.php` (constante `NIVELES`, etiqueta «Niveles», icono `fa-route`, no solo lectura, descripción «Plan de niveles, avance de los miembros y quién está en cada nivel», rutas `niveles.*`)
- [X] T013 Crear `app/Services/CaminoService.php` con `camino(Persona)` (estaciones, actual, siguiente, completados, total, terminado, sin_iniciar; una consulta de niveles activos y una de avances) y `geometria(int)` (zigzag, puntos y trazado SVG) según `contracts/camino-y-componentes.md`
- [X] T014 Crear `app/Services/AvancesNivelService.php` con `registrarManual`, `quitarManual`, `recalcular(Persona, NivelFormacion)`, `recalcularEncuentro(Encuentro, ?int $nivelAnterior)` y `copiarATrasladado(Persona $origen, Persona $destino)`, registrando cada cambio con `LiderazgoService::registrarCambio` (acciones nuevas `nivel_registrado`, `nivel_quitado`, `nivel_automatico` en `app/Models/PersonaCambio.php`)
- [X] T015 Registrar `avances_nivel` en `MigracionSedes::TABLAS` de `app/Support/MigracionSedes.php` y ajustar `tests/Feature/MigracionSedesTest.php` si recorre la lista

**Punto de control**: migraciones, modelos, módulo de permisos, `CaminoService` y sincronía de avances con sus pruebas en verde.

---

## Fase 3: Historia de Usuario 1 - Ver el camino de niveles de un miembro (Prioridad: P1) MVP

**Objetivo**: la ficha del miembro muestra el camino gráfico y las listas muestran el progreso resumido (RF-005 a RF-008, RF-013, RF-014).

**Prueba independiente**: ficha de un miembro con dos niveles completados: estaciones 1 y 2 completas, la 3 siguiente y la 4 pendiente, también a 360 px y con lector de pantalla.

### Pruebas

- [X] T016 [P] [HU1] Prueba de la ficha: con lectura en `niveles` muestra el camino con estados y fechas en texto («Completado el …», «Siguiente», «Pendiente», «Pendiente (saltado)»), «Aún no inicia su camino», «¡Camino terminado!»; sin acceso a `niveles` no se muestra; con solo lectura no aparecen botones de registro en `tests/Feature/Niveles/CaminoFichaTest.php`
- [X] T017 [P] [HU1] Prueba del componente de puntos: un punto por nivel activo, etiqueta accesible «2 de 4 niveles; actual: …», «Sin iniciar», sin consultas por fila (una sola consulta adicional por página con carga ansiosa) en `tests/Feature/Niveles/PuntosProgresoTest.php`
- [X] T018 [P] [HU1] Agregar `personas/3` y `personas/1` (con avances sembrados) a la auditoría móvil: sin desborde, estaciones y botones de al menos 44 px, texto de estado visible, y verificar el camino con 0, 4 y 12 estaciones en `tests/movil/niveles.spec.js`

### Implementación

- [X] T019 [P] [HU1] Crear el componente `resources/views/components/niveles/camino.blade.php` (sección con `<ol>`, SVG de sendero, estaciones con círculo de 64 px, número, nombre, estado en texto e icono distinto por estado, `<details>` con fecha, origen, quién registró; mensajes de estados vacíos) según `contracts/camino-y-componentes.md`
- [X] T020 [P] [HU1] Crear el componente `resources/views/components/niveles/puntos.blade.php` (fila de puntos con `role="img"` y `aria-label`, nombre del nivel actual en texto)
- [X] T021 [HU1] Crear `resources/css/niveles.css` (camino, estaciones y estados completado/siguiente/pendiente, sendero, pulso solo con `prefers-reduced-motion: no-preference`, círculo de 44 px mínimo a 320 px, texto a 16 px en formularios, apilado en una columna en teléfono y ancho máximo centrado en escritorio) e importarlo en `resources/css/app.css` antes de `movil.css`
- [X] T022 [HU1] Mostrar el camino en `resources/views/personas/show.blade.php` (tarjeta «Camino de niveles» con `<x-niveles.camino>`, solo con lectura en `niveles`), con `PersonasController@show` cargando `CaminoService::camino`
- [X] T023 [HU1] Agregar la columna «Nivel» (puntos y nivel actual, prioridad 3 en teléfono, orden por posición del nivel actual) y el filtro de lista (niveles activos y «Sin iniciar», parámetro `nivel`) a `app/Grillas/PersonasGrilla.php` y a `resources/views/personas/_celda.blade.php`, con carga ansiosa de avances en la consulta base y visibles solo con lectura en `niveles`
- [X] T024 [HU1] Ejecutar `php artisan test --filter=Niveles`, `npm run build` y `npm run test:movil -- niveles`; corregir lo que falle

**Punto de control**: la ficha y el listado de miembros muestran el camino y el progreso.

---

## Fase 4: Historia de Usuario 2 - Registrar y ordenar los niveles en un módulo (Prioridad: P1)

**Objetivo**: el módulo «Niveles» permite crear, editar, ordenar, desactivar, reactivar y eliminar niveles (RF-001 a RF-004, RF-020, RF-021).

**Prueba independiente**: abrir Niveles, ver los cuatro niveles, crear un quinto, subirlo a la posición 2 y verificar el camino de un miembro.

### Pruebas

- [X] T025 [P] [HU2] Prueba del módulo: lista los 4 niveles numerados 1 a 4; crear (al final), nombre repetido (sin distinguir mayúsculas) con error junto al campo, editar, subir y bajar (intercambia con el vecino; en los extremos no hace nada), desactivar y reactivar, eliminar solo sin avances ni encuentros (si no, error con sugerencia de desactivar) en `tests/Feature/Niveles/ModuloNivelesTest.php`
- [X] T026 [P] [HU2] Prueba de permisos: sin acceso (403 y sin menú), solo lectura (sin botones y rutas de escritura responden 403), escritura (todo); administrador en «Todas las sedes» puede administrar el plan (es común) en `tests/Feature/Niveles/PermisosNivelesTest.php`
- [X] T027 [P] [HU2] Prueba del historial del plan: cada alta, edición, orden, desactivación, reactivación y eliminación deja un `NivelCambio` con quién, cuándo y antes/después en `tests/Feature/Niveles/HistorialNivelesTest.php`
- [X] T028 [P] [HU2] Prueba de que el nuevo módulo aparece en la matriz de roles (pestaña Roles) y que un rol nuevo parte sin acceso a Niveles en `tests/Feature/Roles/ModuloNivelesEnRolesTest.php`

### Implementación

- [X] T029 [P] [HU2] Crear `app/Services/NivelesService.php` (`crear`, `actualizar`, `subir`, `bajar`, `desactivar`, `reactivar`, `eliminar` con transacción, posiciones consecutivas 1..n y registro en `NivelCambio`) y `app/Http/Requests/NivelRequest.php` (`nombre` obligatorio, máx. 80, único sin distinguir mayúsculas ignorando el propio; `descripcion` opcional, máx. 500)
- [X] T030 [HU2] Crear `app/Http/Controllers/NivelesController.php` (`index`, `create`, `store`, `edit`, `update`, `subir`, `bajar`, `desactivar`, `reactivar`, `destroy`) y declarar las rutas del contrato en `routes/web.php` dentro de `Route::middleware('modulo:niveles')`, con las fijas antes de `/niveles/{nivel}`
- [X] T031 [P] [HU2] Crear las vistas `resources/views/niveles/create.blade.php`, `edit.blade.php` y `_form.blade.php` (componentes `x-form.*`)
- [X] T032 [HU2] Crear `resources/views/niveles/index.blade.php`: lista del plan como lista de tarjetas (no tabla) con número, nombre, estado, controles de subir, bajar, editar, desactivar y eliminar solo con escritura (botones de 44 px), y historial reciente al final
- [X] T033 [HU2] Agregar la opción «Niveles» (icono `fa-route`, módulo `niveles`) al grupo Congregación de `config/menu.php`
- [X] T034 [HU2] Ejecutar `php artisan test --filter="Niveles|Roles|Menu"` y corregir lo que falle (incluida la matriz de roles)

**Punto de control**: el plan de niveles se administra por completo y respeta los permisos.

---

## Fase 5: Historia de Usuario 3 - Registrar el avance de un miembro (Prioridad: P1)

**Objetivo**: avance manual desde la ficha y avance automático por asistencia a encuentros vinculados (RF-009 a RF-012, RF-021, RF-022).

**Prueba independiente**: vincular un encuentro al nivel 1, registrar la asistencia de un miembro y ver el nivel 1 completado con la fecha del encuentro; registrar a mano el nivel 2 de otro miembro.

### Pruebas

- [X] T035 [P] [HU3] Prueba de registro manual: crea con origen «Registro manual», fecha y quién lo registró; fecha por defecto hoy, futura rechazada; nivel ya completado rechazado sin duplicar; nivel inactivo rechazado; miembro fuera de alcance 404; solo escritura en `niveles`; quitar solo avances manuales; el automático no se puede quitar (mensaje) en `tests/Feature/Niveles/AvancesManualesTest.php`
- [X] T036 [P] [HU3] Prueba de integración con encuentros: formulario con selector de nivel (opcional), asistencia (`encuentros.asistencia`), alta y edición con miembros (`sync`), cancelar, desvincular, cambiar de nivel y eliminar el encuentro recalculan los avances automáticos y dejan constancia en el historial del miembro en `tests/Feature/Niveles/EncuentrosNivelTest.php`
- [X] T037 [P] [HU3] Prueba del traslado: el avance del miembro se copia al nuevo registro de la sede de destino (con nota «Trasladado desde …» y la fecha original) y el registro de origen conserva el suyo en `tests/Feature/Niveles/TrasladoNivelesTest.php`
- [X] T038 [P] [HU3] Prueba de aislamiento: un pastor o líder de otra sede no puede registrar ni quitar avances de miembros ajenos ni ver sus avances; un líder solo registra en su rama según el alcance del rol en `tests/Feature/Niveles/AislamientoNivelesTest.php`

### Implementación

- [X] T039 [HU3] Crear `app/Http/Controllers/AvancesNivelController.php` (`store`, `destroy`) con validación del contrato y rutas `niveles.avances.store` y `niveles.avances.destroy` en `routes/web.php`
- [X] T040 [HU3] Agregar al componente `resources/views/components/niveles/camino.blade.php` el formulario «Registrar avance» (`<details>`: nivel activo pendiente, fecha, nota, «Marcar completado») y el botón «Quitar» con confirmación para avances manuales, solo con escritura en `niveles`
- [X] T041 [HU3] Agregar el selector opcional «Nivel del plan» a `resources/views/encuentros/_form.blade.php` (o a los formularios `create` y `edit`) y `nivel_id` a la validación y el guardado de `app/Http/Controllers/EncuentrosController.php`
- [X] T042 [HU3] Llamar a `AvancesNivelService::recalcularEncuentro` desde `EncuentrosController` en `store`, `update` (con el nivel anterior), `actualizarAsistencia` y antes de `destroy`; mostrar el nivel en el detalle (`encuentros/show.blade.php`) y como columna «Nivel» en `app/Grillas/EncuentrosGrilla.php` y `resources/views/encuentros/_celda.blade.php`
- [X] T043 [HU3] Llamar a `AvancesNivelService::copiarATrasladado` desde `app/Services/TrasladoService.php` al crear el nuevo registro del miembro
- [X] T044 [HU3] Mostrar las acciones nuevas (`nivel_registrado`, `nivel_quitado`, `nivel_automatico`) en el historial de la ficha del miembro con su etiqueta en `app/Models/PersonaCambio.php` y `resources/views/personas/show.blade.php`
- [X] T045 [HU3] Ejecutar `php artisan test --filter="Niveles|Encuentro|Traslado"` y `npm run test:movil -- niveles`; corregir lo que falle

**Punto de control**: el avance se registra a mano y por encuentros, con historial y sin duplicados.

---

## Fase 6: Historia de Usuario 4 - Saber quién está en qué nivel (Prioridad: P2)

**Objetivo**: vista «Quién está en cada nivel» con conteos, listas con grilla estándar y exportación (RF-015 a RF-017, RF-019, RF-023).

**Prueba independiente**: con miembros repartidos entre niveles, abrir la vista y comprobar que los conteos y listas coinciden con las fichas y con el filtro de nivel de Miembros.

### Pruebas

- [X] T046 [P] [HU4] Prueba de los conteos y tarjetas de `niveles.index` (por nivel activo y «Sin iniciar»; coinciden con `enNivel`/`sinNivel` y con las fichas; inactivos excluidos; respeta alcance y sede) en `tests/Feature/Niveles/ConteosNivelesTest.php`
- [X] T047 [P] [HU4] Agregar `NivelesMiembrosGrilla` a la lista de grillas de `tests/Feature/Grillas/ContratoGrillaTest.php` y escribir `tests/Feature/Niveles/MiembrosPorNivelTest.php`: vistas `actual`, `completaron` y `siguiente`, filtros por columna, orden, CSV con nivel actual y fecha del último nivel completado, aislamiento por sede y rol, sin acceso 403 en JSON
- [X] T048 [P] [HU4] Prueba del filtro «Nivel» en el listado de miembros (id y `sin`) y de su coincidencia con las tarjetas de Niveles en `tests/Feature/Niveles/FiltroNivelMiembrosTest.php`

### Implementación

- [X] T049 [HU4] Crear `app/Grillas/NivelesMiembrosGrilla.php` (`id` = `niveles.miembros`, extras `nivel`, `vista`, `inactivos`; columnas nombre, nivel, celular con contacto, «Completó el nivel el», sede, acciones; base `personasVisibles` filtrada por `enNivel`, `sinNivel`, avance del nivel o siguiente pendiente) y la celda `resources/views/niveles/_celda.blade.php`
- [X] T050 [HU4] Agregar a `NivelesController` las acciones `miembros`, `datos` y `exportar` (trait `ServicioGrilla`) y las rutas `niveles.miembros.*` antes de `/niveles/{nivel}`
- [X] T051 [HU4] Crear `resources/views/niveles/miembros.blade.php` (título con el nivel, conmutador de vista «actual / completaron / siguiente», `<x-ui.grilla>` y acciones de contacto) y las tarjetas «Quién está en cada nivel» en `resources/views/niveles/index.blade.php` (nombre, conteo y enlace; tarjeta «Sin iniciar»)
- [X] T052 [HU4] Calcular los conteos con una sola consulta agregada en `NivelesService::resumen(?User $usuario)` (miembros visibles por nivel actual y sin iniciar) y usarla en `niveles.index`
- [X] T053 [HU4] Ejecutar `php artisan test --filter="Niveles|Grilla"` y `npm run test:movil -- niveles listados`; corregir lo que falle

**Punto de control**: se sabe quién está en cada nivel desde Niveles y desde Miembros.

---

## Fase 7: Historia de Usuario 5 - Resumen de niveles en el tablero (Prioridad: P3)

**Objetivo**: tarjeta «Miembros por nivel» en el tablero (RF-018).

**Prueba independiente**: abrir el tablero y comprobar que la tarjeta coincide con la vista de niveles y respeta los permisos.

- [X] T054 [P] [HU5] Prueba del tablero: con lectura en `niveles` aparece la tarjeta con conteos coincidentes y enlaces; sin acceso al módulo no aparece; los totales respetan el alcance del usuario en `tests/Feature/Niveles/TableroNivelesTest.php`
- [X] T055 [HU5] Crear `resources/views/partials/tarjeta-niveles.blade.php` y mostrarla en `resources/views/dashboard.blade.php` (con `@puedeModulo('niveles')`), alimentada por `NivelesService::resumen` desde `AuthController@dashboard`; apilable y táctil en teléfono

---

## Fase 8: Pulido y verificación

- [X] T056 Agregar las pantallas nuevas (`niveles`, `niveles/create`, `niveles/1/edit`, `niveles/miembros?nivel=1`) a `tests/movil/pantallas.mjs`, con datos de prueba (niveles y avances sembrados) en el servidor temporal de `tests/movil/servidor.mjs`
- [X] T057 [P] Documentar en `README.md` la sección «Niveles de formación» (plan común, avance manual y por encuentros, quién está en cada nivel, permisos del módulo y el camino) y agregar el módulo a la lista de la guía de roles si existe
- [X] T058 [P] Revisar el vocabulario (miembro, no persona) en todas las vistas y mensajes nuevos y ejecutar la prueba de vocabulario existente de la spec 010
- [X] T059 Ejecutar `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` completos; todo en verde con la lista de excepciones vacía
- [X] T060 Verificar `quickstart.md` en navegador (ancho de teléfono con una base temporal sembrada): los 12 pasos manuales; borrar archivos temporales (bases SQLite, procesos del servidor)
- [X] T061 [P] Actualizar la memoria del proyecto con la funcionalidad de niveles (modelo `NivelFormacion`, plan común, avance manual y automático, módulo `niveles`) y registrar el resultado final en `specs/014-niveles-miembros/quickstart.md`

---

## Dependencias y orden de ejecución

- **Fase 1 → Fase 2 → Fases 3 a 7 → Fase 8**.
- Fase 2 bloquea todo: T008 → T009; T010 y T011 después de T008; T012 antes de rutas y menú; T013 y T014 después de T010 y T011; T015 después de T008.
- **HU1** (camino) depende de T013; **HU2** (módulo) depende de T010, T012 y T029; **HU3** depende de T014 y de HU2 (necesita niveles); **HU4** depende de T011 y de HU2; **HU5** depende de T052 (resumen).
- Dentro de cada historia: pruebas → servicios y modelos → controladores y rutas → vistas.
- HU1 se puede probar con datos sembrados desde fábricas aunque HU2 y HU3 aún no estén listos; el registro manual (T040) llega con HU3.

## Ejemplos de ejecución en paralelo

- Fase 2: T003 a T007 (pruebas) en paralelo; luego T010, T011 y T012 en paralelo.
- HU1: T016, T017, T018 (pruebas); T019 y T020 (componentes) en paralelo.
- HU2: T025 a T028 (pruebas); T029 y T031 en paralelo.
- HU3: T035 a T038 (pruebas) en paralelo.
- HU4: T046 a T048 (pruebas) en paralelo.

## Estrategia de implementación

1. **MVP**: Fases 1 y 2 más **HU1** (camino y puntos). Valida el diseño visual y el cálculo de nivel actual con datos sembrados antes de construir el resto.
2. **Entrega incremental**: HU2 (administrar el plan), luego HU3 (registrar avances a mano y por encuentros), que completan el flujo diario; después HU4 (quién está en cada nivel) y HU5 (tablero).
3. **Cierre**: pulido, documentación y verificación completa. La lista de excepciones de la auditoría móvil permanece vacía; las fallas se corrigen en la pantalla.
